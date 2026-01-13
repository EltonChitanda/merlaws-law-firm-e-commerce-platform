<?php
/**
 * PayFastService - Handles PayFast payment processing
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/payfast.php';
require_once __DIR__ . '/payfast_helpers.php';

class PayFastService {
    private $pdo;
    
    public function __construct() {
        $this->pdo = db();
    }
    
    /**
     * Generate PayFast payment data for an invoice
     * 
     * @param int $invoice_id
     * @return array ['success' => bool, 'data' => array, 'payment_url' => string, 'error' => string|null]
     */
    public function generatePaymentData(int $invoice_id): array {
        try {
            // Fetch invoice and verify ownership
            $user_id = get_user_id();
            $stmt = $this->pdo->prepare("
                SELECT i.*, u.name as client_name, u.email as client_email, c.id as case_id
                FROM invoices i
                JOIN cases c ON i.case_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE i.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$invoice_id, $user_id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                return [
                    'success' => false,
                    'error' => 'Invoice not found or you do not have permission to pay for it.'
                ];
            }
            
            if ($invoice['status'] === 'paid') {
                return [
                    'success' => false,
                    'error' => 'This invoice has already been paid.'
                ];
            }
            
            // Calculate total amount
            $subtotal = (float)$invoice['amount'];
            $tax_rate = (float)($invoice['tax_rate'] ?? 15.00);
            $tax_amount = $subtotal * ($tax_rate / 100);
            $total_amount = $subtotal + $tax_amount;
            
            // Get PayFast credentials
            $credentials = get_payfast_credentials();
            
            // Split client name into first and last
            $name_parts = explode(' ', trim($invoice['client_name']));
            $name_first = $name_parts[0] ?? 'Client';
            $name_last = implode(' ', array_slice($name_parts, 1)) ?: 'User';
            
            $return_url = $this->appendInvoiceQuery(PAYFAST_RETURN_URL, $invoice['id']);
            $cancel_url = $this->appendInvoiceQuery(PAYFAST_CANCEL_URL, $invoice['id']);

            // Generate PayFast data
            $data = [
                'merchant_id' => $credentials['merchant_id'],
                'merchant_key' => $credentials['merchant_key'],
                'return_url' => $return_url,
                'cancel_url' => $cancel_url,
                'notify_url' => PAYFAST_NOTIFY_URL,
                'name_first' => $name_first,
                'name_last' => $name_last,
                'email_address' => $invoice['client_email'],
                'm_payment_id' => (string)$invoice['id'],
                'amount' => number_format($total_amount, 2, '.', ''),
                'item_name' => 'Invoice #' . $invoice['invoice_number'],
                'item_description' => 'Payment for legal services - Invoice #' . $invoice['invoice_number'],
            ];
            
            // Generate signature
            $data['signature'] = generate_payfast_signature($data, $credentials['passphrase']);
            
            // Get PayFast URL
            $payment_url = get_payfast_url();
            
            return [
                'success' => true,
                'data' => $data,
                'payment_url' => $payment_url
            ];
            
        } catch (Exception $e) {
            error_log("PayFastService::generatePaymentData error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred while processing your payment request.'
            ];
        }
    }
    
    /**
     * Process PayFast ITN (Instant Transaction Notification)
     * 
     * @param array $post_data
     * @return array ['success' => bool, 'message' => string, 'error' => string|null]
     */
    public function processITN(array $post_data): array {
        try {
            // Validate signature
            $credentials = get_payfast_credentials();
            $received_sig = $post_data['signature'] ?? '';
            $is_valid = validate_payfast_signature($post_data, $received_sig, $credentials['passphrase']);
            
            if (!$is_valid) {
                error_log("PayFast ITN: Invalid signature");
                return [
                    'success' => false,
                    'error' => 'Invalid signature'
                ];
            }
            
            // Extract payment data
            $invoice_id = (int)($post_data['m_payment_id'] ?? 0);
            $payment_status = $post_data['payment_status'] ?? '';
            $amount_gross = (float)($post_data['amount_gross'] ?? 0);
            $pf_payment_id = $post_data['pf_payment_id'] ?? '';
            
            if (!$invoice_id || !$pf_payment_id) {
                return [
                    'success' => false,
                    'error' => 'Missing required payment data'
                ];
            }
            
            // Check if transaction has already been processed
            $stmt = $this->pdo->prepare("SELECT id FROM invoice_payments WHERE payfast_payment_id = ?");
            $stmt->execute([$pf_payment_id]);
            if ($stmt->fetch()) {
                error_log("PayFast ITN: Transaction {$pf_payment_id} already processed");
                return [
                    'success' => true,
                    'message' => 'Transaction already processed'
                ];
            }
            
            // Fetch the invoice
            $stmt = $this->pdo->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                return [
                    'success' => false,
                    'error' => "Invoice ID {$invoice_id} not found"
                ];
            }
            
            // Recalculate total to validate
            $subtotal = (float)$invoice['amount'];
            $tax_rate = (float)($invoice['tax_rate'] ?? 15.00);
            $tax_amount = $subtotal * ($tax_rate / 100);
            $expected_total = $subtotal + $tax_amount;
            
            // Validate amount
            if (abs($amount_gross - $expected_total) > 0.01) {
                error_log("PayFast ITN: Amount mismatch. Expected: {$expected_total}, Received: {$amount_gross}");
                return [
                    'success' => false,
                    'error' => 'Amount mismatch'
                ];
            }
            
            // Process payment if status is COMPLETE
            if ($payment_status === 'COMPLETE') {
                $this->pdo->beginTransaction();
                
                try {
                    // Record payment
                    $stmt = $this->pdo->prepare("
                        INSERT INTO invoice_payments 
                        (invoice_id, amount, payment_method, payment_date, payfast_payment_id, transaction_id, status, created_at)
                        VALUES (?, ?, 'payfast', NOW(), ?, ?, 'completed', NOW())
                    ");
                    $stmt->execute([
                        $invoice_id,
                        $amount_gross,
                        $pf_payment_id,
                        $pf_payment_id
                    ]);
                    
                    // Update invoice status
                    $stmt = $this->pdo->prepare("
                        UPDATE invoices 
                        SET status = 'paid', paid_at = NOW(), updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$invoice_id]);
                    
                    $this->pdo->commit();
                    
                    error_log("PayFast ITN: Payment processed successfully for invoice {$invoice_id}");
                    return [
                        'success' => true,
                        'message' => 'Payment processed successfully'
                    ];
                    
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("PayFast ITN: Database error - " . $e->getMessage());
                    return [
                        'success' => false,
                        'error' => 'Database error: ' . $e->getMessage()
                    ];
                }
            } else {
                // Log other statuses for reference
                error_log("PayFast ITN: Payment status '{$payment_status}' for invoice {$invoice_id}");
                return [
                    'success' => true,
                    'message' => "Payment status: {$payment_status}"
                ];
            }
            
        } catch (Exception $e) {
            error_log("PayFastService::processITN error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error processing ITN: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create payment form HTML (for AJAX responses)
     * 
     * @param int $invoice_id
     * @return string HTML form
     */
    public function createPaymentForm(int $invoice_id): string {
        $payment_data = $this->generatePaymentData($invoice_id);
        
        if (!$payment_data['success']) {
            return '<div class="alert alert-danger">' . htmlspecialchars($payment_data['error']) . '</div>';
        }
        
        $data = $payment_data['data'];
        $payment_url = $payment_data['payment_url'];
        
        $form = '<form action="' . htmlspecialchars($payment_url) . '" method="post" id="payfast-form">';
        foreach ($data as $key => $value) {
            $form .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        $form .= '</form>';
        
        return $form;
    }

    /**
     * Append invoice_id as query string whilst preserving existing params.
     */
    private function appendInvoiceQuery(string $url, int $invoice_id): string {
        if (!$invoice_id) {
            return $url;
        }

        $separator = (parse_url($url, PHP_URL_QUERY) === null) ? '?' : '&';
        return $url . $separator . 'invoice_id=' . urlencode((string)$invoice_id);
    }
}

