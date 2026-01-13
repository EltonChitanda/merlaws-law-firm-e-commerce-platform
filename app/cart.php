<?php
// app/cart.php
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';
require_login();

$case_id = (int)($_GET['case_id'] ?? 0);
if (!$case_id) {
    redirect('../cases/index.php');
}

$case = get_case($case_id, get_user_id());
if (!$case) {
    redirect('../cases/index.php');
}

$cart_items = get_cart_items($case_id);
$errors = [];
$success = false;
$success_message = '';
$submitted_requests = [];

// Handle cart submission
if (is_post() && isset($_POST['submit_cart'])) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }
    
    if (empty($cart_items)) {
        $errors[] = 'Your cart is empty. Please add services before submitting.';
    }
    
    if (!$errors) {
        try {
            $pdo = db();
            
            // Check for duplicate open requests before submission
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare('
                    SELECT COUNT(*) 
                    FROM service_requests 
                    WHERE case_id = ? 
                    AND service_id = ? 
                    AND status NOT IN ("resolved", "cancelled", "rejected")
                ');
                $stmt->execute([$case_id, $item['service_id']]);
                $duplicate_count = $stmt->fetchColumn();
                
                if ($duplicate_count > 0) {
                    $errors[] = "You already have an open request for '{$item['name']}' on this case. Please wait for the existing request to be resolved.";
                }
            }
            
            // Validate consultation services have date/time
            foreach ($cart_items as $item) {
                if ($item['category'] === 'consultation') {
                    if (empty($item['consult_date']) || empty($item['consult_time'])) {
                        $errors[] = "Consultation date and time are required for '{$item['name']}'. Please remove and re-add this service with the required scheduling information.";
                    } else {
                        // Validate consultation is not in the past
                        $consult_datetime = $item['consult_date'] . ' ' . $item['consult_time'];
                        if (strtotime($consult_datetime) < time()) {
                            $errors[] = "Consultation date and time for '{$item['name']}' cannot be in the past. Please update the scheduling information.";
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Error validating service requests. Please try again.';
        }
    }
    
    if (!$errors) {
        try {
            $success = submit_service_requests($case_id);
            if ($success) {
                $success_message = 'Service requests submitted successfully! We will review and respond soon.';
                // Don't redirect - stay on cart page to show confirmation
                $submitted_requests = get_submitted_requests($case_id);
            } else {
                $errors[] = 'Failed to submit service requests. Please try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'Failed to submit service requests. Please try again.';
        }
    }
}

// Function to get submitted requests for display
function get_submitted_requests($case_id) {
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
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Cart - <?php echo e($case['title']); ?> | Med Attorneys</title>
    
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
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .cart-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        
        .cart-item {
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            border-color: var(--merlaws-primary);
            background-color: rgba(172, 19, 42, 0.02);
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .service-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .service-category {
            background: var(--merlaws-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .service-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .service-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .btn-remove {
            background: none;
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-remove:hover {
            background: #dc3545;
            color: white;
        }
        
        .btn-merlaws {
            background-color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
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
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
        }
        
        .btn-outline-merlaws:hover {
            background-color: var(--merlaws-primary);
            color: white;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .cart-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-total {
            font-weight: 600;
            font-size: 1.125rem;
            padding-top: 0.5rem;
            border-top: 1px solid #dee2e6;
        }
        
        .notes-section {
            margin-top: 1rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 0.2rem rgba(172, 19, 42, 0.25);
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header .row {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
                justify-content: center;
            }

            .cart-card {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }

            .cart-item {
               	padding: 1.25rem;
               	margin-bottom: 1rem;
            }

            .service-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .service-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-remove {
                width: 100%;
                min-height: 44px;
                justify-content: center;
            }

            .cart-summary {
                padding: 1.25rem;
            }

            .form-control {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .btn-merlaws,
            .btn-outline-merlaws {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
            }

            .empty-cart {
                padding: 2rem 1.5rem;
            }

            .empty-cart i {
                font-size: 3rem;
            }

            .empty-cart h3 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 1.25rem 0;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .cart-card {
                padding: 1rem;
            }

            .cart-item {
                padding: 1rem;
            }

            .empty-cart {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include header -->
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">Service Cart</h1>
                    <p class="mb-0 mt-2">Review and submit service requests for: <strong><?php echo e($case['title']); ?></strong></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="catalogue.php?case_id=<?php echo $case['id']; ?>" class="btn btn-outline-light">
                        <i class="fas fa-plus"></i> Add More Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Service Requests Submitted Successfully!</h5>
            <p class="mb-3"><?php echo e($success_message); ?></p>
            
            <?php if (!empty($submitted_requests)): ?>
            <div class="submitted-requests">
                <h6>Submitted Requests:</h6>
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
                            </div>
                            <span class="badge bg-primary"><?php echo e(ucfirst($request['status'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($cart_items) && !$success_message): ?>
        <!-- Empty Cart -->
        <div class="cart-card">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your Cart is Empty</h3>
                <p>You haven't added any services to your cart yet. Browse our service catalogue to get started.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="catalogue.php?case_id=<?php echo $case_id; ?>" class="btn btn-merlaws">
                        <i class="fas fa-search"></i> Browse Services
                    </a>
                    <a href="../cases/view.php?id=<?php echo $case_id; ?>" class="btn btn-outline-merlaws">
                        <i class="fas fa-arrow-left"></i> Back to Case
                    </a>
                </div>
            </div>
        </div>
        <?php elseif (!empty($cart_items)): ?>
        <!-- Cart Items -->
        <div class="row">
            <div class="col-lg-8">
                <div class="cart-card">
                    <h3 class="mb-4">Service Requests (<?php echo count($cart_items); ?> items)</h3>
                    
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                        <div class="service-header">
                            <h4 class="service-name"><?php echo e($item['name']); ?></h4>
                            <span class="service-category"><?php echo e($item['category']); ?></span>
                        </div>
                        
                        <div class="service-description">
                            <?php echo e($item['description']); ?>
                        </div>
                        
                        <div class="service-meta">
                            <?php if ($item['estimated_duration']): ?>
                            <span><i class="fas fa-clock"></i> <?php echo e($item['estimated_duration']); ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-calendar-plus"></i> Added <?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                            <?php if ($item['urgency'] === 'urgent'): ?>
                            <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Urgent</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($item['notes']): ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                <strong>Notes:</strong> <?php echo e($item['notes']); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-end">
                            <button class="btn-remove" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-card">
                    <h4>Request Summary</h4>
                    
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Total Services:</span>
                            <strong><?php echo count($cart_items); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Case:</span>
                            <strong><?php echo e($case['title']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Status:</span>
                            <span class="badge bg-primary">Ready to Submit</span>
                        </div>
                    </div>
                    
                    <form method="post" action="">
                        <?php echo csrf_field(); ?>
                        
                        <div class="notes-section">
                            <label class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control" 
                                      name="general_notes" 
                                      rows="4" 
                                      placeholder="Any additional information or special requests..."></textarea>
                            <small class="text-muted">These notes will be visible to our team when reviewing your requests.</small>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="submit_cart" class="btn btn-merlaws btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Service Requests
                            </button>
                            <a href="catalogue.php?case_id=<?php echo $case_id; ?>" class="btn btn-outline-merlaws">
                                <i class="fas fa-plus"></i> Add More Services
                            </a>
                        </div>
                        
                        <!-- Quick Submit Button (always visible when cart has items) -->
                        <div class="mt-3">
                            <form method="post" action="" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" name="submit_cart" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> Submit All Requests Now
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
                
                <!-- Help Card -->
                <div class="cart-card mt-3">
                    <h5><i class="fas fa-info-circle text-primary"></i> What Happens Next?</h5>
                    <div class="small text-muted">
                        <p>1. <strong>Review:</strong> Our team will review your service requests</p>
                        <p>2. <strong>Approval:</strong> We'll approve or provide feedback on each request</p>
                        <p>3. <strong>Scheduling:</strong> Approved services will be scheduled and you'll be notified</p>
                        <p class="mb-0">4. <strong>Updates:</strong> Track progress in your case dashboard</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
        // Remove item from cart
        async function removeFromCart(itemId) {
            if (!confirm('Are you sure you want to remove this service from your cart?')) {
                return;
            }
            
            try {
                const response = await fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&case_id=<?php echo $case_id; ?>&item_id=${itemId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove item from DOM
                    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemElement) {
                        itemElement.style.animation = 'fadeOut 0.3s ease-out';
                        setTimeout(() => {
                            itemElement.remove();
                            
                            // Check if cart is empty and reload if so
                            const remainingItems = document.querySelectorAll('[data-item-id]');
                            if (remainingItems.length === 0) {
                                location.reload();
                            } else {
                                // Update item count
                                const countElement = document.querySelector('.mb-4');
                                if (countElement) {
                                    countElement.textContent = `Service Requests (${remainingItems.length} items)`;
                                }
                            }
                        }, 300);
                    }
                    
                    showToast('Service removed from cart', 'info');
                } else {
                    throw new Error(result.error || 'Failed to remove service');
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
        
        // Create toast container
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        }
        
        // Add fade out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.8); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>