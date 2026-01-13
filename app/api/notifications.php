<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Handle mark as read action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }
    
    try {
        $pdo = db();
        $notification_id = (int)$_POST['notification_id'];
        $user_id = get_user_id();
        
        // Verify the notification belongs to the current user
        $stmt = $pdo->prepare('SELECT id FROM user_notifications WHERE id = ? AND user_id = ?');
        $stmt->execute([$notification_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Update the notification as read
            $update_stmt = $pdo->prepare('UPDATE user_notifications SET is_read = TRUE WHERE id = ? AND user_id = ?');
            $update_stmt->execute([$notification_id, $user_id]);
            
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Notification not found']);
        }
        
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark notification as read']);
    }
    exit;
}

// Ensure logged in to read notifications
if (!is_logged_in()) {
    http_response_code(200);
    echo json_encode(['unread' => 0, 'items' => []]);
    exit;
}

$pdo = db();
$userId = get_user_id();

$unreadOnly = isset($_GET['unread']) && (string)$_GET['unread'] === '1';
$badgesOnly = isset($_GET['badges']) && (string)$_GET['badges'] === '1';
$limit = (int)($_GET['limit'] ?? 10);
if ($limit <= 0 || $limit > 100) { $limit = 10; }

try {
    // If badges only requested, return badge counts
    if ($badgesOnly) {
        $badges = [];
        
        // Notifications count
        $notifStmt = $pdo->prepare('SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE');
        $notifStmt->execute([$userId]);
        $badges['notifications'] = (int)$notifStmt->fetchColumn();
        
        // Messages count (unread messages from message_threads - case-related)
        $msgStmt = $pdo->prepare('
            SELECT COUNT(DISTINCT m.id) FROM messages m
            JOIN message_threads mt ON m.thread_id = mt.id
            JOIN cases c ON mt.case_id = c.id
            WHERE c.user_id = ? AND m.read_at IS NULL AND m.sender_id != ?
        ');
        $msgStmt->execute([$userId, $userId]);
        $caseMessages = (int)$msgStmt->fetchColumn();
        
        // Support messages count (unread messages from support threads)
        try {
            $supportStmt = $pdo->prepare('
                SELECT COUNT(DISTINCT m.id) FROM messages m
                JOIN message_threads mt ON m.thread_id = mt.id
                WHERE mt.thread_type = "support" 
                AND mt.created_by = ? 
                AND m.read_at IS NULL 
                AND m.sender_id != ?
            ');
            $supportStmt->execute([$userId, $userId]);
            $supportMessages = (int)$supportStmt->fetchColumn();
        } catch (Throwable $e) {
            $supportMessages = 0;
        }
        
        // Combine case messages and support messages
        $badges['messages'] = $caseMessages + $supportMessages;
        
        // Separate support messages count for Technical Support badge
        $badges['support_messages'] = $supportMessages;
        
        // Active cases count
        $casesStmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE user_id = ? AND status = 'active'");
        $casesStmt->execute([$userId]);
        $badges['cases'] = (int)$casesStmt->fetchColumn();
        
        // Upcoming appointments count (next 7 days)
        $apptStmt = $pdo->prepare('
            SELECT COUNT(*) FROM appointments a
            JOIN cases c ON a.case_id = c.id 
            WHERE c.user_id = ? AND a.status = "scheduled" 
            AND a.start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
        ');
        $apptStmt->execute([$userId]);
        $badges['appointments'] = (int)$apptStmt->fetchColumn();
        
        // Cart items count (service requests in cart status)
        $cartStmt = $pdo->prepare('
            SELECT COUNT(*) FROM service_requests sr
            JOIN cases c ON sr.case_id = c.id
            WHERE c.user_id = ? AND sr.status = "cart"
        ');
        $cartStmt->execute([$userId]);
        $badges['cart'] = (int)$cartStmt->fetchColumn();
        
        // Pending service requests count (service requests in pending status)
        $pendingStmt = $pdo->prepare('
            SELECT COUNT(*) FROM service_requests sr
            JOIN cases c ON sr.case_id = c.id
            WHERE c.user_id = ? AND sr.status = "pending"
        ');
        $pendingStmt->execute([$userId]);
        $badges['pending_requests'] = (int)$pendingStmt->fetchColumn();
        
        // Map legacy/frontend expected keys for backward compatibility
        // Admin header expects: new_messages, pending_requests, system_alerts
        $badges['new_messages'] = $badges['messages'] ?? 0;
        $badges['pending_requests'] = $badges['pending_requests'] ?? 0;
        $badges['system_alerts'] = $badges['notifications'] ?? 0;
        
        echo json_encode($badges);
        exit;
    }
    
    // Regular notifications response
    $cntStmt = $pdo->prepare('SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE');
    $cntStmt->execute([$userId]);
    $unread = (int)$cntStmt->fetchColumn();

    $sql = 'SELECT id, type, title, message, action_url, is_read, created_at FROM user_notifications WHERE user_id = ?';
    $params = [$userId];
    if ($unreadOnly) { $sql .= ' AND is_read = FALSE'; }
    $sql .= ' ORDER BY created_at DESC LIMIT ' . $limit;
    $listStmt = $pdo->prepare($sql);
    $listStmt->execute($params);
    $items = $listStmt->fetchAll();

    echo json_encode(['unread' => $unread, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load notifications']);
}
?>