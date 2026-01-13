<?php
require __DIR__ . '/config.php';
require_login();

$invoice_id = (int)($_GET['invoice_id'] ?? 0);

if ($invoice_id <= 0) {
    redirect('/app/dashboard.php?error=invalid_invoice');
}

$pdo = db();
$user_id = get_user_id();

// Fetch invoice, case, and client details
$sql = "
    SELECT 
        i.*,
        c.title as case_title,
        c.case_number,
        c.user_id as case_owner_id
    FROM invoices i
    JOIN cases c ON i.case_id = c.id
    WHERE i.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice || $invoice['case_owner_id'] != $user_id) {
    $_SESSION['error_message'] = 'Invoice not found or you do not have permission to view it.';
    redirect('/app/dashboard.php');
}

$subtotal = $invoice['amount'];
$tax_rate = $invoice['tax_rate'] ?? 15.00;
$tax_amount = $subtotal * ($tax_rate / 100);
$total = $subtotal + $tax_amount;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo e($invoice['invoice_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .invoice-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 2px solid #c9a96e;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .totals-table {
            width: 100%;
            max-width: 300px;
            margin-left: auto;
        }
        .total-row { font-size: 1.1rem; font-weight: bold; }
        .action-buttons { text-align: right; margin-bottom: 1rem; }
        @media print {
            .action-buttons, .navbar, footer { display: none; }
            .invoice-container { box-shadow: none; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../include/header.php'; ?>

    <div class="container">
        <div class="action-buttons">
            <a href="/app/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Print</button>
        </div>

        <div class="invoice-container">
            <header class="invoice-header row align-items-center">
                <div class="col-md-6">
                    <h3>INVOICE</h3>
                    <div class="text-muted">#<?php echo e($invoice['invoice_number']); ?></div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="mb-1">MerLaws Inc.</h5>
                    <div class="text-muted small">info@merlaws.com | +27 11 555 0101</div>
                </div>
            </header>

            <section class="row mb-4">
                <div class="col-md-6">
                    <strong>Invoice Date:</strong> <?php echo date('F j, Y', strtotime($invoice['created_at'])); ?><br>
                    <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?>
                </div>
                <div class="col-md-6 text-md-end">
                    <strong>Status:</strong> <span class="badge bg-<?php echo $invoice['status'] === 'paid' ? 'success' : 'warning'; ?> text-uppercase"><?php echo e($invoice['status']); ?></span>
                </div>
            </section>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Description</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td><?php echo e($invoice['notes'] ?: 'Professional Services Rendered'); ?></td><td class="text-end">R <?php echo number_format($invoice['amount'], 2); ?></td></tr>
                </tbody>
            </table>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="totals-table">
                        <tr><td>Subtotal</td><td class="text-end">R <?php echo number_format($subtotal, 2); ?></td></tr>
                        <tr><td>Tax (<?php echo number_format($tax_rate, 2); ?>%)</td><td class="text-end">R <?php echo number_format($tax_amount, 2); ?></td></tr>
                        <tr class="total-row"><td>Total Due</td><td class="text-end">R <?php echo number_format($total, 2); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../include/footer.html'; ?>
</body>
</html>