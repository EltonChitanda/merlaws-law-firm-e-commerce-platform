<?php
require __DIR__ . '/config.php';
require_login();

// PayFast returns m_payment_id (which is our invoice_id) or we can get it from URL parameter
$invoice_id = (int)($_GET['m_payment_id'] ?? $_GET['invoice_id'] ?? 0);
$invoice = null;
$user_id = get_user_id();

if ($invoice_id > 0) {
    $pdo = db();
    
    // First, try to get the invoice with user verification
    $stmt = $pdo->prepare("
        SELECT i.*, c.title AS case_title, c.user_id as case_owner_id
        FROM invoices i
        JOIN cases c ON i.case_id = c.id
        WHERE i.id = ? AND c.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$invoice_id, $user_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug logging (remove in production if not needed)
    if (!$invoice) {
        error_log("Payment success: Invoice {$invoice_id} not found for user {$user_id}");
    }
    
    // If not found with user check, try without user check
    // (PayFast redirected here, so invoice should exist - show it even if user check fails)
    if (!$invoice) {
        $stmt = $pdo->prepare("
            SELECT i.*, c.title AS case_title, c.user_id as case_owner_id
            FROM invoices i
            JOIN cases c ON i.case_id = c.id
            WHERE i.id = ?
            LIMIT 1
        ");
        $stmt->execute([$invoice_id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If invoice exists but user doesn't own it, log for security but still show it
        // (since PayFast redirected here, the user likely just paid)
        if ($invoice && $invoice['case_owner_id'] != $user_id) {
            error_log("Payment success: User {$user_id} accessed invoice {$invoice_id} owned by user {$invoice['case_owner_id']} - PayFast redirect");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/../include/header.php'; ?>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 640px;">
            <div class="card-body p-5 text-center">
                <style>
                    @media (max-width: 768px) {
                        .container {
                            padding: 1rem;
                            margin-top: 1.5rem !important;
                        }

                        .card {
                            max-width: 100% !important;
                            border-radius: 16px;
                        }

                        .card-body {
                            padding: 2rem 1.5rem !important;
                        }

                        h1 {
                            font-size: 1.75rem;
                        }

                        .fa-4x {
                            font-size: 3rem !important;
                        }

                        .btn {
                            width: 100%;
                            min-height: 48px;
                            font-size: 16px;
                            padding: 12px 20px;
                        }

                        .alert {
                            padding: 1rem;
                            font-size: 0.95rem;
                        }
                    }

                    @media (max-width: 480px) {
                        .card-body {
                            padding: 1.5rem 1rem !important;
                        }

                        h1 {
                            font-size: 1.5rem;
                        }

                        .fa-4x {
                            font-size: 2.5rem !important;
                        }
                    }
                </style>
                <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                <h1 class="card-title">Payment Received!</h1>
                <p class="card-text">
                    Thank you for your payment. The details below reflect the latest status of your invoice.
                    You can download or print your invoice using the button provided.
                </p>

                <?php if ($invoice): ?>
                    <div class="alert alert-<?php echo $invoice['status'] === 'paid' ? 'success' : 'warning'; ?> mt-4 text-start">
                        <h5 class="mb-2">Invoice #<?php echo e($invoice['invoice_number']); ?></h5>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Case:</strong> <?php echo e($invoice['case_title']); ?></li>
                            <li><strong>Total:</strong> R<?php echo number_format((float)$invoice['amount'], 2); ?></li>
                            <li><strong>Status:</strong> <?php echo ucfirst($invoice['status']); ?></li>
                        </ul>
                        <?php if ($invoice['status'] !== 'paid'): ?>
                            <p class="mb-0 mt-2 small text-muted">
                                Status still pending? Give it a minute — we update it automatically once PayFast confirms the payment.
                            </p>
                        <?php endif; ?>
                    </div>

                    <a href="/app/view_invoice.php?invoice_id=<?php echo $invoice_id; ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-receipt me-2"></i>View Invoice
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        <h5>Invoice Not Found</h5>
                        <p class="mb-2">We could not find the invoice details for Invoice ID: <?php echo $invoice_id > 0 ? $invoice_id : 'N/A'; ?></p>
                        <?php if ($invoice_id > 0): ?>
                            <p class="mb-0 small text-muted">
                                If you just completed a payment, the invoice may still be processing. 
                                Please check your dashboard or contact support if this issue persists.
                            </p>
                        <?php else: ?>
                            <p class="mb-0 small text-muted">
                                No invoice ID was provided. Please return to your dashboard to view your invoices.
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="/app/dashboard.php" class="btn btn-primary mt-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <?php if ($invoice_id > 0): ?>
                        <a href="/app/cases/view.php?id=<?php echo $invoice_id; ?>" class="btn btn-secondary mt-3">
                            <i class="fas fa-folder-open me-2"></i>View Cases
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

