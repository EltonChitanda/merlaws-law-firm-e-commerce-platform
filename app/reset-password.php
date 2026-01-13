<?php
require __DIR__ . '/config.php';

$pdo = db();
$errors = [];
$messages = [];
$token = $_GET['token'] ?? '';

if ($token === '') {
	$errors[] = 'Missing reset token.';
} else {
	$stmt = $pdo->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?');
	$stmt->execute([$token]);
	$reset = $stmt->fetch();
	if (!$reset) {
		$errors[] = 'Invalid reset token.';
	} else if ($reset['used_at']) {
		$errors[] = 'This reset link has already been used.';
	} else if (new DateTime($reset['expires_at']) < new DateTime()) {
		$errors[] = 'This reset link has expired.';
	}
}

if (is_post() && empty($errors)) {
	$password = (string)($_POST['password'] ?? '');
	$confirm = (string)($_POST['confirm'] ?? '');
	if ($password === '' || strlen($password) < 8) {
		$errors[] = 'Password must be at least 8 characters.';
	} else if ($password !== $confirm) {
		$errors[] = 'Passwords do not match.';
	} else {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$pdo->beginTransaction();
		try {
			$pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?')->execute([$hash, (int)$reset['user_id']]);
			$pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([(int)$reset['id']]);
			$pdo->commit();
			$messages[] = 'Your password has been reset. You can now log in.';
		} catch (Throwable $e) {
			$pdo->rollBack();
			$errors[] = 'Failed to update password.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reset Password | MerLaws</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
	<link rel="manifest" href="../favicon/site.webmanifest">
	<link rel="stylesheet" href="../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../include/header.php';
if (file_exists($headerPath)) {
	echo file_get_contents($headerPath);
}
?>
<div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
	<h1 style="margin-top: 0;">Reset Password</h1>
	<?php if ($errors) { echo '<div class="alert alert-danger">' . e(implode(' ', $errors)) . '</div>'; } ?>
	<?php if ($messages) { echo '<div class="alert alert-success">' . e(implode(' ', $messages)) . '</div>'; } ?>
	<?php if (empty($errors) && empty($messages)): ?>
	<p>Resetting password for: <strong><?php echo e($reset['email']); ?></strong></p>
	<form method="post" class="row" style="gap: 10px;">
		<div class="col-12 col-md-6"><input type="password" name="password" class="form-control" placeholder="New password (min 8)" style="font-size: 16px; padding: 12px 16px; min-height: 48px;"></div>
		<div class="col-12 col-md-6"><input type="password" name="confirm" class="form-control" placeholder="Confirm password" style="font-size: 16px; padding: 12px 16px; min-height: 48px;"></div>
		<div class="col-12"><button class="btn btn-primary" style="width: 100%; min-height: 48px; font-size: 16px;">Update Password</button></div>
	</form>
	<?php endif; ?>
	<style>
		@media (max-width: 768px) {
			.container {
				margin: 1.5rem auto !important;
				padding: 1.5rem !important;
			}

			h1 {
				font-size: 1.75rem !important;
			}

			.form-control {
				font-size: 16px !important;
				padding: 12px 16px !important;
				min-height: 48px !important;
			}

			.btn {
				width: 100% !important;
				min-height: 48px !important;
				font-size: 16px !important;
			}
		}

		@media (max-width: 480px) {
			.container {
				margin: 1rem auto !important;
				padding: 1.25rem !important;
			}

			h1 {
				font-size: 1.5rem !important;
			}
		}
	</style>
</div>
<?php 
$footerPath = __DIR__ . '/../include/footer.html';
if (file_exists($footerPath)) {
	echo file_get_contents($footerPath);
}
?>
</body>
</html> 