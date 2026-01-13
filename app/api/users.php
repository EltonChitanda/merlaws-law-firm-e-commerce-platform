<?php
// app/api/users.php
require __DIR__ . '/../config.php';
require_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_attorneys':
            $pdo = db();
            
            $stmt = $pdo->prepare("
                SELECT id, name, email
                FROM users
                WHERE role IN ('attorney', 'partner')
                AND is_active = 1
                ORDER BY name
            ");
            $stmt->execute();
            $attorneys = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'attorneys' => $attorneys
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to process request: ' . $e->getMessage()
    ]);
}
?>
