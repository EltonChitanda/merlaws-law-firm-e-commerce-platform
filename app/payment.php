<?php
require __DIR__ . '/config.php';
require __DIR__ . '/config/payfast.php';
require_login();

$invoice_id = (int)($_GET['invoice_id'] ?? 0);
$user_id = get_user_id();

if ($invoice_id <= 0) {
    redirect('/app/dashboard.php?error=invalid_invoice');
}

$pdo = db();

// Fetch invoice and verify ownership
$stmt = $pdo->prepare("
    SELECT i.*, u.name as client_name, u.email as client_email
    FROM invoices i
    JOIN cases c ON i.case_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE i.id = ? AND c.user_id = ?
");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    $_SESSION['error_message'] = 'Invoice not found or you do not have permission to pay for it.';
    redirect('/app/dashboard.php');
}

if ($invoice['status'] === 'paid') {
    $_SESSION['success_message'] = 'This invoice has already been paid.';
    redirect('/app/view_invoice.php?invoice_id=' . $invoice_id);
}

// --- Calculate Total Amount ---
$subtotal = (float)$invoice['amount'];
$tax_rate = (float)($invoice['tax_rate'] ?? 15.00);
$tax_amount = $subtotal * ($tax_rate / 100);
$total_amount = $subtotal + $tax_amount;

// --- Generate PayFast Data ---
$data = [
    'merchant_id' => PAYFAST_MERCHANT_ID,
    'merchant_key' => PAYFAST_MERCHANT_KEY,
    'return_url' => PAYFAST_RETURN_URL,
    'cancel_url' => PAYFAST_CANCEL_URL,
    'notify_url' => PAYFAST_NOTIFY_URL,
    'name_first' => explode(' ', trim($invoice['client_name']))[0] ?? 'Client',
    'name_last' => implode(' ', array_slice(explode(' ', trim($invoice['client_name'])), 1)) ?: 'User',
    'email_address' => $invoice['client_email'],
    'm_payment_id' => $invoice['id'],
    'amount' => number_format($total_amount, 2, '.', ''),
    'item_name' => 'Invoice #' . $invoice['invoice_number'],
    'item_description' => 'Payment for legal services related to case: ' . $invoice['case_id'],
];

// Generate signature
$passphrase = PAYFAST_PASSPHRASE;
$signature_data = http_build_query($data);
if (!empty($passphrase)) {
    $signature_data .= '&passphrase=' . urlencode($passphrase);
}
$data['signature'] = md5($signature_data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to PayFast...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            background-color: #f8f9fa; 
            padding: 1rem;
        }
        .spinner-border { width: 3rem; height: 3rem; }
        h4 { font-size: 1.25rem; }
        @media (max-width: 480px) {
            h4 { font-size: 1.1rem; }
            p { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="mt-3">Redirecting you to our secure payment gateway...</h4>
        <p class="text-muted">Please wait, you will be redirected shortly.</p>
    </div>

    <form action="<?php echo PAYFAST_URL; ?>" method="post" id="payfast-form">
        <?php foreach ($data as $key => $value): ?>
            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
        <?php endforeach; ?>
    </form>

    <script>
        // Auto-submit the form to redirect to PayFast
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('payfast-form').submit();
        });
    </script>
</body>
</html>

