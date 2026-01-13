<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../services/PayFastService.php';

$invoice_id = (int)($_GET['invoice_id'] ?? 0);
if ($invoice_id <= 0) { http_response_code(400); die('Bad request'); }

$pf = new PayFastService();
$res = $pf->handlePaymentReturn($invoice_id, $_GET);

if (!$res['success']) {
    echo '<h2>Error</h2><p>' . htmlspecialchars($res['error']) . '</p>';
    exit;
}

$inv = $res['invoice'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body text-center">
            <?php if ($res['status'] === 'success'): ?>
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h3 class="text-success">Payment Successful!</h3>
                <p>Invoice <strong>#<?= htmlspecialchars($inv['invoice_number']) ?></strong> is now paid.</p>
            <?php else: ?>
                <i class="fas fa-clock text-warning fa-4x mb-3"></i>
                <h3 class="text-warning">Processing…</h3>
                <p>Your payment is being verified. You’ll receive an email shortly.</p>
            <?php endif; ?>
            <a href="/" class="btn btn-primary mt-3">Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>