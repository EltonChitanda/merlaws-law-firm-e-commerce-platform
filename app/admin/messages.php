<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

require_permission('message:view');

$pdo = db();

// AJAX endpoint for checking new messages
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_new' && isset($_GET['thread_id'])) {
    header('Content-Type: application/json');
    $thread_id = (int)$_GET['thread_id'];
    $user_id = get_user_id();
    
    try {
        // Get last message ID from the request (optional, for incremental updates)
        $last_message_id = isset($_GET['last_message_id']) ? (int)$_GET['last_message_id'] : 0;
        
        // Get messages after the last known message
        if ($last_message_id > 0) {
            $stmt = $pdo->prepare("
                SELECT m.*, u.name AS sender_name 
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE m.thread_id = ? AND m.id > ?
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$thread_id, $last_message_id]);
        } else {
            // Get last 5 messages as fallback
            $stmt = $pdo->prepare("
                SELECT m.*, u.name AS sender_name 
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE m.thread_id = ?
                ORDER BY m.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$thread_id]);
        }
        
        $new_messages = $stmt->fetchAll();
        
        // Get the last message ID for next request
        $last_id = 0;
        if (!empty($new_messages)) {
            $last_id = (int)end($new_messages)['id'];
        }
        
        echo json_encode([
            'success' => true,
            'messages' => $new_messages,
            'last_message_id' => $last_id
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch messages'
        ]);
    }
    exit;
}

// Lightweight AJAX endpoint to fetch cases for a selected client that are assigned to the current admin/paralegal
if (($_GET['ajax'] ?? '') === 'cases_for_client') {
	header('Content-Type: application/json');
	$client_id = (int)($_GET['client_id'] ?? 0);
	$current_admin_id = get_user_id();
	try {
		$stmt = $pdo->prepare("SELECT id, title FROM cases WHERE user_id = ? AND assigned_to = ? ORDER BY updated_at DESC");
		$stmt->execute([$client_id, $current_admin_id]);
		echo json_encode(['ok' => true, 'cases' => $stmt->fetchAll()]);
	} catch (Throwable $ex) {
		echo json_encode(['ok' => false, 'error' => 'Failed to load cases']);
	}
	exit;
}

$threadId = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$from = isset($_GET['from']) ? (string)$_GET['from'] : '';
$to = isset($_GET['to']) ? (string)$_GET['to'] : '';

$whereParts = [];
$params = [];
if ($q !== '') {
    $whereParts[] = '(t.subject LIKE ? OR c.title LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($from !== '') { $whereParts[] = 't.updated_at >= ?'; $params[] = $from; }
if ($to !== '') { $whereParts[] = 't.updated_at <= ?'; $params[] = $to; }
$where = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Threads (most recent first) with role-based filtering
$user_id = get_user_id();
$user_role = get_user_role();

$sql = "SELECT t.*, COALESCE(c.title, 'Support Ticket') AS case_title, u.name AS created_by_name FROM message_threads t LEFT JOIN cases c ON t.case_id=c.id JOIN users u ON t.created_by=u.id WHERE 1=1";
$sql_params = [];

// Apply role-based case filtering
if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see threads from cases assigned to them
    // Exclude support tickets (case_id IS NULL)
    $sql .= " AND (c.assigned_to = ? AND c.id IS NOT NULL)";
    $sql_params[] = $user_id;
} elseif ($user_role === 'billing') {
    // Billing sees threads from cases with financial activity
    $sql .= " AND EXISTS (SELECT 1 FROM service_requests sr WHERE sr.case_id = c.id AND sr.status != 'cart')";
} elseif ($user_role === 'doc_specialist') {
    // Document specialists see threads from cases with document activity
    $sql .= " AND EXISTS (SELECT 1 FROM case_documents cd WHERE cd.case_id = c.id)";
} elseif ($user_role === 'compliance') {
    // Compliance sees threads from cases with compliance requests
    $sql .= " AND EXISTS (SELECT 1 FROM compliance_requests cr WHERE cr.case_id = c.id)";
} elseif ($user_role === 'receptionist') {
    // Receptionists see threads from cases with appointments
    $sql .= " AND EXISTS (SELECT 1 FROM appointments a WHERE a.case_id = c.id)";
} elseif ($user_role === 'it_admin') {
    // IT admins ONLY see support tickets from /app/support/contact.php
    // (threads with NULL case_id and thread_type='support')
    $sql .= " AND (t.case_id IS NULL AND t.thread_type = 'support')";
} elseif (!in_array($user_role, ['super_admin', 'partner', 'case_manager', 'office_admin'])) {
    // Other roles see threads from cases they have access to
    $case_ids = get_user_cases_access($user_id, $user_role);
    if (!empty($case_ids)) {
        $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
        $sql .= " AND (c.id IN ($placeholders) OR c.id IS NULL)";
        $sql_params = array_merge($sql_params, $case_ids);
    } else {
        $sql .= " AND 1=0";
    }
}
// super_admin, partner, case_manager, office_admin see all threads (no additional filter)

if ($where) {
    $sql .= " AND " . substr($where, 6);
    $sql_params = array_merge($sql_params, $params);
}

$sql .= " ORDER BY t.updated_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($sql_params);
$threads = $stmt->fetchAll();

$messages = [];
$current_thread = null;
if ($threadId) {
    // Check if thread exists - handle both regular threads and support tickets
    $access_stmt = $pdo->prepare("SELECT t.id, t.case_id, t.subject, t.updated_at, c.id as case_id_value FROM message_threads t LEFT JOIN cases c ON t.case_id = c.id WHERE t.id = ?");
    $access_stmt->execute([$threadId]);
    $thread_data = $access_stmt->fetch();
    
        if ($thread_data) {
            // Allow access based on role and case assignment
            $has_access = false;
            
            if ($thread_data['case_id'] === null || $thread_data['case_id_value'] === null) {
                // Support ticket - only IT admins can access
                if ($user_role === 'it_admin') {
                    $has_access = true;
                }
            } else {
                // Regular thread - check case access
                if (in_array($user_role, ['super_admin', 'partner', 'case_manager', 'office_admin'])) {
                    $has_access = true;
                } elseif (in_array($user_role, ['attorney', 'paralegal'])) {
                    // Check if case is assigned to this user
                    $stmt = $pdo->prepare("SELECT assigned_to FROM cases WHERE id = ?");
                    $stmt->execute([$thread_data['case_id_value']]);
                    $case_data = $stmt->fetch();
                    $has_access = ($case_data && $case_data['assigned_to'] == $user_id);
                } else {
                    $case_ids = get_user_cases_access($user_id, $user_role);
                    $has_access = in_array($thread_data['case_id_value'], $case_ids);
                }
            }
        
        if ($has_access) {
            $current_thread = $thread_data;
            $stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.thread_id=? ORDER BY m.created_at ASC");
            $stmt->execute([$threadId]);
            $messages = $stmt->fetchAll();
        }
    }
}

// Handle form submissions
$success_message = '';
$error_message = '';

if (is_post()) {
    if (!csrf_validate()) {
        $error_message = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'reply') {
            $thread_id = (int)($_POST['thread_id'] ?? 0);
            $body = trim($_POST['body'] ?? '');
            
            // Debug logging
            error_log("Reply attempt - thread_id: " . $thread_id . ", body length: " . strlen($body));
            error_log("POST data: " . print_r($_POST, true));
            
            if ($thread_id && $body) {
                try {
                    $stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())');
                    $stmt->execute([$thread_id, get_user_id(), $body]);
                    
                    // Update thread timestamp to ensure it appears on client side
                    $stmt = $pdo->prepare('UPDATE message_threads SET updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                    $stmt->execute([$thread_id]);
                    
                    // Check if this is a support thread and create notification for the client
                    $stmt = $pdo->prepare('SELECT created_by, subject, thread_type FROM message_threads WHERE id = ?');
                    $stmt->execute([$thread_id]);
                    $thread_info = $stmt->fetch();
                    
                    if ($thread_info && $thread_info['thread_type'] === 'support' && $thread_info['created_by']) {
                        // Create notification for the client
                        $client_id = (int)$thread_info['created_by'];
                        $subject = $thread_info['subject'];
                        $action_url = '/app/support/contact.php?thread_id=' . $thread_id;
                        
                        $notif_stmt = $pdo->prepare("
                            INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read, created_at) 
                            VALUES (?, 'info', 'New Reply from Support Team', ?, ?, 0, NOW())
                        ");
                        $notif_message = 'You have a new reply to your support request: ' . $subject;
                        $notif_stmt->execute([$client_id, $notif_message, $action_url]);
                    }
                    
                    $success_message = 'Reply sent successfully!';
                    // Redirect to refresh the page
                    redirect('messages.php?thread_id=' . $thread_id);
                } catch (Exception $e) {
                    error_log("Message send error: " . $e->getMessage());
                    // In development, show actual error
                    if (defined('DEBUG') && DEBUG) {
                        $error_message = 'Failed to send reply: ' . $e->getMessage();
                    } else {
                        $error_message = 'Failed to send reply. Please try again.';
                    }
                }
            } else {
                if (!$thread_id) {
                    $error_message = 'Please fill in all required fields. Missing thread_id.';
                } elseif (!$body) {
                    $error_message = 'Please fill in all required fields. Missing message body.';
                } else {
                    $error_message = 'Please fill in all required fields.';
                }
                error_log("Validation failed - thread_id: '$thread_id', body: '$body'");
            }
        } elseif ($action === 'create_thread') {
            $client_id = (int)($_POST['client_id'] ?? 0);
            $case_id = (int)($_POST['case_id'] ?? 0);
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            
            if ($client_id && $case_id && $subject && $body) {
                try {
                    $pdo->beginTransaction();
                    
                    // Validate case belongs to client and is assigned to current admin/paralegal
                    $validation = $pdo->prepare('SELECT id FROM cases WHERE id = ? AND user_id = ? AND assigned_to = ?');
                    $validation->execute([$case_id, $client_id, get_user_id()]);
                    $valid_case = $validation->fetch();
                    
                    if ($valid_case) {
                        $stmt = $pdo->prepare('INSERT INTO message_threads (case_id, created_by, subject, created_at) VALUES (?, ?, ?, NOW())');
                        $stmt->execute([$case_id, get_user_id(), $subject]);
                        $thread_id = $pdo->lastInsertId();
                        
                        // Create first message (write to both columns for compatibility)
                        $stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, body, message, created_at) VALUES (?, ?, ?, ?, NOW())');
                        $stmt->execute([$thread_id, get_user_id(), $body, $body]);
                        
                        // Bump thread timestamp
                        $pdo->prepare('UPDATE message_threads SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$thread_id]);
                        
                        $pdo->commit();
                        $success_message = 'New message thread created successfully!';
                        redirect('messages.php?thread_id=' . $thread_id);
                    } else {
                        $pdo->rollBack();
                        $error_message = 'Selected case is invalid or not assigned to you.';
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_message = 'Failed to create message thread. Please try again.';
                }
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
    }
}

// Get unread count - FIXED: Check if read_at column exists
$unread_count = 0;
try {
    // First check if the read_at column exists
    $check_column = $pdo->query("SHOW COLUMNS FROM messages LIKE 'read_at'");
    $has_read_at = $check_column->rowCount() > 0;
    
    if ($has_read_at) {
        // Use read_at column if it exists
        $unread_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM messages m 
            JOIN message_threads t ON m.thread_id = t.id 
            JOIN cases c ON t.case_id = c.id 
            WHERE m.read_at IS NULL 
            AND m.sender_id != ? 
            AND c.user_id = ?
        ");
        $unread_stmt->execute([$user_id, $user_id]);
        $unread_count = (int)$unread_stmt->fetchColumn();
    } else {
        // Alternative: Count recent messages from last 7 days as "unread"
        $unread_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM messages m 
            JOIN message_threads t ON m.thread_id = t.id 
            JOIN cases c ON t.case_id = c.id 
            WHERE m.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND m.sender_id != ? 
            AND c.user_id = ?
        ");
        $unread_stmt->execute([$user_id, $user_id]);
        $unread_count = (int)$unread_stmt->fetchColumn();
    }
} catch (Exception $e) {
    error_log("Error fetching unread count: " . $e->getMessage());
    $unread_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages | Med Attorneys</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        /* Dashboard Theme Palette */
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            
            /* Grays & Shadows from original file (they are good) */
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e0;
            --merlaws-gray-400: #a0aec0;
            --merlaws-gray-500: #718096;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-700: #2d3748;
            --merlaws-gray-800: #1a202c;
            --merlaws-gray-900: #171923;
            --merlaws-sent-bg: #e6f7ff; /* Light blue for sent messages */
            
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .messages-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Header Section (Aligned with Dashboard) */
        .messages-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, #0d1117 100%);
            color: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }
        
        .messages-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100px;
            width: 200px;
            height: 100%;
            background: linear-gradient(45deg, var(--merlaws-gold), transparent);
            opacity: 0.1;
            transform: skewX(-15deg);
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            border: none;
        }

        .header-text h1 {
            font-family: 'Inter', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .header-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .unread-badge {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-lg);
        }

        /* Search and Filter Section */
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--merlaws-gray-700);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--merlaws-gray-200);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.2);
        }

        .btn-search {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-primary);
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-search:hover {
            background: linear-gradient(135deg, #d4af37, var(--merlaws-gold));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-gold {
            background: var(--merlaws-gold);
            color: var(--merlaws-primary);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            background: #d4af37;
            color: var(--merlaws-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-gold-outline {
            border-color: var(--merlaws-gold);
            color: var(--merlaws-gold);
        }
        .btn-gold-outline:hover {
            background: var(--merlaws-gold);
            color: var(--merlaws-primary);
        }

        /* Messages Layout */
        .messages-layout {
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 2rem;
            height: calc(100vh - 400px);
            min-height: 700px;
        }

        /* Thread List */
        .thread-list-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .thread-list-header {
            background: white;
            padding: 1.5rem;
            border-bottom: 1px solid var(--merlaws-gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .thread-list-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-gray-800);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .thread-list-header h3 i {
            color: var(--merlaws-gold);
        }

        .thread-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .thread-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--merlaws-gray-200);
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .thread-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--merlaws-gold);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .thread-item:hover {
            background: var(--merlaws-gray-50);
            transform: translateX(8px);
        }

        .thread-item:hover::before {
            transform: scaleY(1);
        }

        .thread-item.active {
            background: linear-gradient(90deg, rgba(201, 169, 110, 0.1), transparent);
            border-left: 4px solid var(--merlaws-gold);
        }

        .thread-subject {
            font-weight: 700;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.5rem 0;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .thread-meta {
            color: var(--merlaws-gray-600);
            font-size: 0.875rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .thread-meta-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .thread-meta-row i {
            color: var(--merlaws-gold);
            width: 16px;
        }

        /* Message View */
        .message-view-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .message-view-header {
            background: white;
            color: var(--merlaws-primary);
            padding: 1.5rem;
            border-bottom: 1px solid var(--merlaws-gray-200);
        }

        .message-view-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .message-view-subtitle {
            color: var(--merlaws-gray-600);
            font-size: 0.95rem;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
            color: var(--merlaws-gray-500);
        }

        .empty-state-icon {
            width: 120px;
            height: 120px;
            background: var(--merlaws-gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--merlaws-gray-400);
            margin-bottom: 1.5rem;
        }

        .empty-state h4 {
            color: var(--merlaws-gray-700);
            margin: 0 0 0.5rem 0;
        }

        .message-list {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .message-item {
            display: flex;
            gap: 1rem;
            animation: fadeInUp 0.3s ease;
        }
        
        .message-item.sent {
            flex-direction: row-reverse;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
        }
        
        .message-item.received .message-avatar {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-primary);
        }
        
        .message-item.sent .message-avatar {
            background: linear-gradient(135deg, var(--info), #1d4ed8);
        }

        .message-content {
            flex: 1;
            max-width: 80%;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: 1px solid var(--merlaws-gray-200);
            position: relative;
        }
        
        .message-item.received .message-content {
            background: var(--merlaws-gray-50);
        }
        
        .message-item.sent .message-content {
            background: var(--merlaws-sent-bg);
            border-color: #bde0ff;
        }

        /* Received Arrow */
        .message-item.received .message-content::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 20px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid var(--merlaws-gray-200);
        }

        .message-item.received .message-content::after {
            content: '';
            position: absolute;
            left: -7px;
            top: 20px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid var(--merlaws-gray-50);
        }
        
        /* Sent Arrow (Optional, more complex) */
        .message-item.sent .message-content::before,
        .message-item.sent .message-content::after {
            /* Hide left-pointing arrows for sent messages */
            display: none; 
        }
        
        /* Uncomment to add right-pointing arrow */
        /*
        .message-item.sent .message-content::after {
            display: block;
            content: '';
            position: absolute;
            right: -7px;
            left: auto;
            top: 20px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-left: 8px solid var(--merlaws-sent-bg);
        }
        */

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--merlaws-gray-200);
        }
        
        .message-item.sent .message-header {
             border-bottom-color: #bde0ff;
        }

        .message-sender {
            font-weight: 700;
            color: var(--merlaws-gray-800);
            font-size: 1.05rem;
        }

        .message-time {
            color: var(--merlaws-gray-500);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message-body {
            color: var(--merlaws-gray-700);
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        /* Reply Form */
        .reply-section {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--merlaws-gray-200);
            background: var(--merlaws-gray-50);
        }
        
        .reply-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* Scrollbar Styling */
        .thread-list::-webkit-scrollbar,
        .message-list::-webkit-scrollbar {
            width: 8px;
        }

        .thread-list::-webkit-scrollbar-track,
        .message-list::-webkit-scrollbar-track {
            background: var(--merlaws-gray-100);
        }

        .thread-list::-webkit-scrollbar-thumb,
        .message-list::-webkit-scrollbar-thumb {
            background: var(--merlaws-gray-400);
            border-radius: 4px;
        }

        .thread-list::-webkit-scrollbar-thumb:hover,
        .message-list::-webkit-scrollbar-thumb:hover {
            background: var(--merlaws-gray-500);
        }
        
        /* Alerts */
        .alert-success {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
            border-left: 4px solid var(--success);
        }
        .alert-danger {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
            border-left: 4px solid var(--danger);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .messages-layout {
                grid-template-columns: 400px 1fr;
            }
        }

        @media (max-width: 992px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .messages-layout {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 0;
            }

            .thread-list-container {
                max-height: 400px;
            }

            .message-view-container {
                min-height: 500px;
            }
            
            .message-content {
                max-width: 90%;
            }
        }

        @media (max-width: 768px) {
            .messages-container {
                padding: 1rem 0.75rem;
            }

            .messages-header {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-text h1 {
                font-size: 1.75rem;
            }

            .header-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .search-section {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .search-form {
                gap: 1rem;
            }

            .form-control {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .btn-search {
                width: 100%;
                justify-content: center;
                min-height: 48px;
                font-size: 16px;
            }

            .messages-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
                height: auto;
                min-height: auto;
            }

            .thread-list-container {
                max-height: 300px;
                border-radius: 16px;
            }

            .thread-list-header {
                padding: 1.25rem;
            }

            .thread-list-header h3 {
                font-size: 1.1rem;
            }

            .thread-item {
                padding: 1rem;
            }

            .message-view-container {
                min-height: 500px;
                border-radius: 16px;
            }

            .message-view-header {
                padding: 1.25rem;
            }

            .message-list {
                padding: 1rem;
                max-height: 400px;
            }

            .message-item {
                flex-direction: column;
                margin-bottom: 1rem;
            }
            
            .message-item.sent {
                flex-direction: column;
                align-items: flex-end;
            }
            
            .message-item .message-avatar {
                display: none;
            }
            
            .message-content {
                max-width: 95%;
            }

            .message-content::before,
            .message-content::after {
                display: none;
            }

            .reply-section {
                padding: 1.25rem;
            }

            .reply-form textarea {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 120px;
            }

            .btn-gold {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
                justify-content: center;
            }

            .btn-sm {
                min-height: 44px;
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .messages-container {
                padding: 0.75rem 0.5rem;
            }

            .messages-header {
                padding: 1.25rem;
            }

            .header-text h1 {
                font-size: 1.5rem;
            }

            .header-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .search-section {
                padding: 1.25rem;
            }

            .thread-list-container {
                max-height: 250px;
            }

            .message-list {
                max-height: 350px;
            }

            .message-content {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>

    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 1rem;">
        <i class="fas fa-check-circle me-2"></i><?php echo e($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 1rem;">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="messages-container">
        <div class="messages-header">
            <div class="header-content">
                <div class="header-title">
                    <div class="header-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="header-text">
                        <h1>Secure Messaging</h1>
                        <p>Communicate securely with your legal team</p>
                    </div>
                </div>
                <?php if ($unread_count > 0): ?>
                <div class="unread-badge">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo $unread_count; ?> Unread</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="search-section">
            <form method="get" class="search-form">
                <div class="form-group">
                    <label for="search">Search Messages</label>
                    <input type="text" id="search" name="q" class="form-control" placeholder="Search by subject or case..." value="<?php echo e($q); ?>">
                </div>
                <div class="form-group">
                    <label for="from">From Date</label>
                    <input type="datetime-local" id="from" name="from" class="form-control" value="<?php echo e($from); ?>">
                </div>
                <div class="form-group">
                    <label for="to">To Date</label>
                    <input type="datetime-local" id="to" name="to" class="form-control" value="<?php echo e($to); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="messages-layout">
            <div class="thread-list-container">
                <div class="thread-list-header">
                    <h3>
                        <i class="fas fa-inbox"></i>
                        Threads (<?php echo count($threads); ?>)
                    </h3>
                    <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#newThreadModal">
                        <i class="fas fa-plus me-1"></i> New Thread
                    </button>
                </div>
                <div class="thread-list">
                    <?php if (!$threads): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h4>No threads found</h4>
                            <p>Try adjusting your search filters</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($threads as $t): ?>
                        <a href="?thread_id=<?php echo (int)$t['id']; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?><?php echo $from ? '&from=' . urlencode($from) : ''; ?><?php echo $to ? '&to=' . urlencode($to) : ''; ?>" 
                           class="thread-item <?php echo $threadId === (int)$t['id'] ? 'active' : ''; ?>">
                            <div class="thread-subject">
                                <?php echo e($t['subject']); ?>
                            </div>
                            <div class="thread-meta">
                                <div class="thread-meta-row">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?php echo e($t['case_title']); ?></span>
                                </div>
                                <div class="thread-meta-row">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo e($t['created_by_name']); ?></span>
                                </div>
                                <div class="thread-meta-row">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('M d, Y g:i A', strtotime($t['updated_at'])); ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="message-view-container">
                <?php if (!$threadId): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h4>Select a thread to view messages</h4>
                        <p>Choose a conversation from the list to see the message history</p>
                    </div>
                <?php elseif (!$current_thread): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h4>Access Denied</h4>
                        <p>You don't have permission to view this thread</p>
                    </div>
                <?php else: ?>
                    <div class="message-view-header">
                        <h3><?php echo e($current_thread['subject']); ?></h3>
                        <div class="message-view-subtitle">
                            <i class="fas fa-comments"></i>
                            <?php echo count($messages); ?> message<?php echo count($messages) !== 1 ? 's' : ''; ?> in this thread
                        </div>
                    </div>
                    <?php if (!$messages): ?>
                        <div class="message-list" style="flex:1;">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-comment-slash"></i>
                                </div>
                                <h4>No messages yet</h4>
                                <p>Be the first to send a message</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="message-list">
                            <?php foreach ($messages as $m): ?>
                            <?php $is_sent_by_me = $m['sender_id'] == $user_id; ?>
                            <div class="message-item <?php echo $is_sent_by_me ? 'sent' : 'received'; ?>" data-message-id="<?php echo (int)$m['id']; ?>">
                                <div class="message-avatar">
                                    <?php echo strtoupper(substr($m['sender_name'], 0, 1)); ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <div class="message-sender"><?php echo e($m['sender_name']); ?></div>
                                        <div class="message-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('M d, Y g:i A', strtotime($m['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="message-body"><?php echo nl2br(e($m['message'] ?? '')); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($current_thread): ?>
                    <div class="reply-section">
                        <h4 class="reply-title">Reply to Thread</h4>
                        <form method="post" action="" class="reply-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="thread_id" value="<?php echo (int)$current_thread['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="reply_body" class="form-label visually-hidden">Your Message</label>
                                <textarea name="body" id="reply_body" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-gold">
                                    <i class="fas fa-paper-plane"></i> Send Reply
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearReplyForm()">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                        
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="newThreadModal" tabindex="-1" aria-labelledby="newThreadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="" class="new-thread-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="create_thread">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="newThreadModalLabel">
                            <i class="fas fa-plus-circle me-2 text-success"></i>Create New Message Thread
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Select Client</label>
                                    <select name="client_id" id="client_id" class="form-select form-control" required>
                                        <option value="">Choose a client...</option>
                                        <?php
                                        $client_stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'client' ORDER BY name");
                                        while ($client = $client_stmt->fetch()):
                                        ?>
                                        <option value="<?php echo (int)$client['id']; ?>">
                                            <?php echo e($client['name']); ?> (<?php echo e($client['email']); ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="case_id" class="form-label">Select Case (assigned to you)</label>
                                    <select name="case_id" id="case_id" class="form-select form-control" required disabled>
                                        <option value="">Choose a case...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" name="subject" id="subject" class="form-control" placeholder="Message subject" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_message_body" class="form-label">Message</label>
                            <textarea name="body" id="new_message_body" class="form-control" rows="5" placeholder="Type your message here..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Create Thread
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeMessages();
        });

        function initializeMessages() {
            initializeLastMessageId();
            animateMessageItems();
            scrollToLatestMessage();
            setupAutoRefresh();
            setupKeyboardShortcuts();
            highlightSearchTerms();
            setupScrollToTop();
            addTooltips();
            setupObservers();
        }

        function animateMessageItems() {
            const messageItems = document.querySelectorAll('.message-item');
            messageItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        }

        function scrollToLatestMessage() {
            const messageList = document.querySelector('.message-list');
            if (messageList) {
                setTimeout(() => {
                    messageList.scrollTop = messageList.scrollHeight;
                }, 300);
            }
        }

        function setupAutoRefresh() {
            const threadId = new URLSearchParams(window.location.search).get('thread_id');
            if (threadId) {
                // Check every 5 seconds for new messages
                setInterval(() => {
                    checkForNewMessages(threadId);
                }, 5000);
            }
        }

        // Store last message ID for incremental updates
        let lastMessageId = 0;
        
        // Initialize last message ID from existing messages
        function initializeLastMessageId() {
            const messageList = document.querySelector('.message-list');
            if (messageList) {
                const lastMessage = messageList.querySelector('.message-item:last-child');
                if (lastMessage) {
                    // Try to get message ID from data attribute
                    const msgId = lastMessage.getAttribute('data-message-id');
                    if (msgId) {
                        lastMessageId = parseInt(msgId);
                    }
                }
            }
        }
        
        function checkForNewMessages(threadId) {
            if (!threadId) return;
            
            const messageList = document.querySelector('.message-list');
            if (!messageList) return;
            
            // Fetch new messages via AJAX
            const url = `messages.php?thread_id=${threadId}&ajax=check_new${lastMessageId > 0 ? '&last_message_id=' + lastMessageId : ''}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        // Append new messages
                        const user_id = <?php echo $user_id; ?>;
                        data.messages.forEach(msg => {
                            // Skip if message already exists (check by ID)
                            if (messageList.querySelector(`[data-message-id="${msg.id}"]`)) {
                                return;
                            }
                            
                            const isSent = parseInt(msg.sender_id) === user_id;
                            const messageItem = document.createElement('div');
                            messageItem.className = `message-item ${isSent ? 'sent' : 'received'}`;
                            messageItem.setAttribute('data-message-id', msg.id);
                            messageItem.innerHTML = `
                                <div class="message-avatar">${msg.sender_name.charAt(0).toUpperCase()}</div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <div class="message-sender">${escapeHtml(msg.sender_name)}</div>
                                        <div class="message-time">
                                            <i class="fas fa-clock"></i>
                                            ${formatDate(msg.created_at)}
                                        </div>
                                    </div>
                                    <div class="message-body">${escapeHtml(msg.message || msg.body || '').replace(/\n/g, '<br>')}</div>
                                </div>
                            `;
                            messageList.appendChild(messageItem);
                            
                            // Update last message ID
                            if (parseInt(msg.id) > lastMessageId) {
                                lastMessageId = parseInt(msg.id);
                            }
                        });
                        
                        // Scroll to bottom
                        messageList.scrollTop = messageList.scrollHeight;
                        
                        // Show notification if not focused
                        if (!document.hasFocus()) {
                            if (Notification.permission === 'granted') {
                                new Notification('New Message', {
                                    body: `New message from ${data.messages[0].sender_name}`,
                                    icon: '/favicon/favicon-32x32.png'
                                });
                            }
                        }
                    }
                    
                    // Update last message ID from response
                    if (data.last_message_id && data.last_message_id > lastMessageId) {
                        lastMessageId = data.last_message_id;
                    }
                })
                .catch(error => {
                    // Silently fail - don't spam console
                });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const searchInput = document.getElementById('search');
                    if (searchInput && searchInput.value) {
                        searchInput.value = '';
                    }
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.getElementById('search')?.focus();
                }
                // Ctrl/Cmd + Enter to submit reply
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    if (document.getElementById('reply_body') === document.activeElement) {
                        document.querySelector('.reply-form').submit();
                    }
                }
            });
        }

        // Add visual feedback for thread selection
        const activeThread = document.querySelector('.thread-item.active');
        if (activeThread) {
            activeThread.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Add loading animation for filter button
        const filterBtn = document.querySelector('.btn-search');
        if (filterBtn) {
            filterBtn.closest('form').addEventListener('submit', function() {
                const icon = filterBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-search');
                    icon.classList.add('fa-spinner', 'fa-spin');
                }
            });
        }

        // Add tooltips for truncated text
        function addTooltips() {
            const textElements = document.querySelectorAll('.thread-subject, .message-sender');
            textElements.forEach(el => {
                if (el.scrollWidth > el.clientWidth) {
                    el.setAttribute('title', el.textContent.trim());
                }
            });
        }

        // Handle empty states with animations
        const emptyStates = document.querySelectorAll('.empty-state');
        emptyStates.forEach((state, index) => {
            state.style.animation = `fadeInUp 0.5s ease ${index * 0.1}s both`;
        });

        // Intersection observer for lazy loading threads
        function setupObservers() {
            if ('IntersectionObserver' in window) {
                const threadObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateX(0)';
                            threadObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.thread-item').forEach(item => {
                    if (!item.classList.contains('active')) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        threadObserver.observe(item);
                    }
                });
            }
        }

        // Highlight search terms in results
        function highlightSearchTerms() {
            const searchTerm = new URLSearchParams(window.location.search).get('q');
            if (searchTerm && searchTerm.trim() !== '') {
                const regex = new RegExp(`(${searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
                document.querySelectorAll('.thread-subject, .message-body').forEach(el => {
                    const text = el.textContent;
                    if (regex.test(text)) {
                        el.innerHTML = text.replace(regex, '<mark style="background: var(--merlaws-gold); color: var(--merlaws-primary); padding: 2px 4px; border-radius: 3px;">$1</mark>');
                    }
                });
            }
        }

        // Add "scroll to top" button for message list
        function setupScrollToTop() {
            const messageList = document.querySelector('.message-list');
            if (!messageList) return;

            const scrollToTopBtn = document.createElement('button');
            scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            scrollToTopBtn.style.cssText = `
                position: absolute;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
                color: var(--merlaws-primary);
                border: none;
                border-radius: 50%;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-lg);
                z-index: 1000;
                transition: all 0.3s ease;
            `;
            
            scrollToTopBtn.addEventListener('click', function() {
                messageList.scrollTo({ top: 0, behavior: 'smooth' });
            });

            const container = document.querySelector('.message-view-container');
            if (container) {
                 container.style.position = 'relative'; // Ensure button is positioned correctly
                 container.appendChild(scrollToTopBtn);
            }

            messageList.addEventListener('scroll', function() {
                if (this.scrollTop > 300) {
                    scrollToTopBtn.style.display = 'flex';
                } else {
                    scrollToTopBtn.style.display = 'none';
                }
            });
        }

        // Form clearing functions
        function clearReplyForm() {
            const replyBody = document.getElementById('reply_body');
            if (replyBody) replyBody.value = '';
        }
        
        // Clear modal form on close
        const newThreadModal = document.getElementById('newThreadModal');
        if (newThreadModal) {
            newThreadModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('client_id').value = '';
                const caseSelect = document.getElementById('case_id');
                if (caseSelect) {
                    caseSelect.innerHTML = '<option value="">Choose a case...</option>';
                    caseSelect.disabled = true;
                }
                document.getElementById('subject').value = '';
                document.getElementById('new_message_body').value = '';
            });
        }

        // Dynamic case loading based on selected client
        const clientSelect = document.getElementById('client_id');
        const caseSelect = document.getElementById('case_id');
        if (clientSelect && caseSelect) {
            clientSelect.addEventListener('change', async function() {
                const clientId = this.value;
                caseSelect.innerHTML = '<option value="">Loading cases...</option>';
                caseSelect.disabled = true;
                if (!clientId) {
                    caseSelect.innerHTML = '<option value="">Choose a case...</option>';
                    return;
                }
                try {
                    const resp = await fetch(`messages.php?ajax=cases_for_client&client_id=${encodeURIComponent(clientId)}`);
                    const data = await resp.json();
                    if (data.ok) {
                        if (data.cases.length === 0) {
                            caseSelect.innerHTML = '<option value="">No cases assigned to you for this client</option>';
                            caseSelect.disabled = true;
                        } else {
                            caseSelect.innerHTML = '<option value="">Choose a case...</option>' +
                                data.cases.map(c => `<option value="${c.id}">${c.title}</option>`).join('');
                            caseSelect.disabled = false;
                        }
                    } else {
                        caseSelect.innerHTML = '<option value="">Failed to load cases</option>';
                        caseSelect.disabled = true;
                    }
                } catch (e) {
                    caseSelect.innerHTML = '<option value="">Failed to load cases</option>';
                    caseSelect.disabled = true;
                }
            });
        }
    </script>
</body>
</html>