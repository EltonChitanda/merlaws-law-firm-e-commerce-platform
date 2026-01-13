<?php
// app/cases/service-requests.php - Client Service Requests View
require __DIR__ . '/../config.php';
require_login();

$case_id = (int)($_GET['case_id'] ?? 0);
if (!$case_id) {
    redirect('index.php');
}

$case = get_case($case_id, get_user_id());
if (!$case) {
    redirect('index.php');
}

$pdo = db();

// Get all service requests for this case (excluding cart items)
$stmt = $pdo->prepare("
    SELECT sr.*, 
           s.name as service_name, 
           s.category,
           s.description as service_description,
           u.name as processed_by_name
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    LEFT JOIN users u ON sr.processed_by = u.id
    WHERE sr.case_id = ? AND sr.status != 'cart'
    ORDER BY sr.created_at DESC
");
$stmt->execute([$case_id]);
$service_requests = $stmt->fetchAll();

// Count by status
$status_counts = [
    'pending' => count(array_filter($service_requests, fn($r) => $r['status'] === 'pending')),
    'approved' => count(array_filter($service_requests, fn($r) => $r['status'] === 'approved')),
    'rejected' => count(array_filter($service_requests, fn($r) => $r['status'] === 'rejected'))
];

$status_filter = $_GET['status'] ?? 'all';
if ($status_filter !== 'all') {
    $service_requests = array_filter($service_requests, fn($r) => $r['status'] === $status_filter);
}

$page_title = 'Service Requests - ' . e($case['title']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?> | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-success: #38a169;
            --merlaws-warning: #ed8936;
            --merlaws-danger: #e53e3e;
            --merlaws-info: #3182ce;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e0;
            --merlaws-gray-500: #718096;
            --merlaws-gray-700: #2d3748;
            --merlaws-gray-800: #1a202c;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            border-radius: 24px;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-color: var(--merlaws-primary);
        }

        .filter-btn:not(.active) {
            background: white;
            color: var(--merlaws-gray-700);
            border-color: var(--merlaws-gray-300);
        }

        .filter-btn:not(.active):hover {
            border-color: var(--merlaws-primary);
            color: var(--merlaws-primary);
        }

        .request-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
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
        }

        .request-card.status-pending::before { background: var(--merlaws-warning); }
        .request-card.status-approved::before { background: var(--merlaws-success); }
        .request-card.status-rejected::before { background: var(--merlaws-danger); }

        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
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
            color: var(--merlaws-gray-800);
            margin: 0;
            flex: 1;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, rgba(237, 137, 54, 0.2), rgba(237, 137, 54, 0.1));
            color: #c05621;
            border: 1px solid rgba(237, 137, 54, 0.3);
        }

        .status-badge.approved {
            background: linear-gradient(135deg, rgba(56, 161, 105, 0.2), rgba(56, 161, 105, 0.1));
            color: #22543d;
            border: 1px solid rgba(56, 161, 105, 0.3);
        }

        .status-badge.rejected {
            background: linear-gradient(135deg, rgba(229, 62, 62, 0.2), rgba(229, 62, 62, 0.1));
            color: #c53030;
            border: 1px solid rgba(229, 62, 62, 0.3);
        }

        .request-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--merlaws-gray-700);
            font-size: 0.95rem;
        }

        .meta-item i {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--merlaws-gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--merlaws-primary);
        }

        .admin-notes-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0.05));
            border-left: 4px solid var(--merlaws-info);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .admin-notes-section h6 {
            color: var(--merlaws-info);
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .client-notes-section {
            background: var(--merlaws-gray-50);
            border-left: 4px solid var(--merlaws-gray-300);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--merlaws-gray-300);
            margin-bottom: 2rem;
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
            padding: 1.5rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
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
        }

        .stat-card.pending::before { background: var(--merlaws-warning); }
        .stat-card.approved::before { background: var(--merlaws-success); }
        .stat-card.rejected::before { background: var(--merlaws-danger); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-card.pending .stat-number { color: var(--merlaws-warning); }
        .stat-card.approved .stat-number { color: var(--merlaws-success); }
        .stat-card.rejected .stat-number { color: var(--merlaws-danger); }

        .stat-label {
            color: var(--merlaws-gray-500);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem 0.75rem;
            }

            .page-header {
                padding: 1.5rem;
                border-radius: 16px;
                margin-bottom: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .filter-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }

            .filter-btn {
                width: 100%;
                justify-content: center;
                min-height: 44px;
                padding: 0.875rem 1.25rem;
            }

            .request-card {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .request-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .service-title {
                font-size: 1.15rem;
            }

            .request-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .meta-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0.75rem 0.5rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-number {
                font-size: 1.75rem;
            }

            .request-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Standard Header -->
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        include $headerPath;
    }
    ?>

    <div class="container" style="max-width: 1200px; padding: 2rem 1rem;">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h1>
                            <i class="fas fa-clipboard-list me-2"></i>
                            Service Requests
                        </h1>
                        <p class="mb-0" style="opacity: 0.9;">Case: <?= htmlspecialchars($case['title']) ?></p>
                    </div>
                    <a href="view.php?id=<?= $case_id ?>" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Case
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-number"><?= $status_counts['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?= $status_counts['approved'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?= $status_counts['rejected'] ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?case_id=<?= $case_id ?>&status=all" 
               class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                All Requests (<?= count($service_requests) ?>)
            </a>
            <a href="?case_id=<?= $case_id ?>&status=pending" 
               class="filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i>
                Pending (<?= $status_counts['pending'] ?>)
            </a>
            <a href="?case_id=<?= $case_id ?>&status=approved" 
               class="filter-btn <?= $status_filter === 'approved' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i>
                Approved (<?= $status_counts['approved'] ?>)
            </a>
            <a href="?case_id=<?= $case_id ?>&status=rejected" 
               class="filter-btn <?= $status_filter === 'rejected' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i>
                Rejected (<?= $status_counts['rejected'] ?>)
            </a>
        </div>

        <!-- Service Requests List -->
        <?php if (empty($service_requests)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h4>No Service Requests Found</h4>
                <p class="text-muted">No service requests match the current filter criteria.</p>
                <a href="../services/catalogue.php?case_id=<?= $case_id ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>
                    Request a Service
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($service_requests as $request): ?>
            <div class="request-card status-<?= $request['status'] ?>">
                <div class="request-header">
                    <h3 class="service-title"><?= htmlspecialchars($request['service_name']) ?></h3>
                    <span class="status-badge <?= $request['status'] ?>">
                        <?= ucfirst($request['status']) ?>
                    </span>
                </div>

                <div class="request-meta">
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <strong>Category:</strong> <?= htmlspecialchars($request['category'] ?? 'General') ?>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Requested:</strong> <?= date('M d, Y g:i A', strtotime($request['created_at'])) ?>
                        </div>
                    </div>
                    <?php if ($request['processed_at']): ?>
                    <div class="meta-item">
                        <i class="fas fa-check-double"></i>
                        <div>
                            <strong>Processed:</strong> <?= date('M d, Y g:i A', strtotime($request['processed_at'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['processed_by_name']): ?>
                    <div class="meta-item">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <strong>Processed By:</strong> <?= htmlspecialchars($request['processed_by_name']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['urgency'] === 'urgent'): ?>
                    <div class="meta-item">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <div>
                            <strong class="text-danger">Urgent Request</strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($request['notes']): ?>
                <div class="client-notes-section">
                    <h6>
                        <i class="fas fa-sticky-note"></i>
                        Your Notes
                    </h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($request['notes'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($request['admin_notes']): ?>
                <div class="admin-notes-section">
                    <h6>
                        <i class="fas fa-shield-alt"></i>
                        <?= $request['status'] === 'approved' ? 'Approval Notes' : ($request['status'] === 'rejected' ? 'Rejection Reason' : 'Admin Notes') ?>
                    </h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($request['admin_notes'])) ?></p>
                </div>
                <?php elseif ($request['status'] === 'rejected'): ?>
                <div class="admin-notes-section">
                    <h6>
                        <i class="fas fa-info-circle"></i>
                        Rejection Reason
                    </h6>
                    <p class="mb-0 text-muted">No specific reason provided for rejection.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Include Standard Footer -->
    <?php 
    $footerPath = __DIR__ . '/../../include/footer.php';
    if (file_exists($footerPath)) {
        include $footerPath;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

