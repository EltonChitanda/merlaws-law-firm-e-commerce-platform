<?php
// app/api/cases.php
require __DIR__ . '/../config.php';
require_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$case_id = (int)($_GET['case_id'] ?? 0);

if (!$case_id) {
    echo json_encode(['success' => false, 'error' => 'Case ID is required']);
    exit;
}

// Verify case belongs to user
$case = get_case($case_id, get_user_id());
if (!$case) {
    echo json_encode(['success' => false, 'error' => 'Case not found']);
    exit;
}

try {
    switch ($action) {
        case 'get_attorneys':
            $pdo = db();
            
            // Get attorneys assigned to this case
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.name, u.email
                FROM users u
                JOIN cases c ON c.assigned_to = u.id
                WHERE c.id = ? AND u.role IN ('attorney', 'partner')
                UNION
                SELECT DISTINCT u.id, u.name, u.email
                FROM users u
                JOIN case_assignments ca ON ca.user_id = u.id
                WHERE ca.case_id = ? AND u.role IN ('attorney', 'partner')
            ");
            $stmt->execute([$case_id, $case_id]);
            $attorneys = $stmt->fetchAll();
            
            // If no attorneys assigned to case, get all available attorneys
            if (empty($attorneys)) {
                $stmt = $pdo->prepare("
                    SELECT id, name, email
                    FROM users
                    WHERE role IN ('attorney', 'partner')
                    AND is_active = 1
                    ORDER BY name
                ");
                $stmt->execute();
                $attorneys = $stmt->fetchAll();
            }
            
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
