<?php
// Email template for payment confirmation
// Variables: $invoice, $payment, $client_name, $client_email, $invoice_url

$company_name = "MerLaws Attorneys";
$company_email = "info@merlaws.com";
$company_phone = "+27 11 123 4567";
$company_address = "123 Legal Street, Johannesburg, 2000, South Africa";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Invoice #<?php echo e($invoice['invoice_number']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        
        .email-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .company-tagline {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        
        .success-message {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .success-message h3 {
            color: #065f46;
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        
        .success-message p {
            color: #047857;
            margin: 0;
            font-size: 16px;
        }
        
        .payment-details {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        
        .payment-details h4 {
            color: #1a1a1a;
            margin: 0 0 15px 0;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        
        .detail-value {
            color: #1a1a1a;
            font-size: 16px;
        }
        
        .amount-highlight {
            font-size: 28px;
            font-weight: 800;
            color: #10b981;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 8px;
        }
        
        .invoice-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .invoice-info h4 {
            color: #1a1a1a;
            margin: 0 0 15px 0;
        }
        
        .action-buttons {
            text-align: center;
            margin: 25px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px 10px 0;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f8fafc;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .footer {
            background: #f8fafc;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-info {
            margin-bottom: 15px;
        }
        
        .footer-info p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-links a {
            color: #6b7280;
            text-decoration: none;
            margin: 0 10px;
            font-size: 18px;
        }
        
        .social-links a:hover {
            color: #10b981;
        }
        
        .terms {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
            line-height: 1.4;
        }
        
        .receipt-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .receipt-info h5 {
            color: #92400e;
            margin: 0 0 10px 0;
        }
        
        .receipt-info p {
            color: #b45309;
            margin: 0;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 0 0 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="success-icon">✅</div>
            <div class="company-logo"><?php echo e($company_name); ?></div>
            <div class="company-tagline">Payment Confirmation</div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Dear <?php echo e($client_name); ?>,
            </div>
            
            <div class="success-message">
                <h3>Payment Received Successfully!</h3>
                <p>Thank you for your payment. Your invoice has been marked as paid.</p>
            </div>
            
            <!-- Payment Details -->
            <div class="payment-details">
                <h4>Payment Details</h4>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Payment Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Transaction ID</div>
                        <div class="detail-value"><?php echo e($payment['transaction_id'] ?: $payment['reference_number'] ?: 'N/A'); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                COMPLETED
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="amount-highlight">
                    Amount Paid: R<?php echo number_format($payment['amount'], 2); ?>
                </div>
            </div>
            
            <!-- Invoice Information -->
            <div class="invoice-info">
                <h4>Invoice Information</h4>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Invoice Number</div>
                        <div class="detail-value">#<?php echo e($invoice['invoice_number']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Invoice Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($invoice['invoice_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Total Amount</div>
                        <div class="detail-value">R<?php echo number_format($invoice['total_amount'], 2); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                PAID
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Receipt Information -->
            <div class="receipt-info">
                <h5>📄 Receipt & Records</h5>
                <p>This email serves as your payment confirmation. Please keep it for your records along with your bank statement or payment confirmation.</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?php echo e($invoice_url); ?>" class="btn btn-primary">
                    <i class="fas fa-file-invoice" style="margin-right: 8px;"></i>
                    View Invoice
                </a>
                
                <a href="<?php echo e($invoice_url); ?>&pdf=1" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-file-pdf" style="margin-right: 8px;"></i>
                    Download Receipt
                </a>
            </div>
            
            <p>If you have any questions about this payment or need assistance, please don't hesitate to contact us.</p>
            
            <p>Thank you for your business and prompt payment!</p>
            
            <p>
                Best regards,<br>
                <strong><?php echo e($company_name); ?> Team</strong>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-info">
                <p><strong><?php echo e($company_name); ?></strong></p>
                <p><?php echo e($company_address); ?></p>
                <p>Phone: <?php echo e($company_phone); ?> | Email: <?php echo e($company_email); ?></p>
            </div>
            
            <div class="social-links">
                <a href="#" title="Facebook">📘</a>
                <a href="#" title="LinkedIn">💼</a>
                <a href="#" title="Twitter">🐦</a>
            </div>
            
            <div class="terms">
                <p>This email was sent to <?php echo e($client_email); ?> as a payment confirmation for invoice #<?php echo e($invoice['invoice_number']); ?>.</p>
                <p>If you believe you received this email in error, please contact us immediately.</p>
            </div>
        </div>
    </div>
</body>
</html>
