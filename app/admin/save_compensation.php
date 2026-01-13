<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['case_id']) || !isset($input['compensation_amount'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

$case_id = (int)$input['case_id'];
$amount = (float)$input['compensation_amount'];
$notes = trim($input['notes'] ?? '');
$user_id = get_user_id() ?? 0;

if ($case_id <= 0 || $amount <= 0 || $user_id === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid input or not logged in']);
    exit;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Check case is closed
    $stmt = $pdo->prepare("SELECT id FROM cases WHERE id = ? AND status = 'closed'");
    $stmt->execute([$case_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Case not found or not closed");
    }

    // Prevent duplicate
    $stmt = $pdo->prepare("SELECT id FROM compensations WHERE case_id = ?");
    $stmt->execute([$case_id]);
    if ($stmt->fetch()) {
        throw new Exception("Compensation already exists for this case");
    }

    $client_payment = round($amount * 0.25, 2);

    // Insert compensation
    $stmt = $pdo->prepare("
        INSERT INTO compensations 
        (case_id, compensation_amount, client_payment_amount, notes, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$case_id, $amount, $client_payment, $notes, $user_id]);
    $comp_id = $pdo->lastInsertId();

    // Create invoice
    $invoice_number = 'COMP-' . date('Y') . '-' . str_pad($comp_id, 6, '0', STR_PAD_LEFT);
    $due_date = date('Y-m-d', strtotime('+30 days'));

    $stmt = $pdo->prepare("
        INSERT INTO invoices 
        (case_id, invoice_number, amount, status, due_date, notes, created_by, created_at)
        VALUES (?, ?, ?, 'pending', ?, 'Client 25% compensation payment', ?, NOW())
    ");
    $stmt->execute([$case_id, $invoice_number, $client_payment, $due_date, $user_id]);
    $invoice_id = $pdo->lastInsertId();

    // Link invoice
    $stmt = $pdo->prepare("UPDATE compensations SET invoice_id = ? WHERE id = ?");
    $stmt->execute([$invoice_id, $comp_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'invoice_number' => $invoice_number,
        'client_payment' => $client_payment,
        'message' => 'Success'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("COMPENSATION ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>