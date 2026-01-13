<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('user:update');

$pdo = db();

$errors = [];
$success = '';

$user_id = get_user_id();
$user_role = get_user_role();

// Handle POST actions
if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token. Please refresh and try again.';
	} else {
		$action = (string)($_POST['action'] ?? '');
		try {
			switch ($action) {
				case 'create_user': {
					require_permission('user:create');
					$name = trim((string)($_POST['name'] ?? ''));
					$email = trim((string)($_POST['email'] ?? ''));
					$role = trim((string)($_POST['role'] ?? 'client'));
					$is_active = isset($_POST['is_active']) ? 1 : 0;
					$password = (string)($_POST['password'] ?? '');
					if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
						$errors[] = 'Name, valid email and password are required.';
						break;
					}
					$password_hash = password_hash($password, PASSWORD_DEFAULT);
					$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
					$stmt->execute([$name, $email, $password_hash, $role, $is_active]);
					$success = 'User created successfully.';
					break;
				}
				case 'update_user': {
					require_permission('user:update');
					$user_id = (int)($_POST['user_id'] ?? 0);
					$name = trim((string)($_POST['name'] ?? ''));
					$email = trim((string)($_POST['email'] ?? ''));
					$role = trim((string)($_POST['role'] ?? 'client'));
					$is_active = isset($_POST['is_active']) ? 1 : 0;
					if ($user_id <= 0 || $name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$errors[] = 'User ID, name and valid email are required.';
						break;
					}
					$stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?');
					$stmt->execute([$name, $email, $role, $is_active, $user_id]);
					$success = 'User updated successfully.';
					break;
				}
				case 'toggle_status': {
					require_permission('user:update');
					$user_id = (int)($_POST['user_id'] ?? 0);
					$new_status = (int)($_POST['new_status'] ?? 0);
					if ($user_id <= 0) { $errors[] = 'Invalid user.'; break; }
					$stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
					$stmt->execute([$new_status, $user_id]);
					$success = $new_status ? 'User activated successfully.' : 'User deactivated successfully.';
					break;
				}
				case 'change_role': {
					require_permission('role:assign');
					$user_id = (int)($_POST['user_id'] ?? 0);
					$new_role = trim((string)($_POST['role'] ?? 'client'));
					if ($user_id <= 0) { $errors[] = 'Invalid user.'; break; }
					$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
					$stmt->execute([$new_role, $user_id]);
					$success = 'Role updated successfully.';
					break;
				}
				case 'reset_password': {
					require_permission('user:update');
					$user_id = (int)($_POST['user_id'] ?? 0);
					$new_password = (string)($_POST['new_password'] ?? '');
					if ($user_id <= 0 || $new_password === '') { $errors[] = 'Invalid user or password.'; break; }
					$password_hash = password_hash($new_password, PASSWORD_DEFAULT);
					$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
					$stmt->execute([$password_hash, $user_id]);
					$success = 'Password reset successfully.';
					break;
				}
				case 'delete_user': {
					require_permission('user:delete');
					$user_id = (int)($_POST['user_id'] ?? 0);
					if ($user_id <= 0 || $user_id == get_user_id()) { $errors[] = 'Invalid user or cannot delete yourself.'; break; }
					$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
					$stmt->execute([$user_id]);
					$success = 'User deleted successfully.';
					break;
				}
				default:
					$errors[] = 'Unknown action.';
			}
		} catch (Throwable $e) {
			$errors[] = 'Operation failed. Please try again.';
		}
	}
}

// Filters
$filter_role = trim((string)($_GET['role'] ?? ''));
$filter_status = trim((string)($_GET['status'] ?? ''));
$filter_start = trim((string)($_GET['start'] ?? ''));
$filter_end = trim((string)($_GET['end'] ?? ''));
$filter_q = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT u.*, COUNT(DISTINCT c.id) AS case_count, COUNT(DISTINCT cd.id) AS document_count
	FROM users u
	LEFT JOIN cases c ON u.id = c.user_id
	LEFT JOIN case_documents cd ON c.id = cd.case_id
	WHERE 1=1";
$params = [];

// Role-based user visibility
if (in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
    // Super admin, partners, and office admin see all users
    // No additional filter
} elseif ($user_role === 'case_manager') {
    // Case managers see team members and clients
    $sql .= " AND (u.role IN ('attorney', 'paralegal', 'client') OR u.id = ?)";
    $params[] = $user_id;
} elseif (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals see only collaborating users and their clients
    $sql .= " AND (u.role = 'client' OR u.id = ? OR EXISTS (
        SELECT 1 FROM cases c WHERE c.user_id = u.id AND c.assigned_to = ?
    ))";
    $params[] = $user_id;
    $params[] = $user_id;
} else {
    // Other roles see only themselves
    $sql .= " AND u.id = ?";
    $params[] = $user_id;
}

if ($filter_role !== '') { $sql .= " AND u.role = ?"; $params[] = $filter_role; }
if ($filter_status !== '') { $sql .= " AND u.is_active = ?"; $params[] = $filter_status === 'active' ? 1 : 0; }
if ($filter_start !== '' && $filter_end !== '') { $sql .= " AND DATE(u.created_at) BETWEEN ? AND ?"; $params[] = $filter_start; $params[] = $filter_end; }
if ($filter_q !== '') { $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)"; $like = "%$filter_q%"; $params[] = $like; $params[] = $like; }

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Calculate statistics
$total_users = count($users);
$active_users = count(array_filter($users, fn($u) => $u['is_active']));
$total_cases = array_sum(array_column($users, 'case_count'));
$total_documents = array_sum(array_column($users, 'document_count'));

$roles = ['client','super_admin','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin'];

$role_icons = [
	'super_admin' => 'fa-crown',
	'office_admin' => 'fa-building',
	'partner' => 'fa-handshake',
	'attorney' => 'fa-gavel',
	'paralegal' => 'fa-file-alt',
	'case_manager' => 'fa-tasks',
	'billing' => 'fa-dollar-sign',
	'compliance' => 'fa-shield-alt',
	'client' => 'fa-user',
	'manager' => 'fa-user-tie',
	'admin' => 'fa-cog'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>User Management | Med Attorneys Admin</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../../assets/css/responsive.css">
	<style>
		:root {
			--merlaws-primary: #1a1a1a;
			--merlaws-gold: #c9a96e;
			--success: #10b981;
			--warning: #f59e0b;
			--danger: #ef4444;
			--info: #3b82f6;
		}
		
		body {
			background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
			font-family: 'Inter', system-ui, -apple-system, sans-serif;
		}
		
		.page-header {
			background: linear-gradient(135deg, var(--merlaws-primary) 0%, #0d1117 100%);
			color: white;
			padding: 2.5rem 0;
			margin-bottom: 2rem;
			border-radius: 0 0 20px 20px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.15);
			position: relative;
			overflow: hidden;
		}
		
		.page-header::before {
			content: '';
			position: absolute;
			top: 0;
			right: -100px;
			width: 200px;
			height: 100%;
			background: linear-gradient(45deg, var(--merlaws-gold), transparent);
			opacity: 0.1;
			transform: skewX(-15deg);
		}
		
		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 1.5rem;
			margin-bottom: 2rem;
		}
		
		.stat-card {
			background: white;
			border-radius: 16px;
			padding: 1.75rem;
			box-shadow: 0 4px 20px rgba(0,0,0,0.08);
			transition: all 0.3s ease;
			border-top: 4px solid;
			position: relative;
			overflow: hidden;
		}
		
		.stat-card::before {
			content: '';
			position: absolute;
			top: 0;
			right: 0;
			width: 100px;
			height: 100px;
			background: var(--merlaws-gold);
			opacity: 0.05;
			border-radius: 50%;
			transform: translate(30%, -30%);
		}
		
		.stat-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 8px 30px rgba(0,0,0,0.12);
		}
		
		.stat-card.primary { border-color: var(--info); }
		.stat-card.success { border-color: var(--success); }
		.stat-card.warning { border-color: var(--merlaws-gold); }
		.stat-card.gold { border-color: var(--merlaws-gold); }
		
		.stat-icon {
			width: 50px;
			height: 50px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.5rem;
			color: white;
			margin-bottom: 1rem;
		}
		
		.stat-number {
			font-size: 2.5rem;
			font-weight: 900;
			margin: 0.5rem 0;
			line-height: 1;
		}
		
		.stat-label {
			color: #6b7280;
			font-size: 0.9rem;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		
		.filter-card {
			background: white;
			border-radius: 16px;
			padding: 1.75rem;
			margin-bottom: 2rem;
			box-shadow: 0 4px 20px rgba(0,0,0,0.08);
		}
		
		.filter-title {
			font-size: 1.1rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}
		
		.filter-title i {
			color: var(--merlaws-gold);
		}
		
		.form-label {
			font-weight: 600;
			color: #374151;
			margin-bottom: 0.5rem;
			font-size: 0.85rem;
		}
		
		.form-control, .form-select {
			border: 2px solid #e5e7eb;
			border-radius: 10px;
			padding: 0.65rem 1rem;
			transition: all 0.3s ease;
		}
		
		.form-control:focus, .form-select:focus {
			border-color: var(--merlaws-gold);
			box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
		}
		
		.btn {
			border-radius: 10px;
			padding: 0.65rem 1.5rem;
			font-weight: 600;
			transition: all 0.3s ease;
		}
		
		.btn-primary {
			background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
			border: none;
			color: white;
		}
		
		.btn-primary:hover {
			background: linear-gradient(135deg, #d4af37, var(--merlaws-gold));
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(201, 169, 110, 0.3);
		}
		
		.table-container {
			background: white;
			border-radius: 16px;
			overflow: hidden;
			box-shadow: 0 4px 20px rgba(0,0,0,0.08);
		}
		
		.table {
			margin-bottom: 0;
		}
		
		.table thead th {
			background: linear-gradient(135deg, #f8fafc, #f1f5f9);
			border: none;
			padding: 1rem;
			font-weight: 700;
			color: var(--merlaws-primary);
			text-transform: uppercase;
			font-size: 0.75rem;
			letter-spacing: 0.5px;
		}
		
		.table tbody tr {
			border-bottom: 1px solid #f1f5f9;
			transition: all 0.3s ease;
		}
		
		.table tbody tr:hover {
			background: #f8fafc;
		}
		
		.table tbody td {
			padding: 1rem;
			vertical-align: middle;
		}
		
		.user-avatar {
			width: 45px;
			height: 45px;
			border-radius: 12px;
			background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-weight: 700;
			font-size: 1.1rem;
		}
		
		.user-info {
			display: flex;
			align-items: center;
			gap: 1rem;
		}
		
		.user-details strong {
			display: block;
			color: var(--merlaws-primary);
			margin-bottom: 0.15rem;
		}
		
		.user-email {
			color: #6b7280;
			font-size: 0.85rem;
		}
		
		.role-badge {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.4rem 0.9rem;
			border-radius: 8px;
			font-weight: 700;
			font-size: 0.75rem;
			letter-spacing: 0.3px;
			background: #f1f5f9;
			color: #475569;
		}
		
		.role-badge i {
			font-size: 0.9rem;
		}
		
		.badge {
			padding: 0.4rem 0.9rem;
			border-radius: 8px;
			font-weight: 700;
			font-size: 0.75rem;
			letter-spacing: 0.3px;
		}
		
		.badge.bg-success {
			background: linear-gradient(135deg, var(--success), #059669) !important;
		}
		
		.badge.bg-secondary {
			background: linear-gradient(135deg, #6b7280, #4b5563) !important;
		}
		
		.stat-pill {
			display: inline-block;
			background: #f1f5f9;
			color: #475569;
			padding: 0.25rem 0.75rem;
			border-radius: 12px;
			font-size: 0.8rem;
			font-weight: 600;
			margin-right: 0.5rem;
		}
		
		.btn-group-sm .btn {
			padding: 0.4rem 0.8rem;
			border-radius: 8px;
		}
		
		.btn-outline-primary {
			border: 2px solid var(--merlaws-gold);
			color: var(--merlaws-gold);
		}
		
		.btn-outline-primary:hover {
			background: var(--merlaws-gold);
			color: white;
		}
		
		.btn-outline-success {
			border: 2px solid var(--success);
			color: var(--success);
		}
		
		.btn-outline-success:hover {
			background: var(--success);
			color: white;
		}
		
		.btn-outline-warning {
			border: 2px solid var(--warning);
			color: var(--warning);
		}
		
		.btn-outline-warning:hover {
			background: var(--warning);
			color: white;
		}
		
		.btn-outline-danger {
			border: 2px solid var(--danger);
			color: var(--danger);
		}
		
		.btn-outline-danger:hover {
			background: var(--danger);
			color: white;
		}
		
		.modal-content {
			border-radius: 16px;
			border: none;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3);
		}
		
		.modal-header {
			border-bottom: 2px solid #f1f5f9;
			padding: 1.5rem;
			background: linear-gradient(135deg, #f8fafc, #ffffff);
		}
		
		.modal-title {
			font-weight: 700;
			color: var(--merlaws-primary);
		}
		
		.modal-body {
			padding: 1.5rem;
		}
		
		.modal-footer {
			border-top: 2px solid #f1f5f9;
			padding: 1.5rem;
			background: #f8fafc;
		}
		
		.alert {
			border: none;
			border-radius: 12px;
			padding: 1rem 1.25rem;
			margin-bottom: 1.5rem;
			border-left: 4px solid;
		}
		
		.alert-success {
			background: #d1fae5;
			color: #065f46;
			border-color: var(--success);
		}
		
		.alert-danger {
			background: #fee2e2;
			color: #991b1b;
			border-color: var(--danger);
		}
		
		.form-check-input {
			width: 1.25rem;
			height: 1.25rem;
			border: 2px solid #d1d5db;
			cursor: pointer;
		}
		
		.form-check-input:checked {
			background-color: var(--merlaws-gold);
			border-color: var(--merlaws-gold);
		}
		
		.empty-state {
			text-align: center;
			padding: 3rem;
			color: #9ca3af;
		}
		
		.empty-state i {
			font-size: 3rem;
			margin-bottom: 1rem;
			opacity: 0.3;
		}
		
		@media (max-width: 768px) {
			.page-header {
				padding: 1.5rem 0;
				border-radius: 0 0 16px 16px;
			}
			
			.page-header h1 {
				font-size: 1.75rem;
			}
			
			.stats-grid {
				grid-template-columns: repeat(2, 1fr);
				gap: 1rem;
			}
			
			.stat-card {
				padding: 1.25rem;
			}
			
			.stat-number {
				font-size: 2rem;
			}
			
			.filter-card {
				padding: 1.25rem;
			}
			
			.user-info {
				flex-direction: column;
				align-items: flex-start;
				gap: 0.5rem;
			}
			
			.table-container {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}
			
			.table {
				font-size: 0.9rem;
			}
			
			.table thead th,
			.table tbody td {
				padding: 0.75rem 0.5rem;
			}
			
			.form-control,
			.form-select {
				font-size: 16px;
				padding: 12px 16px;
				min-height: 48px;
			}
			
			.btn {
				min-height: 48px;
				font-size: 16px;
				padding: 12px 20px;
			}
			
			.btn-lg {
				width: 100%;
				justify-content: center;
			}
			
			.modal-dialog {
				margin: 0.5rem;
			}
			
			.modal-content {
				border-radius: 12px;
			}
		}
		
		@media (max-width: 480px) {
			.page-header {
				padding: 1.25rem 0;
			}
			
			.page-header h1 {
				font-size: 1.5rem;
			}
			
			.stats-grid {
				grid-template-columns: 1fr;
			}
			
			.stat-card {
				padding: 1rem;
			}
			
			.stat-number {
				font-size: 1.75rem;
			}
			
			.filter-card {
				padding: 1rem;
			}
			
			.table {
				font-size: 0.85rem;
			}
			
			.table thead {
				display: none;
			}
			
			.table tbody {
				display: block;
			}
			
			.table tbody tr {
				display: block;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				margin-bottom: 1rem;
				padding: 1rem;
				background: white;
			}
			
			.table tbody td {
				display: block;
				padding: 0.5rem 0;
				border-bottom: none;
				text-align: left;
			}
			
			.table tbody td:before {
				content: attr(data-label);
				font-weight: 600;
				display: block;
				margin-bottom: 0.25rem;
				color: #64748b;
				font-size: 0.8rem;
			}
			
			.table tbody td:last-child {
				margin-top: 0.75rem;
				padding-top: 0.75rem;
				border-top: 1px solid #e2e8f0;
			}
		}
	</style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="page-header">
	<div class="container">
		<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
			<div>
				<h1 class="mb-2"><i class="fas fa-users me-3"></i>User Management</h1>
				<p class="mb-0 opacity-75">Manage system users, roles, and permissions</p>
			</div>
		<?php if (has_permission('user:create') || in_array($user_role, ['super_admin', 'it_admin'])): ?>
		<button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createUserModal">
			<i class="fa fa-user-plus me-2"></i>Add New Staff Member
		</button>
		<?php endif; ?>
		</div>
	</div>
</div>

<div class="container mb-5">
	<?php if ($errors): ?>
		<div class="alert alert-danger">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo e(implode(' ', $errors)); ?>
		</div>
	<?php elseif ($success): ?>
		<div class="alert alert-success">
			<i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
		</div>
	<?php endif; ?>

	<div class="stats-grid">
		<div class="stat-card primary">
			<div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #2563eb);">
				<i class="fas fa-users"></i>
			</div>
			<div class="stat-number"><?php echo $total_users; ?></div>
			<div class="stat-label">Total Users</div>
		</div>
		<div class="stat-card success">
			<div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
				<i class="fas fa-user-check"></i>
			</div>
			<div class="stat-number"><?php echo $active_users; ?></div>
			<div class="stat-label">Active Users</div>
		</div>
		<div class="stat-card warning">
			<div class="stat-icon" style="background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);">
				<i class="fas fa-briefcase"></i>
			</div>
			<div class="stat-number"><?php echo $total_cases; ?></div>
			<div class="stat-label">Total Cases</div>
		</div>
		<div class="stat-card gold">
			<div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #2563eb);">
				<i class="fas fa-file-alt"></i>
			</div>
			<div class="stat-number"><?php echo $total_documents; ?></div>
			<div class="stat-label">Documents</div>
		</div>
	</div>

	<div class="filter-card">
		<h5 class="filter-title">
			<i class="fas fa-filter"></i>
			Filter Users
		</h5>
		<form class="row g-3" method="get">
			<div class="col-sm-6 col-md-3">
				<label class="form-label">Role</label>
				<select name="role" class="form-select">
					<option value="">All Roles</option>
					<?php foreach ($roles as $r): ?>
					<option value="<?php echo e($r); ?>" <?php echo $filter_role === $r ? 'selected' : ''; ?>>
						<?php echo e(ucwords(str_replace('_', ' ', $r))); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-sm-6 col-md-3">
				<label class="form-label">Status</label>
				<select name="status" class="form-select">
					<option value="">All Status</option>
					<option value="active" <?php echo $filter_status==='active'?'selected':''; ?>>Active</option>
					<option value="inactive" <?php echo $filter_status==='inactive'?'selected':''; ?>>Inactive</option>
				</select>
			</div>
			<div class="col-sm-6 col-md-2">
				<label class="form-label">Start Date</label>
				<input type="date" name="start" value="<?php echo e($filter_start); ?>" class="form-control">
			</div>
			<div class="col-sm-6 col-md-2">
				<label class="form-label">End Date</label>
				<input type="date" name="end" value="<?php echo e($filter_end); ?>" class="form-control">
			</div>
			<div class="col-12 col-md-2 d-flex align-items-end">
				<button class="btn btn-outline-primary w-100" type="submit">
					<i class="fa fa-search me-2"></i>Search
				</button>
			</div>
			<div class="col-12">
				<label class="form-label">Search by Name or Email</label>
				<div class="input-group">
					<input type="text" name="q" value="<?php echo e($filter_q); ?>" class="form-control" placeholder="Enter name or email...">
					<button class="btn btn-outline-primary" type="submit">
						<i class="fa fa-search"></i>
					</button>
					<a href="users.php" class="btn btn-outline-secondary">
						<i class="fa fa-rotate"></i> Reset
					</a>
				</div>
			</div>
		</form>
	</div>

	<div class="table-container">
		<table class="table table-hover align-middle">
			<thead>
				<tr>
					<th>User</th>
					<th>Role</th>
					<th>Activity</th>
					<th>Status</th>
					<th>Joined</th>
					<th style="width:180px;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($users)): ?>
				<tr>
					<td colspan="6">
						<div class="empty-state">
							<i class="fas fa-users"></i>
							<p class="mb-0">No users found matching your criteria</p>
						</div>
					</td>
				</tr>
				<?php else: ?>
				<?php foreach ($users as $u): 
					$initials = strtoupper(substr($u['name'], 0, 1));
					if (strpos($u['name'], ' ') !== false) {
						$parts = explode(' ', $u['name']);
						$initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
					}
					$role_icon = $role_icons[$u['role']] ?? 'fa-user';
				?>
				<tr>
					<td>
						<div class="user-info">
							<div class="user-avatar"><?php echo $initials; ?></div>
							<div class="user-details">
								<strong><?php echo e($u['name']); ?></strong>
								<span class="user-email"><?php echo e($u['email']); ?></span>
							</div>
						</div>
					</td>
					<td>
						<span class="role-badge">
							<i class="fas <?php echo $role_icon; ?>"></i>
							<?php echo e(ucwords(str_replace('_', ' ', $u['role']))); ?>
						</span>
					</td>
					<td>
						<span class="stat-pill">
							<i class="fas fa-briefcase me-1"></i><?php echo (int)$u['case_count']; ?> Cases
						</span>
						<span class="stat-pill">
							<i class="fas fa-file me-1"></i><?php echo (int)$u['document_count']; ?> Docs
						</span>
					</td>
					<td>
						<span class="badge bg-<?php echo $u['is_active'] ? 'success' : 'secondary'; ?>">
							<?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
						</span>
					</td>
					<td><?php echo $u['created_at'] ? date('M d, Y', strtotime($u['created_at'])) : '-'; ?></td>
					<td>
						<div class="btn-group btn-group-sm" role="group">
							<?php if (has_permission('user:update')): ?>
							<button class="btn btn-outline-primary" 
								data-bs-toggle="modal" 
								data-bs-target="#editUserModal" 
								data-user='<?php echo json_encode(['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'is_active'=>$u['is_active']]); ?>'
								title="Edit User">
								<i class="fa fa-pen"></i>
							</button>
							<?php endif; ?>
							
							<?php if (has_permission('user:update')): ?>
							<form method="post" class="d-inline">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="action" value="toggle_status">
								<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
								<input type="hidden" name="new_status" value="<?php echo $u['is_active'] ? 0 : 1; ?>">
								<button class="btn btn-outline-<?php echo $u['is_active'] ? 'warning' : 'success'; ?>" 
									onclick="return confirm('<?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?> this user?')"
									title="<?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>">
									<i class="fa <?php echo $u['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
								</button>
							</form>
							<?php endif; ?>
							
							<?php if (has_permission('user:update')): ?>
							<button class="btn btn-outline-danger" 
								data-bs-toggle="modal" 
								data-bs-target="#resetPasswordModal" 
								data-user-id="<?php echo (int)$u['id']; ?>"
								data-user-name="<?php echo e($u['name']); ?>"
								title="Reset Password">
								<i class="fa fa-key"></i>
							</button>
							<?php endif; ?>
							
							<?php if (has_permission('user:delete') && $u['id'] != $user_id): ?>
							<form method="post" class="d-inline">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="action" value="delete_user">
								<input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
								<button class="btn btn-outline-danger" 
									onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
									title="Delete User">
									<i class="fa fa-trash"></i>
								</button>
							</form>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Staff Member</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="create_user">
					<div class="mb-3">
						<label class="form-label">Full Name *</label>
						<input type="text" name="name" class="form-control" placeholder="John Doe" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Email Address *</label>
						<input type="email" name="email" class="form-control" placeholder="john@merlaws.com" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Password *</label>
						<input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
						<small class="text-muted">User can change this after first login</small>
					</div>
					<div class="mb-3">
						<label class="form-label">Role *</label>
						<select name="role" class="form-select" required>
							<?php foreach ($roles as $r): ?>
							<option value="<?php echo e($r); ?>"><?php echo e(ucwords(str_replace('_', ' ', $r))); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="is_active" id="createActive" checked>
						<label class="form-check-label" for="createActive">
							Active (user can log in immediately)
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						<i class="fas fa-times me-2"></i>Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-check me-2"></i>Add Staff Member
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="update_user">
					<input type="hidden" name="user_id" id="edit_user_id">
					<div class="mb-3">
						<label class="form-label">Full Name *</label>
						<input type="text" name="name" id="edit_name" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Email Address *</label>
						<input type="email" name="email" id="edit_email" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Role *</label>
						<?php if (has_permission('settings:manage')): ?>
						<select name="role" id="edit_role" class="form-select" required>
							<?php foreach ($roles as $r): ?>
							<option value="<?php echo e($r); ?>"><?php echo e(ucwords(str_replace('_', ' ', $r))); ?></option>
							<?php endforeach; ?>
						</select>
						<?php else: ?>
						<input type="text" id="edit_role_display" class="form-control" readonly>
						<input type="hidden" name="role" id="edit_role">
						<?php endif; ?>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
						<label class="form-check-label" for="edit_is_active">
							Active
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						<i class="fas fa-times me-2"></i>Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save me-2"></i>Save Changes
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" onsubmit="return validateResetPassword()">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="reset_password">
					<input type="hidden" name="user_id" id="reset_user_id">
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle me-2"></i>
						You are resetting the password for: <strong id="reset_user_name"></strong>
					</div>
					<div class="mb-3">
						<label class="form-label">New Password *</label>
						<input type="password" name="new_password" id="new_password" class="form-control" minlength="8" placeholder="Minimum 8 characters" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Confirm Password *</label>
						<input type="password" id="confirm_password" class="form-control" minlength="8" placeholder="Re-enter password" required>
						<div class="invalid-feedback">Passwords do not match.</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						<i class="fas fa-times me-2"></i>Cancel
					</button>
					<button type="submit" class="btn btn-danger">
						<i class="fas fa-key me-2"></i>Reset Password
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const editUserModal = document.getElementById('editUserModal');
editUserModal && editUserModal.addEventListener('show.bs.modal', event => {
	const button = event.relatedTarget;
	if (!button) return;
	const data = button.getAttribute('data-user');
	if (!data) return;
	try {
		const user = JSON.parse(data);
		document.getElementById('edit_user_id').value = user.id;
		document.getElementById('edit_name').value = user.name;
		document.getElementById('edit_email').value = user.email;
		
		// Handle role field based on permission
		const roleSelect = document.getElementById('edit_role');
		const roleDisplay = document.getElementById('edit_role_display');
		if (roleSelect) {
			roleSelect.value = user.role;
		}
		if (roleDisplay) {
			roleDisplay.value = user.role.charAt(0).toUpperCase() + user.role.slice(1).replace('_', ' ');
		}
		
		document.getElementById('edit_is_active').checked = !!user.is_active;
	} catch (e) {
		console.error('Error parsing user data:', e);
	}
});

const resetPasswordModal = document.getElementById('resetPasswordModal');
resetPasswordModal && resetPasswordModal.addEventListener('show.bs.modal', event => {
	const button = event.relatedTarget;
	if (!button) return;
	const userId = button.getAttribute('data-user-id');
	const userName = button.getAttribute('data-user-name');
	document.getElementById('reset_user_id').value = userId;
	document.getElementById('reset_user_name').textContent = userName || 'this user';
	document.getElementById('new_password').value = '';
	document.getElementById('confirm_password').value = '';
	document.getElementById('confirm_password').classList.remove('is-invalid');
});

function validateResetPassword() {
	const p1 = document.getElementById('new_password');
	const p2 = document.getElementById('confirm_password');
	if (!p1 || !p2) return true;
	if (p1.value !== p2.value) {
		p2.classList.add('is-invalid');
		return false;
	}
	p2.classList.remove('is-invalid');
	return true;
}

// Real-time password validation
document.getElementById('confirm_password')?.addEventListener('input', function() {
	const p1 = document.getElementById('new_password');
	if (this.value && p1.value !== this.value) {
		this.classList.add('is-invalid');
	} else {
		this.classList.remove('is-invalid');
	}
});

// Fade in animations
document.addEventListener('DOMContentLoaded', function() {
	const cards = document.querySelectorAll('.stat-card, .filter-card, .table-container');
	cards.forEach((card, index) => {
		card.style.opacity = '0';
		card.style.transform = 'translateY(20px)';
		setTimeout(() => {
			card.style.transition = 'all 0.5s ease';
			card.style.opacity = '1';
			card.style.transform = 'translateY(0)';
		}, index * 100);
	});
});
</script>
<script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>