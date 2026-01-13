<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('case:view');

$pdo = db();

$errors = [];
$success = '';

// Handle POST actions
if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token. Please refresh and try again.';
	} else {
		$action = (string)($_POST['action'] ?? '');
		try {
			switch ($action) {
				case 'bulk_status': {
					// Only super_admin can change case status
					if (get_user_role() !== 'super_admin') {
						$errors[] = 'You do not have permission to change case status.';
						break;
					}
					require_permission('case:update');
					$status = (string)($_POST['status'] ?? '');
					$ids = $_POST['ids'] ?? [];
					$allowed = ['draft','active','under_review','closed'];
					if (!$ids || !in_array($status, $allowed, true)) { $errors[] = 'Invalid selection.'; break; }
					$ids = array_map('intval', (array)$ids);
					$in = implode(',', array_fill(0, count($ids), '?'));
					$paramsBulk = array_merge([$status], $ids);
					$stmt = $pdo->prepare("UPDATE cases SET status = ?, updated_at = NOW() WHERE id IN ($in)");
					$stmt->execute($paramsBulk);
					$success = 'Statuses updated for ' . count($ids) . ' case(s).';
					break;
				}
				case 'bulk_assign': {
					// Only super_admin can assign attorneys
					if (get_user_role() !== 'super_admin') {
						$errors[] = 'You do not have permission to assign attorneys to cases.';
						break;
					}
					require_permission('case:assign');
					$attorney_id = (int)($_POST['attorney_id'] ?? 0);
					$ids = $_POST['ids'] ?? [];
					if (!$ids) { $errors[] = 'No cases selected.'; break; }
					$ids = array_map('intval', (array)$ids);
					$in = implode(',', array_fill(0, count($ids), '?'));
					$paramsBulk = array_merge([($attorney_id ?: null)], $ids);
					$stmt = $pdo->prepare("UPDATE cases SET assigned_to = ?, updated_at = NOW() WHERE id IN ($in)");
					$stmt->execute($paramsBulk);
					$success = 'Assigned ' . count($ids) . ' case(s).';
					break;
				}
					// Permission gating per action
				case 'change_status': {
					// Only super_admin can change case status
					if (get_user_role() !== 'super_admin') {
						$errors[] = 'You do not have permission to change case status.';
						break;
					}
					require_permission('case:update');
					$case_id = (int)($_POST['case_id'] ?? 0);
					$status = (string)($_POST['status'] ?? '');
					$allowed = ['draft','active','under_review','closed'];
					if ($case_id <= 0 || !in_array($status, $allowed, true)) { $errors[] = 'Invalid case or status.'; break; }
					$stmt = $pdo->prepare('UPDATE cases SET status = ?, updated_at = NOW() WHERE id = ?');
					$stmt->execute([$status, $case_id]);
					// Notify client of status change
					try {
						$own = $pdo->prepare('SELECT user_id, title FROM cases WHERE id = ?');
						$own->execute([$case_id]);
						$caseRow = $own->fetch();
						if ($caseRow) {
							$title = 'Case Status Updated';
							$msg = 'Your case "' . ($caseRow['title'] ?? 'Case') . '" status changed to ' . $status . '.';
							create_user_notification((int)$caseRow['user_id'], 'case_update', $title, $msg, '/app/cases/view.php?id=' . $case_id);
						}
					} catch (Throwable $e) {}
					$success = 'Case status updated.';
					break;
				}
				case 'assign_attorney': {
					// Only super_admin can assign attorneys
					if (get_user_role() !== 'super_admin') {
						$errors[] = 'You do not have permission to assign attorneys to cases.';
						break;
					}
					require_permission('case:update');
					$case_id = (int)($_POST['case_id'] ?? 0);
					$attorney_id = (int)($_POST['attorney_id'] ?? 0);
					if ($case_id <= 0) { $errors[] = 'Invalid case.'; break; }
					// attorney_id can be 0 to clear assignment
					$stmt = $pdo->prepare('UPDATE cases SET assigned_to = NULLIF(?, 0), updated_at = NOW() WHERE id = ?');
					$stmt->execute([$attorney_id, $case_id]);
					$success = 'Case assignment updated.';
					break;
				}
				case 'accept_case': {
					require_permission('case:update');
					$case_id = (int)($_POST['case_id'] ?? 0);
					$notes = trim((string)($_POST['notes'] ?? ''));
					if ($case_id <= 0 || $notes === '') { $errors[] = 'Notes are required.'; break; }
					$pdo->prepare('UPDATE cases SET status = "active", updated_at = NOW() WHERE id = ?')->execute([$case_id]);
					// Log activity and notify
					try {
						$own = $pdo->prepare('SELECT user_id, title FROM cases WHERE id = ?');
						$own->execute([$case_id]);
						$caseRow = $own->fetch();
						if ($caseRow) {
							log_case_activity($case_id, get_user_id(), 'admin_action', 'Case Accepted', $notes);
							create_user_notification((int)$caseRow['user_id'], 'case_update', 'Case Accepted', 'Your case "' . ($caseRow['title'] ?? 'Case') . '" has been accepted. Notes: ' . $notes, '/app/cases/view.php?id=' . $case_id);
						}
					} catch (Throwable $e) {}
					$success = 'Case accepted.';
					break;
				}
				case 'decline_case': {
					require_permission('case:update');
					$case_id = (int)($_POST['case_id'] ?? 0);
					$notes = trim((string)($_POST['notes'] ?? ''));
					if ($case_id <= 0 || $notes === '') { $errors[] = 'Notes are required.'; break; }
					$pdo->prepare('UPDATE cases SET status = "closed", updated_at = NOW() WHERE id = ?')->execute([$case_id]);
					// Log activity and notify
					try {
						$own = $pdo->prepare('SELECT user_id, title FROM cases WHERE id = ?');
						$own->execute([$case_id]);
						$caseRow = $own->fetch();
						if ($caseRow) {
							log_case_activity($case_id, get_user_id(), 'admin_action', 'Case Declined', $notes);
							create_user_notification((int)$caseRow['user_id'], 'case_update', 'Case Declined', 'Your case "' . ($caseRow['title'] ?? 'Case') . '" has been declined. Notes: ' . $notes, '/app/cases/index.php');
						}
					} catch (Throwable $e) {}
					$success = 'Case declined.';
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
$f_status = trim((string)($_GET['status'] ?? ''));
$f_type = trim((string)($_GET['case_type'] ?? ''));
$f_priority = trim((string)($_GET['priority'] ?? ''));
$f_attorney = (int)($_GET['attorney'] ?? 0);
$f_date_start = trim((string)($_GET['start'] ?? ''));
$f_date_end = trim((string)($_GET['end'] ?? ''));
$f_q = trim((string)($_GET['q'] ?? ''));
$f_filter = trim((string)($_GET['filter'] ?? '')); // Additional filter parameter

// Load attorneys only - refresh on every page load to get latest
try {
    $attorneys = $pdo->query("SELECT id, name FROM users WHERE role = 'attorney' AND is_active = 1 ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $attorneys = [];
}

// Build query - FIXED: Simplified role-based filtering for super_admin
$sql = "SELECT c.*, u.name AS client_name, u.email AS client_email, au.name AS attorney_name
	FROM cases c
	JOIN users u ON c.user_id = u.id
	LEFT JOIN users au ON c.assigned_to = au.id
	WHERE 1=1";
$params = [];

// Apply role-based filtering - FIXED: More permissive for admin roles
$user_role = get_user_role();
$user_id = get_user_id();

// Only apply restrictive filtering for non-admin roles
if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see their assigned cases
    $sql .= " AND c.assigned_to = ?";
    $params[] = $user_id;
} elseif ($user_role === 'billing') {
    // Billing sees cases with financial activity
    $sql .= " AND EXISTS (SELECT 1 FROM service_requests sr WHERE sr.case_id = c.id AND sr.status != 'cart')";
} elseif ($user_role === 'doc_specialist') {
    // Document specialists see cases with document activity
    $sql .= " AND EXISTS (SELECT 1 FROM case_documents cd WHERE cd.case_id = c.id)";
} elseif ($user_role === 'compliance') {
    // Compliance sees cases with compliance requests
    $sql .= " AND EXISTS (SELECT 1 FROM compliance_requests cr WHERE cr.case_id = c.id)";
} elseif ($user_role === 'receptionist') {
    // Receptionists see cases with appointments
    $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.case_id = c.id)";
}
// super_admin, partner, case_manager, office_admin see all cases (no additional filter)

// Apply user filters
if ($f_status !== '') { 
    $sql .= " AND c.status = ?"; 
    $params[] = $f_status; 
}
if ($f_type !== '') { 
    $sql .= " AND c.case_type = ?"; 
    $params[] = $f_type; 
}
if ($f_priority !== '') { 
    $sql .= " AND c.priority = ?"; 
    $params[] = $f_priority; 
}
if ($f_attorney > 0) { 
    $sql .= " AND c.assigned_to = ?"; 
    $params[] = $f_attorney; 
}
if ($f_date_start !== '' && $f_date_end !== '') { 
    $sql .= " AND DATE(c.created_at) BETWEEN ? AND ?"; 
    $params[] = $f_date_start; 
    $params[] = $f_date_end; 
}
if ($f_q !== '') { 
    $like = "%$f_q%"; 
    $sql .= " AND (c.title LIKE ? OR u.name LIKE ? OR u.email LIKE ?)"; 
    $params[] = $like; 
    $params[] = $like; 
    $params[] = $like; 
}

// Additional filter for unassigned cases
if ($f_filter === 'unassigned') {
    $sql .= " AND (c.assigned_to IS NULL OR c.assigned_to = 0)";
}

// Debug information
$debug_info = "";
if (isset($_GET['debug'])) {
    $debug_info = "
    <div class='alert alert-info'>
        <strong>Debug Info:</strong><br>
        User Role: {$user_role}<br>
        Status Filter: {$f_status}<br>
        SQL Query: " . htmlspecialchars($sql) . "<br>
        Params: " . implode(', ', $params) . "<br>
        Total Cases Found: " . ($total_rows ?? 'Not calculated yet') . "
    </div>
    ";
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (!has_permission('report:export')) {
        die('You do not have permission to export cases.');
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cases-export-' . date('Ymd-His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Title','Client','Client Email','Assigned To','Status','Priority','Updated At']);
    $exportSql = $sql . ' ORDER BY c.updated_at DESC';
    $exportStmt = $pdo->prepare($exportSql);
    $exportStmt->execute($params);
    while ($row = $exportStmt->fetch()) {
        fputcsv($out, [
            (int)$row['id'],
            $row['title'],
            $row['client_name'],
            $row['client_email'],
            $row['attorney_name'],
            $row['status'],
            $row['priority'],
            $row['updated_at']
        ]);
    }
    fclose($out);
    exit;
}

// Quick view JSON endpoint (include recent documents)
if (isset($_GET['view']) && $_GET['view'] === 'json' && isset($_GET['case_id'])) {
    $viewId = (int)$_GET['case_id'];
    $viewStmt = $pdo->prepare("SELECT c.*, u.name AS client_name, u.email AS client_email, au.name AS attorney_name FROM cases c JOIN users u ON c.user_id = u.id LEFT JOIN users au ON c.assigned_to = au.id WHERE c.id = ?");
    $viewStmt->execute([$viewId]);
    $case = $viewStmt->fetch() ?: [];

    // Fetch latest 5 documents for the case
    $docs = [];
    if (!empty($case)) {
        $docStmt = $pdo->prepare("SELECT id, original_filename, file_path, uploaded_at FROM case_documents WHERE case_id = ? ORDER BY uploaded_at DESC LIMIT 5");
        $docStmt->execute([$viewId]);
        $docs = $docStmt->fetchAll();
    }

    header('Content-Type: application/json');
    echo json_encode(['case' => $case, 'documents' => $docs]);
    exit;
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Count total
$countSql = "SELECT COUNT(*) FROM (" . $sql . ") t";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total_rows = (int)$countStmt->fetchColumn();
$total_pages = (int)ceil($total_rows / $perPage);

// Add order and limit
$sql .= " ORDER BY c.updated_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

$statuses = ['draft' => 'Secondary','active' => 'Primary','under_review' => 'Warning','closed' => 'Success'];
$priorities = ['low','medium','high','urgent'];

// Calculate summary statistics
$total_cases = count($cases);
$status_counts = [];
$priority_counts = [];
foreach ($statuses as $status => $class) {
    $status_counts[$status] = count(array_filter($cases, fn($c) => $c['status'] === $status));
}
foreach ($priorities as $priority) {
    $priority_counts[$priority] = count(array_filter($cases, fn($c) => $c['priority'] === $priority));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Case Management | Med Attorneys Admin</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">

    <style>
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --merlaws-dark: #0d1117;
            --admin-blue: #3b82f6;
            --admin-blue-dark: #2563eb;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --neutral-gray: #6b7280;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-dark) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
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

        .admin-badge {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(201, 169, 110, 0.3);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 1rem 0;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 16px 16px 0 0;
        }

        .summary-card.total::before { background: linear-gradient(90deg, var(--admin-blue), var(--admin-blue-dark)); }
        .summary-card.active::before { background: linear-gradient(90deg, var(--success-green), #059669); }
        .summary-card.review::before { background: linear-gradient(90deg, var(--warning-orange), #d97706); }
        .summary-card.urgent::before { background: linear-gradient(90deg, var(--danger-red), #dc2626); }

        .summary-number {
            font-size: 2rem;
            font-weight: 900;
            margin: 0 0 0.5rem 0;
            line-height: 1;
        }

        .summary-number.total { color: var(--admin-blue); }
        .summary-number.active { color: var(--success-green); }
        .summary-number.review { color: var(--warning-orange); }
        .summary-number.urgent { color: var(--danger-red); }

        .summary-label {
            color: var(--neutral-gray);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        /* Enhanced Table */
        .cases-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8fafc;
            border: none;
            font-weight: 700;
            color: var(--merlaws-primary);
            padding: 1.25rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 0.2rem rgba(201, 169, 110, 0.25);
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }

        .btn-outline-primary {
            color: var(--admin-blue);
            border-color: var(--admin-blue);
        }

        .btn-outline-primary:hover {
            background: var(--admin-blue);
            border-color: var(--admin-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .btn-outline-secondary {
            color: var(--neutral-gray);
            border-color: #e5e7eb;
        }

        .btn-outline-secondary:hover {
            background: var(--neutral-gray);
            border-color: var(--neutral-gray);
            color: white;
        }

        /* Status Badges */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bg-primary {
            background: linear-gradient(135deg, var(--admin-blue), var(--admin-blue-dark)) !important;
        }

        .bg-success {
            background: linear-gradient(135deg, var(--success-green), #059669) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, var(--warning-orange), #d97706) !important;
        }

        .bg-secondary {
            background: linear-gradient(135deg, var(--neutral-gray), #4b5563) !important;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .input-group-sm .form-select {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }

        .input-group-sm .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        /* Case Info */
        .case-title {
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 0.25rem;
        }

        .case-meta {
            font-size: 0.85rem;
            color: var(--neutral-gray);
        }

        .client-info {
            font-weight: 600;
            color: var(--merlaws-primary);
        }

        .client-email {
            font-size: 0.85rem;
            color: var(--neutral-gray);
        }

        /* Priority Indicators */
        .priority-low { color: var(--success-green); }
        .priority-medium { color: var(--warning-orange); }
        .priority-high { color: var(--danger-red); }
        .priority-urgent { 
            color: var(--danger-red); 
            font-weight: 700;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            color: var(--success-green);
            border-left: 4px solid var(--success-green);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
                margin-bottom: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .content-card {
                padding: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="admin-badge">
            <i class="fas fa-briefcase"></i>
            Case Management System
        </div>
        <h1 class="page-title">
            <?php if (in_array($user_role, ['attorney', 'paralegal'])): ?>
                My Cases
            <?php else: ?>
                Case Overview
            <?php endif; ?>
        </h1>
        <p class="page-subtitle">
            <?php if (in_array($user_role, ['attorney', 'paralegal'])): ?>
                View and manage cases assigned to you
            <?php else: ?>
                Manage and track all client cases across your practice
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="container my-4">
    <!-- Debug Information -->
    <?= $debug_info ?>

    <!-- Summary Statistics -->
    <div class="summary-grid">
        <div class="summary-card total">
            <div class="summary-number total"><?php echo $total_cases; ?></div>
            <div class="summary-label">Total Cases</div>
        </div>
        <div class="summary-card active">
            <div class="summary-number active"><?php echo $status_counts['active'] ?? 0; ?></div>
            <div class="summary-label">Active Cases</div>
        </div>
        <div class="summary-card review">
            <div class="summary-number review"><?php echo $status_counts['under_review'] ?? 0; ?></div>
            <div class="summary-label">Under Review</div>
        </div>
        <div class="summary-card urgent">
            <div class="summary-number urgent"><?php echo $priority_counts['urgent'] ?? 0; ?></div>
            <div class="summary-label">Urgent Cases</div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo e(implode(' ', $errors)); ?>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e($success); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-filter"></i>
            Filter & Search Cases
        </h3>
        <form class="row g-3" method="get">
            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (array_keys($statuses) as $s): ?>
                    <option value="<?php echo e($s); ?>" <?php echo $f_status===$s?'selected':''; ?>><?php echo e(ucfirst(str_replace('_',' ',$s))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Case Type</label>
                <input type="text" name="case_type" class="form-control" value="<?php echo e($f_type); ?>" placeholder="e.g. medical_negligence">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <?php foreach ($priorities as $p): ?>
                    <option value="<?php echo e($p); ?>" <?php echo $f_priority===$p?'selected':''; ?>><?php echo e(ucfirst($p)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Attorney</label>
                <select name="attorney" class="form-select">
                    <option value="0">All Attorneys</option>
                    <?php foreach ($attorneys as $a): ?>
                    <option value="<?php echo (int)$a['id']; ?>" <?php echo $f_attorney===(int)$a['id']?'selected':''; ?>><?php echo e($a['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="start" value="<?php echo e($f_date_start); ?>" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="end" value="<?php echo e($f_date_end); ?>" class="form-control">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="<?php echo e($f_q); ?>" class="form-control" placeholder="Case title, client name, or email">
            </div>
            <!-- Additional filter for unassigned cases -->
            <div class="col-12 col-md-6">
                <label class="form-label">Additional Filter</label>
                <select name="filter" class="form-select">
                    <option value="">No Additional Filter</option>
                    <option value="unassigned" <?php echo $f_filter==='unassigned'?'selected':''; ?>>Unassigned Cases Only</option>
                </select>
            </div>
            <div class="col-12 d-flex align-items-end gap-2">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search me-2"></i>Apply Filters
                </button>
                <a href="cases.php" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
                <a href="cases.php?debug=1" class="btn btn-outline-info">
                    <i class="fas fa-bug me-2"></i>Debug
                </a>
            </div>
        </form>
    </div>

    <!-- Cases Table -->
    <?php if ($total_rows > 0): ?>
    <div class="cases-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="selectAll"></th>
                        <th>Case Details</th>
                        <th>Client Information</th>
                        <th>Assigned Attorney</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Last Updated</th>
                        <th style="width:260px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $c): ?>
                    <?php 
                        $rowClass = '';
                        if ($c['status'] === 'under_review' && isset($c['updated_at'])) {
                            $updatedTs = strtotime($c['updated_at']);
                            if ($updatedTs && (time() - $updatedTs) > (7*24*60*60)) { // >7 days
                                $rowClass = 'table-warning';
                            }
                        }
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td><input type="checkbox" class="row-select" value="<?php echo (int)$c['id']; ?>"></td>
                        <td>
                            <div class="case-title"><?php echo e($c['title']); ?></div>
                            <div class="case-meta"><?php echo e($c['case_type']); ?> • Case #<?php echo (int)$c['id']; ?></div>
                        </td>
                        <td>
                            <div class="client-info"><?php echo e($c['client_name']); ?></div>
                            <div class="client-email"><?php echo e($c['client_email']); ?></div>
                        </td>
                        <td>
                            <?php if (get_user_role() === 'super_admin'): ?>
                            <form method="post" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="assign_attorney">
                                <input type="hidden" name="case_id" value="<?php echo (int)$c['id']; ?>">
                                <div class="input-group input-group-sm">
                                    <select name="attorney_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="0">Unassigned</option>
                                        <?php foreach ($attorneys as $a): ?>
                                        <option value="<?php echo (int)$a['id']; ?>" <?php echo ((int)($c['assigned_to'] ?? 0) === (int)$a['id']) ? 'selected' : ''; ?>><?php echo e($a['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                            <?php else: ?>
                            <span><?php echo e($c['attorney_name'] ?? 'Unassigned'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo strtolower($statuses[$c['status']] ?? 'secondary'); ?>"><?php echo e(ucfirst(str_replace('_',' ',$c['status']))); ?></span>
                        </td>
                        <td>
                            <span class="priority-<?php echo $c['priority']; ?>"><?php echo e(ucfirst($c['priority'])); ?></span>
                        </td>
                        <td><?php echo $c['updated_at'] ? date('M d, Y', strtotime($c['updated_at'])) : '-'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if (get_user_role() === 'super_admin' && has_permission('case:update')): ?>
                                <form method="post" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="case_id" value="<?php echo (int)$c['id']; ?>">
                                    <div class="input-group input-group-sm">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php foreach (array_keys($statuses) as $s): ?>
                                            <option value="<?php echo e($s); ?>" <?php echo $c['status']===$s?'selected':''; ?>><?php echo e(ucfirst(str_replace('_',' ',$s))); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline-primary btn-sm" type="submit">Update</button>
                                    </div>
                                </form>
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openQuickView(<?php echo (int)$c['id']; ?>)">Quick View</button>
                                
                                <?php if (!in_array($c['status'], ['active','closed'], true) && get_user_role() === 'super_admin' && has_permission('case:update')): ?>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="openDecisionModal(<?php echo (int)$c['id']; ?>,'accept')">Accept</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="openDecisionModal(<?php echo (int)$c['id']; ?>,'decline')">Decline</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bulk Actions & Pagination -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mt-3">
        <div class="d-flex align-items-center gap-2">
            <?php if (get_user_role() === 'super_admin' && has_permission('case:update')): ?>
            <select id="bulkStatus" class="form-select form-select-sm" style="width:auto;">
                <option value="">Bulk set status…</option>
                <?php foreach (array_keys($statuses) as $s): ?>
                <option value="<?php echo e($s); ?>"><?php echo e(ucfirst(str_replace('_',' ',$s))); ?></option>
                <?php endforeach; ?>
            </select>
            <button id="applyBulk" class="btn btn-outline-primary btn-sm">Apply</button>
            <?php endif; ?>
            
            <?php if (get_user_role() === 'super_admin' && has_permission('case:assign')): ?>
            <select id="bulkAssign" class="form-select form-select-sm" style="width:auto;">
                <option value="">Bulk assign attorney…</option>
                <option value="0">Unassigned</option>
                <?php foreach ($attorneys as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button id="applyAssign" class="btn btn-outline-primary btn-sm">Assign</button>
            <?php endif; ?>
            
            <?php if (has_permission('report:export')): ?>
            <button id="exportCsv" class="btn btn-outline-secondary btn-sm">Export CSV</button>
            <?php endif; ?>
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,["page"=>$page-1])); ?>">Prev</a></li>
                <?php endif; ?>
                <?php for ($p = 1; $p <= max(1,$total_pages); $p++): ?>
                <li class="page-item <?php echo $p===$page?'active':''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,["page"=>$p])); ?>"><?php echo $p; ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($page < max(1,$total_pages)): ?>
                <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,["page"=>$page+1])); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php else: ?>
    <div class="content-card text-center py-5">
        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
        <h3>No Cases Found</h3>
        <p class="text-muted">No cases match your current filters. Try adjusting your search criteria.</p>
        <a href="cases.php" class="btn btn-outline-primary">View All Cases</a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/mobile-responsive.js"></script>
<script>
async function openQuickView(caseId) {
    try {
        const params = new URLSearchParams(window.location.search);
        params.set('case_id', caseId);
        params.set('view', 'json');
        const url = window.location.pathname + '?' + params.toString();
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
        const payload = await res.json();
        const data = payload.case || {};
        const docs = Array.isArray(payload.documents) ? payload.documents : [];
        const modalHtml = `
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Case #${data.id} - ${data.title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><strong>Client:</strong> ${data.client_name} <div class="text-muted">${data.client_email}</div></div>
          <div class="col-md-6"><strong>Assigned To:</strong> ${data.attorney_name || 'Unassigned'}</div>
          <div class="col-md-4"><strong>Status:</strong> ${data.status}</div>
          <div class="col-md-4"><strong>Priority:</strong> ${data.priority}</div>
          <div class="col-md-4"><strong>Updated:</strong> ${data.updated_at || '-'}</div>
        </div>
        <hr>
        <div><strong>Description</strong></div>
        <div class="mt-1">${(data.description || '').replaceAll('\n','<br>')}</div>
        <hr>
        <div><strong>Recent Documents</strong></div>
        ${docs.length === 0 ? '<div class="text-muted">No documents uploaded yet.</div>' : `
          <ul class="mt-2">
            ${docs.map(d => `<li><a href="/uploads/${d.file_path}" target="_blank" rel="noopener">${d.original_filename}</a> <span class="text-muted">(${new Date(d.uploaded_at).toLocaleString()})</span></li>`).join('')}
          </ul>
        `}
      </div>
      <div class="modal-footer">
        <a href="view.php?id=${data.id}" class="btn btn-outline-primary">Open Case</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>`;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = modalHtml;
        document.body.appendChild(wrapper.firstElementChild);
        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
        document.getElementById('quickViewModal').addEventListener('hidden.bs.modal', e => e.target.remove());
    } catch (e) {}
}

document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});

document.getElementById('applyBulk')?.addEventListener('click', async function() {
    const status = document.getElementById('bulkStatus').value;
    if (!status) return;
    const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.value);
    if (ids.length === 0) return;
    try {
        const form = new FormData();
        form.append('action', 'bulk_status');
        form.append('status', status);
        ids.forEach(id => form.append('ids[]', id));
        // CSRF token if present on page
        const csrf = document.querySelector('input[name="csrf_token"]');
        if (csrf) form.append('csrf_token', csrf.value);
        const res = await fetch(window.location.href, { method: 'POST', body: form, credentials: 'same-origin' });
        if (res.ok) { window.location.reload(); }
    } catch (e) {}
});

document.getElementById('exportCsv')?.addEventListener('click', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('export', 'csv');
    window.location.href = url.toString();
});

document.getElementById('applyAssign')?.addEventListener('click', async function() {
    const attorneyId = document.getElementById('bulkAssign').value;
    if (attorneyId === '') return;
    const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.value);
    if (ids.length === 0) return;
    try {
        const form = new FormData();
        form.append('action', 'bulk_assign');
        form.append('attorney_id', attorneyId);
        ids.forEach(id => form.append('ids[]', id));
        const csrf = document.querySelector('input[name="csrf_token"]');
        if (csrf) form.append('csrf_token', csrf.value);
        const res = await fetch(window.location.href, { method: 'POST', body: form, credentials: 'same-origin' });
        if (res.ok) { window.location.reload(); }
    } catch (e) {}
});

function openDecisionModal(caseId, action) {
    const modalHtml = `
<div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header ${action==='accept'?'bg-success text-white':'bg-danger text-white'}">
        <h5 class="modal-title">${action==='accept'?'Accept Case':'Decline Case'}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        ${document.querySelector('input[name="_csrf"]').outerHTML}
        <input type="hidden" name="action" value="${action==='accept'?'accept_case':'decline_case'}">
        <input type="hidden" name="case_id" value="${caseId}">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Notes (required)</label>
            <textarea class="form-control" name="notes" rows="4" placeholder="Add a short rationale and client-facing note..." required></textarea>
            <div class="form-text">A brief message will be sent to the client.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn ${action==='accept'?'btn-success':'btn-danger'}">${action==='accept'?'Accept Case':'Decline Case'}</button>
        </div>
      </form>
    </div>
  </div>
</div>`;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = modalHtml;
    document.body.appendChild(wrapper.firstElementChild);
    const modal = new bootstrap.Modal(document.getElementById('decisionModal'));
    modal.show();
    document.getElementById('decisionModal').addEventListener('hidden.bs.modal', e => e.target.remove());
}
</script>
</body>
</html>