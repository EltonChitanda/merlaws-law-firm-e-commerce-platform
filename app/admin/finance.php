<?php
require __DIR__ . '/../config.php';
if (!has_permission('invoice:create') && !has_permission('payment:process')) {
	require_permission('invoice:create');
}

$pdo = db();
$user_id = get_user_id();
$user_role = get_user_role();

// Role-based data filtering
$case_filter_sql = "";
$case_filter_params = [];

if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see finances for their assigned cases
    $case_filter_sql = " AND c.assigned_to = ?";
    $case_filter_params[] = $user_id;
} elseif (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
    // Billing, partners, and super_admin see all financial data
    // No additional filter
} else {
    // Other roles have no access to financial data
    $case_filter_sql = " AND 1=0"; // No results
}

// Get financial summary
$financial_summary = [
    'total_revenue' => 0,
    'pending_invoices' => 0,
    'overdue_amount' => 0,
    'trust_balance' => 0
];

if (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN i.status = 'paid' THEN i.amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN i.status = 'pending' THEN i.amount ELSE 0 END) as pending_invoices,
            SUM(CASE WHEN i.status = 'overdue' THEN i.amount ELSE 0 END) as overdue_amount
        FROM invoices i
        JOIN cases c ON i.case_id = c.id
        WHERE i.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)" . $case_filter_sql
    );
    $stmt->execute($case_filter_params);
    $summary_data = $stmt->fetch();
    
    if ($summary_data) {
        $financial_summary['total_revenue'] = $summary_data['total_revenue'] ?? 0;
        $financial_summary['pending_invoices'] = $summary_data['pending_invoices'] ?? 0;
        $financial_summary['overdue_amount'] = $summary_data['overdue_amount'] ?? 0;
    }
    
    // Get trust balance (if trust_accounts table exists)
    try {
        $stmt = $pdo->query("SELECT SUM(balance) as trust_balance FROM trust_accounts WHERE status = 'active'");
        $trust_data = $stmt->fetch();
        $financial_summary['trust_balance'] = $trust_data['trust_balance'] ?? 0;
    } catch (Exception $e) {
        // Trust accounts table doesn't exist, set to 0
        $financial_summary['trust_balance'] = 0;
    }
}

// Get all invoices (not just recent)
$recent_invoices = [];
if (in_array($user_role, ['billing', 'partner', 'super_admin', 'attorney', 'paralegal'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                i.id,
                i.invoice_number,
                u.name as client_name,
                c.title as case_title,
                i.amount,
                i.status,
                i.due_date,
                i.created_at
            FROM invoices i
            JOIN cases c ON i.case_id = c.id
            JOIN users u ON c.user_id = u.id
            WHERE 1=1" . $case_filter_sql . "
            ORDER BY i.created_at DESC
        ");
        $stmt->execute($case_filter_params);
        $recent_invoices = $stmt->fetchAll();
    } catch (Exception $e) {
        // If invoices table doesn't exist or has issues, use empty array
        $recent_invoices = [];
    }
}

// Get pending approvals (financial requests)
$pending_approvals = [];
if (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                fr.id,
                fr.type,
                u.name as client_name,
                fr.amount,
                fr.reason,
                au.name as requested_by,
                fr.created_at as requested_at
            FROM financial_requests fr
            JOIN cases c ON fr.case_id = c.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN users au ON fr.requested_by = au.id
            WHERE fr.status = 'pending'" . $case_filter_sql . "
            ORDER BY fr.created_at DESC
            LIMIT 10
        ");
        $stmt->execute($case_filter_params);
        $pending_approvals = $stmt->fetchAll();
    } catch (Exception $e) {
        // Financial requests table doesn't exist, use empty array
        $pending_approvals = [];
    }
}

// NEW: Get closed cases for compensation management
$closed_cases = [];
if (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.title,
                c.case_number,
                u.name as client_name,
                c.updated_at
            FROM cases c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'closed'" . $case_filter_sql . "
            ORDER BY c.updated_at DESC, c.id DESC
        ");
        $stmt->execute($case_filter_params);
        $closed_cases = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching closed cases: " . $e->getMessage());
        $closed_cases = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Financial Management | Med Attorneys Admin</title>
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
            --revenue-purple: #8b5cf6;
            --trust-teal: #14b8a6;
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

        /* Financial Summary Cards */
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .financial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .financial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
        }

        .financial-card.revenue::before { background: linear-gradient(90deg, var(--revenue-purple), #7c3aed); }
        .financial-card.pending::before { background: linear-gradient(90deg, var(--warning-orange), #d97706); }
        .financial-card.overdue::before { background: linear-gradient(90deg, var(--danger-red), #dc2626); }
        .financial-card.trust::before { background: linear-gradient(90deg, var(--trust-teal), #0d9488); }

        .financial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .financial-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .financial-amount {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0;
            line-height: 1;
        }

        .financial-amount.revenue { color: var(--revenue-purple); }
        .financial-amount.pending { color: var(--warning-orange); }
        .financial-amount.overdue { color: var(--danger-red); }
        .financial-amount.trust { color: var(--trust-teal); }

        .financial-label {
            color: var(--neutral-gray);
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }

        .financial-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .financial-icon.revenue { background: linear-gradient(135deg, var(--revenue-purple), #7c3aed); }
        .financial-icon.pending { background: linear-gradient(135deg, var(--warning-orange), #d97706); }
        .financial-icon.overdue { background: linear-gradient(135deg, var(--danger-red), #dc2626); }
        .financial-icon.trust { background: linear-gradient(135deg, var(--trust-teal), #0d9488); }

        .trend-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            margin-top: 0.75rem;
        }

        .trend-up {
            color: var(--success-green);
        }

        .trend-down {
            color: var(--danger-red);
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

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            text-decoration: none;
            color: var(--merlaws-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(201, 169, 110, 0.1), transparent);
            transition: left 0.5s;
        }

        .action-card:hover {
            border-color: var(--merlaws-gold);
            color: var(--merlaws-primary);
            transform: translateY(-5px);
            text-decoration: none;
            box-shadow: 0 10px 40px rgba(201, 169, 110, 0.2);
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--merlaws-gold);
            transition: transform 0.3s ease;
        }

        .action-card:hover i {
            transform: scale(1.1);
        }

        .action-card h6 {
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .action-card small {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }

        /* Invoice Table */
        .invoice-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
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

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: var(--success-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: var(--warning-orange);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-overdue {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: var(--danger-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Approval Cards */
        .approval-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .approval-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--warning-orange);
            border-radius: 16px 0 0 16px;
        }

        .approval-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .approval-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .approval-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin: 0;
        }

        .approval-amount {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--merlaws-gold);
        }

        .approval-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }

        .approval-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
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

        .btn-primary {
            background: linear-gradient(135deg, var(--admin-blue), var(--admin-blue-dark));
            color: white;
            border-color: var(--admin-blue);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--admin-blue-dark), #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
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

        /* Charts Container */
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neutral-gray);
            border: 2px dashed #e5e7eb;
        }

        /* Case option styling */
        .case-option-details {
            font-size: 0.85em;
            color: #6c757d;
            display: block;
            margin-top: 2px;
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
            
            .financial-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .content-card {
                padding: 1.5rem;
            }
            
            .approval-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .approval-actions {
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
            <i class="fas fa-chart-line"></i>
            Financial Management System
        </div>
        <h1 class="page-title">Financial Overview</h1>
        <p class="page-subtitle">Comprehensive invoice management, payment processing, and financial reporting</p>
    </div>
</div>

<div class="container my-4">
    <!-- Financial Summary -->
    <div class="financial-grid">
        <div class="financial-card revenue">
            <div class="financial-header">
                <div>
                    <h2 class="financial-amount revenue">R<?php echo number_format($financial_summary['total_revenue'], 0); ?></h2>
                    <div class="financial-label">Total Revenue</div>
                    <div class="trend-indicator trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12.5% from last month</span>
                    </div>
                </div>
                <div class="financial-icon revenue">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="financial-card pending">
            <div class="financial-header">
                <div>
                    <h2 class="financial-amount pending">R<?php echo number_format($financial_summary['pending_invoices'], 0); ?></h2>
                    <div class="financial-label">Pending Invoices</div>
                    <div class="trend-indicator trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>-3.2% from last month</span>
                    </div>
                </div>
                <div class="financial-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="financial-card overdue">
            <div class="financial-header">
                <div>
                    <h2 class="financial-amount overdue">R<?php echo number_format($financial_summary['overdue_amount'], 0); ?></h2>
                    <div class="financial-label">Overdue Amount</div>
                    <div class="trend-indicator trend-up">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Requires attention</span>
                    </div>
                </div>
                <div class="financial-icon overdue">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>

        <div class="financial-card trust">
            <div class="financial-header">
                <div>
                    <h2 class="financial-amount trust">R<?php echo number_format($financial_summary['trust_balance'], 0); ?></h2>
                    <div class="financial-label">Trust Balance</div>
                    <div class="trend-indicator trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8.1% from last month</span>
                    </div>
                </div>
                <div class="financial-icon trust">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Financial Actions
        </h3>
        <div class="quick-actions">
            <a href="invoices/index.php" class="action-card">
                <i class="fas fa-file-invoice"></i>
                <h6>Invoice Management</h6>
                <small>View, edit, and manage all client invoices</small>
            </a>
            
            <?php if (in_array($user_role, ['billing', 'partner', 'super_admin'])): ?>
            <a href="#" class="action-card" onclick="manageCompensation()">
                <i class="fas fa-hand-holding-usd"></i>
                <h6>Compensation Management</h6>
                <small>Enter compensation amounts with automatic 25% client payment calculation</small>
            </a>
            <?php endif; ?>
            
            <?php if (has_permission('report:view')): ?>
            <a href="#" class="action-card" onclick="generateReport()">
                <i class="fas fa-chart-bar"></i>
                <h6>Financial Reports</h6>
                <small>Generate comprehensive financial and billing reports</small>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-card">
            <div class="text-center">
                <i class="fas fa-chart-area fa-3x mb-3"></i>
                <h5>Revenue Trends Chart</h5>
                <p>Interactive revenue and expense tracking visualization would be implemented here using Chart.js or similar</p>
            </div>
        </div>
        <div class="chart-card">
            <div class="text-center">
                <i class="fas fa-chart-pie fa-3x mb-3"></i>
                <h5>Payment Distribution</h5>
                <p>Breakdown of payment methods and client payment patterns</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Invoices -->
        <div class="col-lg-8">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-title mb-0">
                        <i class="fas fa-file-invoice"></i>
                        Recent Invoices
                    </h3>
                    <a href="invoices/index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-external-link-alt me-2"></i>View All Invoices
                    </a>
                </div>
                
                <div class="invoice-table">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice ID</th>
                                    <th>Client & Case</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No invoices found. Create your first invoice to get started.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($recent_invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($invoice['invoice_number'] ?? 'INV-' . $invoice['id']); ?></strong>
                                    </td>
                                    <td>
                                        <div><strong><?php echo e($invoice['client_name']); ?></strong></div>
                                        <div class="text-muted small"><?php echo e($invoice['case_title'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <strong>R<?php echo number_format($invoice['amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $invoice['status']; ?>">
                                            <?php echo ucfirst($invoice['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice('<?php echo $invoice['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="downloadInvoice('<?php echo $invoice['id']; ?>')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="col-lg-4">
            <div class="content-card">
                <h3 class="section-title">
                    <i class="fas fa-gavel"></i>
                    Pending Approvals
                </h3>
                
                <?php if (empty($pending_approvals)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Caught Up!</h5>
                    <p class="text-muted">No pending financial approvals at this time.</p>
                </div>
                <?php else: ?>
                <?php foreach ($pending_approvals as $approval): ?>
                <div class="approval-card">
                    <div class="approval-header">
                        <div class="approval-title">
                            <?php echo ucfirst($approval['type']); ?> Request
                        </div>
                        <div class="approval-amount">
                            R<?php echo number_format($approval['amount'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="approval-details">
                        <div>
                            <strong>Client:</strong> <?php echo e($approval['client_name']); ?>
                        </div>
                        <div>
                            <strong>Requested by:</strong> <?php echo e($approval['requested_by']); ?>
                        </div>
                        <div>
                            <strong>Date:</strong> <?php echo date('M d, Y', strtotime($approval['requested_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Reason:</strong> <?php echo e($approval['reason']); ?>
                    </div>
                    
                    <div class="approval-actions">
                        <button class="btn btn-approve btn-sm" onclick="approveFinancial(<?php echo $approval['id']; ?>, '<?php echo $approval['type']; ?>')">
                            <i class="fas fa-check me-2"></i>Approve
                        </button>
                        <button class="btn btn-reject btn-sm" onclick="rejectFinancial(<?php echo $approval['id']; ?>, '<?php echo $approval['type']; ?>')">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Additional Tools -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-tools"></i>
            Financial Tools & Settings
        </h3>
        <div class="row">
            <div class="col-md-6">
                <h5>Payment Gateway Settings</h5>
                <p class="text-muted">Configure payment processors, merchant accounts, and transaction fees.</p>
                <button class="btn btn-outline-primary" onclick="configurePayments()">
                    <i class="fas fa-cog me-2"></i>Configure Settings
                </button>
            </div>
            <div class="col-md-6">
                <h5>Tax & Compliance</h5>
                <p class="text-muted">Manage tax rates, compliance reporting, and regulatory requirements.</p>
                <button class="btn btn-outline-primary" onclick="manageTax()">
                    <i class="fas fa-calculator me-2"></i>Tax Settings
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/mobile-responsive.js"></script>

<script>
// Store closed cases data from PHP
const closedCasesData = <?php echo json_encode($closed_cases); ?>;

// Invoice Management Functions
function createInvoice() {
    <?php if (!has_permission('invoice:create')): ?>
    showToast('You do not have permission to create invoices.', 'error');
    return;
    <?php endif; ?>
    
    showToast('Create Invoice functionality would open invoice composer', 'info');
}

function viewInvoice(invoiceId) {
    showToast(`Opening invoice ${invoiceId} for viewing`, 'info');
}

function downloadInvoice(invoiceId) {
    showToast(`Downloading invoice ${invoiceId} as PDF`, 'success');
}

function viewAllInvoices() {
    window.location.href = 'invoices/index.php';
}

// Payment Processing Functions
function processPayment() {
    <?php if (!has_permission('payment:process')): ?>
    showToast('You do not have permission to process payments.', 'error');
    return;
    <?php endif; ?>
    
    showToast('Payment processing interface would be launched', 'info');
}

// Financial Approval Functions
function approveFinancial(approvalId, type) {
    <?php if (!has_permission('payment:approve')): ?>
    showToast('You do not have permission to approve financial requests.', 'error');
    return;
    <?php endif; ?>
    
    if (confirm(`Are you sure you want to approve this ${type} request?`)) {
        const card = event.target.closest('.approval-card');
        
        if (card) {
            card.style.opacity = '0.6';
            card.style.pointerEvents = 'none';
        }
        
        setTimeout(() => {
            if (card) {
                card.style.animation = 'slideOut 0.5s ease-out forwards';
                setTimeout(() => card.remove(), 500);
            }
            showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} request approved successfully`, 'success');
        }, 1000);
    }
}

function rejectFinancial(approvalId, type) {
    if (confirm(`Are you sure you want to reject this ${type} request?`)) {
        const card = event.target.closest('.approval-card');
        
        if (card) {
            card.style.opacity = '0.6';
            card.style.pointerEvents = 'none';
        }
        
        setTimeout(() => {
            if (card) {
                card.style.animation = 'slideOut 0.5s ease-out forwards';
                setTimeout(() => card.remove(), 500);
            }
            showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} request rejected`, 'warning');
        }, 1000);
    }
}

// Report Generation
function generateReport() {
    <?php if (!has_permission('report:export')): ?>
    showToast('You do not have permission to export reports.', 'error');
    return;
    <?php endif; ?>
    
    // Show loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    
    // Default to 30 days for financial report
    const days = 30;
    const reportType = 'financial';
    
    // Generate and download the report
    const exportUrl = '../api/export-report.php?type=' + reportType + '&days=' + days + '&format=csv';
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = 'merlaws-report-financial-' + new Date().toISOString().split('T')[0] + '.csv';
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Remove loading overlay after a short delay
    setTimeout(() => {
        if (document.body.contains(loadingOverlay)) {
            document.body.removeChild(loadingOverlay);
        }
        showToast('Financial report downloaded successfully', 'success');
    }, 1000);
}

// Settings Functions
function configurePayments() {
    showToast('Payment gateway configuration panel would be opened', 'info');
}

function manageTax() {
    showToast('Tax management interface would be launched', 'info');
}

// Toast notification system
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

// Animation for slide out effect
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from { 
            opacity: 1; 
            transform: translateX(0); 
            max-height: 300px;
            margin-bottom: 1rem;
        }
        to { 
            opacity: 0; 
            transform: translateX(100%); 
            max-height: 0;
            margin-bottom: 0;
        }
    }
    
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
`;
document.head.appendChild(style);

// Initialize financial dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Animate financial cards on load
    const cards = document.querySelectorAll('.financial-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
    
    // Auto-refresh financial data every 2 minutes
    setInterval(() => {
        console.log('Refreshing financial data...');
    }, 120000);
});

// Compensation Management - UPDATED TO LOAD REAL CLOSED CASES
function manageCompensation() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'compensationModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-hand-holding-usd me-2"></i>Compensation Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="compensationForm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="caseSelect" class="form-label">Select Closed Case *</label>
                                    <input type="text" id="caseSearch" class="form-control mb-2" placeholder="Search cases... (type to filter)">
                                    <select class="form-select" id="caseSelect" required>
                                        <option value="">Choose a closed case...</option>
                                    </select>
                                    <div class="form-text">Only closed cases are available for compensation entry</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="compensationAmount" class="form-label">Compensation Amount (R) *</label>
                                    <input type="number" class="form-control" id="compensationAmount" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client Payment Amount (25%)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R</span>
                                        <input type="text" class="form-control" id="clientPaymentAmount" 
                                               readonly style="background-color: #f8f9fa;">
                                    </div>
                                    <div class="form-text">Automatically calculated as 25% of compensation amount</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="compensationNotes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="compensationNotes" rows="3" 
                                              placeholder="Additional notes about this compensation..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Compensation Process:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Enter the total compensation amount awarded to the client</li>
                                <li>The system will automatically calculate 25% as the client's payment</li>
                                <li>An invoice will be generated for the client payment amount</li>
                                <li>The client will pay 25% of the compensation through the payment system</li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="sendCompensationInvoice()">
                        <i class="fas fa-paper-plane me-2"></i>Send Invoice
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Load closed cases from backend API
    fetchClosedCases();
    
    // Auto-calculate client payment
    document.getElementById('compensationAmount').addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        const clientPayment = amount * 0.25;
        document.getElementById('clientPaymentAmount').value = clientPayment.toFixed(2);
    });
    
    // Clean up modal when closed
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function fetchClosedCases() {
    const caseSelect = document.getElementById('caseSelect');
    const searchInput = document.getElementById('caseSearch');

    caseSelect.innerHTML = '<option value="">Loading closed cases...</option>';

    fetch('./fetch_closed_cases.php?admin=1', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            caseSelect.innerHTML = '<option value="">Choose a closed case...</option>';
            if (!res.success || !Array.isArray(res.cases) || res.cases.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No closed cases available';
                option.disabled = true;
                caseSelect.appendChild(option);
                showToast('No closed cases found. Cases must be closed in Admin before compensation.', 'info');
                return;
            }

            // Populate options
            res.cases.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                opt.setAttribute('data-case-title', item.title || '');
                opt.setAttribute('data-client-name', item.client_name || '');
                caseSelect.appendChild(opt);
            });

            // Wire up live filtering
            searchInput.addEventListener('input', () => {
                const term = searchInput.value.toLowerCase();
                const options = Array.from(caseSelect.options);
                options.forEach((o, idx) => {
                    if (idx === 0) return; // skip placeholder
                    const txt = (o.textContent || '').toLowerCase();
                    o.style.display = txt.includes(term) ? '' : 'none';
                });
            });
        })
        .catch(() => {
            caseSelect.innerHTML = '<option value="">Failed to load cases</option>';
            showToast('Failed to load closed cases', 'error');
        });
}

function sendCompensationInvoice() {
    const caseId = document.getElementById('caseSelect').value;
    const clientPaymentAmount = parseFloat(document.getElementById('clientPaymentAmount').value);

    if (!caseId || !clientPaymentAmount || clientPaymentAmount <= 0) {
        showToast('Please select a case and enter a valid compensation amount first.', 'error');
        return;
    }

    const sendBtn = document.querySelector('#compensationModal .btn-info');
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    sendBtn.disabled = true;

    fetch('./send_compensation_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            case_id: caseId,
            client_payment_amount: clientPaymentAmount
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Invoice notification sent successfully to the client.', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('compensationModal'));
            modal.hide();
        } else {
            showToast('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An unexpected error occurred while sending the notification.', 'error');
    })
    .finally(() => {
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

function submitCompensation() {
    const form = document.getElementById('compensationForm');
    
    const caseId = document.getElementById('caseSelect').value;
    const compensationAmount = parseFloat(document.getElementById('compensationAmount').value);
    const clientPaymentAmount = parseFloat(document.getElementById('clientPaymentAmount').value);
    const notes = document.getElementById('compensationNotes').value;
    
    if (!caseId || !compensationAmount || compensationAmount <= 0) {
        showToast('Please fill in all required fields with valid values', 'error');
        return;
    }
    
    // Validate that client payment is exactly 25%
    const expectedClientPayment = compensationAmount * 0.25;
    if (Math.abs(clientPaymentAmount - expectedClientPayment) > 0.01) {
        showToast('Client payment amount must be exactly 25% of compensation amount', 'error');
        return;
    }
    
    // Get selected case details
    const selectedOption = document.getElementById('caseSelect').selectedOptions[0];
    const caseNumber = selectedOption.getAttribute('data-case-number');
    const clientName = selectedOption.getAttribute('data-client-name');
    const caseTitle = selectedOption.getAttribute('data-case-title');
    
    // Show loading state
    const submitBtn = document.querySelector('#compensationModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
    submitBtn.disabled = true;
    
    // Prepare data for submission
    const compensationData = {
        case_id: caseId,
        case_number: caseNumber,
        client_name: clientName,
        case_title: caseTitle,
        compensation_amount: compensationAmount,
        client_payment_amount: clientPaymentAmount,
        notes: notes
    };
    
    console.log('Submitting compensation entry:', compensationData);
    
    fetch('./save_compensation.php?admin=1', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            case_id: caseId,
            compensation_amount: compensationAmount,
            notes: notes
        })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            const err = res.error || 'Failed to create compensation entry';
            showToast(err, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }

        showToast(`Compensation saved. Invoice ${res.invoice_number} created for R${clientPaymentAmount.toFixed(2)}.`, 'success');
        const modal = bootstrap.Modal.getInstance(document.getElementById('compensationModal'));
        modal.hide();
    })
    .catch(() => {
        showToast('Failed to create compensation entry. Please try again.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + I for new invoice
    if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
        e.preventDefault();
        createInvoice();
    }
    
    // Ctrl/Cmd + P for process payment
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        processPayment();
    }
    
    // Ctrl/Cmd + C for compensation management
    if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
        e.preventDefault();
        manageCompensation();
    }
});
</script>
</body>
</html>