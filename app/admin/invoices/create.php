<?php
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../services/InvoiceService.php';

// Check permissions
require_permission('invoice:create');

$invoice_service = new InvoiceService();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'client_id' => (int)$_POST['client_id'],
        'case_id' => !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null,
        'invoice_date' => $_POST['invoice_date'],
        'due_date' => $_POST['due_date'],
        'tax_rate' => (float)$_POST['tax_rate'],
        'discount_amount' => (float)$_POST['discount_amount'],
        'notes' => $_POST['notes'] ?? null,
        'terms_conditions' => $_POST['terms_conditions'] ?? null,
        'payment_instructions' => $_POST['payment_instructions'] ?? null,
        'items' => json_decode($_POST['items'], true) ?: []
    ];
    
    $result = $invoice_service->createInvoice($data);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Invoice created successfully';
        header('Location: view.php?id=' . $result['invoice_id']);
        exit;
    } else {
        $error_message = $result['error'];
    }
}

// Get clients and cases for dropdowns
$clients = $invoice_service->getClients();
$cases = $invoice_service->getCases();

// Get services for line items
$services = get_services();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Invoice | Med Attorneys Admin</title>
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

        .form-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .line-items-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .line-items-table th {
            background: #f8fafc;
            border: none;
            font-weight: 700;
            color: var(--merlaws-primary);
            padding: 1rem;
            font-size: 0.9rem;
        }

        .line-items-table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .totals-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
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
            font-size: 1.1rem;
            background: var(--merlaws-gold);
            color: white;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .totals-table .total-row td {
            border: none;
            padding: 1rem;
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

        .btn-outline-secondary {
            color: var(--neutral-gray);
            border-color: #e5e7eb;
        }

        .btn-outline-secondary:hover {
            background: var(--neutral-gray);
            border-color: var(--neutral-gray);
            color: white;
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

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 0.2rem rgba(201, 169, 110, 0.25);
        }

        .item-row {
            transition: all 0.3s ease;
        }

        .item-row:hover {
            background: #f8fafc;
        }

        .remove-item {
            color: var(--danger-red);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            color: #dc2626;
            transform: scale(1.1);
        }

        .add-item-btn {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-item-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .preview-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-card {
                padding: 1.5rem;
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
            <i class="fas fa-file-invoice-dollar"></i>
            Create New Invoice
        </div>
        <h1 class="page-title">Create Invoice</h1>
    </div>
</div>

<div class="container my-4">
    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo e($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" id="invoice-form">
        <!-- Basic Information -->
        <div class="form-card">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                Invoice Information
            </h3>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="client_id" class="form-label">Client *</label>
                    <select class="form-select" id="client_id" name="client_id" required>
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>" <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                            <?php echo e($client['name']); ?> (<?php echo e($client['email']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="case_id" class="form-label">Case (Optional)</label>
                    <select class="form-select" id="case_id" name="case_id">
                        <option value="">No Case</option>
                        <?php foreach ($cases as $case): ?>
                        <option value="<?php echo $case['id']; ?>" <?php echo (isset($_POST['case_id']) && $_POST['case_id'] == $case['id']) ? 'selected' : ''; ?>>
                            <?php echo e($case['title']); ?> - <?php echo e($case['client_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="invoice_date" class="form-label">Invoice Date *</label>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" 
                           value="<?php echo $_POST['invoice_date'] ?? date('Y-m-d'); ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label for="due_date" class="form-label">Due Date *</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" 
                           value="<?php echo $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')); ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                           value="<?php echo $_POST['tax_rate'] ?? '15.00'; ?>" step="0.01" min="0" max="100">
                </div>
                
                <div class="col-md-3">
                    <label for="discount_amount" class="form-label">Discount Amount (R)</label>
                    <input type="number" class="form-control" id="discount_amount" name="discount_amount" 
                           value="<?php echo $_POST['discount_amount'] ?? '0.00'; ?>" step="0.01" min="0">
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0">
                    <i class="fas fa-list"></i>
                    Line Items
                </h3>
                <button type="button" class="add-item-btn" onclick="addLineItem()">
                    <i class="fas fa-plus me-2"></i>Add Item
                </button>
            </div>
            
            <div class="line-items-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Description</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 15%;">Unit Price</th>
                            <th style="width: 10%;">Tax %</th>
                            <th style="width: 15%;">Amount</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="line-items-tbody">
                        <!-- Line items will be added here dynamically -->
                    </tbody>
                </table>
            </div>
            
            <!-- Totals -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end" id="subtotal">R 0.00</td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-end" id="tax-amount">R 0.00</td>
                    </tr>
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end" id="discount-display">R 0.00</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total:</td>
                        <td class="text-end" id="total-amount">R 0.00</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="form-card">
            <h3 class="section-title">
                <i class="fas fa-file-text"></i>
                Additional Information
            </h3>
            
            <div class="row g-3">
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Internal notes about this invoice"><?php echo e($_POST['notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                    <textarea class="form-control" id="terms_conditions" name="terms_conditions" rows="4" 
                              placeholder="Payment terms and conditions"><?php echo e($_POST['terms_conditions'] ?? 'Payment is due within 30 days of invoice date. Late payments may incur interest charges at 2% per month.'); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="payment_instructions" class="form-label">Payment Instructions</label>
                    <textarea class="form-control" id="payment_instructions" name="payment_instructions" rows="4" 
                              placeholder="Instructions for payment"><?php echo e($_POST['payment_instructions'] ?? 'Payments can be made via PayFast online payment, bank transfer, or cash. For bank transfers, please use the invoice number as reference.'); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-card">
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                </a>
                
                <div>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-save me-2"></i>Save as Draft
                    </button>
                    <button type="submit" name="action" value="create" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Line items management
let lineItemCount = 0;

function addLineItem() {
    lineItemCount++;
    const tbody = document.getElementById('line-items-tbody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td>
            <input type="text" class="form-control" name="item_description_${lineItemCount}" placeholder="Item description" required>
        </td>
        <td>
            <input type="number" class="form-control item-qty" name="item_quantity_${lineItemCount}" value="1" min="0" step="0.01" onchange="calculateLineTotal(this)">
        </td>
        <td>
            <input type="number" class="form-control item-price" name="item_unit_price_${lineItemCount}" value="0" min="0" step="0.01" onchange="calculateLineTotal(this)">
        </td>
        <td>
            <input type="number" class="form-control item-tax" name="item_tax_rate_${lineItemCount}" value="15" min="0" max="100" step="0.01" onchange="calculateTotals()">
        </td>
        <td>
            <input type="text" class="form-control item-amount" name="item_amount_${lineItemCount}" value="0.00" readonly>
        </td>
        <td>
            <i class="fas fa-trash remove-item" onclick="removeLineItem(this)" title="Remove item"></i>
        </td>
    `;
    tbody.appendChild(row);
    calculateTotals();
}

function removeLineItem(button) {
    const row = button.closest('tr');
    row.remove();
    calculateTotals();
}

function calculateLineTotal(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const amount = qty * price;
    
    row.querySelector('.item-amount').value = amount.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll('.item-row');
    
    rows.forEach(row => {
        const amount = parseFloat(row.querySelector('.item-amount').value) || 0;
        subtotal += amount;
    });
    
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const taxAmount = subtotal * (taxRate / 100);
    const totalAmount = subtotal + taxAmount - discountAmount;
    
    document.getElementById('subtotal').textContent = 'R ' + subtotal.toFixed(2);
    document.getElementById('tax-amount').textContent = 'R ' + taxAmount.toFixed(2);
    document.getElementById('discount-display').textContent = 'R ' + discountAmount.toFixed(2);
    document.getElementById('total-amount').textContent = 'R ' + totalAmount.toFixed(2);
}

// Update items JSON before form submission
document.getElementById('invoice-form').addEventListener('submit', function(e) {
    const items = [];
    const rows = document.querySelectorAll('.item-row');
    
    rows.forEach((row, index) => {
        const description = row.querySelector('input[name^="item_description_"]').value;
        const quantity = parseFloat(row.querySelector('input[name^="item_quantity_"]').value) || 0;
        const unitPrice = parseFloat(row.querySelector('input[name^="item_unit_price_"]').value) || 0;
        const taxRate = parseFloat(row.querySelector('input[name^="item_tax_rate_"]').value) || 0;
        
        if (description.trim()) {
            items.push({
                description: description,
                quantity: quantity,
                unit_price: unitPrice,
                tax_rate: taxRate
            });
        }
    });
    
    // Add hidden input with items JSON
    const itemsInput = document.createElement('input');
    itemsInput.type = 'hidden';
    itemsInput.name = 'items';
    itemsInput.value = JSON.stringify(items);
    this.appendChild(itemsInput);
});

// Auto-calculate discount display
document.getElementById('discount_amount').addEventListener('input', calculateTotals);
document.getElementById('tax_rate').addEventListener('input', calculateTotals);

// Add initial line item
document.addEventListener('DOMContentLoaded', function() {
    addLineItem();
});

// Case selection based on client
document.getElementById('client_id').addEventListener('change', function() {
    const clientId = this.value;
    const caseSelect = document.getElementById('case_id');
    
    if (clientId) {
        // In a real implementation, you would fetch cases via AJAX
        // For now, we'll just show all cases
        console.log('Client selected:', clientId);
    }
});
</script>
</body>
</html>
