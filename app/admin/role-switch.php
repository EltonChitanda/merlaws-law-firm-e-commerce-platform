<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

require_login();
if (get_user_role() !== 'super_admin') {
	header('Location: /app/admin/dashboard.php?error=insufficient_permissions');
	exit;
}

if (!is_post() || !csrf_validate()) {
	header('Location: /app/admin/dashboard.php?error=invalid_request');
	exit;
}

action:
$action = (string)($_POST['action'] ?? '');
if ($action === 'clear') {
	unset($_SESSION['role_override']);
} elseif ($action === 'apply') {
	$role = (string)($_POST['role'] ?? '');
	$allowed = ['office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin'];
	if (in_array($role, $allowed, true)) {
		$_SESSION['role_override'] = $role;
	}
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/app/admin/dashboard.php';
header('Location: ' . $ref);
exit; 