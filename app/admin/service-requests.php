<?php
// app/admin/service-requests.php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('case:view');

$status_filter = $_GET['status'] ?? '';
$user_id = get_user_id();
$user_role = get_user_role();
$pdo = db();

// Handle approve/reject actions
if (is_post() && isset($_POST['action']) && isset($_POST['request_id'])) {
    // Check permissions for service request actions
    if ($_POST['action'] === 'approve' && !has_permission('case:update')) {
        $_SESSION['error'] = 'You do not have permission to approve service requests';
    } elseif ($_POST['action'] === 'reject' && !has_permission('case:update')) {
        $_SESSION['error'] = 'You do not have permission to reject service requests';
    } elseif ($_POST['action'] === 'delete' && !has_permission('case:delete')) {
        $_SESSION['error'] = 'You do not have permission to delete service requests';
    }
    
    if (!isset($_SESSION['error'])) {
        if (!csrf_validate()) {
            $_SESSION['error'] = 'Invalid security token';
        } else {
        $request_id = (int)$_POST['request_id'];
        $action = $_POST['action'];
        $notes = trim($_POST['notes'] ?? '');
        
        try {
            // Check if user has access to this service request's case
            $stmt = $pdo->prepare("SELECT sr.*, c.assigned_to, c.id as case_id 
                                   FROM service_requests sr 
                                   JOIN cases c ON sr.case_id = c.id 
                                   WHERE sr.id = ?");
            $stmt->execute([$request_id]);
            $request_data = $stmt->fetch();
            
            if (!$request_data) {
                $_SESSION['error'] = 'Service request not found.';
            } else {
                // Check access permissions
                $has_access = false;
                if ($user_role === 'super_admin' || in_array($user_role, ['partner', 'case_manager', 'office_admin'])) {
                    $has_access = true;
                } elseif (in_array($user_role, ['attorney', 'paralegal'])) {
                    // Attorneys can only approve/reject for their assigned cases
                    $has_access = ($request_data['assigned_to'] == $user_id);
                } else {
                    $case_ids = get_user_cases_access($user_id, $user_role);
                    $has_access = in_array($request_data['case_id'], $case_ids);
                }
                
                if (!$has_access) {
                    $_SESSION['error'] = 'You do not have permission to process this service request.';
                } else {
                    $success = false;
                    if ($action === 'approve') {
                        $success = approve_service_request($request_id, get_user_id(), $notes);
                        if ($success) {
                            $_SESSION['success'] = 'Service request approved successfully';
                        } else {
                            $_SESSION['error'] = 'Failed to approve service request. It may have already been processed.';
                        }
                    } elseif ($action === 'reject') {
                        $success = reject_service_request($request_id, get_user_id(), $notes);
                        if ($success) {
                            $_SESSION['success'] = 'Service request rejected successfully';
                        } else {
                            $_SESSION['error'] = 'Failed to reject service request. It may have already been processed.';
                        }
                    } elseif ($action === 'delete') {
                        // Only super_admin can delete
                        if ($user_role !== 'super_admin') {
                            $_SESSION['error'] = 'You do not have permission to delete service requests.';
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM service_requests WHERE id = ?");
                            $stmt->execute([$request_id]);
                            $success = $stmt->rowCount() > 0;
                            if ($success) {
                                $_SESSION['success'] = 'Service request deleted successfully';
                            } else {
                                $_SESSION['error'] = 'Failed to delete service request. It may not exist.';
                            }
                        }
                    } else {
                        $_SESSION['error'] = 'Invalid action specified.';
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Service request action error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to process request: ' . $e->getMessage();
        }
        
        // Preserve status filter in redirect
        $redirect_url = 'service-requests.php';
        if ($status_filter) {
            $redirect_url .= '?status=' . urlencode($status_filter);
        }
        redirect($redirect_url);
        }
    }
}

// Fetch requests AFTER action processing to ensure fresh data
$requests = get_all_service_requests($status_filter);

// Enhance requests with case assignment info if not already present
foreach ($requests as &$request) {
    if (!isset($request['assigned_to'])) {
        $stmt = $pdo->prepare("SELECT assigned_to FROM cases WHERE id = ?");
        $stmt->execute([$request['case_id']]);
        $case_data = $stmt->fetch();
        $request['assigned_to'] = $case_data['assigned_to'] ?? null;
    }
}
unset($request);

// Filter requests based on user role
if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see requests for their assigned cases
    $requests = array_filter($requests, function($request) use ($user_id) {
        return isset($request['assigned_to']) && $request['assigned_to'] == $user_id;
    });
} elseif ($user_role === 'billing') {
    // Billing sees all financial requests
    // No additional filtering needed
} elseif ($user_role === 'case_manager') {
    // Case managers see all pending requests
    // No additional filtering needed
} elseif (in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
    // Management roles see all requests
    // No additional filtering needed
} else {
    // Other roles see requests for cases they have access to
    $case_ids = get_user_cases_access($user_id, $user_role);
    $requests = array_filter($requests, function($request) use ($case_ids) {
        return in_array($request['case_id'], $case_ids);
    });
}

// Calculate status counts and statistics
$status_counts = [
    'pending' => count(array_filter($requests, fn($r) => $r['status'] === 'pending')),
    'approved' => count(array_filter($requests, fn($r) => $r['status'] === 'approved')),
    'rejected' => count(array_filter($requests, fn($r) => $r['status'] === 'rejected'))
];

$urgent_count = count(array_filter($requests, fn($r) => ($r['urgency'] ?? '') === 'urgent'));
$total_value = array_sum(array_column($requests, 'estimated_cost'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Requests Management | Med Attorneys Admin</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
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

        .summary-card.pending::before { background: linear-gradient(90deg, var(--warning-orange), #d97706); }
        .summary-card.approved::before { background: linear-gradient(90deg, var(--success-green), #059669); }
        .summary-card.rejected::before { background: linear-gradient(90deg, var(--danger-red), #dc2626); }
        .summary-card.urgent::before { background: linear-gradient(90deg, var(--danger-red), #dc2626); }

        .summary-number {
            font-size: 2rem;
            font-weight: 900;
            margin: 0 0 0.5rem 0;
            line-height: 1;
        }

        .summary-number.pending { color: var(--warning-orange); }
        .summary-number.approved { color: var(--success-green); }
        .summary-number.rejected { color: var(--danger-red); }
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

        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .filter-btn {
            background: transparent;
            border: 2px solid #e9ecef;
            color: var(--neutral-gray);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-btn:hover, .filter-btn.active {
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .filter-btn.active.all {
            background: var(--admin-blue);
            border-color: var(--admin-blue);
            color: white;
        }

        .filter-btn.active.pending {
            background: var(--warning-orange);
            border-color: var(--warning-orange);
            color: white;
        }

        .filter-btn.active.approved {
            background: var(--success-green);
            border-color: var(--success-green);
            color: white;
        }

        .filter-btn.active.rejected {
            background: var(--danger-red);
            border-color: var(--danger-red);
            color: white;
        }

        .filter-btn:not(.active):hover {
            border-color: var(--merlaws-gold);
            color: var(--merlaws-gold);
        }

        /* Request Cards */
        .request-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .request-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            transition: all 0.3s ease;
        }

        .request-card.status-pending::before { background: var(--warning-orange); }
        .request-card.status-approved::before { background: var(--success-green); }
        .request-card.status-rejected::before { background: var(--danger-red); }

        .request-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .request-card.urgent {
            border-color: var(--danger-red);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.02), rgba(220, 38, 38, 0.02));
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin: 0;
            flex: 1;
        }

        .request-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: var(--warning-orange);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-approved {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: var(--success-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-rejected {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: var(--danger-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-urgent {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            color: white;
            animation: pulse-urgent 2s infinite;
        }

        @keyframes pulse-urgent {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .badge-category {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: white;
        }

        /* Request Details */
        .request-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--neutral-gray);
        }

        .meta-item i {
            width: 20px;
            color: var(--merlaws-gold);
            font-size: 1rem;
        }

        .meta-value {
            color: var(--merlaws-primary);
            font-weight: 600;
        }

        /* Notes Section */
        .notes-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid var(--merlaws-gold);
        }

        .notes-title {
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-notes {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0.05));
            border-left-color: var(--admin-blue);
        }

        .admin-notes .notes-title {
            color: var(--admin-blue);
        }

        /* Action Buttons */
        .request-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            border-color: var(--success-green);
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            color: white;
            border-color: var(--danger-red);
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-view {
            background: transparent;
            color: var(--admin-blue);
            border-color: var(--admin-blue);
        }

        .btn-view:hover {
            background: var(--admin-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            color: white;
            border-color: var(--danger-red);
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            border-radius: 20px 20px 0 0;
            border-bottom: none;
            padding: 2rem 2rem 1rem 2rem;
        }

        .modal-body {
            padding: 1rem 2rem;
        }

        .modal-footer {
            border-radius: 0 0 20px 20px;
            border-top: none;
            padding: 1rem 2rem 2rem 2rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 0.2rem rgba(201, 169, 110, 0.25);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--neutral-gray);
        }

        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 2rem;
        }

        .empty-state h3 {
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
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

        /* Loading States */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid var(--merlaws-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            
            .request-card {
                padding: 1.5rem;
            }
            
            .request-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .request-meta {
                grid-template-columns: 1fr;
            }
            
            .request-actions {
                flex-direction: column;
            }
            
            .filter-buttons {
                flex-direction: column;
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
            <i class="fas fa-clipboard-check"></i>
            Service Request Management
        </div>
        <h1 class="page-title">Service Requests</h1>
        <p class="page-subtitle">Review, approve, and manage client service requests efficiently</p>
    </div>
</div>

<div class="container">
    <!-- Summary Statistics -->
    <div class="summary-grid">
        <div class="summary-card pending">
            <div class="summary-number pending"><?php echo $status_counts['pending']; ?></div>
            <div class="summary-label">Pending Review</div>
        </div>
        <div class="summary-card approved">
            <div class="summary-number approved"><?php echo $status_counts['approved']; ?></div>
            <div class="summary-label">Approved</div>
        </div>
        <div class="summary-card rejected">
            <div class="summary-number rejected"><?php echo $status_counts['rejected']; ?></div>
            <div class="summary-label">Rejected</div>
        </div>
        <div class="summary-card urgent">
            <div class="summary-number urgent"><?php echo $urgent_count; ?></div>
            <div class="summary-label">Urgent Requests</div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filter Controls -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-filter"></i>
            Filter Requests
        </h3>
        
        <div class="filter-buttons">
            <a href="?" class="filter-btn <?php echo empty($status_filter) ? 'active all' : ''; ?>">
                <i class="fas fa-list"></i>
                All Requests (<?php echo count($requests); ?>)
            </a>
            <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active pending' : ''; ?>">
                <i class="fas fa-clock"></i>
                Pending (<?php echo $status_counts['pending']; ?>)
            </a>
            <a href="?status=approved" class="filter-btn <?php echo $status_filter === 'approved' ? 'active approved' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                Approved (<?php echo $status_counts['approved']; ?>)
            </a>
            <a href="?status=rejected" class="filter-btn <?php echo $status_filter === 'rejected' ? 'active rejected' : ''; ?>">
                <i class="fas fa-times-circle"></i>
                Rejected (<?php echo $status_counts['rejected']; ?>)
            </a>
        </div>
    </div>

    <!-- Requests List -->
    <?php if (empty($requests)): ?>
    <div class="content-card">
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No Service Requests Found</h3>
            <p>No service requests match the current filter criteria.</p>
            <a href="?" class="btn btn-outline-primary">
                <i class="fas fa-refresh me-2"></i>View All Requests
            </a>
        </div>
    </div>
    <?php else: ?>
    
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title mb-0">
                <i class="fas fa-tasks"></i>
                Request Details
            </h3>
            <div class="d-flex gap-2">
                <?php if (has_permission('report:export')): ?>
                <button class="btn btn-outline-secondary" onclick="exportRequests()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <?php foreach ($requests as $request): ?>
        <div class="request-card status-<?php echo $request['status']; ?> <?php echo (($request['urgency'] ?? '') === 'urgent') ? 'urgent' : ''; ?>" 
             id="request-<?php echo $request['id']; ?>">
             
            <div class="request-header">
                <h3 class="service-title"><?php echo e($request['service_name']); ?></h3>
                <div class="request-badges">
                    <?php if (($request['urgency'] ?? '') === 'urgent'): ?>
                    <span class="badge badge-urgent">
                        <i class="fas fa-exclamation-triangle me-1"></i>URGENT
                    </span>
                    <?php endif; ?>
                    <span class="badge badge-category">
                        <?php echo e($request['category'] ?? 'General'); ?>
                    </span>
                    <span class="badge badge-<?php echo $request['status']; ?>">
                        <?php echo ucfirst($request['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="request-meta">
                <div class="meta-item">
                    <i class="fas fa-briefcase"></i>
                    <div>
                        <strong>Case:</strong> 
                        <span class="meta-value"><?php echo e($request['case_title']); ?></span>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <strong>Client:</strong> 
                        <span class="meta-value"><?php echo e($request['client_name']); ?></span>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <strong>Requested:</strong> 
                        <span class="meta-value"><?php echo date('M d, Y g:i A', strtotime($request['requested_at'])); ?></span>
                    </div>
                </div>
                <?php if (isset($request['estimated_cost']) && $request['estimated_cost'] > 0): ?>
                <div class="meta-item">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <strong>Est. Cost:</strong> 
                        <span class="meta-value">R<?php echo number_format($request['estimated_cost'], 2); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($request['notes']): ?>
            <div class="notes-section">
                <div class="notes-title">
                    <i class="fas fa-sticky-note"></i>
                    Client Notes
                </div>
                <p class="mb-0"><?php echo e($request['notes']); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($request['status'] !== 'pending' && $request['admin_notes']): ?>
            <div class="notes-section admin-notes">
                <div class="notes-title">
                    <i class="fas fa-shield-alt"></i>
                    Admin Decision Notes
                    <small class="text-muted ms-auto">
                        <?php echo date('M d, Y g:i A', strtotime($request['processed_at'])); ?>
                    </small>
                </div>
                <p class="mb-0"><?php echo e($request['admin_notes']); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="request-actions">
                <?php if ($request['status'] === 'pending'): ?>
                    <?php 
                    // Check if user can approve/reject this request
                    $can_process = false;
                    if ($user_role === 'super_admin' || in_array($user_role, ['partner', 'case_manager', 'office_admin'])) {
                        $can_process = has_permission('case:update');
                    } elseif (in_array($user_role, ['attorney', 'paralegal'])) {
                        // Attorneys can only process requests for their assigned cases
                        $can_process = has_permission('case:update') && isset($request['assigned_to']) && $request['assigned_to'] == $user_id;
                    } else {
                        $case_ids = get_user_cases_access($user_id, $user_role);
                        $can_process = has_permission('case:update') && in_array($request['case_id'], $case_ids);
                    }
                    ?>
                    <?php if ($can_process): ?>
                    <button class="btn btn-approve" onclick="showProcessModal(<?php echo $request['id']; ?>, 'approve', '<?php echo e($request['service_name']); ?>')">
                        <i class="fas fa-check"></i>Approve Request
                    </button>
                    <button class="btn btn-reject" onclick="showProcessModal(<?php echo $request['id']; ?>, 'reject', '<?php echo e($request['service_name']); ?>')">
                        <i class="fas fa-times"></i>Reject Request
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="../cases/view.php?id=<?php echo $request['case_id']; ?>" class="btn btn-view">
                    <i class="fas fa-eye"></i>View Case
                </a>
                
                <?php if ($user_role === 'super_admin' && has_permission('case:delete')): ?>
                <button class="btn btn-delete" onclick="deleteRequest(<?php echo $request['id']; ?>)">
                    <i class="fas fa-trash"></i>Delete
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Process Request Modal -->
<div class="modal fade" id="processModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title text-white" id="modalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="requestAction" name="action">
                <input type="hidden" id="requestId" name="request_id">
                
                <div class="modal-body">
                    <p id="modalMessage"></p>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Decision Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" 
                                  placeholder="Add any notes or comments about this decision..."></textarea>
                        <div class="form-text">These notes will be visible to the client and other team members.</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn" id="confirmBtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/mobile-responsive.js"></script>

<script>
function showProcessModal(requestId, action, serviceName) {
    const modal = document.getElementById('processModal');
    const modalHeader = document.getElementById('modalHeader');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    
    // Set form values
    document.getElementById('requestId').value = requestId;
    document.getElementById('requestAction').value = action;
    
    // Clear previous notes
    document.getElementById('notes').value = '';
    
    // Configure modal based on action
    if (action === 'approve') {
        modalHeader.className = 'modal-header bg-success';
        modalTitle.innerHTML = '<i class="fas fa-check-circle me-2"></i>Approve Service Request';
        modalMessage.innerHTML = `Are you sure you want to <strong>approve</strong> the service request for "<em>${serviceName}</em>"?<br><br>This will allow the service to proceed and may generate billing.`;
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve Request';
    } else {
        modalHeader.className = 'modal-header bg-danger';
        modalTitle.innerHTML = '<i class="fas fa-times-circle me-2"></i>Reject Service Request';
        modalMessage.innerHTML = `Are you sure you want to <strong>reject</strong> the service request for "<em>${serviceName}</em>"?<br><br>The client will be notified of this decision.`;
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i>Reject Request';
    }
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Process request with loading state
document.querySelector('#processModal form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('confirmBtn');
    const originalContent = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    // Re-enable after 3 seconds if something goes wrong
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
    }, 3000);
});

// Export functionality
function exportRequests() {
    // This would implement CSV/PDF export functionality
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    
    setTimeout(() => {
        document.body.removeChild(loadingOverlay);
        showToast('Export functionality would be implemented here', 'info');
    }, 1500);
}

// Toast notifications
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const bgClass = type === 'success' ? 'bg-success' : 
                   type === 'error' ? 'bg-danger' : 
                   type === 'warning' ? 'bg-warning' : 'bg-info';
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${bgClass} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 4000
    });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Real-time updates simulation
let updateInterval;

function startRealTimeUpdates() {
    updateInterval = setInterval(async () => {
        try {
            // This would fetch real-time updates from the server
            console.log('Checking for updates...');
        } catch (error) {
            console.error('Update check failed:', error);
        }
    }, 30000); // Check every 30 seconds
}

function stopRealTimeUpdates() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Start real-time updates
    startRealTimeUpdates();
    
    // Add smooth animations to cards
    const cards = document.querySelectorAll('.request-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Highlight urgent requests
    const urgentCards = document.querySelectorAll('.request-card.urgent');
    urgentCards.forEach(card => {
        setInterval(() => {
            card.style.boxShadow = card.style.boxShadow === 'none' || !card.style.boxShadow ?
                '0 0 20px rgba(239, 68, 68, 0.3)' : 'none';
        }, 2000);
    });
});

// Delete request function
function deleteRequest(requestId) {
    if (confirm('Are you sure you want to delete this service request? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        // Get CSRF token from existing form
        const csrfInput = document.querySelector('input[name="_csrf"]');
        const csrfToken = csrfInput ? csrfInput.value : '';
        
        form.innerHTML = `
            <input type="hidden" name="_csrf" value="${csrfToken}">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="request_id" value="${requestId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopRealTimeUpdates();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + R to refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        window.location.reload();
    }
    
    // Escape to close modal
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('processModal'));
        if (modal) {
            modal.hide();
        }
    }
});
</script>
</body>
</html>