<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    require_admin();
    $role = get_user_role();
    if (!in_array($role, ['billing', 'partner', 'super_admin'], true)) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $pdo = db();

    // Check if compensations table exists
    $hasCompTable = $pdo->query("SHOW TABLES LIKE 'compensations'")->rowCount() > 0;
    $compensationFilter = $hasCompTable
        ? " AND NOT EXISTS (SELECT 1 FROM compensations cmp WHERE cmp.case_id = c.id)"
        : "";

    $sql = "
        SELECT 
            c.id,
            c.title,
            c.case_number,
            COALESCE(c.title, 'Untitled Case') AS display_title,
            u.name AS client_name
        FROM cases c
        JOIN users u ON u.id = c.user_id
        WHERE c.status = 'closed'
        $compensationFilter
        ORDER BY c.updated_at DESC, c.id DESC
        LIMIT 200
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cases = [];
    foreach ($rows as $r) {
        $caseRef = $r['case_number'];
        if (!$caseRef || trim($caseRef) === '') {
            $caseRef = 'CASE-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT);
        }

        $label = sprintf(
            '%s - %s (Client: %s)',
            $caseRef,
            $r['display_title'],
            $r['client_name'] ?: 'Unknown Client'
        );

        $cases[] = [
            'id' => (int)$r['id'],
            'label' => $label,
            'title' => $r['display_title'],
            'case_number' => $caseRef,
            'client_name' => $r['client_name'] ?: 'Unknown',
        ];
    }

    echo json_encode(['success' => true, 'cases' => $cases]);

} catch (Throwable $e) {
    error_log("fetch_closed_cases error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}