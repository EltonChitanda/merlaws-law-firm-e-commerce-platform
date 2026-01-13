<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check for required permissions
if (!has_permission('invoice:create') && !has_permission('notification:create')) {
    echo json_encode(['success' => false, 'error' => 'Permission denied.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$case_id = $data['case_id'] ?? null;
$client_payment_amount = $data['client_payment_amount'] ?? null;

if (!$case_id || !$client_payment_amount) {
    echo json_encode(['success' => false, 'error' => 'Missing case_id or client_payment_amount.']);
    exit;
}

$pdo = db();

try {
    // Get client user_id from case
    $stmt = $pdo->prepare("SELECT user_id FROM cases WHERE id = ?");
    $stmt->execute([$case_id]);
    $case = $stmt->fetch();

    if (!$case) {
        echo json_encode(['success' => false, 'error' => 'Case not found.']);
        exit;
    }
    $client_user_id = $case['user_id'];

    // Create a new invoice
    $invoice_number = 'COMP-' . time();
    $due_date = date('Y-m-d', strtotime('+30 days'));
    
    $stmt = $pdo->prepare("
        INSERT INTO invoices (case_id, invoice_number, amount, status, due_date, notes, created_by)
        VALUES (?, ?, ?, 'pending', ?, ?, ?)
    ");
    $created_by = get_user_id();
    $stmt->execute([$case_id, $invoice_number, $client_payment_amount, $due_date, 'Compensation Client Payment', $created_by]);
    $invoice_id = $pdo->lastInsertId();

    // Create notification for the client
    $notification_title = 'New Invoice for Compensation';
    $notification_message = 'An invoice for your compensation payment of R' . number_format($client_payment_amount, 2) . ' has been generated.';
    $notification_url = '/app/view_invoice.php?invoice_id=' . $invoice_id;

    create_user_notification(
        $client_user_id,
        'invoice',
        $notification_title,
        $notification_message,
        $notification_url
    );

    echo json_encode([
        'success' => true, 
        'message' => 'Invoice created and notification sent.',
        'invoice_id' => $invoice_id,
        'invoice_number' => $invoice_number
    ]);

} catch (Exception $e) {
    error_log('Error in send_compensation_notification.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
