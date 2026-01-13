<?php
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/models/AuthResult.php';
require __DIR__ . '/services/AuthService.php';

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$registration_success = false;
$phone = trim($_POST['phone'] ?? '');

if (is_post()) {
    if (!csrf_validate()) { 
        $errors[] = 'Invalid security token. Please refresh and try again.'; 
    }

    if (!$errors) {
        try {
            $pdo = db();
            $security = new SecurityService($pdo);
            $session = new SessionService($pdo);
            $auth = new AuthService($pdo, $session, $security);

            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => (string)($_POST['password'] ?? ''),
                'confirm_password' => (string)($_POST['confirm_password'] ?? ''),
                'terms_accepted' => isset($_POST['terms']) ? 1 : 0,
                'role' => 'client',
            ];

            $result = $auth->register($userData);
            if ($result->isSuccess()) {
                // Auto-login new client for smoother UX
                $login = $auth->authenticate($email, (string)($_POST['password'] ?? ''));
                if ($login->isSuccess()) {
                    $data = $login->getData();
                    $user = $data['user'] ?? [];
                    
                    // FIXED: Use namespaced session keys
                    $_SESSION['client']['user_id'] = (int)($user['id'] ?? 0);
                    $_SESSION['client']['role'] = (string)($user['role'] ?? 'client');
                    $_SESSION['client']['name'] = (string)($user['name'] ?? '');
                    $_SESSION['client']['email'] = (string)($user['email'] ?? '');
                    $_SESSION['client']['login_time'] = time();
                    
                    // Set flag for success message instead of immediate redirect
                    $registration_success = true;
                    
                    // redirect('dashboard.php'); // We will redirect with JavaScript instead
                }
                redirect('login.php?message=' . urlencode('Account created. Please sign in.'));
            } else {
                $errors[] = $result->getMessage();
            }
        } catch (Throwable $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'Registration failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="/">
    <title>Create Account | Med Attorneys</title>
    
    <!-- Favicon with corrected paths -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/site.webmanifest">
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/default.css">
    
    <!-- Load Google script -->
    <script src="../script/google.js"></script>
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-white: #ffffff;
            --merlaws-gray-50: #f8f9fa;
            --merlaws-gray-100: #e9ecef;
            --merlaws-gray-200: #dee2e6;
            --merlaws-gray-300: #ced4da;
            --merlaws-gray-500: #6c757d;
            --merlaws-gray-700: #343a40;
            --merlaws-gray-800: #212529;
            --merlaws-danger: #dc3545;
            --merlaws-success: #28a745;
            --merlaws-warning: #ffc107;
            --merlaws-info: #17a2b8;
        }

        body {
            background-color: var(--merlaws-gray-50);
            font-family: 'Montserrat', sans-serif;
        }

        .auth-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .auth-card {
            background: var(--merlaws-white);
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
            border: 1px solid var(--merlaws-gray-200);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: var(--merlaws-white);
            padding: 2rem;
            text-align: center;
        }

        .auth-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .auth-header p {
            margin: 0;
            opacity: 0.9;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--merlaws-gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid var(--merlaws-gray-300);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 0.2rem rgba(172, 19, 42, 0.25);
        }

        .form-control.is-invalid {
            border-color: var(--merlaws-danger);
        }

        .btn-merlaws {
            background-color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
            color: var(--merlaws-white);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
        }

        .btn-merlaws:hover {
            background-color: var(--merlaws-primary-dark);
            border-color: var(--merlaws-primary-dark);
            color: var(--merlaws-white);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--merlaws-gray-500);
            cursor: pointer;
            padding: 0.25rem;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .password-strength-meter {
            height: 4px;
            background-color: var(--merlaws-gray-200);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }

        .password-strength-meter-fill {
            height: 100%;
            width: 0%;
            transition: all 0.15s ease-in-out;
        }

        .password-strength-meter-fill.weak { background-color: var(--merlaws-danger); width: 25%; }
        .password-strength-meter-fill.fair { background-color: var(--merlaws-warning); width: 50%; }
        .password-strength-meter-fill.good { background-color: var(--merlaws-info); width: 75%; }
        .password-strength-meter-fill.strong { background-color: var(--merlaws-success); width: 100%; }

        .password-strength-text {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .password-strength-text.weak { color: var(--merlaws-danger); }
        .password-strength-text.fair { color: var(--merlaws-warning); }
        .password-strength-text.good { color: var(--merlaws-info); }
        .password-strength-text.strong { color: var(--merlaws-success); }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 0.25rem;
            flex-shrink: 0;
        }

        .terms-checkbox label {
            font-size: 0.875rem;
            line-height: 1.4;
            margin-bottom: 0;
            text-transform: none;
            letter-spacing: normal;
        }

        .terms-checkbox a {
            color: var(--merlaws-primary);
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        .auth-footer {
            padding: 1.5rem 2rem;
            background-color: var(--merlaws-gray-50);
            border-top: 1px solid var(--merlaws-gray-200);
            text-align: center;
        }

        .auth-footer a {
            color: var(--merlaws-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Include header using PHP instead of JavaScript -->
    <?php 
    $headerPath = __DIR__ . '/../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <section class="spring">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join Med Attorneys to access your legal services</p>
                </div>

                <div class="auth-body">
                    <?php if ($errors) { ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err) { echo '<li>' . e($err) . '</li>'; } ?>
                        </ul>
                    </div>
                    <?php } ?>

                    <form method="post" action="" id="registerForm">
                        <?php if ($registration_success): ?>
                            <div class="alert alert-success text-center">
                                <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Registration Successful!</h4>
                                <p>Welcome to Med Attorneys! Your account has been created.</p>
                                <p class="mb-0">You will be automatically redirected to your dashboard in a moment.</p>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    setTimeout(function() {
                                        window.location.href = 'dashboard.php';
                                    }, 3000); // Redirect after 3 seconds
                                });
                            </script>
                        <?php else: ?>
                        <!-- The form will only show if registration is not yet successful -->

                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="+27 XX XXX XXXX">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                                    <span id="password-toggle-text">Show</span>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-meter">
                                    <div class="password-strength-meter-fill" id="password-strength-meter"></div>
                                </div>
                                <div class="password-strength-text" id="password-strength-text">Enter a password</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">
                                    <span id="confirm_password-toggle-text">Show</span>
                                </button>
                            </div>
                        </div>

                        <div class="terms-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                I agree to the <a href="../terms-of-service.html" target="_blank">Terms of Service</a> 
                                and <a href="../privacy-policy.html" target="_blank">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-merlaws w-100">Create Account</button>

                        <?php endif; ?>
                    </form>
                </div>

                <div class="auth-footer">
                    <p class="mb-0">Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer using PHP instead of JavaScript -->
    <?php 
    $footerPath = __DIR__ . '/../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <!-- External JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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

            meter.className = 'password-strength-meter-fill';
            text.className = 'password-strength-text';

            if (password.length === 0) {
                text.textContent = 'Enter a password';
                return;
            }

            if (strength < 2) {
                meter.classList.add('weak');
                text.classList.add('weak');
                text.textContent = 'Weak - needs ' + feedback.join(', ');
            } else if (strength < 3) {
                meter.classList.add('fair');
                text.classList.add('fair');
                text.textContent = 'Fair - needs ' + feedback.join(', ');
            } else if (strength < 4) {
                meter.classList.add('good');
                text.classList.add('good');
                text.textContent = 'Good - needs ' + feedback.join(', ');
            } else {
                meter.classList.add('strong');
                text.classList.add('strong');
                text.textContent = 'Strong password';
            }
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }

            if (!terms) {
                e.preventDefault();
                alert('You must accept the terms of service');
                return;
            }
        });
    </script>
    <script src="assets/js/mobile-responsive.js"></script>
</body>
</html>