<?php
// app/api/admin-analytics.php
require __DIR__ . '/../config.php';
require_admin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pdo = db();

try {
    switch ($action) {
        case 'dashboard_stats':
            // Get dashboard statistics without stored procedure
            try {
                // Try stored procedure first
                $stmt = $pdo->query("CALL sp_get_admin_dashboard_stats()");
                $stats = $stmt->fetch();
            } catch (Exception $e) {
                // Fallback to direct queries
                $stats = [];
                
                // Total users
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
                $stats['total_users'] = (int)$stmt->fetchColumn();
                
                // Active cases
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
                $stats['active_cases'] = (int)$stmt->fetchColumn();
                
                // Pending requests
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
                $stats['pending_requests'] = (int)$stmt->fetchColumn();
                
                // Average processing hours
                $stmt = $pdo->query("
                    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) 
                    FROM cases 
                    WHERE status IN ('resolved', 'closed') 
                    AND updated_at IS NOT NULL
                ");
                $avg_hours = $stmt->fetchColumn();
                $stats['avg_processing_hours'] = $avg_hours ? round((float)$avg_hours) : 0;
            }
            
            // Additional analytics
            try {
                $stmt = $pdo->query("
                    SELECT 
                        COUNT(DISTINCT u.id) as active_users_today,
                        COUNT(DISTINCT CASE WHEN u.last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN u.id END) as active_users_week,
                        AVG(CASE WHEN sr.processed_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, sr.requested_at, sr.processed_at) END) as avg_processing_hours
                    FROM users u
                    LEFT JOIN service_requests sr ON sr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    WHERE u.role = 'client'
                ");
                $additional_stats = $stmt->fetch();
            } catch (Exception $e) {
                $additional_stats = [
                    'active_users_today' => 0,
                    'active_users_week' => 0,
                    'avg_processing_hours' => 0
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => array_merge($stats, $additional_stats)
            ]);
            break;
            
        case 'user_activity':
            $days = (int)($_GET['days'] ?? 30);
            
            // Get login data from audit_logs (primary source)
            $data = [];
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(al.created_at) as date,
                        'login' as event_type,
                        COUNT(*) as count
                    FROM audit_logs al
                    WHERE al.event_type = 'login' 
                    AND al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(al.created_at)
                    ORDER BY date ASC
                ");
                $stmt->execute([$days]);
                $login_data = $stmt->fetchAll();
                $data = array_merge($data, $login_data);
            } catch (Exception $e) {
                // Fallback to security_logs if audit_logs doesn't exist
                try {
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(sl.created_at) as date,
                            'login' as event_type,
                            COUNT(*) as count
                        FROM security_logs sl
                        WHERE sl.event_type = 'login' 
                        AND sl.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        GROUP BY DATE(sl.created_at)
                        ORDER BY date ASC
                    ");
                    $stmt->execute([$days]);
                    $login_data = $stmt->fetchAll();
                    $data = array_merge($data, $login_data);
                } catch (Exception $e2) {
                    // If both fail, try analytics_events
                }
            }
            
            // Get case creation data
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(c.created_at) as date,
                        'case_created' as event_type,
                        COUNT(*) as count
                    FROM cases c
                    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(c.created_at)
                    ORDER BY date ASC
                ");
                $stmt->execute([$days]);
                $case_data = $stmt->fetchAll();
                $data = array_merge($data, $case_data);
            } catch (Exception $e) {
                // Continue without case data
            }
            
            // Also try analytics_events as fallback
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(ae.created_at) as date,
                        ae.event_type,
                        COUNT(*) as count
                    FROM analytics_events ae
                    WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND ae.event_type IN ('login', 'case_created')
                    GROUP BY DATE(ae.created_at), ae.event_type
                    ORDER BY date ASC
                ");
                $stmt->execute([$days]);
                $analytics_data = $stmt->fetchAll();
                // Merge with existing data, avoiding duplicates
                foreach ($analytics_data as $item) {
                    $exists = false;
                    foreach ($data as $existing) {
                        if ($existing['date'] === $item['date'] && $existing['event_type'] === $item['event_type']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $data[] = $item;
                    }
                }
            } catch (Exception $e) {
                // Continue without analytics_events data
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'case_statistics':
            $stmt = $pdo->query("
                SELECT 
                    c.case_type,
                    c.status,
                    COUNT(*) as count,
                    AVG(TIMESTAMPDIFF(DAY, c.created_at, COALESCE(c.updated_at, NOW()))) as avg_duration_days
                FROM cases c
                WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY c.case_type, c.status
                ORDER BY count DESC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'service_performance':
            $stmt = $pdo->query("
                SELECT 
                    s.name,
                    s.category,
                    COUNT(sr.id) as total_requests,
                    COUNT(CASE WHEN sr.status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN sr.status = 'rejected' THEN 1 END) as rejected,
                    AVG(CASE WHEN sr.processed_at IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, sr.requested_at, sr.processed_at) END) as avg_hours
                FROM services s
                LEFT JOIN service_requests sr ON s.id = sr.service_id
                    AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY s.id, s.name, s.category
                HAVING total_requests > 0
                ORDER BY total_requests DESC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'monthly_trends':
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(DISTINCT CASE WHEN role = 'client' THEN id END) as new_users,
                    (SELECT COUNT(*) FROM cases WHERE DATE_FORMAT(created_at, '%Y-%m') = month) as new_cases,
                    (SELECT COUNT(*) FROM service_requests WHERE DATE_FORMAT(requested_at, '%Y-%m') = month AND status != 'cart') as service_requests
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>