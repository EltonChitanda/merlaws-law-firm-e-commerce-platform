<?php
session_start();
require_once '../config.php';
if (!has_permission('invoice:create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
$pdo = db();
$case_id = isset($_POST['case_id']) ? (int)$_POST['case_id'] : 0;
$total_won_amount = isset($_POST['total_won_amount']) ? floatval($_POST['total_won_amount']) : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
if (!$case_id || !$total_won_amount) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}
try {
    $stmt = $pdo->prepare('SELECT user_id FROM cases WHERE id = ?');
    $stmt->execute([$case_id]);
    $client_id = $stmt->fetchColumn();
    if (!$client_id) {
        echo json_encode(['success' => false, 'error' => 'Case not found']);
        exit;
    }
    $fee_amount_due = round($total_won_amount * 0.25, 2);
    $stmt = $pdo->prepare('SELECT id FROM invoices WHERE case_id = ? AND status IN ("pending","draft")');
    $stmt->execute([$case_id]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'error' => 'An unpaid invoice already exists for this case.']);
        exit;
    }
    $invoice_number = 'INV' . $case_id . time();
    $due_date = date('Y-m-d', strtotime('+21 days'));
    $sql = 'INSERT INTO invoices (invoice_number, case_id, amount, status, due_date, created_at, created_by, notes) 
            VALUES (?, ?, ?, "pending", ?, NOW(), ?, ?)';
    $created_by = get_user_id();
    $pdo->prepare($sql)->execute([
        $invoice_number,
        $case_id,
        $fee_amount_due,
        $due_date,
        $created_by,
        $notes
    ]);
    echo json_encode(['success' => true, 'invoice_number' => $invoice_number]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
