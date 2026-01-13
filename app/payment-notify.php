<?php
require __DIR__ . '/config.php';
require __DIR__ . '/config/payfast.php';

header('HTTP/1.0 200 OK');
flush();

error_log("PayFast ITN Received: " . print_r($_POST, true));

$pfData = $_POST;
$pfHost = PAYFAST_SANDBOX ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
$error = false;

// --- 1. Validate the data from PayFast ---

// Strip slashes
foreach ($pfData as $key => $val) {
    $pfData[$key] = stripslashes($val);
}

// Generate signature
$passphrase = PAYFAST_PASSPHRASE;
$signature_data = http_build_query($pfData);
if (!empty($passphrase)) {
    $signature_data .= '&passphrase=' . urlencode($passphrase);
}
$expected_signature = md5($signature_data);

if ($pfData['signature'] !== $expected_signature) {
    error_log("PayFast ITN Error: Signature mismatch.");
    $error = true;
}

// --- 2. If valid, process the payment ---
if (!$error) {
    $invoice_id = (int)$pfData['m_payment_id'];
    $payment_status = $pfData['payment_status'];
    $amount_gross = (float)$pfData['amount_gross'];
    $pf_payment_id = $pfData['pf_payment_id'];

    try {
        $pdo = db();

        // Check if transaction has already been processed
        $stmt = $pdo->prepare("SELECT id FROM invoice_payments WHERE payfast_payment_id = ?");
        $stmt->execute([$pf_payment_id]);
        if ($stmt->fetch()) {
            error_log("PayFast ITN Info: Transaction {$pf_payment_id} already processed. Skipping.");
            exit();
        }

        // Fetch the invoice
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$invoice_id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            throw new Exception("Invoice ID {$invoice_id} not found.");
        }

        // Recalculate the total amount to validate against
        $subtotal = (float)$invoice['amount'];
        $tax_rate = (float)($invoice['tax_rate'] ?? 15.00);
        $total_amount = $subtotal + ($subtotal * ($tax_rate / 100));

        // Check if amount matches
        if (abs($amount_gross - $total_amount) > 0.01) {
            throw new Exception("Amount mismatch. Expected: " . number_format($total_amount, 2) . ", Received: " . number_format($amount_gross, 2));
        }

        // Process based on status
        if ($payment_status === 'COMPLETE') {
            $pdo->beginTransaction();

            // Insert into invoice_payments
            $stmt = $pdo->prepare("
                INSERT INTO invoice_payments 
                (invoice_id, payment_method, amount, payment_date, transaction_id, payfast_payment_id, payfast_status, payfast_raw_response, created_by)
                VALUES (?, 'payfast', ?, CURDATE(), ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $invoice_id,
                $pfData['amount_net'],
                $pf_payment_id,
                $pf_payment_id,
                $payment_status,
                json_encode($pfData),
                $invoice['created_by'] // Assuming the invoice creator is the relevant user
            ]);

            // Update invoice status
            $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = ?");
            $stmt->execute([$invoice_id]);

            $pdo->commit();
            error_log("PayFast ITN Success: Payment for invoice {$invoice_id} completed.");

        } else {
            // Log other statuses (e.g., FAILED)
            error_log("PayFast ITN Status: Received status '{$payment_status}' for invoice {$invoice_id}.");
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("PayFast ITN Processing Error: " . $e->getMessage());
    }
}

exit();

