<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../services/InvoiceService.php';
require __DIR__ . '/../../services/PayFastService.php';

// Check permissions
require_permission('invoice:view');

$invoice_service = new InvoiceService();
$payfast_service = new PayFastService();

// Get invoice ID
$invoice_id = (int)($_GET['id'] ?? 0);
if (!$invoice_id) {
    header('Location: index.php');
    exit;
}

// Get invoice data
$invoice = $invoice_service->getInvoice($invoice_id);
if (!$invoice) {
    header('Location: index.php');
    exit;
}

// Get invoice items and payments
$items = $invoice_service->getInvoiceItems($invoice_id);
$payments = $invoice_service->getInvoicePayments($invoice_id);

// Check if invoice can be edited
$can_edit = $invoice_service->canEdit($invoice_id);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send':
            if (has_permission('invoice:send')) {
                $result = $invoice_service->sendInvoice($invoice_id);
                if ($result['success']) {
                    $_SESSION['success_message'] = 'Invoice sent successfully';
                    header('Location: view.php?id=' . $invoice_id);
                    exit;
                } else {
                    $error_message = $result['error'];
                }
            }
            break;
            
        case 'void':
            if (has_permission('invoice:delete')) {
                $reason = $_POST['reason'] ?? '';
                $result = $invoice_service->voidInvoice($invoice_id, $reason);
                if ($result['success']) {
                    $_SESSION['success_message'] = 'Invoice voided successfully';
                    header('Location: view.php?id=' . $invoice_id);
                    exit;
                } else {
                    $error_message = $result['error'];
                }
            }
            break;
    }
}

// Get PayFast payment data if invoice is unpaid
$payfast_data = null;
if ($invoice['status'] === 'sent') {
    $payfast_result = $payfast_service->generatePaymentData($invoice_id);
    if ($payfast_result['success']) {
        $payfast_data = $payfast_result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #<?php echo e($invoice['invoice_number']); ?> | Med Attorneys Admin</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../../favicon/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/default.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">

    <style>
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --merlaws-dark: #0d1117;
            --admin-blue: #3b82f6;
            --admin-blue-dark: #2563eb;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --neutral-gray: #6b7280;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .admin-badge {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(201, 169, 110, 0.3);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .invoice-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .invoice-info h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--merlaws-primary);
            margin: 0 0 0.5rem 0;
        }

        .invoice-meta {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-draft {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.2));
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .status-sent {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.2), rgba(6, 182, 212, 0.2));
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }

        .status-paid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: var(--success-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-overdue {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: var(--danger-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-cancelled, .status-void {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.2));
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        .client-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .client-info h5 {
            color: var(--merlaws-primary);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .items-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .items-table th {
            background: #f8fafc;
            border: none;
            font-weight: 700;
            color: var(--merlaws-primary);
            padding: 1rem;
            font-size: 0.9rem;
        }

        .items-table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .totals-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .totals-table {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
        }

        .totals-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-table .total-row {
            font-weight: 700;
            font-size: 1.25rem;
            background: var(--merlaws-gold);
            color: white;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .totals-table .total-row td {
            border: none;
            padding: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--admin-blue), var(--admin-blue-dark));
            color: white;
            border-color: var(--admin-blue);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--admin-blue-dark), #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            border-color: var(--success-green);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-orange), #d97706);
            color: white;
            border-color: var(--warning-orange);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            color: white;
            border-color: var(--danger-red);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-outline-secondary {
            color: var(--neutral-gray);
            border-color: #e5e7eb;
        }

        .btn-outline-secondary:hover {
            background: var(--neutral-gray);
            border-color: var(--neutral-gray);
            color: white;
        }

        .payment-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .payment-link {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .payment-link:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .payments-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .payments-table th {
            background: #f8fafc;
            border: none;
            font-weight: 700;
            color: var(--merlaws-primary);
            padding: 1rem;
            font-size: 0.9rem;
        }

        .payments-table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .invoice-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="admin-badge">
            <i class="fas fa-file-invoice"></i>
            Invoice Details
        </div>
        <h1 class="page-title">Invoice #<?php echo e($invoice['invoice_number']); ?></h1>
    </div>
</div>

<div class="container my-4">
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo e($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo e($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Invoice Header -->
    <div class="invoice-card">
        <div class="invoice-header">
            <div class="invoice-info">
                <h1>Invoice #<?php echo e($invoice['invoice_number']); ?></h1>
                <div class="invoice-meta">
                    <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['invoice_date'])); ?></div>
                    <div><strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?></div>
                    <div><strong>Created by:</strong> <?php echo e($invoice['created_by_name']); ?></div>
                </div>
            </div>
            <div>
                <span class="status-badge status-<?php echo $invoice['status']; ?>">
                    <?php echo ucfirst($invoice['status']); ?>
                </span>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-info">
            <h5><i class="fas fa-user me-2"></i>Bill To</h5>
            <div class="row">
                <div class="col-md-6">
                    <strong><?php echo e($invoice['client_name']); ?></strong><br>
                    <?php echo e($invoice['client_email']); ?>
                </div>
                <div class="col-md-6">
                    <?php if ($invoice['case_title']): ?>
                    <strong>Case:</strong> <?php echo e($invoice['case_title']); ?><br>
                    <?php endif; ?>
                    <strong>Total Amount:</strong> R<?php echo number_format($invoice['total_amount'], 2); ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($can_edit && has_permission('invoice:edit')): ?>
            <a href="edit.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Invoice
            </a>
            <?php endif; ?>
            
            <?php if (has_permission('invoice:pdf')): ?>
            <a href="pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </a>
            <?php endif; ?>
            
            <?php if ($invoice['status'] === 'draft' && has_permission('invoice:send')): ?>
            <button class="btn btn-warning" onclick="sendInvoice()">
                <i class="fas fa-paper-plane me-2"></i>Send Invoice
            </button>
            <?php endif; ?>
            
            <?php if ($invoice['status'] === 'sent' && $payfast_data): ?>
            <a href="payfast-checkout.php?invoice_id=<?php echo $invoice['id']; ?>" class="btn btn-success">
                <i class="fas fa-credit-card me-2"></i>Pay with PayFast
            </a>
            <?php endif; ?>
            
            <?php if (in_array($invoice['status'], ['draft', 'sent']) && has_permission('invoice:delete')): ?>
            <button class="btn btn-danger" onclick="voidInvoice()">
                <i class="fas fa-ban me-2"></i>Void Invoice
            </button>
            <?php endif; ?>
            
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoices
            </a>
        </div>
    </div>

    <!-- Line Items -->
    <div class="invoice-card">
        <h3 class="mb-3"><i class="fas fa-list me-2"></i>Line Items</h3>
        
        <div class="items-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center">Tax %</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo e($item['description']); ?></td>
                        <td class="text-center"><?php echo number_format($item['quantity'], 2); ?></td>
                        <td class="text-end">R<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-center"><?php echo number_format($item['tax_rate'], 1); ?>%</td>
                        <td class="text-end">R<?php echo number_format($item['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end">R<?php echo number_format($invoice['subtotal'], 2); ?></td>
                </tr>
                <tr>
                    <td>Tax (<?php echo number_format($invoice['tax_rate'], 1); ?>%):</td>
                    <td class="text-end">R<?php echo number_format($invoice['tax_amount'], 2); ?></td>
                </tr>
                <?php if ($invoice['discount_amount'] > 0): ?>
                <tr>
                    <td>Discount:</td>
                    <td class="text-end">-R<?php echo number_format($invoice['discount_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td>Total:</td>
                    <td class="text-end">R<?php echo number_format($invoice['total_amount'], 2); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Payment Information -->
    <?php if ($invoice['status'] === 'sent' && $payfast_data): ?>
    <div class="payment-section">
        <h5><i class="fas fa-credit-card me-2"></i>Payment Options</h5>
        <p class="mb-3">This invoice can be paid online using PayFast:</p>
        <a href="payfast-checkout.php?invoice_id=<?php echo $invoice['id']; ?>" class="payment-link">
            <i class="fas fa-credit-card"></i>
            Pay R<?php echo number_format($invoice['total_amount'], 2); ?> Online
        </a>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <?php if (!empty($payments)): ?>
    <div class="invoice-card">
        <h3 class="mb-3"><i class="fas fa-history me-2"></i>Payment History</h3>
        
        <div class="payments-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                        <td class="text-end">R<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo e($payment['reference_number'] ?: $payment['transaction_id'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-success">Completed</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Additional Information -->
    <?php if ($invoice['notes'] || $invoice['terms_conditions'] || $invoice['payment_instructions']): ?>
    <div class="invoice-card">
        <h3 class="mb-3"><i class="fas fa-file-text me-2"></i>Additional Information</h3>
        
        <?php if ($invoice['notes']): ?>
        <div class="mb-3">
            <h6>Notes:</h6>
            <p class="text-muted"><?php echo nl2br(e($invoice['notes'])); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($invoice['terms_conditions']): ?>
        <div class="mb-3">
            <h6>Terms & Conditions:</h6>
            <p class="text-muted"><?php echo nl2br(e($invoice['terms_conditions'])); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($invoice['payment_instructions']): ?>
        <div class="mb-3">
            <h6>Payment Instructions:</h6>
            <p class="text-muted"><?php echo nl2br(e($invoice['payment_instructions'])); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Send Invoice Modal -->
<div class="modal fade" id="sendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="send">
                    <p>Are you sure you want to send this invoice to <strong><?php echo e($invoice['client_name']); ?></strong>?</p>
                    <p class="text-muted">The client will receive an email with the invoice PDF and payment instructions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Void Invoice Modal -->
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Void Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="void">
                    <p>Are you sure you want to void this invoice?</p>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for voiding:</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Void Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function sendInvoice() {
    const modal = new bootstrap.Modal(document.getElementById('sendModal'));
    modal.show();
}

function voidInvoice() {
    const modal = new bootstrap.Modal(document.getElementById('voidModal'));
    modal.show();
}
</script>
</body>
</html>
