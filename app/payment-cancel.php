<?php
require __DIR__ . '/config.php';
require_login();

$invoice_id = (int)($_GET['invoice_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Cancelled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/../include/header.php'; ?>
    <div class="container text-center mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-body p-5">
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
                <i class="fas fa-times-circle fa-4x text-warning mb-4"></i>
                <h1 class="card-title">Payment Cancelled</h1>
                <p class="card-text">Your payment process was cancelled. Your invoice has not been paid. You can try again at any time.</p>
                <a href="/app/view_invoice.php?invoice_id=<?php echo $invoice_id; ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-receipt me-2"></i>Return to Invoice
                </a>
            </div>
        </div>
    </div>
</body>
</html>

