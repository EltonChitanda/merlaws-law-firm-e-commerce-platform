<?php
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>Admin Login | Med Attorneys</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body{font-family:Inter,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#f8fafc;}
		.container-login{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
		.card{max-width:420px;width:100%;border-radius:1rem;border:1px solid #e2e8f0}
		.card-header{padding:1.5rem 1.5rem 0;border:none;background:transparent;text-align:center}
		.card-body{padding:1.5rem}
	</style>
</head>
<body>
	<div class="container-login">
		<div class="card shadow-sm">
			<div class="card-header">
				<h1 class="h4 mb-1">Admin Portal</h1>
				<p class="text-muted">Secure access for firm administrators</p>
			</div>
			<div class="card-body">
				<div id="alert" class="alert alert-danger d-none"></div>
				<form id="adminLoginForm" method="post" action="api/auth-login.php?admin=1" novalidate>
					<?php echo csrf_field(); ?>
					<div class="mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" class="form-control" id="email" name="email" required>
					</div>
					<div class="mb-3">
						<label for="password" class="form-label">Password</label>
						<input type="password" class="form-control" id="password" name="password" required>
					</div>
					<button type="submit" class="btn btn-primary w-100" id="btn">Sign in</button>
				</form>
			</div>
		</div>
	</div>
	<script src="assets/js/ui-toasts.js"></script>
	<script src="assets/js/auth-validate.js?v=20250929"></script>
</body>
</html> 