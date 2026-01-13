<?php
require __DIR__ . '/../config.php';

// This is a placeholder file for viewing an invoice.
// The full implementation will be done later.

$invoice_id = (int)($_GET['id'] ?? 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Invoice Details</h1>
        <p>Invoice ID: <?php echo htmlspecialchars($invoice_id); ?></p>
        <div class="alert alert-info">
            This is a placeholder page. The full invoice details will be displayed here.
        </div>
    </div>
</body>
</html>