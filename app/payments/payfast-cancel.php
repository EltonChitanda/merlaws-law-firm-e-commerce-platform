<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../config/payfast.php';
require __DIR__ . '/../services/PayFastService.php';

// Get invoice ID from URL parameter
$invoice_id = (int)($_GET['invoice_id'] ?? 0);
if (!$invoice_id) {
    http_response_code(404);
    die('Invoice not found');
}

$payfast_service = new PayFastService();

// Handle payment cancellation
$result = $payfast_service->handlePaymentCancel($invoice_id);

// Get invoice details for display
$invoice_service = new InvoiceService();
$invoice = $invoice_service->getInvoice($invoice_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Cancelled | Med Attorneys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        
        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6b7280, #4b5563);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2rem;
        }
        
        .payment-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 1rem 0;
        }
        
        .payment-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            color: white;
        }
        
        .btn-outline-secondary {
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }
        
        .btn-outline-secondary:hover {
            background: #6b7280;
            border-color: #6b7280;
            color: white;
        }
        
        .status-message {
            font-size: 1.1rem;
            margin: 1rem 0;
            color: #6b7280;
        }
        
        .help-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .help-section h6 {
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .help-section ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .help-section li {
            margin-bottom: 0.5rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-icon">
            <i class="fas fa-times"></i>
        </div>
        
        <h2>Payment Cancelled</h2>
        <p class="status-message">
            <i class="fas fa-info-circle me-2"></i>
            Your payment was cancelled. No charges have been made to your account.
        </p>
        
        <?php if ($invoice): ?>
        <div class="payment-details">
            <h5>Invoice Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Invoice #:</strong> <?php echo e($invoice['invoice_number']); ?><br>
                    <strong>Client:</strong> <?php echo e($invoice['client_name']); ?><br>
                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['invoice_date'])); ?>
                </div>
                <div class="col-md-6">
                    <strong>Amount:</strong> R<?php echo number_format($invoice['total_amount'], 2); ?><br>
                    <strong>Status:</strong> 
                    <span class="badge bg-warning">
                        <?php echo ucfirst($invoice['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="help-section">
            <h6><i class="fas fa-question-circle me-2"></i>What can you do now?</h6>
            <ul>
                <li>Try the payment again using the same or a different payment method</li>
                <li>Contact us if you're experiencing technical difficulties</li>
                <li>Use alternative payment methods like bank transfer or cash</li>
                <li>Download the invoice PDF for your records</li>
            </ul>
        </div>
        
        <div class="mt-4">
            <a href="payfast-checkout.php?invoice_id=<?php echo $invoice_id; ?>" class="btn btn-primary me-2">
                <i class="fas fa-credit-card me-2"></i>
                Try Payment Again
            </a>
            
            <?php if ($invoice): ?>
            <a href="../../app/admin/invoices/pdf.php?id=<?php echo $invoice_id; ?>" class="btn btn-outline-secondary me-2" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>
                Download Invoice
            </a>
            <?php endif; ?>
            
            <a href="../../app/admin/invoices/index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Invoices
            </a>
        </div>
        
        <div class="mt-4">
            <p class="text-muted">
                <i class="fas fa-phone me-2"></i>
                Need help? Contact us at +27 11 123 4567 or email info@merlaws.com
            </p>
        </div>
    </div>
</body>
</html>
