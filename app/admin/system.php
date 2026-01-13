<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('settings:manage');

$pdo = db();
$errors = [];
$success = '';

if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token. Please refresh and try again.';
	} else {
		$action = (string)($_POST['action'] ?? '');
		try {
							switch ($action) {
					// Permission gating per action
				case 'clear_sessions':
					require_permission('backup:run');
					try { $pdo->exec('DELETE FROM sessions'); $success = 'Sessions cleared.'; } catch (Throwable $e) { $errors[] = 'No session store to clear or operation failed.'; }
					break;
				case 'reindex':
					require_permission('settings:manage');
					$success = 'Index maintenance acknowledged. Please run DB OPTIMIZE/ANALYZE manually via your DB tool.';
					break;
				default:
					$errors[] = 'Unknown action.';
			}
		} catch (Throwable $e) {
			$errors[] = 'Operation failed.';
		}
	}
}

$stats = [
	'users' => 0,
	'cases' => 0,
	'appointments' => 0,
	'case_documents' => 0,
];
foreach ($stats as $table => $_) {
	try { $stats[$table] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(); } catch (Throwable $e) {}
}

$logs = [];
try { $logs = $pdo->query('SELECT * FROM security_logs ORDER BY created_at DESC LIMIT 50')->fetchAll(); } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin - System Administration | Med Attorneys</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container my-4">
	<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
		<h1 class="h3 mb-3 mb-md-0">System Administration</h1>
	</div>

	<?php if ($errors): ?>
		<div class="alert alert-danger"><?php echo e(implode(' ', $errors)); ?></div>
	<?php elseif ($success): ?>
		<div class="alert alert-success"><?php echo e($success); ?></div>
	<?php endif; ?>

	<div class="row g-3 mb-4">
		<div class="col-12 col-lg-4">
			<div class="card h-100"><div class="card-body">
				<h5 class="card-title">System Overview</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item d-flex justify-content-between"><span>Users</span><strong><?php echo (int)$stats['users']; ?></strong></li>
					<li class="list-group-item d-flex justify-content-between"><span>Cases</span><strong><?php echo (int)$stats['cases']; ?></strong></li>
					<li class="list-group-item d-flex justify-content-between"><span>Appointments</span><strong><?php echo (int)$stats['appointments']; ?></strong></li>
					<li class="list-group-item d-flex justify-content-between"><span>Documents</span><strong><?php echo (int)$stats['case_documents']; ?></strong></li>
				</ul>
			</div></div>
		</div>
		<div class="col-12 col-lg-8">
			<div class="card h-100"><div class="card-body">
				<h5 class="card-title">Maintenance</h5>
				<div class="mb-3">Use the options below to perform non-destructive maintenance. For backups and schema changes, use your DB tool as per policy.</div>
				<form method="post" class="d-inline">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="clear_sessions">
					<button class="btn btn-outline-warning me-2" onclick="return confirm('Clear stored sessions?')"><i class="fa fa-user-slash me-1"></i>Clear Sessions</button>
				</form>
				<form method="post" class="d-inline">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="reindex">
					<button class="btn btn-outline-primary"><i class="fa fa-database me-1"></i>Reindex (Advisory)</button>
				</form>
				<a class="btn btn-success ms-2" href="../api/admin-analytics.php?export=1" target="_blank"><i class="fa fa-download me-1"></i>Export Analytics JSON</a>
			</div></div>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<h5 class="card-title">Security Logs (latest 50)</h5>
			<?php if (!$logs): ?>
				<div class="text-muted">No logs available or table missing.</div>
			<?php else: ?>
			<div class="table-responsive">
				<table class="table table-sm table-striped">
					<thead><tr><th>Time</th><th>Type</th><th>Message</th><th>User</th><th>IP</th></tr></thead>
					<tbody>
						<?php foreach ($logs as $lg): ?>
						<tr>
							<td><?php echo e($lg['created_at']); ?></td>
							<td><?php echo e($lg['event_type'] ?? ''); ?></td>
							<td><?php echo e($lg['message'] ?? ''); ?></td>
							<td><?php echo e($lg['user_id'] ?? ''); ?></td>
							<td><?php echo e($lg['ip_address'] ?? ''); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/mobile-responsive.js"></script>
</body>
</html> 