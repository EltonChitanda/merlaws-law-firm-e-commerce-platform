<?php
// app/support/contact.php
require __DIR__ . '/../config.php';
require_login();
require __DIR__ . '/../csrf.php';

$user_id = get_user_id();
$errors = [];
$success = false;
$success_sla_text = '';
$reply_success = isset($_GET['reply_sent']) && $_GET['reply_sent'] == '1';
$selected_thread_id = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;

// Handle reply to support thread
if (is_post() && isset($_POST['reply_support']) && csrf_validate()) {
    $thread_id = (int)($_POST['thread_id'] ?? 0);
    $reply_message = trim($_POST['reply_message'] ?? '');
    
    if (empty($reply_message)) {
        $errors[] = 'Reply message is required';
    }
    
    if ($thread_id <= 0) {
        $errors[] = 'Invalid thread';
    }
    
    if (!$errors) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id FROM message_threads WHERE id = ? AND created_by = ? AND thread_type = 'support'");
            $stmt->execute([$thread_id, $user_id]);
            if (!$stmt->fetch()) {
                $errors[] = 'Invalid thread';
            } else {
                // Insert the reply message - write to both body and message columns for compatibility
                $stmt = $pdo->prepare("INSERT INTO messages (thread_id, sender_id, body, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$thread_id, $user_id, $reply_message, $reply_message]);
                
                // Update thread timestamp to ensure it appears in admin view
                $stmt = $pdo->prepare("UPDATE message_threads SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$thread_id]);
                
                // Redirect to avoid duplicate submissions and refresh the conversation
                header("Location: ?thread_id=" . $thread_id . "&reply_sent=1");
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Failed to send reply. Please try again.';
            error_log("Support reply error: " . $e->getMessage());
        }
    }
}

// Handle form submission
if (is_post() && isset($_POST['submit_support'])) {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
        $priority = 'medium';
    }
    
    if (!$errors) {
        try {
            $pdo = db();
            
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, subject, description, priority, status) 
                VALUES (?, ?, ?, ?, 'open')
            ");
            $stmt->execute([$user_id, $subject, $description, $priority]);
            $ticket_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("
                INSERT INTO message_threads (case_id, subject, created_by, thread_type) 
                VALUES (NULL, ?, ?, 'support')
            ");
            $stmt->execute([$subject, $user_id]);
            $thread_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("
                INSERT INTO messages (thread_id, sender_id, body, message, message_type, created_at) 
                VALUES (?, ?, ?, ?, 'support_request', NOW())
            ");
            $stmt->execute([$thread_id, $user_id, $description, $description]);
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'it_admin' LIMIT 1");
            $stmt->execute();
            $it_admin = $stmt->fetch();
            
            if ($it_admin) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ? WHERE id = ?");
                $stmt->execute([$it_admin['id'], $ticket_id]);
                
                $stmt = $pdo->prepare("UPDATE message_threads SET assigned_to = ? WHERE id = ?");
                $stmt->execute([$it_admin['id'], $thread_id]);
            }
            
            $success = true;
            if ($priority === 'urgent') {
                $success_sla_text = '< 1 hour';
            } elseif ($priority === 'high') {
                $success_sla_text = '< 2 hours';
            } elseif ($priority === 'medium') {
                $success_sla_text = '< 4 hours';
            } else {
                $success_sla_text = '< 24 hours';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Failed to submit support request. Please try again.';
            error_log("Support ticket creation error: " . $e->getMessage());
        }
    }
}

// Get user's recent support tickets
$recent_tickets = [];
try {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, subject, status, priority, created_at, resolved_at
        FROM support_tickets 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_tickets = $stmt->fetchAll();
} catch (Exception $e) {
}

// Load user's support message threads with unread counts
$support_threads = [];
try {
    $pdo = db();
    $sth = $pdo->prepare("
        SELECT t.id, t.subject, t.created_at, t.updated_at,
        COUNT(CASE WHEN m.read_at IS NULL AND m.sender_id != ? THEN 1 END) as unread_count
        FROM message_threads t
        LEFT JOIN messages m ON m.thread_id = t.id
        WHERE t.thread_type = 'support' AND t.created_by = ?
        GROUP BY t.id, t.subject, t.created_at, t.updated_at
        ORDER BY t.updated_at DESC
    ");
    $sth->execute([$user_id, $user_id]);
    $support_threads = $sth->fetchAll();
    
    if ($selected_thread_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM message_threads WHERE id = ? AND created_by = ? AND thread_type = 'support'");
        $stmt->execute([$selected_thread_id, $user_id]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE messages 
                SET read_at = NOW() 
                WHERE thread_id = ? AND sender_id != ? AND read_at IS NULL
            ");
            $stmt->execute([$selected_thread_id, $user_id]);
        }
    }
} catch (Throwable $e) {
}

$name = get_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Technical Support | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-accent: #f7fafc;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
            --merlaws-warning: #ed8936;
            --merlaws-danger: #e53e3e;
            --merlaws-info: #3182ce;
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
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .support-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Professional Welcome Header */
        .support-welcome {
            background: linear-gradient(135deg, var(--merlaws-info) 0%, #2c5aa0 50%, var(--merlaws-secondary) 100%);
            color: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .support-welcome::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 600;
            margin: 0 0 1rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .support-stats {
            display: flex;
            gap: 3rem;
            margin-top: 2rem;
        }

        .stat-box {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Support Cards */
        .support-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-info), var(--merlaws-secondary));
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--merlaws-gray-100);
        }

        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-right: 1.5rem;
            background: linear-gradient(135deg, var(--merlaws-info), var(--merlaws-secondary));
            box-shadow: 0 8px 25px rgba(49, 130, 206, 0.3);
        }

        .card-title-custom {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0;
        }

        .card-subtitle {
            color: var(--merlaws-gray-600);
            font-size: 0.9rem;
            margin: 0.25rem 0 0 0;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: var(--merlaws-gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--merlaws-gray-200);
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--merlaws-info);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
            outline: none;
        }

        /* Priority Badges */
        .priority-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-urgent { background: #fee; color: #c53030; }
        .priority-high { background: #fef3c7; color: #92400e; }
        .priority-medium { background: #dbeafe; color: #1e40af; }
        .priority-low { background: #f3f4f6; color: #374151; }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-open { background: #d1fae5; color: #065f46; }
        .status-in_progress { background: #fef3c7; color: #92400e; }
        .status-resolved { background: #dbeafe; color: #1e40af; }
        .status-closed { background: #f3f4f6; color: #374151; }

        /* Conversation Interface */
        .conversation-wrapper {
            display: flex;
            gap: 1.5rem;
            height: 600px;
        }

        .threads-sidebar {
            width: 350px;
            background: var(--merlaws-gray-50);
            border-radius: 16px;
            padding: 1.5rem;
            overflow-y: auto;
            border: 1px solid var(--merlaws-gray-200);
        }

        .thread-item {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
        }

        .thread-item:hover {
            border-color: var(--merlaws-info);
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .thread-item.active {
            background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
            border-color: var(--merlaws-info);
        }

        .thread-subject {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .thread-time {
            font-size: 0.8rem;
            color: var(--merlaws-gray-500);
        }

        .unread-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--merlaws-danger);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .conversation-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--merlaws-gray-200);
            overflow: hidden;
        }

        .conversation-header {
            background: linear-gradient(135deg, var(--merlaws-gray-50) 0%, white 100%);
            padding: 1.5rem 2rem;
            border-bottom: 2px solid var(--merlaws-gray-200);
        }

        .conversation-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.25rem 0;
        }

        .conversation-meta {
            font-size: 0.85rem;
            color: var(--merlaws-gray-500);
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            background: var(--merlaws-gray-50);
        }

        .message-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-end;
        }

        .message-group.client {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .message-avatar.client-avatar {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
        }

        .message-avatar.support-avatar {
            background: linear-gradient(135deg, var(--merlaws-info), var(--merlaws-secondary));
            color: white;
        }

        .message-bubble {
            max-width: 70%;
            padding: 1.25rem 1.5rem;
            border-radius: 20px;
            position: relative;
        }

        .message-group.client .message-bubble {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-group.support .message-bubble {
            background: white;
            color: var(--merlaws-gray-800);
            border: 1px solid var(--merlaws-gray-200);
            border-bottom-left-radius: 4px;
        }

        .message-sender {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .message-group.client .message-sender {
            opacity: 0.9;
        }

        .message-content {
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .message-time {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            opacity: 0.7;
        }

        .reply-section {
            padding: 1.5rem 2rem;
            background: white;
            border-top: 2px solid var(--merlaws-gray-200);
        }

        .reply-input-group {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .reply-input {
            flex: 1;
        }

        .reply-textarea {
            resize: none;
            min-height: 60px;
            max-height: 120px;
        }

        .btn-send {
            background: linear-gradient(135deg, var(--merlaws-info), var(--merlaws-secondary));
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-send:hover {
            background: linear-gradient(135deg, #2c5aa0, var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(49, 130, 206, 0.4);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--merlaws-info), var(--merlaws-secondary));
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #2c5aa0, var(--merlaws-secondary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(49, 130, 206, 0.4);
        }

        /* Info Boxes */
        .info-box {
            background: var(--merlaws-gray-50);
            border-left: 4px solid var(--merlaws-info);
            padding: 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .info-box h6 {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin-bottom: 0.75rem;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--merlaws-gray-200);
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        /* Alerts */
        .alert-custom {
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: none;
            margin-bottom: 2rem;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, #fee 0%, #fecaca 100%);
            color: #991b1b;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: var(--merlaws-gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--merlaws-gray-400);
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--merlaws-gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--merlaws-gray-500);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .conversation-wrapper {
                flex-direction: column;
                height: auto;
            }

            .threads-sidebar {
                width: 100%;
                max-height: 300px;
            }

            .conversation-main {
                min-height: 500px;
            }

            .support-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .support-container {
                padding: 1rem;
            }

            .support-welcome {
                padding: 2rem;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .support-card {
                padding: 1.5rem;
            }

            .message-bubble {
                max-width: 85%;
            }

            .reply-input-group {
                flex-direction: column;
            }

            .btn-send {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
            }

            .conversation-wrapper {
                flex-direction: column;
                height: auto;
            }

            .threads-sidebar {
                width: 100%;
                max-height: 300px;
                margin-bottom: 1.5rem;
            }

            .conversation-main {
                min-height: 500px;
            }

            .form-control,
            .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .btn-primary-custom {
                width: 100%;
                min-height: 48px;
                font-size: 16px;
            }

            .support-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .stat-box {
                padding: 1.25rem;
            }

            .stat-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .support-container {
                padding: 1rem 0.75rem;
            }

            .support-welcome {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .welcome-title {
                font-size: 1.5rem;
            }

            .support-card {
                padding: 1.25rem;
            }

            .card-header-custom {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        /* Scrollbar Styling */
        .threads-sidebar::-webkit-scrollbar,
        .messages-container::-webkit-scrollbar {
            width: 8px;
        }

        .threads-sidebar::-webkit-scrollbar-track,
        .messages-container::-webkit-scrollbar-track {
            background: var(--merlaws-gray-100);
            border-radius: 10px;
        }

        .threads-sidebar::-webkit-scrollbar-thumb,
        .messages-container::-webkit-scrollbar-thumb {
            background: var(--merlaws-gray-300);
            border-radius: 10px;
        }

        .threads-sidebar::-webkit-scrollbar-thumb:hover,
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: var(--merlaws-gray-400);
        }
    </style>
</head>
<body>
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <div class="support-container">
        <!-- Professional Welcome Section -->
        <div class="support-welcome">
            <div class="welcome-content">
                <h1 class="welcome-title">
                    <i class="fas fa-headset me-3"></i>Technical Support
                </h1>
                <p class="welcome-subtitle">
                    Hello <?php echo htmlspecialchars($name); ?>, we're here to help! Our IT team is ready to assist you with any technical issues.
                </p>
                
                <div class="support-stats">
                    <div class="stat-box">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support Available</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">< 2hrs</div>
                        <div class="stat-label">Avg Response Time</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Resolution Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert-custom alert-success-custom">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> Your support request has been submitted. Our IT team will respond within <?php echo htmlspecialchars($success_sla_text ?: '< 2 hours'); ?>.
            </div>
        <?php endif; ?>

        <?php if ($reply_success): ?>
            <div class="alert-custom alert-success-custom">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> Your reply has been sent.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert-custom alert-danger-custom">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: New Request Form -->
            <div class="col-lg-8">
                <!-- New Support Request Card -->
                <div class="support-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <h3 class="card-title-custom">Submit New Support Request</h3>
                            <p class="card-subtitle">Describe your technical issue and we'll assist you promptly</p>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-heading me-2"></i>Subject *
                                </label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                                       placeholder="Brief description of your issue" required>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-flag me-2"></i>Priority Level
                                </label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low" <?php echo ($_POST['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo ($_POST['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo ($_POST['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                                <div id="priorityHelp" class="form-text mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Detailed Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="6" 
                                      placeholder="Please provide detailed information about your technical issue, including steps to reproduce, error messages, and any relevant screenshots..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="info-box mb-4">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Tip:</strong> Including screenshots, error messages, and step-by-step details helps us resolve your issue faster.
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="submit_support" class="btn-primary-custom">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Conversation Interface (if threads exist) -->
                <?php if (!empty($support_threads)): ?>
                <div class="support-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h3 class="card-title-custom">Your Support Conversations</h3>
                            <p class="card-subtitle">View and reply to your ongoing support tickets</p>
                        </div>
                    </div>

                    <div class="conversation-wrapper">
                        <!-- Threads Sidebar -->
                        <div class="threads-sidebar">
                            <h6 class="mb-3 text-muted text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">
                                <i class="fas fa-inbox me-2"></i>All Conversations (<?php echo count($support_threads); ?>)
                            </h6>
                            <?php foreach ($support_threads as $thread): ?>
                                <a href="?thread_id=<?php echo $thread['id']; ?>" class="thread-item text-decoration-none <?php echo $selected_thread_id === (int)$thread['id'] ? 'active' : ''; ?>">
                                    <div class="thread-subject"><?php echo htmlspecialchars($thread['subject']); ?></div>
                                    <div class="thread-time">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($thread['updated_at'])); ?>
                                    </div>
                                    <?php if ((int)$thread['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?php echo (int)$thread['unread_count']; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Conversation Main -->
                        <div class="conversation-main">
                            <?php if ($selected_thread_id > 0): ?>
                                <?php
                                $selected_thread = null;
                                foreach ($support_threads as $thread) {
                                    if ((int)$thread['id'] === $selected_thread_id) {
                                        $selected_thread = $thread;
                                        break;
                                    }
                                }
                                if ($selected_thread):
                                    try {
                                        $msgStmt = $pdo->prepare("
                                            SELECT m.*, u.name AS sender_name, u.role AS sender_role 
                                            FROM messages m 
                                            JOIN users u ON u.id = m.sender_id 
                                            WHERE m.thread_id = ? 
                                            ORDER BY m.created_at ASC
                                        ");
                                        $msgStmt->execute([$selected_thread_id]);
                                        $msgs = $msgStmt->fetchAll();
                                        
                                        // Mark messages as read for the current user
                                        if (!empty($msgs)) {
                                            $pdo->prepare("
                                                UPDATE messages 
                                                SET read_at = NOW() 
                                                WHERE thread_id = ? AND sender_id != ? AND read_at IS NULL
                                            ")->execute([$selected_thread_id, $user_id]);
                                        }
                                    } catch (Throwable $e) {
                                        $msgs = [];
                                        error_log("Error fetching messages: " . $e->getMessage());
                                    }
                                ?>
                                
                                <!-- Conversation Header -->
                                <div class="conversation-header">
                                    <h4 class="conversation-title">
                                        <i class="fas fa-ticket-alt me-2"></i>
                                        <?php echo htmlspecialchars($selected_thread['subject']); ?>
                                    </h4>
                                    <div class="conversation-meta">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Started <?php echo date('M j, Y', strtotime($selected_thread['created_at'])); ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-sync me-2"></i>
                                        Last updated <?php echo date('M j, Y g:i A', strtotime($selected_thread['updated_at'])); ?>
                                    </div>
                                </div>

                                <!-- Messages Container -->
                                <div class="messages-container" id="messagesContainer">
                                    <?php if (empty($msgs)): ?>
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-comments"></i>
                                            </div>
                                            <div class="empty-state-title">No messages yet</div>
                                            <div class="empty-state-text">Start the conversation by sending a message below.</div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($msgs as $m): ?>
                                            <?php $isClient = ((int)$m['sender_id'] === $user_id); ?>
                                            <div class="message-group <?php echo $isClient ? 'client' : 'support'; ?>">
                                                <div class="message-avatar <?php echo $isClient ? 'client-avatar' : 'support-avatar'; ?>">
                                                    <?php echo strtoupper(substr(($m['sender_name'] ?? ($isClient ? 'You' : 'IT')), 0, 1)); ?>
                                                </div>
                                                <div class="message-bubble">
                                                    <div class="message-sender">
                                                        <?php echo htmlspecialchars($m['sender_name']); ?>
                                                        <?php if (!$isClient): ?>
                                                            <span class="badge bg-light text-dark ms-2" style="font-size: 0.7rem;">IT Support</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="message-content">
                                                        <?php echo nl2br(htmlspecialchars($m['message'] ?? $m['body'] ?? '')); ?>
                                                    </div>
                                                    <div class="message-time">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Reply Section -->
                                <div class="reply-section">
                                    <form method="POST" action="" id="replyForm">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="thread_id" value="<?php echo $selected_thread_id; ?>">
                                        <div class="reply-input-group">
                                            <div class="reply-input">
                                                <textarea class="form-control reply-textarea" name="reply_message" 
                                                          placeholder="Type your message here..." required></textarea>
                                            </div>
                                            <button type="submit" name="reply_support" class="btn-send">
                                                <i class="fas fa-paper-plane me-2"></i>Send
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <?php endif; ?>
                            <?php else: ?>
                                <!-- No Thread Selected -->
                                <div class="empty-state" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-hand-pointer"></i>
                                    </div>
                                    <div class="empty-state-title">Select a Conversation</div>
                                    <div class="empty-state-text">Choose a conversation from the sidebar to view and reply to messages.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Information & Recent Tickets -->
            <div class="col-lg-4">
                <!-- Support Information Card -->
                <div class="support-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h3 class="card-title-custom">Support Info</h3>
                            <p class="card-subtitle">Response times & contact details</p>
                        </div>
                    </div>

                    <div class="info-box">
                        <h6><i class="fas fa-clock me-2"></i>Response Time SLA</h6>
                        <ul class="info-list">
                            <li>
                                <span class="priority-badge priority-urgent">Urgent</span>
                                <span class="float-end text-muted">< 1 hour</span>
                            </li>
                            <li>
                                <span class="priority-badge priority-high">High</span>
                                <span class="float-end text-muted">< 2 hours</span>
                            </li>
                            <li>
                                <span class="priority-badge priority-medium">Medium</span>
                                <span class="float-end text-muted">< 4 hours</span>
                            </li>
                            <li>
                                <span class="priority-badge priority-low">Low</span>
                                <span class="float-end text-muted">< 24 hours</span>
                            </li>
                        </ul>
                    </div>

                    <div class="info-box">
                        <h6><i class="fas fa-phone me-2"></i>Contact Methods</h6>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <strong>Email:</strong> support@merlaws.com
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-phone-alt me-2 text-primary"></i>
                            <strong>Phone:</strong> +27 11 123 4567
                        </p>
                    </div>

                    <div class="info-box" style="border-left-color: var(--merlaws-gold);">
                        <h6><i class="fas fa-question-circle me-2"></i>Common Issues</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none">
                                    <i class="fas fa-chevron-right me-2 text-muted"></i>Login & Access Problems
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none">
                                    <i class="fas fa-chevron-right me-2 text-muted"></i>File Upload Issues
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none">
                                    <i class="fas fa-chevron-right me-2 text-muted"></i>Payment Processing
                                </a>
                            </li>
                            <li class="mb-0">
                                <a href="#" class="text-decoration-none">
                                    <i class="fas fa-chevron-right me-2 text-muted"></i>Document Access
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Tickets Card -->
                <?php if (!empty($recent_tickets)): ?>
                <div class="support-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="card-title-custom">Recent Tickets</h3>
                            <p class="card-subtitle">Your latest support requests</p>
                        </div>
                    </div>

                    <?php foreach ($recent_tickets as $ticket): ?>
                        <div class="mb-3 p-3" style="background: var(--merlaws-gray-50); border-radius: 12px; border-left: 4px solid <?php 
                            echo $ticket['status'] === 'open' ? 'var(--merlaws-success)' : 
                                ($ticket['status'] === 'in_progress' ? 'var(--merlaws-warning)' : 
                                ($ticket['status'] === 'resolved' ? 'var(--merlaws-info)' : 'var(--merlaws-gray-400)')); 
                        ?>;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0" style="font-weight: 600;"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="priority-badge priority-<?php echo $ticket['priority']; ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                    <span class="status-badge status-<?php echo $ticket['status']; ?> ms-2">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('M j', strtotime($ticket['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php 
    $footerPath = __DIR__ . '/../../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Priority helper text
            function updatePriorityHelp() {
                const prioritySelect = document.getElementById('priority');
                const helpText = document.getElementById('priorityHelp');
                
                if (prioritySelect && helpText) {
                    const etaMap = {
                        'urgent': '< 1 hour',
                        'high': '< 2 hours',
                        'medium': '< 4 hours',
                        'low': '< 24 hours'
                    };
                    
                    const priority = prioritySelect.value || 'medium';
                    helpText.innerHTML = '<i class="fas fa-info-circle me-1"></i>Expected first response: ' + etaMap[priority];
                }
            }
            
            const prioritySelect = document.getElementById('priority');
            if (prioritySelect) {
                prioritySelect.addEventListener('change', updatePriorityHelp);
                updatePriorityHelp();
            }

            // Auto-scroll messages to bottom
            const messagesContainer = document.getElementById('messagesContainer');
            if (messagesContainer) {
                setTimeout(function() {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 100);
            }

            // Auto-resize textarea
            const replyTextarea = document.querySelector('.reply-textarea');
            if (replyTextarea) {
                replyTextarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
            }

            // Form submission animation
            const replyForm = document.getElementById('replyForm');
            if (replyForm) {
                replyForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Smooth scroll for conversation updates
            if (window.location.search.includes('thread_id=') && messagesContainer) {
                messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    </script>
</body>
</html>