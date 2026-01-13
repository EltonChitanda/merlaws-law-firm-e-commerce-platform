<?php
// app/services/cart.php - Service Cart Management
require __DIR__ . '/../config.php';
require_login();

$user_id = get_user_id();
$user_role = get_user_role();

// Only allow clients to access cart
if ($user_role !== 'client') {
    redirect('/app/dashboard.php');
}

$pdo = db();
$errors = [];
$success = false;

// Handle browse services action
if (isset($_GET['action']) && $_GET['action'] === 'browse_services') {
    // Get user's cases count
    $stmt = $pdo->prepare("SELECT id, title FROM cases WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $all_cases = $stmt->fetchAll();
    $case_count = count($all_cases);
    
    if ($case_count === 0) {
        // No cases - redirect to create case page
        redirect('../cases/create.php');
    } elseif ($case_count === 1) {
        // One case - redirect to catalogue with that case_id
        $case_id = $all_cases[0]['id'];
        redirect("catalogue.php?case_id={$case_id}");
    } else {
        // Multiple cases - redirect to cases page with notification
        $notif_stmt = $pdo->prepare("INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read, created_at) VALUES (?, 'info', ?, ?, ?, 0, NOW())");
        $notif_title = "Select a Case to Browse Services";
        $notif_message = "Please select a case from your case list to browse and request services. Each case has its own service catalogue.";
        $notif_url = "../cases/index.php";
        $notif_stmt->execute([$user_id, $notif_title, $notif_message, $notif_url]);
        
        redirect('../cases/index.php?message=' . urlencode('Please select a case to browse services. Each case has its own service catalogue.'));
    }
}

// Handle form submissions
if (is_post()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'remove_item') {
        $service_request_id = (int)($_POST['service_request_id'] ?? 0);
        
        if ($service_request_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM service_requests WHERE id = ? AND case_id IN (SELECT id FROM cases WHERE user_id = ?) AND status = 'cart'");
            if ($stmt->execute([$service_request_id, $user_id])) {
                $success = 'Service removed from cart successfully.';
            } else {
                $errors[] = 'Failed to remove service from cart.';
            }
        }
    } elseif ($action === 'submit_requests') {
        $case_id = (int)($_POST['case_id'] ?? 0);
        
        if ($case_id > 0) {
            // Update cart items to pending status
            $stmt = $pdo->prepare("UPDATE service_requests SET status = 'pending', requested_at = NOW() WHERE case_id = ? AND status = 'cart'");
            if ($stmt->execute([$case_id])) {
                $success = 'Service requests submitted successfully.';
            } else {
                $errors[] = 'Failed to submit service requests.';
            }
        }
    }
}

// Get user's cases
$stmt = $pdo->prepare("SELECT id, title, status FROM cases WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_cases = $stmt->fetchAll();

// Get cart items grouped by case
$stmt = $pdo->prepare("
    SELECT 
        sr.id,
        sr.case_id,
        sr.service_id,
        sr.quantity,
        sr.notes,
        sr.urgency,
        s.name as service_name,
        s.description as service_description,
        s.category,
        s.estimated_duration,
        c.title as case_title,
        c.status as case_status
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    JOIN cases c ON sr.case_id = c.id
    WHERE sr.status = 'cart'
    AND c.user_id = ?
    ORDER BY c.title, s.name
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Group items by case
$cart_by_case = [];
foreach ($cart_items as $item) {
    $cart_by_case[$item['case_id']][] = $item;
}

// Get selected case filter
$selected_case = $_GET['case_id'] ?? '';
if ($selected_case && !empty($cart_by_case[$selected_case])) {
    $filtered_items = $cart_by_case[$selected_case];
} else {
    $filtered_items = !empty($cart_by_case) ? array_merge(...array_values($cart_by_case)) : [];
}

$page_title = 'Service Cart';
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
            --merlaws-accent: #f7fafc;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
            --merlaws-warning: #ed8936;
            --merlaws-danger: #e53e3e;
            --merlaws-info: #3182ce;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e0;
            --merlaws-gray-400: #a0aec0;
            --merlaws-gray-500: #718096;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-700: #2d3748;
            --merlaws-gray-800: #1a202c;
            --merlaws-gray-900: #171923;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .cart-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Professional Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            border-radius: 24px;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-header-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-right: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Alert Messages */
        .alert-modern {
            border: none;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4f4dd, #a7f3d0);
            border-left: 4px solid var(--merlaws-success);
            color: #065f46;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            border-left: 4px solid var(--merlaws-danger);
            color: #991b1b;
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            margin-bottom: 2rem;
        }

        .filter-card h5 {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin-bottom: 1.5rem;
        }

        /* Cart Summary */
        .cart-summary {
            background: linear-gradient(135deg, #ffffff, #f7fafc);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--merlaws-primary);
            margin-bottom: 2rem;
        }

        .cart-summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--merlaws-gray-200);
        }

        .cart-summary h4 {
            font-family: 'Playfair Display', serif;
            color: var(--merlaws-primary);
            margin: 0;
        }

        .cart-count {
            background: var(--merlaws-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Service Cards */
        .service-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .service-card-header {
            background: linear-gradient(135deg, var(--merlaws-gray-50), white);
            padding: 1.5rem;
            border-bottom: 1px solid var(--merlaws-gray-200);
        }

        .service-title {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }

        .service-badge {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-urgent {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .badge-normal {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #3730a3;
            border: 1px solid #a5b4fc;
        }

        .service-card-body {
            padding: 1.5rem;
            flex: 1;
        }

        .service-description {
            color: var(--merlaws-gray-600);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .service-details {
            background: var(--merlaws-gray-50);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            color: var(--merlaws-primary);
            font-size: 0.875rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--merlaws-gray-700);
            margin-right: 0.5rem;
        }

        .detail-value {
            color: var(--merlaws-gray-600);
        }

        .service-notes {
            background: #fef3c7;
            border-left: 4px solid var(--merlaws-gold);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .service-notes strong {
            color: #92400e;
        }

        .service-card-footer {
            padding: 1.5rem;
            background: var(--merlaws-gray-50);
            border-top: 1px solid var(--merlaws-gray-200);
        }

        /* Empty Cart */
        .empty-cart {
            background: white;
            border-radius: 24px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
        }

        .empty-cart-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--merlaws-gray-100), var(--merlaws-gray-200));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--merlaws-gray-400);
        }

        .empty-cart h4 {
            font-family: 'Playfair Display', serif;
            color: var(--merlaws-gray-700);
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: var(--merlaws-gray-500);
            margin-bottom: 2rem;
        }

        /* Buttons */
        .btn-modern {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(172, 19, 42, 0.3);
        }

        .btn-primary-modern:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
            color: white;
        }

        .btn-outline-modern {
            background: white;
            color: var(--merlaws-primary);
            border: 2px solid var(--merlaws-primary);
        }

        .btn-outline-modern:hover {
            background: var(--merlaws-primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-danger-modern {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
        }

        .btn-danger-modern:hover {
            background: linear-gradient(135deg, #991b1b, #7f1d1d);
            transform: translateY(-2px);
            color: white;
        }

        /* Modal Enhancements */
        .modal-content {
            border-radius: 24px;
            border: none;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-radius: 24px 24px 0 0;
            padding: 1.5rem 2rem;
            border: none;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--merlaws-gray-200);
            background: var(--merlaws-gray-50);
            border-radius: 0 0 24px 24px;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        /* Form Controls */
        .form-select, .form-control {
            border-radius: 12px;
            border: 2px solid var(--merlaws-gray-300);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 0.2rem rgba(172, 19, 42, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--merlaws-gray-700);
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-container {
                padding: 1rem 0.75rem;
            }

            .page-header {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .page-header-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .header-actions .btn-modern {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
                justify-content: center;
            }

            .filter-card,
            .cart-summary {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .filter-card h5 {
                font-size: 1.15rem;
            }

            .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .service-card {
                margin-bottom: 1rem;
            }

            .btn-modern {
                min-height: 48px;
                font-size: 16px;
                padding: 12px 20px;
            }

            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-content {
                border-radius: 16px;
            }

            .form-control,
            .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }
        }

        @media (max-width: 480px) {
            .cart-container {
                padding: 0.75rem 0.5rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .page-header-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .filter-card,
            .cart-summary {
                padding: 1.25rem;
            }

            .service-card {
                padding: 1rem;
            }

            .empty-cart {
                padding: 2rem 1.5rem;
            }

            .empty-cart-icon {
                font-size: 3rem;
            }

            .empty-cart h4 {
                font-size: 1.25rem;
            }
        }

        /* Loading Animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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

    <div class="cart-container">
        <!-- Professional Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="header-left">
                    <div class="page-header-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h1>Service Cart</h1>
                        <p class="mb-0" style="opacity: 0.9;">Review and submit your service requests</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="?action=browse_services" class="btn-modern btn-outline-modern">
                        <i class="fas fa-arrow-left"></i>
                        Continue Shopping
                    </a>
                    <?php if (!empty($filtered_items)): ?>
                        <button type="button" class="btn-modern btn-primary-modern" data-bs-toggle="modal" data-bs-target="#submitModal">
                            <i class="fas fa-paper-plane"></i>
                            Submit Requests
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cart Summary -->
        <?php if (!empty($filtered_items)): ?>
            <div class="cart-summary">
                <div class="cart-summary-header">
                    <h4><i class="fas fa-clipboard-list me-2"></i>Cart Summary</h4>
                    <span class="cart-count"><?= count($filtered_items) ?> Item<?= count($filtered_items) !== 1 ? 's' : '' ?></span>
                </div>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <div class="h3 text-primary mb-1"><?= count($filtered_items) ?></div>
                            <div class="small text-muted">Total Services</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="text-center">
                            <div class="h3 text-danger mb-1"><?= count(array_filter($filtered_items, fn($i) => $i['urgency'] === 'urgent')) ?></div>
                            <div class="small text-muted">Urgent Items</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 text-info mb-1"><?= count($cart_by_case) ?></div>
                            <div class="small text-muted">Case<?= count($cart_by_case) !== 1 ? 's' : '' ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center">
                            <div class="h3 text-success mb-1"><?= array_sum(array_column($filtered_items, 'quantity')) ?></div>
                            <div class="small text-muted">Total Quantity</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Case Filter -->
        <?php if (count($cart_by_case) > 1): ?>
            <div class="filter-card">
                <h5><i class="fas fa-filter me-2"></i>Filter by Case</h5>
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-select" id="caseFilter" onchange="filterByCase()">
                            <option value="">All Cases (<?= count($filtered_items) ?> items)</option>
                            <?php foreach ($cart_by_case as $case_id => $items): ?>
                                <option value="<?= $case_id ?>" <?= $selected_case == $case_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($items[0]['case_title']) ?> (<?= count($items) ?> items)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cart Items -->
        <?php if (empty($filtered_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h4>Your cart is empty</h4>
                <p>Add services to your cart to get started with your legal requests.</p>
                <a href="?action=browse_services" class="btn-modern btn-primary-modern">
                    <i class="fas fa-plus"></i>
                    Browse Services
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($filtered_items as $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="service-card">
                            <div class="service-card-header">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="service-title"><?= htmlspecialchars($item['service_name']) ?></h6>
                                    <span class="service-badge <?= $item['urgency'] === 'urgent' ? 'badge-urgent' : 'badge-normal' ?>">
                                        <?= $item['urgency'] === 'urgent' ? '🔥 Urgent' : '📋 Normal' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="service-card-body">
                                <p class="service-description">
                                    <?= htmlspecialchars($item['service_description']) ?>
                                </p>
                                
                                <div class="service-details">
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <span class="detail-label">Case:</span>
                                        <span class="detail-value"><?= htmlspecialchars($item['case_title']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <span class="detail-label">Category:</span>
                                        <span class="detail-value"><?= htmlspecialchars($item['category']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <span class="detail-label">Duration:</span>
                                        <span class="detail-value"><?= htmlspecialchars($item['estimated_duration']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-hashtag"></i>
                                        </div>
                                        <span class="detail-label">Quantity:</span>
                                        <span class="detail-value"><?= $item['quantity'] ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($item['notes']): ?>
                                    <div class="service-notes">
                                        <strong><i class="fas fa-sticky-note me-1"></i>Notes:</strong>
                                        <div class="mt-1"><?= htmlspecialchars($item['notes']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="service-card-footer">
                                <form method="POST" class="d-inline w-100" onsubmit="return confirmRemove(event, '<?= htmlspecialchars($item['service_name']) ?>')">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="service_request_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn-modern btn-danger-modern w-100">
                                        <i class="fas fa-trash"></i>
                                        Remove from Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Submit Modal -->
    <?php if (!empty($filtered_items)): ?>
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane me-2"></i>Submit Service Requests
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" onsubmit="return confirmSubmit(event)">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="submit_requests">
                        
                        <?php if (count($cart_by_case) > 1): ?>
                            <div class="mb-3">
                                <label for="case_id" class="form-label">
                                    <i class="fas fa-briefcase me-1"></i>Select Case
                                </label>
                                <select class="form-select" name="case_id" id="case_id" required>
                                    <option value="">Choose a case...</option>
                                    <?php foreach ($cart_by_case as $case_id => $items): ?>
                                        <option value="<?= $case_id ?>" <?= $selected_case == $case_id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($items[0]['case_title']) ?> (<?= count($items) ?> items)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>Select which case these services are for
                                </div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="case_id" value="<?= array_key_first($cart_by_case) ?>">
                            <div class="alert alert-info mb-3" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                                <div class="d-flex align-items-center">
                                    <div style="width: 50px; height: 50px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                        <i class="fas fa-briefcase" style="color: var(--merlaws-info); font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <strong style="color: #1e40af;">Case:</strong>
                                        <div style="color: #1e3a8a;"><?= htmlspecialchars($filtered_items[0]['case_title']) ?></div>
                                        <small style="color: #3730a3;">Submitting <?= count($filtered_items) ?> service request<?= count($filtered_items) !== 1 ? 's' : '' ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Summary of items -->
                        <div style="background: var(--merlaws-gray-50); padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
                            <h6 style="color: var(--merlaws-gray-800); margin-bottom: 1rem;">
                                <i class="fas fa-list-check me-1"></i>Summary
                            </h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 text-primary mb-1"><?= count($filtered_items) ?></div>
                                    <small class="text-muted">Services</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-danger mb-1"><?= count(array_filter($filtered_items, fn($i) => $i['urgency'] === 'urgent')) ?></div>
                                    <small class="text-muted">Urgent</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-success mb-1"><?= array_sum(array_column($filtered_items, 'quantity')) ?></div>
                                    <small class="text-muted">Total Qty</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-0" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #fef3c7, #fde68a); border-left: 4px solid var(--merlaws-gold);">
                            <div class="d-flex">
                                <i class="fas fa-info-circle me-2" style="color: #92400e; margin-top: 2px;"></i>
                                <div style="color: #92400e;">
                                    <strong>Important:</strong> Once submitted, these requests will be sent to our legal team for review and approval. You will receive a notification when your requests are processed.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn-modern btn-primary-modern">
                            <i class="fas fa-paper-plane"></i>
                            Submit Requests
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Include Standard Footer -->
    <?php 
    $footerPath = __DIR__ . '/../../include/footer.php';
    if (file_exists($footerPath)) {
        include $footerPath;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced cart interactions
        document.addEventListener('DOMContentLoaded', function() {
            initializeCart();
        });

        function initializeCart() {
            animateCards();
            setupCardHoverEffects();
            initializeAlertAutoDismiss();
        }

        function animateCards() {
            const cards = document.querySelectorAll('.service-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        function setupCardHoverEffects() {
            const cards = document.querySelectorAll('.service-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.zIndex = '1';
                });
            });
        }

        function initializeAlertAutoDismiss() {
            const alerts = document.querySelectorAll('.alert-modern');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        }

        function filterByCase() {
            const caseId = document.getElementById('caseFilter').value;
            const url = new URL(window.location);
            
            if (caseId) {
                url.searchParams.set('case_id', caseId);
            } else {
                url.searchParams.delete('case_id');
            }
            
            // Add loading state
            document.getElementById('caseFilter').disabled = true;
            document.body.style.cursor = 'wait';
            
            window.location.href = url.toString();
        }

        function confirmRemove(event, serviceName) {
            event.preventDefault();
            
            const modal = document.createElement('div');
            modal.innerHTML = `
                <div class="modal fade" id="confirmRemoveModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #991b1b); color: white; border-radius: 24px 24px 0 0;">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Removal
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
                            </div>
                            <div class="modal-body" style="padding: 2rem;">
                                <div class="text-center mb-3">
                                    <div style="width: 80px; height: 80px; margin: 0 auto; border-radius: 50%; background: linear-gradient(135deg, #fee2e2, #fecaca); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-trash" style="font-size: 2rem; color: #dc2626;"></i>
                                    </div>
                                </div>
                                <h5 class="text-center mb-3">Remove Service from Cart?</h5>
                                <p class="text-center text-muted mb-0">
                                    Are you sure you want to remove <strong style="color: var(--merlaws-gray-800);">${serviceName}</strong> from your cart? This action cannot be undone.
                                </p>
                            </div>
                            <div class="modal-footer" style="background: var(--merlaws-gray-50); border-radius: 0 0 24px 24px;">
                                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </button>
                                <button type="button" class="btn-modern btn-danger-modern" onclick="proceedWithRemoval()">
                                    <i class="fas fa-trash"></i>
                                    Yes, Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));
            
            window.proceedWithRemoval = function() {
                confirmModal.hide();
                event.target.submit();
            };
            
            confirmModal.show();
            
            document.getElementById('confirmRemoveModal').addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
            
            return false;
        }

        function confirmSubmit(event) {
            const caseSelect = document.getElementById('case_id');
            if (caseSelect && !caseSelect.value) {
                event.preventDefault();
                
                // Highlight the field
                caseSelect.classList.add('border-danger');
                caseSelect.focus();
                
                setTimeout(() => {
                    caseSelect.classList.remove('border-danger');
                }, 2000);
                
                return false;
            }
            
            // Show loading state
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            
            return true;
        }

        // Smooth scroll to top after form submission
        if (window.location.search.includes('success') || window.location.search.includes('error')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add loading indicator for case filter
        const caseFilter = document.getElementById('caseFilter');
        if (caseFilter) {
            caseFilter.addEventListener('change', function() {
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                `;
                overlay.innerHTML = `
                    <div style="text-align: center;">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div style="margin-top: 1rem; color: var(--merlaws-gray-700); font-weight: 600;">
                            Filtering cart items...
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);
            });
        }
    </script>
</body>
</html>