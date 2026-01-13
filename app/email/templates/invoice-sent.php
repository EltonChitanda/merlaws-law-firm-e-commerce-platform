<?php
// Email template for invoice delivery
// Variables: $invoice, $client_name, $client_email, $invoice_url, $payment_url

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
    <title>Invoice #<?php echo e($invoice['invoice_number']); ?> - <?php echo e($company_name); ?></title>
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
            background: linear-gradient(135deg, #1a1a1a 0%, #0d1117 100%);
            color: white;
            padding: 30px;
            text-align: center;
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
        
        .invoice-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #c9a96e;
        }
        
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
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
            font-size: 24px;
            font-weight: 800;
            color: #1a1a1a;
            text-align: center;
            margin: 20px 0;
        }
        
        .payment-section {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        
        .payment-button {
            display: inline-block;
            background: white;
            color: #059669;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            margin: 15px 0;
            transition: all 0.3s ease;
        }
        
        .payment-button:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }
        
        .payment-instructions {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .payment-instructions h4 {
            color: #1a1a1a;
            margin-bottom: 15px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .payment-method {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        
        .payment-method-icon {
            font-size: 24px;
            margin-bottom: 10px;
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
            color: #c9a96e;
        }
        
        .terms {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
            line-height: 1.4;
        }
        
        @media (max-width: 600px) {
            .invoice-details {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="company-logo"><?php echo e($company_name); ?></div>
            <div class="company-tagline">Professional Legal Services</div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Dear <?php echo e($client_name); ?>,
            </div>
            
            <p>Thank you for choosing <?php echo e($company_name); ?> for your legal needs. Please find your invoice attached below.</p>
            
            <!-- Invoice Summary -->
            <div class="invoice-summary">
                <h3 style="margin: 0 0 15px 0; color: #1a1a1a;">Invoice Summary</h3>
                
                <div class="invoice-details">
                    <div class="detail-item">
                        <div class="detail-label">Invoice Number</div>
                        <div class="detail-value">#<?php echo e($invoice['invoice_number']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Invoice Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($invoice['invoice_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Due Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($invoice['due_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                PENDING PAYMENT
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="amount-highlight">
                    Total Amount: R<?php echo number_format($invoice['total_amount'], 2); ?>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div class="payment-section">
                <h3 style="margin: 0 0 15px 0;">Pay Online Now</h3>
                <p style="margin: 0 0 20px 0; opacity: 0.9;">Secure payment processing with PayFast</p>
                
                <a href="<?php echo e($payment_url); ?>" class="payment-button">
                    <i class="fas fa-credit-card" style="margin-right: 8px;"></i>
                    Pay R<?php echo number_format($invoice['total_amount'], 2); ?> Now
                </a>
                
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">
                    <i class="fas fa-shield-alt" style="margin-right: 5px;"></i>
                    Secured by PayFast's industry-standard encryption
                </p>
            </div>
            
            <!-- Payment Instructions -->
            <div class="payment-instructions">
                <h4>Payment Options</h4>
                <p>You can pay this invoice using any of the following methods:</p>
                
                <div class="payment-methods">
                    <div class="payment-method">
                        <div class="payment-method-icon">💳</div>
                        <div style="font-weight: 600;">Online Payment</div>
                        <div style="font-size: 12px; color: #6b7280;">Credit/Debit Card</div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="payment-method-icon">🏦</div>
                        <div style="font-weight: 600;">Bank Transfer</div>
                        <div style="font-size: 12px; color: #6b7280;">EFT Payment</div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="payment-method-icon">💵</div>
                        <div style="font-weight: 600;">Cash</div>
                        <div style="font-size: 12px; color: #6b7280;">In Person</div>
                    </div>
                </div>
                
                <?php if ($invoice['payment_instructions']): ?>
                <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 6px; border-left: 3px solid #c9a96e;">
                    <strong>Payment Instructions:</strong><br>
                    <?php echo nl2br(e($invoice['payment_instructions'])); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Terms and Conditions -->
            <?php if ($invoice['terms_conditions']): ?>
            <div style="margin: 25px 0; padding: 20px; background: #f8fafc; border-radius: 8px;">
                <h4 style="margin: 0 0 15px 0; color: #1a1a1a;">Terms & Conditions</h4>
                <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(e($invoice['terms_conditions'])); ?></p>
            </div>
            <?php endif; ?>
            
            <p>If you have any questions about this invoice or need assistance with payment, please don't hesitate to contact us.</p>
            
            <p>Thank you for your business!</p>
            
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
                <p>This email was sent to <?php echo e($client_email); ?> because you are a client of <?php echo e($company_name); ?>.</p>
                <p>If you believe you received this email in error, please contact us immediately.</p>
            </div>
        </div>
    </div>
</body>
</html>
