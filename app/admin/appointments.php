<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

require_permission('appointment:view');

$pdo = db();

$user_id = get_user_id();
$user_role = get_user_role();

// Handle status updates
$errors = [];
if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token.';
	} else {
		$action = (string)($_POST['action'] ?? '');
		$apptId = (int)($_POST['appointment_id'] ?? 0);
		
		// Check permissions for appointment actions
		if ($action === 'complete' && !has_permission('appointment:update')) {
			$errors[] = 'You do not have permission to complete appointments.';
		} elseif ($action === 'cancel' && !has_permission('appointment:delete')) {
			$errors[] = 'You do not have permission to cancel appointments.';
		} elseif ($apptId > 0 && in_array($action, ['complete','cancel'], true)) {
			$new = $action === 'complete' ? 'completed' : 'cancelled';
			$stmt = $pdo->prepare('UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
			$stmt->execute([$new, $apptId]);
		}
	}
}

$status = isset($_GET['status']) ? (string)$_GET['status'] : '';
$attorney = isset($_GET['attorney']) ? (int)$_GET['attorney'] : 0;
$from = isset($_GET['from']) ? (string)$_GET['from'] : '';
$to = isset($_GET['to']) ? (string)$_GET['to'] : '';

$whereParts = [];
$params = [];

// Role-based appointment filtering
if (in_array($user_role, ['receptionist', 'super_admin', 'partner', 'office_admin'])) {
    // Receptionists and management see all appointments
    // No additional filter
} elseif (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals see only their assigned appointments
    $whereParts[] = 'a.assigned_to = ?';
    $params[] = $user_id;
} else {
    // Other roles see appointments for their accessible cases
    $whereParts[] = 'EXISTS (
        SELECT 1 FROM cases c2 WHERE c2.id = a.case_id AND (
            c2.user_id = ? OR 
            c2.assigned_to = ?
        )
    )';
    $params[] = $user_id;
    $params[] = $user_id;
}

if (in_array($status, ['pending','proposed','confirmed','scheduled','completed','cancelled'], true)) {
	$whereParts[] = 'a.status = ?';
	$params[] = $status;
}
if ($attorney > 0) {
	$whereParts[] = 'a.assigned_to = ?';
	$params[] = $attorney;
}
if ($from !== '') {
	$whereParts[] = 'a.start_time >= ?';
	$params[] = $from;
}
if ($to !== '') {
	$whereParts[] = 'a.start_time <= ?';
	$params[] = $to;
}
$where = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Fetch attorneys list
$attorneys = $pdo->query("SELECT id, name FROM users WHERE role IN ('admin','staff','manager') ORDER BY name")->fetchAll();

$sql = "
SELECT a.*, c.title AS case_title, u.name AS client_name, u2.name AS assigned_name
FROM appointments a
JOIN cases c ON a.case_id = c.id
JOIN users u ON c.user_id = u.id
LEFT JOIN users u2 ON a.assigned_to = u2.id
" . $where . "
ORDER BY a.start_time DESC
LIMIT 500
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin - Appointments | Med Attorneys</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../../assets/css/responsive.css">
	<style>
		.appointments-container {
			max-width: 1200px;
			margin: 40px auto;
			padding: 20px;
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
		}
		.filter-form {
			margin-bottom: 12px;
			display: flex;
			gap: 8px;
			align-items: end;
			flex-wrap: wrap;
		}
		.filter-form > div {
			flex: 1;
			min-width: 150px;
		}
		.filter-form label {
			display: block;
			margin-bottom: 4px;
			font-weight: 600;
			font-size: 0.9rem;
		}
		.filter-form select,
		.filter-form input[type="datetime-local"] {
			width: 100%;
			padding: 8px 12px;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			font-size: 0.9rem;
		}
		.filter-form button {
			padding: 8px 20px;
			background: #1a1a1a;
			color: white;
			border: none;
			border-radius: 6px;
			cursor: pointer;
			font-weight: 600;
			min-height: 44px;
		}
		.appointments-table {
			width: 100%;
			border-collapse: collapse;
		}
		.appointments-table th {
			text-align: left;
			border-bottom: 1px solid #e2e8f0;
			padding: 12px 8px;
			font-weight: 600;
			background: #f8f9fa;
		}
		.appointments-table td {
			padding: 12px 8px;
			border-bottom: 1px solid #f1f5f9;
		}
		.appointments-table button {
			padding: 6px 12px;
			margin-right: 6px;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			background: white;
			cursor: pointer;
			font-size: 0.85rem;
			min-height: 36px;
		}
		@media (max-width: 768px) {
			.appointments-container {
				margin: 20px auto;
				padding: 1rem;
				border-radius: 12px;
			}
			.appointments-container h1 {
				font-size: 1.5rem;
				margin-bottom: 1rem;
			}
			.filter-form {
				flex-direction: column;
				gap: 1rem;
			}
			.filter-form > div {
				width: 100%;
			}
			.filter-form select,
			.filter-form input[type="datetime-local"] {
				font-size: 16px;
				padding: 12px 16px;
				min-height: 48px;
			}
			.filter-form button {
				width: 100%;
				min-height: 48px;
				font-size: 16px;
			}
			.appointments-table {
				display: block;
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}
			.appointments-table thead {
				display: none;
			}
			.appointments-table tbody {
				display: block;
			}
			.appointments-table tr {
				display: block;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				margin-bottom: 1rem;
				padding: 1rem;
				background: white;
			}
			.appointments-table td {
				display: block;
				padding: 0.5rem 0;
				border-bottom: none;
				text-align: left;
			}
			.appointments-table td:before {
				content: attr(data-label);
				font-weight: 600;
				display: block;
				margin-bottom: 0.25rem;
				color: #64748b;
				font-size: 0.8rem;
			}
			.appointments-table td:last-child {
				margin-top: 0.75rem;
				padding-top: 0.75rem;
				border-top: 1px solid #e2e8f0;
			}
			.appointments-table button {
				width: 100%;
				margin-bottom: 0.5rem;
				margin-right: 0;
				min-height: 44px;
			}
		}
		@media (max-width: 480px) {
			.appointments-container {
				padding: 0.75rem;
				margin: 10px auto;
			}
			.appointments-container h1 {
				font-size: 1.35rem;
			}
		}
	</style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="appointments-container">
    <h1 style="margin-top: 0;">All Appointments</h1>
    <input type="hidden" id="_csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
	<form method="get" class="filter-form">
		<div>
			<label>Status:</label>
			<select name="status">
				<option value="">All</option>
				<option value="scheduled" <?php echo $status==='scheduled'?'selected':''; ?>>Scheduled</option>
				<option value="completed" <?php echo $status==='completed'?'selected':''; ?>>Completed</option>
				<option value="cancelled" <?php echo $status==='cancelled'?'selected':''; ?>>Cancelled</option>
			</select>
		</div>
		<div>
			<label>Attorney:</label>
			<select name="attorney">
				<option value="0">All</option>
				<?php foreach ($attorneys as $a): ?>
					<option value="<?php echo (int)$a['id']; ?>" <?php echo $attorney===(int)$a['id']?'selected':''; ?>><?php echo e($a['name']); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label>From:</label>
			<input type="datetime-local" name="from" value="<?php echo e($from); ?>">
		</div>
		<div>
			<label>To:</label>
			<input type="datetime-local" name="to" value="<?php echo e($to); ?>">
		</div>
		<div>
			<button type="submit">Filter</button>
		</div>
	</form>
	<?php if (!$rows): ?>
		<p>No appointments found.</p>
	<?php else: ?>
		<table class="appointments-table">
			<thead>
				<tr>
					<th>When</th>
					<th>Title</th>
					<th>Case</th>
					<th>Client</th>
					<th>Assigned</th>
					<th>Status</th>
                    <th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($rows as $r): ?>
				<tr>
					<td data-label="When"><?php echo e($r['start_time']); ?><?php if(!empty($r['end_time'])): ?> - <?php echo e($r['end_time']); ?><?php endif; ?></td>
					<td data-label="Title"><?php echo e($r['title']); ?></td>
					<td data-label="Case"><?php echo e($r['case_title']); ?></td>
					<td data-label="Client"><?php echo e($r['client_name']); ?></td>
					<td data-label="Assigned"><?php echo e($r['assigned_name'] ?? ''); ?></td>
					<td data-label="Status"><?php echo e($r['status']); ?></td>
                    <td data-label="Actions">
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php if (in_array($r['status'], ['pending','proposed'], true)): ?>
                            <?php if (has_permission('appointment:update')): ?>
                            <button onclick="apptAccept(<?php echo (int)$r['id']; ?>)">Accept</button>
                            <button onclick="apptDecline(<?php echo (int)$r['id']; ?>)">Decline</button>
                            <button onclick="openPropose(<?php echo (int)$r['id']; ?>,'<?php echo e($r['start_time']); ?>','<?php echo e($r['end_time']); ?>')">Propose New Time</button>
                            <?php endif; ?>
                        <?php elseif ($r['status'] === 'scheduled'): ?>
                            <?php if (has_permission('appointment:update')): ?>
                            <form method="post" style="display:inline-block;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int)$r['id']; ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit">Mark Completed</button>
                            </form>
                            <?php endif; ?>
                            
                            <?php if (has_permission('appointment:delete')): ?>
                            <form method="post" style="display:inline-block;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="appointment_id" value="<?php echo (int)$r['id']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit">Cancel</button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>
                    </td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
<script>
async function callAppt(action, payload) {
    const form = new URLSearchParams();
    form.set('action', action);
    for (const k in payload) { form.set(k, payload[k]); }
    form.set('csrf_token', document.getElementById('_csrf').value);
    const res = await fetch('../api/appointments.php', { method:'POST', body: form, credentials:'same-origin' });
    const j = await res.json();
    if (!j.success) { alert(j.error || 'Operation failed'); return false; }
    location.reload();
}
function apptAccept(id) { return callAppt('accept', { appointment_id: id }); }
function apptDecline(id) { const reason = prompt('Optional reason for decline:'); return callAppt('decline', { appointment_id: id, reason }); }
function openPropose(id, curStart, curEnd) {
    const start = prompt('New start (YYYY-MM-DD HH:MM:SS)', curStart.replace('T',' ').slice(0,19));
    if (!start) return;
    const end = prompt('New end (YYYY-MM-DD HH:MM:SS)', curEnd.replace('T',' ').slice(0,19));
    if (!end) return;
    return callAppt('propose', { appointment_id: id, start_at: start, end_at: end });
}
</script>
</body>
</html> 