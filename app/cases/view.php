<?php
// app/cases/view.php
require __DIR__ . '/../config.php';
require_login();

$case_id = (int)($_GET['id'] ?? 0);
if (!$case_id) {
    redirect('index.php');
}

$case = get_case($case_id, get_user_id());
if (!$case) {
    redirect('index.php');
}

$documents = get_case_documents($case_id);
$activities = get_case_activities($case_id);
$cart_items = get_cart_items($case_id);

// Check if service requests can be added to this case
$service_restriction = can_add_service_requests($case);

// Get submitted service requests for this case
$submitted_requests = [];
try {
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT sr.*, s.name as service_name, s.category
        FROM service_requests sr
        JOIN services s ON sr.service_id = s.id
        WHERE sr.case_id = ? AND sr.status = "pending"
        ORDER BY sr.requested_at DESC
    ');
    $stmt->execute([$case_id]);
    $submitted_requests = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($case['title']); ?> | Med Attorneys</title>
    
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
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .case-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: var(--merlaws-primary);
        }
        
        .btn-merlaws {
            background-color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-merlaws:hover {
            background-color: var(--merlaws-primary-dark);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-merlaws {
            background-color: transparent;
            border: 2px solid var(--merlaws-primary);
            color: var(--merlaws-primary);
        }
        
        .btn-outline-merlaws:hover {
            background-color: var(--merlaws-primary);
            color: white;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .document-item:hover {
            border-color: var(--merlaws-primary);
            background-color: rgba(172, 19, 42, 0.02);
        }
        
        .document-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .document-icon {
            width: 40px;
            height: 40px;
            background: var(--merlaws-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .document-details h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .document-details small {
            color: #6c757d;
        }
        
        .activity-item {
            display: flex;
            gap: 1rem;
            padding-bottom: 1rem;
            border-left: 3px solid #e9ecef;
            padding-left: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .activity-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 13px;
            height: 13px;
            background: var(--merlaws-primary);
            border-radius: 50%;
            border: 3px solid white;
        }
        
        .activity-item:last-child {
            border-left-color: transparent;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--merlaws-primary);
            flex-shrink: 0;
        }
        
        .activity-content h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
            color: #333;
        }
        
        .activity-content p {
            margin: 0 0 0.5rem 0;
            color: #666;
        }
        
        .activity-content small {
            color: #6c757d;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }
        
        .service-info h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .service-info small {
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <!-- Include header -->
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Case Header -->
    <div class="case-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2"><?php echo e($case['title']); ?></h1>
                    <div class="d-flex gap-3 align-items-center">
                        <?php echo get_case_status_badge($case['status']); ?>
                        <?php echo get_priority_badge($case['priority']); ?>
                        <span class="badge bg-light text-dark"><?php echo ucfirst(str_replace('_', ' ', $case['case_type'])); ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <?php if ($case['status'] !== 'closed'): ?>
                        <a href="edit.php?id=<?php echo $case['id']; ?>" class="btn btn-light">
                            <i class="fas fa-edit"></i> Edit Case
                        </a>
                        <?php endif; ?>
                        <?php if ($service_restriction['can_add']): ?>
                        <a href="../services/catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-shopping-cart"></i> Add Services
                        </a>
                        <?php else: ?>
                        <button class="btn btn-warning" 
                                disabled
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="<?php echo e($service_restriction['message']); ?>">
                            <i class="fas fa-lock"></i> Add Services
                        </button>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Case Details -->
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Case Details
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Created:</strong> <?php echo date('F d, Y', strtotime($case['created_at'])); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('F d, Y g:i A', strtotime($case['updated_at'])); ?></p>
                            <p><strong>Case Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $case['case_type'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <?php echo get_case_status_badge($case['status']); ?></p>
                            <p><strong>Priority:</strong> <?php echo get_priority_badge($case['priority']); ?></p>
                            <p><strong>Client:</strong> <?php echo e($case['client_name']); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($case['description']): ?>
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="mt-2"><?php echo nl2br(e($case['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Invoice Section Add -->
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-file-invoice-dollar"></i> Invoices
                    </h3>
                    <?php
                    // Fetch invoices for this case
                    $pdo = db();
                    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE case_id = ? ORDER BY created_at DESC');
                    $stmt->execute([$case_id]);
                    $case_invoices = $stmt->fetchAll();
                    ?>
                    <?php if (empty($case_invoices)): ?>
                        <div class="empty-state text-center">
                            <i class="fas fa-file-invoice fa-2x mb-2"></i>
                            <div>No invoices for this case yet.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Amount Due</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($case_invoices as $inv): ?>
                                        <tr>
                                            <td><?php echo e($inv['invoice_number']); ?></td>
                                            <td>R<?php echo number_format($inv['amount'], 2); ?></td>
                                            <td><?php echo ucfirst($inv['status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($inv['due_date'])); ?></td>
                                            <td>
                                                <?php if ($inv['status'] === 'pending' || $inv['status'] === 'draft'): ?>
                                                    <form method="POST" action="../payments/payfast-checkout.php" style="display:inline;">
                                                        <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>" />
                                                        <button class="btn btn-success btn-sm" type="submit">
                                                            <i class="fas fa-money-check-alt"></i> Pay Now
                                                        </button>
                                                    </form>
                                                <?php elseif ($inv['status'] === 'paid'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- END Invoice Section Add -->

                <!-- Service Request Notifications -->
                <?php if (!empty($submitted_requests)): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-bell"></i>
                        Pending Service Requests
                    </h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Service requests submitted!</strong> The following service requests are pending review by our team.
                    </div>
                    <div class="list-group">
                        <?php foreach ($submitted_requests as $request): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo e($request['service_name']); ?></h6>
                                    <p class="mb-1 text-muted"><?php echo e($request['category']); ?></p>
                                    <?php if ($request['notes']): ?>
                                    <small class="text-muted">Notes: <?php echo e($request['notes']); ?></small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-1">
                                        Submitted: <?php echo date('M d, Y g:i A', strtotime($request['requested_at'])); ?>
                                    </small>
                                </div>
                                <span class="badge bg-warning"><?php echo e(ucfirst($request['status'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Documents -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="section-title mb-0">
                            <i class="fas fa-file-alt"></i>
                            Documents (<?php echo count($documents); ?>)
                        </h3>
                        <div class="d-flex gap-2">
                            <button class="btn btn-merlaws btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i> Upload Document
                            </button>
                        </div>
                    </div>
                    
                    <?php if (empty($documents)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h5>No Documents Yet</h5>
                        <p>Upload documents related to your case to keep everything organized.</p>
                        <button class="btn btn-merlaws" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload First Document
                        </button>
                    </div>
                    <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="document-details">
                                <h6><?php echo e($doc['original_filename']); ?></h6>
                                <small>
                                    <?php echo format_file_size($doc['file_size']); ?> • 
                                    Uploaded <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?> by 
                                    <?php echo e($doc['uploaded_by_name']); ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="../documents/view.php?id=<?php echo $doc['id']; ?>" 
                               class="btn btn-outline-merlaws btn-sm" 
                               target="_blank"
                               title="View Document">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="../documents/download.php?id=<?php echo $doc['id']; ?>" 
                               class="btn btn-outline-merlaws btn-sm"
                               title="Download Document">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Service Requests -->
                <div class="content-card" id="service-requests">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="section-title mb-0">
                            <i class="fas fa-clipboard-list"></i>
                            Service Requests
                        </h3>
                        <a href="service-requests.php?case_id=<?php echo $case_id; ?>" class="btn btn-merlaws btn-sm">
                            <i class="fas fa-eye"></i> View All Requests
                        </a>
                    </div>
                    
                    <?php
                    // Get service requests count
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as total,
                               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                               SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                               SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                        FROM service_requests
                        WHERE case_id = ? AND status != 'cart'
                    ");
                    $stmt->execute([$case_id]);
                    $request_stats = $stmt->fetch();
                    ?>
                    
                    <?php if ($request_stats['total'] == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h5>No Service Requests Yet</h5>
                        <p>You haven't submitted any service requests for this case.</p>
                        <?php if ($service_restriction['can_add']): ?>
                        <a href="../services/catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-merlaws">
                            <i class="fas fa-plus"></i> Request a Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-1 text-primary"><?php echo $request_stats['total']; ?></div>
                                <div class="small text-muted">Total Requests</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <div class="h3 mb-1 text-warning"><?php echo $request_stats['pending']; ?></div>
                                <div class="small text-muted">Pending</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <div class="h3 mb-1 text-success"><?php echo $request_stats['approved']; ?></div>
                                <div class="small text-muted">Approved</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <div class="h3 mb-1 text-danger"><?php echo $request_stats['rejected']; ?></div>
                                <div class="small text-muted">Rejected</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="service-requests.php?case_id=<?php echo $case_id; ?>" class="btn btn-merlaws">
                            <i class="fas fa-eye me-2"></i>View All Service Requests & Details
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Activity Timeline -->
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-clock"></i>
                        Activity Timeline
                    </h3>
                    
                    <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <h5>No Activity Yet</h5>
                        <p>Case activity will appear here as actions are taken.</p>
                    </div>
                    <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                $icons = [
                                    'note' => 'fas fa-sticky-note',
                                    'status_change' => 'fas fa-exchange-alt',
                                    'document_upload' => 'fas fa-upload',
                                    'service_request' => 'fas fa-shopping-cart',
                                    'admin_action' => 'fas fa-user-shield'
                                ];
                                ?>
                                <i class="<?php echo $icons[$activity['activity_type']] ?? 'fas fa-circle'; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <h6><?php echo e($activity['title']); ?></h6>
                                <?php if ($activity['description']): ?>
                                <p><?php echo e($activity['description']); ?></p>
                                <?php endif; ?>
                                <small>
                                    <i class="fas fa-user"></i> <?php echo e($activity['user_name']); ?> • 
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Service Cart -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="section-title mb-0">
                            <i class="fas fa-shopping-cart"></i>
                            Service Cart (<?php echo count($cart_items); ?>)
                        </h4>
                        <?php if (!empty($cart_items)): ?>
                        <a href="../services/cart.php?case_id=<?php echo $case['id']; ?>" class="btn btn-merlaws btn-sm">
                            View Cart
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($cart_items)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h6>No Services in Cart</h6>
                        <p>Browse our service catalogue to add services to your case.</p>
                        <?php if ($service_restriction['can_add']): ?>
                        <a href="../services/catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-merlaws btn-sm">
                            <i class="fas fa-plus"></i> Browse Services
                        </a>
                        <?php else: ?>
                        <button class="btn btn-merlaws btn-sm" 
                                disabled
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="<?php echo e($service_restriction['message']); ?>">
                            <i class="fas fa-lock"></i> Browse Services
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($cart_items, 0, 3) as $item): ?>
                    <div class="cart-item">
                        <div class="service-info">
                            <h6><?php echo e($item['name']); ?></h6>
                            <small><?php echo e($item['category']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($cart_items) > 3): ?>
                    <p class="text-center mb-0">
                        <small>And <?php echo count($cart_items) - 3; ?> more...</small>
                    </p>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="../services/cart.php?case_id=<?php echo $case['id']; ?>" class="btn btn-merlaws">
                            <i class="fas fa-eye"></i> View Full Cart
                        </a>
                        <?php if ($service_restriction['can_add']): ?>
                        <a href="../services/catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-outline-merlaws">
                            <i class="fas fa-plus"></i> Add More Services
                        </a>
                        <?php else: ?>
                        <button class="btn btn-outline-merlaws" 
                                disabled
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="<?php echo e($service_restriction['message']); ?>">
                            <i class="fas fa-lock"></i> Add More Services
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="content-card">
                    <h4 class="section-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h4>
                    
                    <div class="d-grid gap-2">
                        <?php if ($case['status'] !== 'closed'): ?>
                        <a href="edit.php?id=<?php echo $case['id']; ?>" class="btn btn-outline-merlaws">
                            <i class="fas fa-edit"></i> Edit Case Details
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-merlaws" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload Document
                        </button>
                        
                        <?php if ($service_restriction['can_add']): ?>
                        <a href="../services/catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-outline-merlaws">
                            <i class="fas fa-plus"></i> Request Services
                        </a>
                        <?php else: ?>
                        <button class="btn btn-outline-merlaws" 
                                disabled
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="<?php echo e($service_restriction['message']); ?>">
                            <i class="fas fa-lock"></i> Request Services
                        </button>
                        <?php endif; ?>
                        
                        <a href="/contact-us.php" class="btn btn-outline-merlaws">
                            <i class="fas fa-phone"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="upload-document.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="document" name="document" required>
                            <div class="form-text">Allowed types: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type</label>
                            <select class="form-select" id="document_type" name="document_type">
                                <option value="">Select type...</option>
                                <option value="medical_record">Medical Record</option>
                                <option value="legal_document">Legal Document</option>
                                <option value="correspondence">Correspondence</option>
                                <option value="evidence">Evidence</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of this document..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-merlaws">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php 
    $footerPath = __DIR__ . '/../../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-scroll to service-requests section if hash is present
            function scrollToServiceRequests() {
                const hash = window.location.hash;
                if (hash === '#service-requests') {
                    const element = document.getElementById('service-requests');
                    if (element) {
                        // Use scrollIntoView with smooth behavior
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // Add offset for fixed headers after a short delay
                        setTimeout(function() {
                            window.scrollBy(0, -100);
                        }, 300);
                        return true;
                    }
                }
                return false;
            }
            
            // Try immediately
            if (!scrollToServiceRequests()) {
                // If element not found, try again after a short delay
                setTimeout(function() {
                    scrollToServiceRequests();
                }, 200);
            }
        });
        
        // Also handle hash changes (in case user navigates with hash)
        window.addEventListener('hashchange', function() {
            if (window.location.hash === '#service-requests') {
                setTimeout(function() {
                    const element = document.getElementById('service-requests');
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        setTimeout(function() {
                            window.scrollBy(0, -100);
                        }, 300);
                    }
                }, 100);
            }
        });
        
        async function clientApptCall(action, payload){
        const form = new URLSearchParams();
        form.set('action', action);
        for (const k in payload) form.set(k, payload[k]);
        const csrf = document.getElementById('_csrf');
        if (csrf) form.set('csrf_token', csrf.value);
        const res = await fetch('../api/appointments.php', { method:'POST', body: form, credentials:'same-origin' });
        const j = await res.json();
        if (!j.success) { alert(j.error || 'Operation failed'); return; }
        location.reload();
    }
    </script>
    <script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>