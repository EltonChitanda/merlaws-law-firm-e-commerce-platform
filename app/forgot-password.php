<?php
require __DIR__ . '/config.php';

$pdo = db();
$errors = [];
$messages = [];
$step = 1; // 1 = email, 2 = identity verification, 3 = new password

// Session is already started by config.php

// Handle form submissions
if (is_post()) {
    $action = $_POST['action'] ?? 'email';
    
    if ($action === 'email') {
        // Step 1: Email input
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $errors[] = 'Email is required.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Store user info in session for next step
                $_SESSION['password_reset_email'] = $email;
                $_SESSION['password_reset_user_id'] = (int)$user['id'];
                $step = 2;
            } else {
                // Generic message for security (don't reveal if email exists)
                $messages[] = 'If the email exists, you will receive further instructions.';
            }
        }
    } elseif ($action === 'verify_identity') {
        // Step 2: Identity verification (ID number only)
        if (!isset($_SESSION['password_reset_user_id'])) {
            $errors[] = 'Session expired. Please start over.';
            $step = 1;
            session_unset();
        } else {
            $user_id = $_SESSION['password_reset_user_id'];
            $email = $_SESSION['password_reset_email'];
            $provided_id_number = trim($_POST['id_number'] ?? '');
            
            if (empty($provided_id_number)) {
                $errors[] = 'ID number is required.';
                $step = 2;
            } else {
                // Get user data from database
                $stmt = $pdo->prepare('SELECT id_number FROM users WHERE id = ? AND email = ? AND is_active = 1');
                $stmt->execute([$user_id, $email]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $errors[] = 'User not found. Please start over.';
                    $step = 1;
                    session_unset();
                } else {
                    // Compare ID number (exact match, trimmed)
                    $db_id_number = trim($user['id_number'] ?? '');
                    $provided_id_number_trimmed = trim($provided_id_number);
                    
                    // Validate ID number matches
                    if (!empty($db_id_number) && $provided_id_number_trimmed === $db_id_number) {
                        // ID verified - create password reset record
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
                        
                        // Check if columns exist (for backward compatibility)
                        $columns_check = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'verification_id_number'")->fetch();
                        if ($columns_check) {
                            // New columns exist - use them
                            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at, verification_id_number, verification_status, verified_at, created_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
                            $stmt->execute([$user_id, $token, $expiresAt, $provided_id_number_trimmed, 'verified']);
                        } else {
                            // Old structure - just create basic reset
                            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())');
                            $stmt->execute([$user_id, $token, $expiresAt]);
                        }
                        
                        // Store token in session for password reset step
                        $_SESSION['password_reset_token'] = $token;
                        $_SESSION['password_reset_verified'] = true;
                        
                        // Create notification for user
                        create_user_notification(
                            $user_id,
                            'info',
                            'Identity Verified',
                            'Your identity has been verified. Please set your new password.',
                            '/app/forgot-password.php'
                        );
                        
                        $messages[] = 'Identity verified. Please set your new password.';
                        $step = 3;
                    } else {
                        // Verification failed - generic error message
                        $errors[] = 'The ID number provided does not match our records. Please verify your details and try again.';
                        
                        // Create notification for failed attempt
                        create_user_notification(
                            $user_id,
                            'warning',
                            'Password Reset Attempt Failed',
                            'A password reset attempt failed - identity verification was unsuccessful.',
                            null
                        );
                        
                        // Log failed attempt (for security)
                        if (function_exists('log_audit_event')) {
                            log_audit_event('security', 'password_reset_failed', 'Password reset identity verification failed', [
                                'user_id' => $user_id,
                                'email' => $email,
                                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                            ]);
                        }
                        
                        $step = 2;
                    }
                }
            }
        }
    } elseif ($action === 'reset_password') {
        // Step 3: New password entry
        if (!isset($_SESSION['password_reset_verified']) || !$_SESSION['password_reset_verified']) {
            $errors[] = 'Please complete identity verification first.';
            $step = 1;
            session_unset();
        } else {
            $token = $_SESSION['password_reset_token'] ?? '';
            $password = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['confirm'] ?? '');
            
            if (empty($token)) {
                $errors[] = 'Invalid reset session. Please start over.';
                $step = 1;
                session_unset();
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
                $step = 3;
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
                $step = 3;
            } else {
                // Verify token is still valid and matches session
                $stmt = $pdo->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at FROM password_resets pr WHERE pr.token = ? AND pr.user_id = ?');
                $stmt->execute([$token, $_SESSION['password_reset_user_id'] ?? 0]);
                $reset = $stmt->fetch();
                
                if (!$reset) {
                    $errors[] = 'Invalid reset token. Please start over.';
                    $step = 1;
                    session_unset();
                } elseif ($reset['used_at']) {
                    $errors[] = 'This reset link has already been used.';
                    $step = 1;
                    session_unset();
                } elseif (new DateTime($reset['expires_at']) < new DateTime()) {
                    $errors[] = 'This reset link has expired. Please start over.';
                    $step = 1;
                    session_unset();
                } else {
                    // Update password
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->beginTransaction();
                    try {
                        $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?')->execute([$hash, (int)$reset['user_id']]);
                        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([(int)$reset['id']]);
                        $pdo->commit();
                        
                        // Create success notification
                        create_user_notification(
                            (int)$reset['user_id'],
                            'success',
                            'Password Reset Successful',
                            'Your password has been successfully reset. You can now log in with your new password.',
                            '/app/login.php'
                        );
                        
                        $messages[] = 'Password reset successful! You can now log in with your new password.';
                        
                        // Clear session
                        session_unset();
                        
                        // Redirect to login after 3 seconds
                        header('Refresh: 3; url=/app/login.php');
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        $errors[] = 'Failed to update password. Please try again.';
                        $step = 3;
                    }
                }
            }
        }
    }
}

// Get step from session if not set by form
if (!isset($step) && isset($_SESSION['password_reset_verified']) && $_SESSION['password_reset_verified']) {
    $step = 3;
} elseif (!isset($step) && isset($_SESSION['password_reset_user_id'])) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Forgot Password | MerLaws</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
	<link rel="manifest" href="../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">
	<style>
		.password-reset-container {
			max-width: 600px;
			margin: 40px auto;
			padding: 2rem;
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}
		.step-indicator {
			display: flex;
			justify-content: space-between;
			margin-bottom: 2rem;
			padding-bottom: 1rem;
			border-bottom: 2px solid #e2e8f0;
		}
		.step {
			flex: 1;
			text-align: center;
			padding: 0.5rem;
			color: #94a3b8;
			font-weight: 500;
		}
		.step.active {
			color: #AC132A;
			font-weight: 600;
		}
		.step.completed {
			color: #10b981;
		}
		.step-number {
			display: inline-block;
			width: 30px;
			height: 30px;
			line-height: 30px;
			border-radius: 50%;
			background: #e2e8f0;
			color: #64748b;
			margin-right: 0.5rem;
		}
		.step.active .step-number {
			background: #AC132A;
			color: white;
		}
		.step.completed .step-number {
			background: #10b981;
			color: white;
		}
		.form-group {
			margin-bottom: 1.5rem;
		}
		.form-label {
			font-weight: 600;
			margin-bottom: 0.5rem;
			color: #1e293b;
		}
		.btn-primary {
			background: linear-gradient(135deg, #AC132A, #8a0f22);
			border: none;
			padding: 0.75rem 2rem;
			font-weight: 600;
		}
		.btn-primary:hover {
			background: linear-gradient(135deg, #8a0f22, #AC132A);
		}

		@media (max-width: 768px) {
			.password-reset-container {
				margin: 1.5rem auto;
				padding: 1.5rem;
			}

			h1 {
				font-size: 1.75rem;
			}

			.step-indicator {
				flex-direction: column;
				gap: 0.75rem;
			}

			.step {
				text-align: left;
				padding: 0.75rem;
			}

			.form-control {
				font-size: 16px;
				padding: 12px 16px;
				min-height: 48px;
			}

			.btn {
				width: 100%;
				min-height: 48px;
				font-size: 16px;
				padding: 12px 20px;
			}

			.password-toggle-btn {
				min-width: 44px;
				min-height: 44px;
			}
		}

		@media (max-width: 480px) {
			.password-reset-container {
				margin: 1rem auto;
				padding: 1.25rem;
			}

			h1 {
				font-size: 1.5rem;
			}

			.step {
				font-size: 0.9rem;
			}

			.step-number {
				width: 25px;
				height: 25px;
				line-height: 25px;
				font-size: 0.85rem;
			}
		}
	</style>
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../include/header.php';
if (file_exists($headerPath)) {
	echo file_get_contents($headerPath);
}
?>
<div class="container">
	<div class="password-reset-container">
		<h1 style="margin-top: 0; margin-bottom: 1.5rem; color: #1e293b;">Forgot Password</h1>
		
		<!-- Step Indicator -->
		<div class="step-indicator">
			<div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
				<span class="step-number">1</span>
				<span>Email</span>
			</div>
			<div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
				<span class="step-number">2</span>
				<span>Verify Identity</span>
			</div>
			<div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
				<span class="step-number">3</span>
				<span>New Password</span>
			</div>
		</div>
		
		<?php if ($errors): ?>
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-circle me-2"></i>
				<?php echo e(implode('<br>', $errors)); ?>
			</div>
		<?php endif; ?>
		
		<?php if ($messages): ?>
			<div class="alert alert-success">
				<i class="fas fa-check-circle me-2"></i>
				<?php echo e(implode('<br>', $messages)); ?>
			</div>
		<?php endif; ?>
		
		<?php if ($step === 1): ?>
			<!-- Step 1: Email Input -->
			<p style="color: #64748b; margin-bottom: 1.5rem;">Enter your account email address to begin the password reset process.</p>
			<form method="post">
				<input type="hidden" name="action" value="email">
				<div class="form-group">
					<label class="form-label">Email Address</label>
					<input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
				</div>
				<button type="submit" class="btn btn-primary w-100">
					<i class="fas fa-arrow-right me-2"></i>Continue
				</button>
			</form>
		<?php elseif ($step === 2): ?>
			<!-- Step 2: Identity Verification (ID Number Only) -->
			<p style="color: #64748b; margin-bottom: 1.5rem;">Please verify your identity by providing your ID number as it appears on your account.</p>
			<form method="post">
				<input type="hidden" name="action" value="verify_identity">
				<div class="form-group">
					<label class="form-label">ID Number</label>
					<input type="text" name="id_number" class="form-control" placeholder="Enter your ID number" required autofocus>
					<small class="form-text text-muted">Enter the ID number associated with your account.</small>
				</div>
				<div class="d-flex gap-2">
					<a href="forgot-password.php" class="btn btn-outline-secondary" style="flex: 1;">Start Over</a>
					<button type="submit" class="btn btn-primary" style="flex: 1;">
						<i class="fas fa-check me-2"></i>Verify Identity
					</button>
				</div>
			</form>
		<?php elseif ($step === 3): ?>
			<!-- Step 3: New Password -->
			<p style="color: #64748b; margin-bottom: 1.5rem;">Your identity has been verified. Please enter your new password below.</p>
			<form method="post" id="resetPasswordForm">
				<input type="hidden" name="action" value="reset_password">
				<div class="form-group">
					<label class="form-label">New Password</label>
					<div class="password-toggle" style="position: relative;">
						<input type="password" name="password" id="password" class="form-control" placeholder="Enter your new password" minlength="8" required autofocus>
						<button type="button" class="password-toggle-btn" onclick="togglePassword('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
							<span id="password-toggle-text">Show</span>
						</button>
					</div>
					<div class="password-strength" style="margin-top: 0.5rem;">
						<div class="password-strength-meter" style="height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; margin-bottom: 0.25rem;">
							<div class="password-strength-meter-fill" id="password-strength-meter" style="height: 100%; transition: all 0.3s ease;"></div>
						</div>
						<div class="password-strength-text" id="password-strength-text" style="font-size: 0.875rem;">Enter a password</div>
					</div>
				</div>
				<div class="form-group">
					<label class="form-label">Confirm Password</label>
					<div class="password-toggle" style="position: relative;">
						<input type="password" name="confirm" id="confirm_password" class="form-control" placeholder="Re-enter your password" minlength="8" required>
						<button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
							<span id="confirm_password-toggle-text">Show</span>
						</button>
					</div>
				</div>
				<button type="submit" class="btn btn-primary w-100">
					<i class="fas fa-key me-2"></i>Reset Password
				</button>
			</form>
		<?php endif; ?>
	</div>
</div>
<?php 
$footerPath = __DIR__ . '/../include/footer.html';
if (file_exists($footerPath)) {
	echo file_get_contents($footerPath);
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
	// Password toggle functionality
	function togglePassword(fieldId) {
		const field = document.getElementById(fieldId);
		const toggleText = document.getElementById(fieldId + '-toggle-text');

		if (field.type === 'password') {
			field.type = 'text';
			toggleText.textContent = 'Hide';
		} else {
			field.type = 'password';
			toggleText.textContent = 'Show';
		}
	}

	// Password strength checker
	function checkPasswordStrength(password) {
		let strength = 0;
		let feedback = [];

		if (password.length >= 8) strength += 1;
		else feedback.push('at least 8 characters');

		if (/[a-z]/.test(password)) strength += 1;
		else feedback.push('lowercase letter');

		if (/[A-Z]/.test(password)) strength += 1;
		else feedback.push('uppercase letter');

		if (/[0-9]/.test(password)) strength += 1;
		else feedback.push('number');

		if (/[^A-Za-z0-9]/.test(password)) strength += 1;
		else feedback.push('special character');

		const meter = document.getElementById('password-strength-meter');
		const text = document.getElementById('password-strength-text');

		if (!meter || !text) return;

		meter.className = 'password-strength-meter-fill';
		text.className = 'password-strength-text';

		if (password.length === 0) {
			text.textContent = 'Enter a password';
			meter.style.width = '0%';
			return;
		}

		if (strength < 2) {
			meter.classList.add('weak');
			text.classList.add('weak');
			meter.style.width = '25%';
			meter.style.backgroundColor = '#dc3545';
			text.style.color = '#dc3545';
			text.textContent = 'Weak - needs ' + feedback.join(', ');
		} else if (strength < 3) {
			meter.classList.add('fair');
			text.classList.add('fair');
			meter.style.width = '50%';
			meter.style.backgroundColor = '#ffc107';
			text.style.color = '#ffc107';
			text.textContent = 'Fair - needs ' + feedback.join(', ');
		} else if (strength < 4) {
			meter.classList.add('good');
			text.classList.add('good');
			meter.style.width = '75%';
			meter.style.backgroundColor = '#17a2b8';
			text.style.color = '#17a2b8';
			text.textContent = 'Good - needs ' + feedback.join(', ');
		} else {
			meter.classList.add('strong');
			text.classList.add('strong');
			meter.style.width = '100%';
			meter.style.backgroundColor = '#28a745';
			text.style.color = '#28a745';
			text.textContent = 'Strong password';
		}
	}

	// Event listeners for password strength
	const passwordField = document.getElementById('password');
	if (passwordField) {
		passwordField.addEventListener('input', function() {
			checkPasswordStrength(this.value);
		});
	}

	// Form validation
	const resetForm = document.getElementById('resetPasswordForm');
	if (resetForm) {
		resetForm.addEventListener('submit', function(e) {
			const password = document.getElementById('password')?.value || '';
			const confirmPassword = document.getElementById('confirm_password')?.value || '';

			if (password !== confirmPassword) {
				e.preventDefault();
				alert('Passwords do not match');
				return false;
			}

			// Check password strength
			let strength = 0;
			if (password.length >= 8) strength += 1;
			if (/[a-z]/.test(password)) strength += 1;
			if (/[A-Z]/.test(password)) strength += 1;
			if (/[0-9]/.test(password)) strength += 1;
			if (/[^A-Za-z0-9]/.test(password)) strength += 1;

			if (strength < 3) {
				e.preventDefault();
				alert('Password is too weak. Please use a stronger password with at least 8 characters, including uppercase, lowercase, and numbers.');
				return false;
			}
		});
	}
</script>
</body>
</html>
