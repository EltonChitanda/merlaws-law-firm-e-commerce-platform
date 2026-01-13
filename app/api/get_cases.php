<?php
require_once __DIR__ . '/../config.php';
if (!has_permission('invoice:create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
$pdo = db();
$stmt = $pdo->prepare("SELECT id, title FROM cases WHERE status = 'closed' ORDER BY created_at DESC");
$stmt->execute();
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'cases' => $cases]);
