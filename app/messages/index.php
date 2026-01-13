<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

// ENFORCE CLIENT-ONLY ACCESS
require_login();

$role = get_user_role();
$admin_roles = [
    'super_admin', 'admin', 'manager', 'office_admin', 'partner', 
    'attorney', 'paralegal', 'intake', 'case_manager', 'billing', 
    'doc_specialist', 'it_admin', 'compliance', 'receptionist'
];

if (in_array($role, $admin_roles, true)) {
    header('Location: ../admin/dashboard.php');
    exit;
}

if ($role !== 'client') {
    session_destroy();
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = get_user_id();
$pdo = db();

// AJAX endpoint for checking new messages
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_new' && isset($_GET['thread_id'])) {
    header('Content-Type: application/json');
    $thread_id = (int)$_GET['thread_id'];
    
    try {
        $last_message_id = isset($_GET['last_message_id']) ? (int)$_GET['last_message_id'] : 0;
        
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

// User's cases
$stmt = $pdo->prepare("SELECT id, title FROM cases WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : (count($cases) ? (int)$cases[0]['id'] : 0);
$threadId = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;

$errors = [];
$success = '';

if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create_thread') {
            $subject = trim((string)($_POST['subject'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            if ($subject === '' || $body === '') { $errors[] = 'Subject and message are required.'; }
            if (!$errors) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM cases WHERE id = ? AND user_id = ?');
                $stmt->execute([$caseId, $user_id]);
                if (!$stmt->fetchColumn()) { $errors[] = 'Invalid case.'; }
            }
            if (!$errors) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO message_threads (case_id, subject, created_by) VALUES (?, ?, ?)');
                $stmt->execute([$caseId, $subject, $user_id]);
                $newThreadId = (int)$pdo->lastInsertId();
                $stmt = $pdo->prepare('INSERT INTO message_participants (thread_id, user_id, last_read_message_id) VALUES (?, ?, NULL) ON DUPLICATE KEY UPDATE joined_at = CURRENT_TIMESTAMP');
                $stmt->execute([$newThreadId, $user_id]);
                $stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, body, message, has_attachments) VALUES (?, ?, ?, ?, 0)');
                $stmt->execute([$newThreadId, $user_id, $body, $body]);
                $pdo->commit();
                redirect('index.php?case_id=' . $caseId . '&thread_id=' . $newThreadId);
            }
        } elseif ($action === 'reply') {
            $body = trim((string)($_POST['body'] ?? ''));
            $threadId = (int)($_POST['thread_id'] ?? 0);
            if ($threadId <= 0 || $body === '') { $errors[] = 'Message is required.'; }
            if (!$errors) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM message_threads t JOIN cases c ON t.case_id=c.id WHERE t.id=? AND c.user_id=?');
                $stmt->execute([$threadId, $user_id]);
                if (!$stmt->fetchColumn()) { $errors[] = 'Invalid thread.'; }
            }
            if (!$errors) {
                $stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, body, message, has_attachments) VALUES (?, ?, ?, ?, 0)');
                $stmt->execute([$threadId, $user_id, $body, $body]);
                $stmt = $pdo->prepare('UPDATE message_threads SET updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $stmt->execute([$threadId]);
                $success = 'Message sent successfully!';
                redirect('index.php?case_id=' . $caseId . '&thread_id=' . $threadId);
            }
        }
    }
}

$threads = [];
if ($caseId) {
    $stmt = $pdo->prepare("SELECT t.* FROM message_threads t WHERE t.case_id = ? ORDER BY t.updated_at DESC");
    $stmt->execute([$caseId]);
    $threads = $stmt->fetchAll();
    if (!$threadId && $threads) { $threadId = (int)$threads[0]['id']; }
}

$messages = [];
$currentThread = null;
if ($threadId) {
    $stmt = $pdo->prepare("SELECT * FROM message_threads WHERE id = ?");
    $stmt->execute([$threadId]);
    $currentThread = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name, u.role AS sender_role FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$threadId]);
    $messages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Messages | Med Attorneys</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-secondary: #1a365d;
            --merlaws-gold: #c9a96e;
            --merlaws-success: #38a169;
            --merlaws-info: #3182ce;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-800: #1a202c;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
        }

        .messages-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }

        .case-selector {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .case-selector select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--merlaws-gray-100);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .case-selector select:focus {
            outline: none;
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .messages-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 1.5rem;
            min-height: 600px;
        }

        .threads-sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--merlaws-gray-800), var(--merlaws-secondary));
            color: white;
        }

        .sidebar-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }

        .threads-list {
            flex: 1;
            overflow-y: auto;
            max-height: 450px;
        }

        .thread-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--merlaws-gray-100);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .thread-item:hover {
            background: var(--merlaws-gray-50);
            border-left: 4px solid var(--merlaws-gold);
        }

        .thread-item.active {
            background: linear-gradient(90deg, rgba(201, 169, 110, 0.1), transparent);
            border-left: 4px solid var(--merlaws-primary);
        }

        .thread-subject {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--merlaws-gray-800);
        }

        .thread-date {
            font-size: 0.85rem;
            color: var(--merlaws-gray-600);
        }

        .new-thread-section {
            padding: 1.5rem;
            border-top: 2px solid var(--merlaws-gray-100);
            background: var(--merlaws-gray-50);
        }

        .conversation-panel {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
        }

        .conversation-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--merlaws-gray-800), var(--merlaws-secondary));
            color: white;
            border-radius: 20px 20px 0 0;
        }

        .conversation-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .messages-area {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            max-height: 500px;
            background: linear-gradient(to bottom, var(--merlaws-gray-50), white);
        }

        .message-bubble {
            margin-bottom: 1.5rem;
            max-width: 75%;
        }

        .message-bubble.sent {
            margin-left: auto;
        }

        .message-bubble.received {
            margin-right: auto;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .message-sender {
            font-weight: 600;
            color: var(--merlaws-gray-800);
        }

        .message-time {
            font-size: 0.85rem;
            color: var(--merlaws-gray-600);
        }

        .message-content {
            background: var(--merlaws-gray-50);
            padding: 1rem 1.25rem;
            border-radius: 16px;
            border: 1px solid var(--merlaws-gray-100);
            line-height: 1.6;
        }

        .message-bubble.sent .message-content {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-color: var(--merlaws-primary);
        }

        .reply-section {
            padding: 1.5rem;
            border-top: 2px solid var(--merlaws-gray-100);
            background: var(--merlaws-gray-50);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--merlaws-gray-100);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 1px solid #f87171;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 1px solid #34d399;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--merlaws-gray-600);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--merlaws-gray-400);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .messages-container {
                padding: 0 0.75rem 1.5rem;
            }

            .page-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .case-selector {
                padding: 1.25rem;
                border-radius: 12px;
                margin-bottom: 1rem;
            }

            .case-selector select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .messages-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
                min-height: auto;
            }

            .threads-sidebar {
                max-height: 300px;
                border-radius: 12px;
            }

            .sidebar-header {
                padding: 1.25rem;
            }

            .sidebar-title {
                font-size: 1rem;
            }

            .thread-item {
                padding: 0.875rem 1.25rem;
                min-height: 44px;
            }

            .thread-subject {
                font-size: 0.95rem;
            }

            .new-thread-section {
                padding: 1.25rem;
            }

            .conversation-panel {
                border-radius: 12px;
            }

            .conversation-header {
                padding: 1.25rem;
                border-radius: 12px 12px 0 0;
            }

            .conversation-title {
                font-size: 1.25rem;
            }

            .messages-area {
                padding: 1.25rem;
                max-height: 400px;
            }

            .message-bubble {
                max-width: 90%;
            }

            .message-content {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }

            .reply-section {
                padding: 1.25rem;
            }

            .form-control {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            textarea.form-control {
                min-height: 120px;
            }

            .btn-primary {
                width: 100%;
                justify-content: center;
                min-height: 48px;
                font-size: 16px;
            }

            .empty-state {
                padding: 2rem 1.5rem;
            }

            .empty-state i {
                font-size: 3rem;
            }

            .empty-state h3 {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .messages-container {
                padding: 0 0.5rem 1rem;
            }

            .page-header {
                padding: 1.25rem 0;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .case-selector {
                padding: 1rem;
            }

            .threads-sidebar {
                max-height: 250px;
            }

            .thread-item {
                padding: 0.75rem 1rem;
            }

            .conversation-header {
                padding: 1rem;
            }

            .conversation-title {
                font-size: 1.15rem;
            }

            .messages-area {
                padding: 1rem;
                max-height: 350px;
            }

            .message-bubble {
                max-width: 95%;
            }

            .reply-section {
                padding: 1rem;
            }
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

    <div class="page-header">
        <div class="container">
            <h1 class="page-title"><i class="fas fa-comments me-3"></i>Secure Messages</h1>
            <p class="mb-0">Communicate securely with your legal team</p>
        </div>
    </div>

    <div class="messages-container">
        <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo e(implode(' ', $errors)); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e($success); ?>
        </div>
        <?php endif; ?>

        <?php if (!$cases): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Cases Yet</h3>
            <p>You don't have any cases yet. Create a case to start messaging your legal team.</p>
            <a href="../cases/create.php" class="btn-primary mt-3">
                <i class="fas fa-plus"></i> Create New Case
            </a>
        </div>
        <?php else: ?>
            <div class="case-selector">
                <form method="get">
                    <label class="form-label"><i class="fas fa-briefcase me-2"></i>Select Case</label>
                    <select name="case_id" id="case_id" onchange="this.form.submit()">
                        <?php foreach ($cases as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo $caseId===(int)$c['id']?'selected':''; ?>>
                            <?php echo e($c['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="messages-layout">
                <!-- Threads Sidebar -->
                <div class="threads-sidebar">
                    <div class="sidebar-header">
                        <h3 class="sidebar-title"><i class="fas fa-list me-2"></i>Message Threads</h3>
                    </div>

                    <div class="threads-list">
                        <?php if (!$threads): ?>
                        <div class="empty-state" style="padding: 2rem 1rem;">
                            <i class="fas fa-comments" style="font-size: 2rem;"></i>
                            <p style="font-size: 0.9rem; margin: 0.5rem 0 0;">No threads yet</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($threads as $t): ?>
                        <a href="?case_id=<?php echo (int)$caseId; ?>&thread_id=<?php echo (int)$t['id']; ?>" 
                           class="thread-item <?php echo $threadId === (int)$t['id'] ? 'active' : ''; ?>">
                            <div class="thread-subject"><?php echo e($t['subject']); ?></div>
                            <div class="thread-date">
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('M d, Y', strtotime($t['updated_at'])); ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="new-thread-section">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem; font-weight: 700;">
                            <i class="fas fa-plus-circle me-2"></i>New Thread
                        </h4>
                        <form method="post">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="create_thread">
                            <div class="form-group">
                                <input type="text" name="subject" placeholder="Subject" required class="form-control">
                            </div>
                            <div class="form-group">
                                <textarea name="body" rows="3" placeholder="Your message..." required class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> Create Thread
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Conversation Panel -->
                <div class="conversation-panel">
                    <?php if ($currentThread): ?>
                    <div class="conversation-header">
                        <h2 class="conversation-title"><?php echo e($currentThread['subject']); ?></h2>
                        <small><i class="far fa-calendar me-1"></i>Started <?php echo date('F j, Y', strtotime($currentThread['created_at'])); ?></small>
                    </div>

                    <div class="messages-area">
                        <?php if (!$messages): ?>
                        <div class="empty-state">
                            <i class="fas fa-comment-slash"></i>
                            <p>No messages in this thread yet</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($messages as $m): 
                            $isSent = (int)$m['sender_id'] === $user_id;
                            $initials = strtoupper(substr($m['sender_name'], 0, 1));
                        ?>
                        <div class="message-bubble <?php echo $isSent ? 'sent' : 'received'; ?>" data-message-id="<?php echo (int)$m['id']; ?>">
                            <div class="message-header">
                                <div class="message-avatar"><?php echo $initials; ?></div>
                                <div>
                                    <div class="message-sender"><?php echo e($m['sender_name']); ?></div>
                                    <div class="message-time">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('M d, Y g:i A', strtotime($m['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(e(isset($m['body']) ? (string)$m['body'] : (isset($m['message']) ? (string)$m['message'] : ''))); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="reply-section">
                        <form method="post">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="thread_id" value="<?php echo (int)$threadId; ?>">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-reply me-2"></i>Reply to Thread</label>
                                <textarea name="body" rows="4" placeholder="Type your message here..." required class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Select a Thread</h3>
                        <p>Choose a thread from the sidebar or create a new one to start messaging</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    $footerPath = __DIR__ . '/../../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll to latest message
        document.addEventListener('DOMContentLoaded', function() {
            const messagesArea = document.querySelector('.messages-area');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
            
            // Setup auto-refresh for messages
            setupAutoRefresh();
        });
        
        // Store last message ID for incremental updates
        let lastMessageId = 0;
        
        function initializeLastMessageId() {
            const messagesArea = document.querySelector('.messages-area');
            if (messagesArea) {
                const lastMessage = messagesArea.querySelector('.message-bubble:last-child');
                if (lastMessage) {
                    const msgId = lastMessage.getAttribute('data-message-id');
                    if (msgId) {
                        lastMessageId = parseInt(msgId);
                    }
                }
            }
        }
        
        function setupAutoRefresh() {
            const threadId = new URLSearchParams(window.location.search).get('thread_id');
            if (threadId) {
                initializeLastMessageId();
                
                // Check for new messages every 5 seconds
                setInterval(() => {
                    checkForNewMessages(threadId);
                }, 5000);
            }
        }
        
        function checkForNewMessages(threadId) {
            if (!threadId) return;
            
            const messagesArea = document.querySelector('.messages-area');
            if (!messagesArea) return;
            
            const url = `index.php?case_id=<?php echo $caseId; ?>&thread_id=${threadId}&ajax=check_new${lastMessageId > 0 ? '&last_message_id=' + lastMessageId : ''}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        const user_id = <?php echo $user_id; ?>;
                        data.messages.forEach(msg => {
                            if (messagesArea.querySelector(`[data-message-id="${msg.id}"]`)) {
                                return;
                            }
                            
                            const isSent = parseInt(msg.sender_id) === user_id;
                            const messageBubble = document.createElement('div');
                            messageBubble.className = `message-bubble ${isSent ? 'sent' : 'received'}`;
                            messageBubble.setAttribute('data-message-id', msg.id);
                            
                            const initials = msg.sender_name.charAt(0).toUpperCase();
                            messageBubble.innerHTML = `
                                <div class="message-header">
                                    <div class="message-avatar">${initials}</div>
                                    <div>
                                        <div class="message-sender">${escapeHtml(msg.sender_name)}</div>
                                        <div class="message-time">
                                            <i class="far fa-clock me-1"></i>
                                            ${formatDate(msg.created_at)}
                                        </div>
                                    </div>
                                </div>
                                <div class="message-content">
                                    ${escapeHtml(msg.message || msg.body || '').replace(/\n/g, '<br>')}
                                </div>
                            `;
                            messagesArea.appendChild(messageBubble);
                            
                            if (parseInt(msg.id) > lastMessageId) {
                                lastMessageId = parseInt(msg.id);
                            }
                        });
                        
                        messagesArea.scrollTop = messagesArea.scrollHeight;
                    }
                    
                    if (data.last_message_id && data.last_message_id > lastMessageId) {
                        lastMessageId = data.last_message_id;
                    }
                })
                .catch(error => {
                    // Silently fail
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
    </script>
</body>
</html>