<?php
// app/services/catalogue.php
require __DIR__ . '/../config.php';
require_login();

$case_id = (int)($_GET['case_id'] ?? 0);
if (!$case_id) {
    redirect('../cases/index.php');
}

$case = get_case($case_id, get_user_id());
if (!$case) {
    redirect('../cases/index.php');
}

// Check if service requests can be added to this case
$service_restriction = can_add_service_requests($case);

$selected_category = $_GET['category'] ?? '';
$services = get_services($selected_category);
$categories = get_service_categories();
$cart_items = get_cart_items($case_id);
$cart_count = count($cart_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Catalogue - <?php echo e($case['title']); ?> | Med Attorneys</title>
    
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
        
        .category-filter {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .category-btn {
            background: transparent;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            margin: 0.25rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .category-btn:hover, .category-btn.active {
            border-color: var(--merlaws-primary);
            color: var(--merlaws-primary);
            background: rgba(172, 19, 42, 0.05);
        }
        
        .service-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .service-title {
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
        
        .service-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-add-to-cart {
            background-color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-add-to-cart:hover {
            background-color: var(--merlaws-primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-add-to-cart:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .cart-sidebar {
            position: sticky;
            top: 2rem;
        }
        
        .cart-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .cart-count {
            background: var(--merlaws-primary);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-name {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }
        
        .cart-item-category {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .btn-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .btn-remove:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .empty-cart {
            text-align: center;
            color: #6c757d;
            padding: 2rem 0;
        }
        
        .empty-cart i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .btn-view-cart {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-view-cart:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), #6b0f1a);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header p {
                font-size: 0.9rem;
            }

            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 12px 20px;
            }

            .category-filter {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }

            .category-btn {
                padding: 0.75rem 1rem;
                margin: 0.5rem 0.25rem;
                min-height: 44px;
                font-size: 0.9rem;
            }

            .service-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }

            .service-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .service-title {
                font-size: 1.15rem;
            }

            .service-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-add-to-cart {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
            }

            .cart-sidebar {
                margin-top: 2rem;
            }

            .cart-card {
                padding: 1.25rem;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .btn-remove {
                align-self: flex-end;
                min-height: 44px;
                min-width: 44px;
            }

            .btn-view-cart {
                min-height: 48px;
                font-size: 16px;
            }

            .modal-dialog {
                margin: 0.5rem;
            }

            .form-control,
            .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 1.25rem 0;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .category-filter {
                padding: 1rem;
            }

            .service-card {
                padding: 1rem;
            }

            .cart-card {
                padding: 1rem;
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
                    <h1 class="mb-0">Service Catalogue</h1>
                    <p class="mb-0 mt-2">Browse and request services for: <strong><?php echo e($case['title']); ?></strong></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../cases/view.php?id=<?php echo $case['id']; ?>" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Back to Case
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (!$service_restriction['can_add']): ?>
        <!-- Service Restriction Alert -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Service Requests Restricted:</strong> <?php echo e($service_restriction['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Category Filter -->
                <div class="category-filter">
                    <h5 class="mb-3">Filter by Category</h5>
                    <div>
                        <a href="?case_id=<?php echo $case_id; ?>" class="category-btn <?php echo empty($selected_category) ? 'active' : ''; ?>">
                            All Services
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="?case_id=<?php echo $case_id; ?>&category=<?php echo urlencode($category); ?>" 
                           class="category-btn <?php echo $selected_category === $category ? 'active' : ''; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Services List -->
                <div id="services-container">
                    <?php if (empty($services)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No Services Found</h4>
                        <p class="text-muted">No services available in this category.</p>
                        <a href="?case_id=<?php echo $case_id; ?>" class="btn btn-outline-primary">View All Services</a>
                    </div>
                    <?php else: ?>
                    <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <h3 class="service-title"><?php echo e($service['name']); ?></h3>
                            <span class="service-category"><?php echo e($service['category']); ?></span>
                        </div>
                        
                        <div class="service-description">
                            <?php echo e($service['description']); ?>
                        </div>
                        
                        <div class="service-meta">
                            <?php if ($service['estimated_duration']): ?>
                            <span><i class="fas fa-clock"></i> <?php echo e($service['estimated_duration']); ?></span>
                            <?php endif; ?>
                            <?php if ($service['subcategory']): ?>
                            <span><i class="fas fa-tag"></i> <?php echo e($service['subcategory']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($service['requirements']): ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                <strong>Requirements:</strong> <?php echo e($service['requirements']); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($service_restriction['can_add']): ?>
                            <button class="btn-add-to-cart" 
                                    onclick="addToCart(<?php echo $service['id']; ?>, <?php echo $case_id; ?>)"
                                    data-service-id="<?php echo $service['id']; ?>">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                            <?php else: ?>
                            <button class="btn-add-to-cart" 
                                    disabled
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="<?php echo e($service_restriction['message']); ?>"
                                    data-service-id="<?php echo $service['id']; ?>">
                                <i class="fas fa-lock"></i> Add to Cart
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div class="col-lg-4">
                <div class="cart-sidebar">
                    <div class="cart-card">
                        <div class="cart-header">
                            <h4 class="mb-0">Service Cart</h4>
                            <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span>
                        </div>
                        
                        <div id="cartItems">
                            <?php if (empty($cart_items)): ?>
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h6>Cart is Empty</h6>
                                <p class="small">Add services to your cart to get started.</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                                <div>
                                    <div class="cart-item-name"><?php echo e($item['name']); ?></div>
                                    <div class="cart-item-category"><?php echo e($item['category']); ?></div>
                                </div>
                                <button class="btn-remove" onclick="removeFromCart(<?php echo $item['id']; ?>, <?php echo $case_id; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($cart_items)): ?>
                        <div class="mt-3 pt-3 border-top">
                            <a href="cart.php?case_id=<?php echo $case_id; ?>" class="btn-view-cart">
                                <i class="fas fa-eye"></i> View Full Cart & Submit
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation Scheduling Modal -->
    <div class="modal fade" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultationModalLabel">Schedule Consultation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="consultationForm">
                        <div class="mb-3">
                            <label for="attorneySelect" class="form-label">Select Attorney</label>
                            <select class="form-select" id="attorneySelect" required>
                                <option value="">Choose an attorney...</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="consultDate" class="form-label">Select Date</label>
                            <input type="date" class="form-control" id="consultDate" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="consultTime" class="form-label">Select Time</label>
                            <select class="form-select" id="consultTime" required>
                                <option value="">Choose a time slot...</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="consultNotes" class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control" id="consultNotes" rows="3" placeholder="Any specific requirements or notes for this consultation..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmConsultation">Add to Cart</button>
                </div>
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
        });
        // Add service to cart
        async function addToCart(serviceId, caseId) {
            const button = document.querySelector(`[data-service-id="${serviceId}"]`);
            const serviceCard = button.closest('.service-card');
            const serviceCategory = serviceCard.querySelector('.service-category').textContent.toLowerCase();
            
            // Check if this is a consultation service
            if (serviceCategory === 'consultation') {
                // Show consultation scheduling modal
                showConsultationModal(serviceId, caseId);
            } else {
                // Add directly to cart for non-consultation services
                await addToCartDirect(serviceId, caseId);
            }
        }
        
        // Add service directly to cart (for non-consultation services)
        async function addToCartDirect(serviceId, caseId) {
            const button = document.querySelector(`[data-service-id="${serviceId}"]`);
            const originalText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            try {
                const response = await fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&case_id=${caseId}&service_id=${serviceId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update cart count
                    document.getElementById('cartCount').textContent = result.cart_count;
                    
                    // Show success message
                    showToast('Service added to cart!', 'success');
                    
                    // Refresh cart items
                    await refreshCartItems(caseId);
                    
                    // Update button text
                    button.innerHTML = '<i class="fas fa-check"></i> Added';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Failed to add service to cart');
                }
            } catch (error) {
                showToast(error.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        // Remove service from cart
        async function removeFromCart(itemId, caseId) {
            try {
                const response = await fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&case_id=${caseId}&item_id=${itemId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update cart count
                    document.getElementById('cartCount').textContent = result.cart_count;
                    
                    // Remove item from DOM
                    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemElement) {
                        itemElement.remove();
                    }
                    
                    // Show empty cart if no items left
                    if (result.cart_count === 0) {
                        document.getElementById('cartItems').innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h6>Cart is Empty</h6>
                                <p class="small">Add services to your cart to get started.</p>
                            </div>
                        `;
                    }
                    
                    showToast('Service removed from cart', 'info');
                } else {
                    throw new Error(result.error || 'Failed to remove service from cart');
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        // Refresh cart items
        async function refreshCartItems(caseId) {
            try {
                const response = await fetch(`../api/cart.php?action=get&case_id=${caseId}`);
                const result = await response.json();
                
                if (result.success) {
                    const cartItemsContainer = document.getElementById('cartItems');
                    
                    if (result.items.length === 0) {
                        cartItemsContainer.innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h6>Cart is Empty</h6>
                                <p class="small">Add services to your cart to get started.</p>
                            </div>
                        `;
                        // Hide cart action buttons
                        const cartActions = document.querySelector('.cart-sidebar .mt-3.pt-3.border-top');
                        if (cartActions) {
                            cartActions.style.display = 'none';
                        }
                    } else {
                        cartItemsContainer.innerHTML = result.items.map(item => `
                            <div class="cart-item" data-item-id="${item.id}">
                                <div>
                                    <div class="cart-item-name">${item.name}</div>
                                    <div class="cart-item-category">${item.category}</div>
                                </div>
                                <button class="btn-remove" onclick="removeFromCart(${item.id}, ${caseId})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `).join('');
                        
                        // Show cart action buttons
                        const cartActions = document.querySelector('.cart-sidebar .mt-3.pt-3.border-top');
                        if (cartActions) {
                            cartActions.style.display = 'block';
                        } else {
                            // Create cart action buttons if they don't exist
                            const cartCard = document.querySelector('.cart-card');
                            if (cartCard) {
                                const actionsDiv = document.createElement('div');
                                actionsDiv.className = 'mt-3 pt-3 border-top';
                                actionsDiv.innerHTML = `
                                    <a href="cart.php?case_id=${caseId}" class="btn-view-cart">
                                        <i class="fas fa-eye"></i> View Full Cart & Submit
                                    </a>
                                `;
                                cartCard.appendChild(actionsDiv);
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to refresh cart items:', error);
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
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
        
        // Create toast container if it doesn't exist
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        }
        
        // Global variables for consultation modal
        let currentServiceId = null;
        let currentCaseId = null;
        
        // Show consultation scheduling modal
        async function showConsultationModal(serviceId, caseId) {
            currentServiceId = serviceId;
            currentCaseId = caseId;
            
            // Load attorneys for this case
            await loadAttorneysForCase(caseId);
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('consultationModal'));
            modal.show();
        }
        
        // Load attorneys assigned to the case
        async function loadAttorneysForCase(caseId) {
            try {
                const response = await fetch(`../api/cases.php?action=get_attorneys&case_id=${caseId}`);
                const result = await response.json();
                
                const attorneySelect = document.getElementById('attorneySelect');
                attorneySelect.innerHTML = '<option value="">Choose an attorney...</option>';
                
                if (result.success && result.attorneys) {
                    result.attorneys.forEach(attorney => {
                        const option = document.createElement('option');
                        option.value = attorney.id;
                        option.textContent = attorney.name;
                        attorneySelect.appendChild(option);
                    });
                } else {
                    // Fallback: load all attorneys
                    await loadAllAttorneys();
                }
            } catch (error) {
                console.error('Failed to load attorneys:', error);
                await loadAllAttorneys();
            }
        }
        
        // Load all attorneys as fallback
        async function loadAllAttorneys() {
            try {
                const response = await fetch('../api/users.php?action=get_attorneys');
                const result = await response.json();
                
                const attorneySelect = document.getElementById('attorneySelect');
                attorneySelect.innerHTML = '<option value="">Choose an attorney...</option>';
                
                if (result.success && result.attorneys) {
                    result.attorneys.forEach(attorney => {
                        const option = document.createElement('option');
                        option.value = attorney.id;
                        option.textContent = attorney.name;
                        attorneySelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Failed to load all attorneys:', error);
            }
        }
        
        // Load available time slots for selected attorney and date
        async function loadAvailableTimeSlots(attorneyId, selectedDate) {
            if (!attorneyId || !selectedDate) {
                return;
            }
            
            try {
                const response = await fetch(`../api/availability.php?attorney_id=${attorneyId}&date=${selectedDate}`);
                const result = await response.json();
                
                const timeSelect = document.getElementById('consultTime');
                timeSelect.innerHTML = '<option value="">Choose a time slot...</option>';
                
                if (result.success && result.timeSlots) {
                    result.timeSlots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.time;
                        option.textContent = slot.display;
                        timeSelect.appendChild(option);
                    });
                } else {
                    timeSelect.innerHTML = '<option value="">No available time slots</option>';
                }
            } catch (error) {
                console.error('Failed to load time slots:', error);
                const timeSelect = document.getElementById('consultTime');
                timeSelect.innerHTML = '<option value="">Error loading time slots</option>';
            }
        }
        
        // Event listeners for consultation modal
        document.addEventListener('DOMContentLoaded', function() {
            // Attorney selection change
            document.getElementById('attorneySelect').addEventListener('change', function() {
                const selectedDate = document.getElementById('consultDate').value;
                if (selectedDate) {
                    loadAvailableTimeSlots(this.value, selectedDate);
                }
            });
            
            // Date selection change
            document.getElementById('consultDate').addEventListener('change', function() {
                const selectedAttorney = document.getElementById('attorneySelect').value;
                if (selectedAttorney) {
                    loadAvailableTimeSlots(selectedAttorney, this.value);
                }
            });
            
            // Confirm consultation button
            document.getElementById('confirmConsultation').addEventListener('click', async function() {
                const attorneyId = document.getElementById('attorneySelect').value;
                const consultDate = document.getElementById('consultDate').value;
                const consultTime = document.getElementById('consultTime').value;
                const consultNotes = document.getElementById('consultNotes').value;
                
                if (!attorneyId || !consultDate || !consultTime) {
                    showToast('Please fill in all required fields', 'error');
                    return;
                }
                
                // Add consultation to cart with scheduling info
                await addConsultationToCart(currentServiceId, currentCaseId, consultDate, consultTime, consultNotes);
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('consultationModal'));
                modal.hide();
            });
        });
        
        // Add consultation to cart with scheduling information
        async function addConsultationToCart(serviceId, caseId, consultDate, consultTime, notes) {
            const button = document.querySelector(`[data-service-id="${serviceId}"]`);
            const originalText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            try {
                const response = await fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&case_id=${caseId}&service_id=${serviceId}&consult_date=${consultDate}&consult_time=${consultTime}&notes=${encodeURIComponent(notes)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update cart count
                    document.getElementById('cartCount').textContent = result.cart_count;
                    
                    // Show success message
                    showToast('Consultation scheduled and added to cart!', 'success');
                    
                    // Refresh cart items
                    await refreshCartItems(caseId);
                    
                    // Update button text
                    button.innerHTML = '<i class="fas fa-check"></i> Added';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Failed to add consultation to cart');
                }
            } catch (error) {
                showToast(error.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
    </script>
</body>
</html>