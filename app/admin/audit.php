<?php
require __DIR__ . '/../config.php';
require_permission('audit:view');

$pdo = db();

$user_id = get_user_id();
$user_role = get_user_role();

// Inputs
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$eventType = isset($_GET['event']) ? trim((string)$_GET['event']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$export = isset($_GET['export']) && $_GET['export'] === 'csv';

// Build query
$where = [];
$params = [];

// Role-based audit log filtering
if (in_array($user_role, ['super_admin', 'compliance'])) {
    // Super admin and compliance see all audit logs
    // No additional filter
} elseif (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals see only logs related to their cases
    $where[] = '(sl.user_id = ? OR EXISTS (
        SELECT 1 FROM cases c WHERE c.assigned_to = ? AND (
            sl.message LIKE CONCAT("%case_id:", c.id, "%") OR
            sl.message LIKE CONCAT("%Case #", c.id, "%")
        )
    ))';
    $params[] = $user_id;
    $params[] = $user_id;
} else {
    // Other roles see only their own logs
    $where[] = 'sl.user_id = ?';
    $params[] = $user_id;
}

if ($userId > 0) { $where[] = 'user_id = ?'; $params[] = $userId; }
if ($eventType !== '') { $where[] = 'event_type = ?'; $params[] = $eventType; }
if ($from !== '') { $where[] = 'created_at >= ?'; $params[] = $from; }
if ($to !== '') { $where[] = 'created_at <= ?'; $params[] = $to; }
if ($q !== '') { $where[] = '(message LIKE ? OR ip_address LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Fetch events - try audit_logs first (new comprehensive table), fallback to security_logs
try {
    $stmt = $pdo->prepare("SELECT 
        al.id, 
        al.event_type, 
        al.event_category,
        al.event_action,
        al.message, 
        al.user_id, 
        al.user_role,
        u.name AS user_name, 
        al.ip_address, 
        al.severity,
        al.status,
        al.entity_type,
        al.entity_id,
        al.created_at 
    FROM audit_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    $sqlWhere 
    ORDER BY al.created_at DESC 
    LIMIT 1000");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback to security_logs if audit_logs doesn't exist
    try {
        $stmt = $pdo->prepare("SELECT 
            sl.id, 
            sl.event_type, 
            'general' as event_category,
            sl.event_type as event_action,
            sl.message, 
            sl.user_id, 
            NULL as user_role,
            u.name AS user_name, 
            sl.ip_address, 
            'medium' as severity,
            'success' as status,
            NULL as entity_type,
            NULL as entity_id,
            sl.created_at 
        FROM security_logs sl 
        LEFT JOIN users u ON sl.user_id = u.id 
        $sqlWhere 
        ORDER BY sl.created_at DESC 
        LIMIT 1000");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (Exception $e2) {
        $rows = [];
    }
}

// Export CSV
if ($export) {
	if (!has_permission('audit:export')) {
		die('You do not have permission to export audit logs.');
	}
	
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="audit_logs.csv"');
	$out = fopen('php://output', 'w');
	fputcsv($out, ['ID','Event Type','User ID','IP Address','Created At','Message']);
	foreach ($rows as $r) {
		fputcsv($out, [$r['id'],$r['event_type'],$r['user_id'],$r['ip_address'],$r['created_at'],$r['message']]);
	}
	fclose($out);
	exit;
}

// For filters: event types and users
$eventTypes = [];
try { 
    $eventTypes = $pdo->query("SELECT DISTINCT event_type FROM audit_logs ORDER BY event_type")->fetchAll(PDO::FETCH_COLUMN); 
} catch (Throwable $e) {
    try {
        $eventTypes = $pdo->query("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e2) {
        $eventTypes = [];
    }
}
$users = [];
try { $users = $pdo->query("SELECT id, name FROM users ORDER BY name LIMIT 500")->fetchAll(); } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Audit Logs | Med Attorneys Admin</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container my-4">
	<h1 class="h3 mb-3">Audit Logs</h1>
	<form class="row gy-2 gx-2 align-items-end mb-3" method="get" action="">
		<div class="col-12 col-md-3">
			<label class="form-label">User</label>
			<select name="user_id" class="form-select">
				<option value="0">All Users</option>
				<?php foreach ($users as $u) { $sel = ($userId === (int)$u['id']) ? ' selected' : ''; ?>
				<option value="<?php echo (int)$u['id']; ?>"<?php echo $sel; ?>><?php echo e($u['name']); ?> (<?php echo (int)$u['id']; ?>)</option>
				<?php } ?>
			</select>
		</div>
		<div class="col-12 col-md-2">
			<label class="form-label">Event</label>
			<select name="event" class="form-select">
				<option value="">All</option>
				<?php foreach ($eventTypes as $et) { $sel = ($eventType === $et) ? ' selected' : ''; ?>
				<option value="<?php echo e($et); ?>"<?php echo $sel; ?>><?php echo e($et); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="col-6 col-md-2">
			<label class="form-label">From</label>
			<input type="date" name="from" class="form-control" value="<?php echo e($from); ?>">
		</div>
		<div class="col-6 col-md-2">
			<label class="form-label">To</label>
			<input type="date" name="to" class="form-control" value="<?php echo e($to); ?>">
		</div>
		<div class="col-12 col-md-2">
			<label class="form-label">Keyword</label>
			<input type="text" name="q" class="form-control" placeholder="IP or message" value="<?php echo e($q); ?>">
		</div>
		<div class="col-12 col-md-1 d-grid">
			<button type="submit" class="btn btn-primary">Filter</button>
		</div>
		<div class="col-12 col-md-1 d-grid">
			<?php if (has_permission('audit:export')): ?>
			<a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn btn-outline-secondary">Export</a>
			<?php else: ?>
			<button class="btn btn-outline-secondary" disabled title="You do not have permission to export audit logs">Export</button>
			<?php endif; ?>
		</div>
	</form>

	<div class="table-responsive">
		<table class="table table-sm table-striped align-middle">
			<thead>
				<tr>
					<th>ID</th>
					<th>Event</th>
					<th>Category</th>
					<th>User</th>
					<th>IP</th>
					<th>Severity</th>
					<th>Status</th>
					<th>At</th>
					<th>Message</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!$rows) { ?>
				<tr><td colspan="9" class="text-muted">No records found.</td></tr>
			<?php } else { foreach ($rows as $r) { 
				$severity_class = match($r['severity'] ?? 'medium') {
					'critical' => 'danger',
					'high' => 'warning',
					'medium' => 'info',
					'low' => 'secondary',
					default => 'secondary'
				};
				$status_class = match($r['status'] ?? 'success') {
					'success' => 'success',
					'failure' => 'danger',
					'warning' => 'warning',
					default => 'secondary'
				};
			?>
				<tr>
					<td><?php echo (int)$r['id']; ?></td>
					<td><span class="badge bg-secondary"><?php echo e($r['event_type']); ?></span></td>
					<td><span class="badge bg-light text-dark"><?php echo e($r['event_category'] ?? 'general'); ?></span></td>
					<td><?php echo e($r['user_name'] ?: ('#' . (int)$r['user_id'])); ?><?php if ($r['user_role']): ?> <small class="text-muted">(<?php echo e($r['user_role']); ?>)</small><?php endif; ?></td>
					<td><?php echo e($r['ip_address'] ?? 'N/A'); ?></td>
					<td><span class="badge bg-<?php echo $severity_class; ?>"><?php echo e($r['severity'] ?? 'medium'); ?></span></td>
					<td><span class="badge bg-<?php echo $status_class; ?>"><?php echo e($r['status'] ?? 'success'); ?></span></td>
					<td><?php echo date('M d, Y H:i', strtotime($r['created_at'])); ?></td>
					<td><code class="small"><?php echo e($r['message']); ?></code></td>
				</tr>
			<?php } } ?>
			</tbody>
		</table>
	</div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html> 