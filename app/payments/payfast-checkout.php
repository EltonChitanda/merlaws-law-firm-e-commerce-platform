<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../config/payfast.php';
require_login();
require __DIR__ . '/../services/PayFastService.php';

// Get invoice ID from POST (form submission) or GET (direct link)
$invoice_id = (int)($_POST['invoice_id'] ?? $_GET['invoice_id'] ?? 0);
if (!$invoice_id) {
    http_response_code(404);
    die('Invoice not found');
}

$payfast_service = new PayFastService();

// Generate payment data
$payment_data = $payfast_service->generatePaymentData($invoice_id);

if (!$payment_data['success']) {
    http_response_code(400);
    die('Error: ' . $payment_data['error']);
}

$data = $payment_data['data'];
$payment_url = $payment_data['payment_url'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Processing Payment | Med Attorneys</title>
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
            max-width: 500px;
            width: 100%;
        }
        
        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2rem;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #10b981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .payment-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .payment-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 1rem 0;
        }
        
        .payment-note {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
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
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-icon">
            <i class="fas fa-credit-card"></i>
        </div>
        
        <h2>Processing Payment</h2>
        <p class="text-muted">Redirecting to PayFast secure payment gateway...</p>
        
        <div class="spinner"></div>
        
        <div class="payment-details">
            <div class="payment-amount">R<?php echo number_format($data['amount'], 2); ?></div>
            <p><strong><?php echo htmlspecialchars($data['item_name']); ?></strong></p>
            <p class="text-muted"><?php echo htmlspecialchars($data['item_description']); ?></p>
        </div>
        
        <div class="payment-note">
            <i class="fas fa-shield-alt me-2"></i>
            Your payment is secured by PayFast's industry-standard encryption
        </div>
        
        <form id="payfast-form" action="<?php echo htmlspecialchars($payment_url); ?>" method="post" style="display: none;">
            <?php foreach ($data as $key => $value): ?>
            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php endforeach; ?>
        </form>
        
        <div class="mt-4">
            <button type="button" class="btn btn-primary" onclick="submitPayment()">
                <i class="fas fa-credit-card me-2"></i>
                Continue to PayFast
            </button>
        </div>
    </div>

    <script>
        // Auto-submit form after 3 seconds
        setTimeout(function() {
            submitPayment();
        }, 3000);
        
        function submitPayment() {
            document.getElementById('payfast-form').submit();
        }
        
        // Show manual submit button if auto-submit fails
        setTimeout(function() {
            document.querySelector('.spinner').style.display = 'none';
            document.querySelector('button').style.display = 'inline-block';
        }, 2000);
    </script>
</body>
</html>
