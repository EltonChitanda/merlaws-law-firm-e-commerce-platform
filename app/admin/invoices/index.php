<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../services/InvoiceService.php';

// Check permissions
if (!has_permission('invoice:view') && !has_permission('invoice:create')) {
    require_permission('invoice:view');
}

$invoice_service = new InvoiceService();

// Get filters from request
$filters = [
    'status' => $_GET['status'] ?? '',
    'client_id' => $_GET['client_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? '',
    'limit' => 50
];

// Get invoices - try InvoiceService first, fallback to direct query
$pdo = db();
$user_role = get_user_role();
$user_id = get_user_id();
$invoices = [];

try {
    $invoices = $invoice_service->getInvoices($filters);
    // Ensure all invoices have required fields
    foreach ($invoices as &$inv) {
        $inv['invoice_number'] = $inv['invoice_number'] ?? 'INV-' . ($inv['id'] ?? 'N/A');
        $inv['client_name'] = $inv['client_name'] ?? 'Unknown Client';
        $inv['client_email'] = $inv['client_email'] ?? '';
        $inv['case_title'] = $inv['case_title'] ?? null;
        $inv['total_amount'] = $inv['total_amount'] ?? $inv['amount'] ?? 0;
        $inv['total_paid'] = $inv['total_paid'] ?? 0;
        $inv['due_date'] = $inv['due_date'] ?? null;
        $inv['status'] = $inv['status'] ?? 'draft';
    }
    unset($inv);
} catch (Exception $e) {
    // InvoiceService failed, use direct query
    $invoices = [];
}

// Always use direct query to ensure we get real data with proper joins
// If InvoiceService returned empty or failed, use direct query
if (empty($invoices)) {
    // Direct query with proper joins to get real client and case data
    $sql = "SELECT i.*, 
                   COALESCE(u.name, u2.name, 'Unknown Client') as client_name, 
                   COALESCE(u.email, u2.email, '') as client_email,
                   COALESCE(c.title, 'No Case') as case_title,
                   c.id as case_id,
                   COALESCE((SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id), 0) as total_paid,
                   COALESCE(i.total_amount, i.amount, 0) as total_amount
            FROM invoices i
            LEFT JOIN users u ON i.client_id = u.id
            LEFT JOIN cases c ON i.case_id = c.id
            LEFT JOIN users u2 ON c.user_id = u2.id
            WHERE 1=1";
    
    $params = [];
    
    // Role-based filtering
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $sql .= " AND (c.assigned_to = ? OR i.assigned_to = ?)";
        $params[] = $user_id;
        $params[] = $user_id;
    }
    
    // Apply filters
    if (!empty($filters['status'])) {
        $sql .= " AND i.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['client_id'])) {
        $sql .= " AND (i.client_id = ? OR c.user_id = ?)";
        $params[] = $filters['client_id'];
        $params[] = $filters['client_id'];
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND (i.invoice_date >= ? OR i.created_at >= ?)";
        $params[] = $filters['date_from'];
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND (i.invoice_date <= ? OR i.created_at <= ?)";
        $params[] = $filters['date_to'];
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (i.invoice_number LIKE ? OR CAST(i.id AS CHAR) LIKE ? OR u.name LIKE ? OR c.title LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " ORDER BY COALESCE(i.created_at, i.invoice_date, NOW()) DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();
    } catch (Exception $e2) {
        // Last resort: simple query without filters
        try {
            $invoices = $pdo->query("SELECT i.*, 
                                           COALESCE(u.name, u2.name, 'Unknown Client') as client_name,
                                           COALESCE(u.email, u2.email, '') as client_email,
                                           COALESCE(c.title, 'No Case') as case_title,
                                           c.id as case_id,
                                           COALESCE((SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id), 0) as total_paid,
                                           COALESCE(i.total_amount, i.amount, 0) as total_amount
                                    FROM invoices i
                                    LEFT JOIN users u ON i.client_id = u.id
                                    LEFT JOIN cases c ON i.case_id = c.id
                                    LEFT JOIN users u2 ON c.user_id = u2.id
                                    ORDER BY COALESCE(i.created_at, i.invoice_date, NOW()) DESC LIMIT 100")->fetchAll();
        } catch (Exception $e3) {
            // Even simpler query - but still try to get client/case info
            try {
                $invoices = $pdo->query("SELECT i.*, 
                                               COALESCE(u.name, u2.name, 'Unknown Client') as client_name,
                                               COALESCE(u.email, u2.email, '') as client_email,
                                               COALESCE(c.title, 'No Case') as case_title,
                                               COALESCE(i.total_amount, i.amount, 0) as total_amount,
                                               0 as total_paid
                                        FROM invoices i
                                        LEFT JOIN users u ON i.client_id = u.id
                                        LEFT JOIN cases c ON i.case_id = c.id
                                        LEFT JOIN users u2 ON c.user_id = u2.id
                                        ORDER BY COALESCE(i.created_at, i.invoice_date, NOW()) DESC LIMIT 100")->fetchAll();
                // Ensure all fields exist
                foreach ($invoices as &$inv) {
                    $inv['client_name'] = $inv['client_name'] ?? 'Unknown Client';
                    $inv['client_email'] = $inv['client_email'] ?? '';
                    $inv['case_title'] = $inv['case_title'] ?? 'No Case';
                    $inv['total_paid'] = $inv['total_paid'] ?? 0;
                    $inv['total_amount'] = $inv['total_amount'] ?? $inv['amount'] ?? 0;
                }
                unset($inv);
            } catch (Exception $e4) {
                $invoices = [];
            }
        }
    }
    
    // Ensure all invoices have required fields and fetch missing client/case data
    foreach ($invoices as &$inv) {
        $inv['invoice_number'] = $inv['invoice_number'] ?? 'INV-' . ($inv['id'] ?? 'N/A');
        $inv['total_amount'] = $inv['total_amount'] ?? $inv['amount'] ?? 0;
        $inv['total_paid'] = $inv['total_paid'] ?? 0;
        $inv['due_date'] = $inv['due_date'] ?? null;
        $inv['status'] = $inv['status'] ?? 'draft';
        
        // If client_name is missing or 'Unknown Client', try to fetch it
        if (empty($inv['client_name']) || $inv['client_name'] === 'Unknown Client') {
            if (!empty($inv['client_id'])) {
                try {
                    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
                    $stmt->execute([$inv['client_id']]);
                    $client = $stmt->fetch();
                    if ($client) {
                        $inv['client_name'] = $client['name'];
                        $inv['client_email'] = $client['email'];
                    }
                } catch (Exception $e) {}
            }
            // If still no client, try getting from case
            if (($inv['client_name'] === 'Unknown Client' || empty($inv['client_name'])) && !empty($inv['case_id'])) {
                try {
                    $stmt = $pdo->prepare("SELECT u.name, u.email FROM cases c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
                    $stmt->execute([$inv['case_id']]);
                    $client = $stmt->fetch();
                    if ($client) {
                        $inv['client_name'] = $client['name'];
                        $inv['client_email'] = $client['email'];
                    }
                } catch (Exception $e) {}
            }
        }
        
        // If case_title is missing, try to fetch it
        if (empty($inv['case_title']) || $inv['case_title'] === 'No Case') {
            if (!empty($inv['case_id'])) {
                try {
                    $stmt = $pdo->prepare("SELECT title FROM cases WHERE id = ?");
                    $stmt->execute([$inv['case_id']]);
                    $case = $stmt->fetch();
                    if ($case && !empty($case['title'])) {
                        $inv['case_title'] = $case['title'];
                    }
                } catch (Exception $e) {}
            }
        }
        
        // Final fallbacks
        $inv['client_name'] = $inv['client_name'] ?? 'Unknown Client';
        $inv['client_email'] = $inv['client_email'] ?? '';
        $inv['case_title'] = $inv['case_title'] ?? 'No Case';
    }
    unset($inv);
}

// Get statistics
$stats = $invoice_service->getInvoiceStats();

// Get clients for filter dropdown
$clients = $invoice_service->getClients();

// Get status options
$status_options = [
    'draft' => 'Draft',
    'sent' => 'Sent',
    'paid' => 'Paid',
    'overdue' => 'Overdue',
    'cancelled' => 'Cancelled',
    'void' => 'Void'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Management | Med Attorneys Admin</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../../favicon/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/default.css">
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

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-dark) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
        }

        .stat-card.outstanding::before { background: linear-gradient(90deg, var(--warning-orange), #d97706); }
        .stat-card.overdue::before { background: linear-gradient(90deg, var(--danger-red), #dc2626); }
        .stat-card.paid::before { background: linear-gradient(90deg, var(--success-green), #059669); }
        .stat-card.total::before { background: linear-gradient(90deg, var(--revenue-purple), #7c3aed); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .stat-amount {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0;
            line-height: 1;
        }

        .stat-amount.outstanding { color: var(--warning-orange); }
        .stat-amount.overdue { color: var(--danger-red); }
        .stat-amount.paid { color: var(--success-green); }
        .stat-amount.total { color: var(--revenue-purple); }

        .stat-label {
            color: var(--neutral-gray);
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.outstanding { background: linear-gradient(135deg, var(--warning-orange), #d97706); }
        .stat-icon.overdue { background: linear-gradient(135deg, var(--danger-red), #dc2626); }
        .stat-icon.paid { background: linear-gradient(135deg, var(--success-green), #059669); }
        .stat-icon.total { background: linear-gradient(135deg, var(--revenue-purple), #7c3aed); }

        .filters-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .invoices-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
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

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-draft {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.2));
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .status-sent {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.2), rgba(6, 182, 212, 0.2));
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }

        .status-paid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: var(--success-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-overdue {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: var(--danger-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-cancelled {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.2));
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .status-void {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.2));
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
                margin-bottom: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="admin-badge">
            <i class="fas fa-file-invoice-dollar"></i>
            Invoice Management System
        </div>
        <h1 class="page-title">Invoice Management</h1>
        <p class="page-subtitle">Create, manage, and track client invoices with integrated payment processing</p>
    </div>
</div>

<div class="container my-4">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card outstanding">
            <div class="stat-header">
                <div>
                    <h2 class="stat-amount outstanding">R<?php echo number_format($stats['total_outstanding'], 0); ?></h2>
                    <div class="stat-label">Outstanding</div>
                </div>
                <div class="stat-icon outstanding">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card overdue">
            <div class="stat-header">
                <div>
                    <h2 class="stat-amount overdue">R<?php echo number_format($stats['overdue_amount'], 0); ?></h2>
                    <div class="stat-label">Overdue</div>
                </div>
                <div class="stat-icon overdue">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card paid">
            <div class="stat-header">
                <div>
                    <h2 class="stat-amount paid">R<?php echo number_format($stats['paid_this_month'], 0); ?></h2>
                    <div class="stat-label">Paid This Month</div>
                </div>
                <div class="stat-icon paid">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card total">
            <div class="stat-header">
                <div>
                    <h2 class="stat-amount total"><?php echo number_format($stats['total_invoices']); ?></h2>
                    <div class="stat-label">Total Invoices</div>
                </div>
                <div class="stat-icon total">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <?php if (has_permission('invoice:create')): ?>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Invoice
        </a>
        <?php endif; ?>
        
        <button class="btn btn-outline-secondary" onclick="exportInvoices()">
            <i class="fas fa-download me-2"></i>Export
        </button>
        
        <button class="btn btn-outline-secondary" onclick="refreshData()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($status_options as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="client_id" class="form-label">Client</label>
                <select class="form-select" id="client_id" name="client_id">
                    <option value="">All Clients</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['id']; ?>" <?php echo $filters['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                        <?php echo e($client['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e($filters['date_from']); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e($filters['date_to']); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Invoice #, Client..." value="<?php echo e($filters['search']); ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="index.php" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="invoices-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Case</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td>
                            <strong><?php echo e($invoice['invoice_number'] ?? 'INV-' . ($invoice['id'] ?? 'N/A')); ?></strong>
                        </td>
                        <td>
                            <div><strong><?php echo e($invoice['client_name'] ?? 'Unknown Client'); ?></strong></div>
                            <div class="text-muted small"><?php echo e($invoice['client_email'] ?? ''); ?></div>
                        </td>
                        <td>
                            <?php if (!empty($invoice['case_title'])): ?>
                            <div class="text-muted small"><?php echo e($invoice['case_title']); ?></div>
                            <?php else: ?>
                            <span class="text-muted">No case</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>R<?php echo number_format($invoice['total_amount'] ?? $invoice['amount'] ?? 0, 2); ?></strong>
                            <?php if (($invoice['total_paid'] ?? 0) > 0): ?>
                            <div class="text-success small">Paid: R<?php echo number_format($invoice['total_paid'], 2); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $invoice['status']; ?>">
                                <?php echo ucfirst($invoice['status']); ?>
                            </span>
                        </td>
                        <td><?php echo !empty($invoice['due_date']) ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (has_permission('invoice:edit') && in_array($invoice['status'], ['draft', 'sent'])): ?>
                                <a href="edit.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (has_permission('invoice:pdf')): ?>
                                <a href="pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-info" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($invoice['status'] === 'sent' && has_permission('invoice:send')): ?>
                                <button class="btn btn-sm btn-outline-warning" onclick="sendInvoice(<?php echo $invoice['id']; ?>)" title="Send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <h5>No invoices found</h5>
                            <p class="text-muted">Try adjusting your filters or create a new invoice.</p>
                            <?php if (has_permission('invoice:create')): ?>
                            <a href="create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Invoice
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function sendInvoice(invoiceId) {
    if (confirm('Are you sure you want to send this invoice to the client?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Make AJAX request
        fetch('send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ invoice_id: invoiceId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Invoice sent successfully', 'success');
                // Reload page to update status
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to send invoice: ' + data.error, 'error');
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
        })
        .catch(error => {
            showToast('Error sending invoice: ' + error.message, 'error');
            button.innerHTML = originalHTML;
            button.disabled = false;
        });
    }
}

function exportInvoices() {
    showToast('Export functionality would be implemented here', 'info');
}

function refreshData() {
    location.reload();
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
</script>
</body>
</html>
