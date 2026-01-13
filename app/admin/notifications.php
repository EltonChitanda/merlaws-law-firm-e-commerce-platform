<?php
require __DIR__ . '/../config.php';
if (!has_permission('notification:create') && !has_permission('notification:manage') && !has_permission('settings:manage')) {
    require_permission('notification:create');
}

$pdo = db();
$errors = [];
$messages = [];

$user_id = get_user_id();
$user_role = get_user_role();

// Role-based data filtering - FIXED: More permissive for admin roles
$case_filter_sql = "";
$case_filter_params = [];

// Only apply restrictive filtering for non-admin roles
if (in_array($user_role, ['attorney', 'paralegal'])) {
    $case_filter_sql = " AND c.assigned_to = ?";
    $case_filter_params[] = $user_id;
} elseif ($user_role === 'billing') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM service_requests sr WHERE sr.case_id = c.id AND sr.status != 'cart')";
} elseif ($user_role === 'doc_specialist') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM case_documents cd WHERE cd.case_id = c.id)";
} elseif ($user_role === 'receptionist') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM appointments a WHERE a.case_id = c.id)";
} elseif ($user_role === 'compliance') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM compliance_requests cr WHERE cr.case_id = c.id)";
}
// super_admin, partner, case_manager, office_admin see all (no filter)

// Get notification counts
$notification_counts = [
    'case_submissions' => 0,
    'service_requests' => 0,
    'messages' => 0,
    'appointments' => 0,
    'compliance_alerts' => 0,
    'system_alerts' => 0
];

// Case submissions count - all pending cases that need admin review
// FIXED: Show pending cases for users who can review them
$case_submission_sql = "
    SELECT COUNT(*) 
    FROM cases c 
    WHERE c.status IN ('pending_review', 'draft', 'pending')
";

// For non-admin roles, apply their specific filters
if ($case_filter_sql) {
    $case_submission_sql .= $case_filter_sql;
}

$stmt = $pdo->prepare($case_submission_sql);
$stmt->execute($case_filter_params);
$notification_counts['case_submissions'] = $stmt->fetchColumn();

// Service requests count - all pending service requests
$service_request_sql = "
    SELECT COUNT(*) 
    FROM service_requests sr 
    LEFT JOIN cases c ON sr.case_id = c.id 
    WHERE sr.status IN ('pending', 'requested', 'awaiting_approval')
";

if ($case_filter_sql) {
    $service_request_sql .= $case_filter_sql;
} else {
    $service_request_sql .= " AND 1=1";
}

$stmt = $pdo->prepare($service_request_sql);
$stmt->execute($case_filter_params ?: []);
$notification_counts['service_requests'] = $stmt->fetchColumn();

// Messages count - try multiple approaches
try {
    $sql = "
        SELECT COUNT(*) 
        FROM messages m
        JOIN message_threads mt ON m.thread_id = mt.id
        LEFT JOIN cases c ON mt.case_id = c.id
        WHERE m.is_read = 0 AND m.recipient_id = ?
    ";
    if ($case_filter_sql) {
        $sql .= $case_filter_sql;
    } else {
        $sql .= " AND 1=1";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($case_filter_sql ? array_merge([$user_id], $case_filter_params) : [$user_id]);
    $notification_counts['messages'] = $stmt->fetchColumn();
} catch (Exception $e) {
    // Try simpler query without message_threads
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM messages m
            WHERE m.is_read = 0 AND m.recipient_id = ?
        ");
        $stmt->execute([$user_id]);
        $notification_counts['messages'] = $stmt->fetchColumn();
    } catch (Exception $e2) {
        $notification_counts['messages'] = 0;
    }
}

// Appointments count - show scheduled appointments that need attention
try {
    $appointment_sql = "
        SELECT COUNT(*) 
        FROM appointments a 
        LEFT JOIN cases c ON a.case_id = c.id 
        WHERE a.status = 'scheduled' 
        AND a.start_time >= NOW() 
        AND a.start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    ";
    
    if ($case_filter_sql) {
        $appointment_sql .= $case_filter_sql;
    } else {
        $appointment_sql .= " AND 1=1";
    }
    
    $stmt = $pdo->prepare($appointment_sql);
    $stmt->execute($case_filter_params ?: []);
    $notification_counts['appointments'] = $stmt->fetchColumn();
} catch (Exception $e) {
    // Try with appointment_requests table
    try {
        $appointment_request_sql = "
            SELECT COUNT(*) 
            FROM appointment_requests ar 
            LEFT JOIN cases c ON ar.case_id = c.id 
            WHERE ar.status = 'pending'
        ";
        
        if ($case_filter_sql) {
            $appointment_request_sql .= $case_filter_sql;
        } else {
            $appointment_request_sql .= " AND 1=1";
        }
        
        $stmt = $pdo->prepare($appointment_request_sql);
        $stmt->execute($case_filter_params ?: []);
        $notification_counts['appointments'] = $stmt->fetchColumn();
    } catch (Exception $e2) {
        $notification_counts['appointments'] = 0;
    }
}

// Compliance alerts count
if (in_array($user_role, ['compliance', 'super_admin', 'partner', 'office_admin'])) {
    try {
        $compliance_sql = "
            SELECT COUNT(*) 
            FROM compliance_requests cr 
            JOIN cases c ON cr.case_id = c.id 
            WHERE cr.status = 'pending'
        ";
        
        if ($case_filter_sql) {
            $compliance_sql .= $case_filter_sql;
        }
        
        $stmt = $pdo->prepare($compliance_sql);
        $stmt->execute($case_filter_params);
        $notification_counts['compliance_alerts'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        $notification_counts['compliance_alerts'] = 0;
    }
}

// System alerts count
if (in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM system_alerts WHERE status = 'active'");
        $notification_counts['system_alerts'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        $notification_counts['system_alerts'] = 0;
    }
}

// Get recent notifications
$recent_notifications = [];

// Recent case submissions - all pending cases
$recent_cases_sql = "
    SELECT 
        c.id,
        c.title,
        u.name as client_name,
        c.created_at,
        'case_submission' as type,
        c.assigned_to,
        au.name as assigned_attorney,
        c.status
    FROM cases c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users au ON c.assigned_to = au.id
    WHERE c.status IN ('pending_review', 'draft', 'pending')
";

if ($case_filter_sql) {
    $recent_cases_sql .= $case_filter_sql;
}

$recent_cases_sql .= " ORDER BY c.created_at DESC LIMIT 5";

$stmt = $pdo->prepare($recent_cases_sql);
$stmt->execute($case_filter_params);
$case_notifications = $stmt->fetchAll();

// Add assignment status to case notifications
foreach ($case_notifications as $case_notif) {
    $assignment_status = $case_notif['assigned_to'] ? ' (Assigned)' : ' (Unassigned)';
    $case_notif['title'] = $case_notif['title'] . $assignment_status;
    $recent_notifications[] = $case_notif;
}

// Recent service requests - all pending service requests
$recent_services_sql = "
    SELECT 
        sr.id,
        COALESCE(s.name, 'Service Request') as title,
        COALESCE(u.name, 'Unknown Client') as client_name,
        sr.created_at,
        'service_request' as type
    FROM service_requests sr
    LEFT JOIN services s ON sr.service_id = s.id
    LEFT JOIN cases c ON sr.case_id = c.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE sr.status IN ('pending', 'requested', 'awaiting_approval')
";

if ($case_filter_sql) {
    $recent_services_sql .= $case_filter_sql;
} else {
    $recent_services_sql .= " AND 1=1";
}

$recent_services_sql .= " ORDER BY sr.created_at DESC LIMIT 5";

$stmt = $pdo->prepare($recent_services_sql);
$stmt->execute($case_filter_params ?: []);
$recent_notifications = array_merge($recent_notifications, $stmt->fetchAll());

// Recent messages - all unread messages requiring admin attention
try {
    $message_sql = "
        SELECT 
            m.id,
            CONCAT('Message from ', COALESCE(u.name, 'Unknown User')) as title,
            COALESCE(u.name, 'Unknown User') as client_name,
            m.created_at,
            'message' as type,
            m.thread_id,
            mt.case_id
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN message_threads mt ON m.thread_id = mt.id
        LEFT JOIN cases c ON mt.case_id = c.id
        WHERE m.is_read = 0 AND m.recipient_id = ?
    ";
    if ($case_filter_sql) {
        $message_sql .= $case_filter_sql;
    } else {
        // For admins, show all unread messages
        $message_sql .= " AND 1=1";
    }
    $message_sql .= " ORDER BY m.created_at DESC LIMIT 5";
    
    $stmt = $pdo->prepare($message_sql);
    $stmt->execute($case_filter_sql ? array_merge([$user_id], $case_filter_params) : [$user_id]);
    $recent_notifications = array_merge($recent_notifications, $stmt->fetchAll());
} catch (Exception $e) {
    // Messages table might have different structure, try alternative
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                CONCAT('Message from ', COALESCE(u.name, 'Unknown User')) as title,
                COALESCE(u.name, 'Unknown User') as client_name,
                m.created_at,
                'message' as type,
                m.thread_id
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.is_read = 0 AND m.recipient_id = ?
            ORDER BY m.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_notifications = array_merge($recent_notifications, $stmt->fetchAll());
    } catch (Exception $e2) {
        // Messages might not exist
    }
}

// Recent appointment requests - show scheduled appointments and pending requests
try {
    // First try appointments table - show upcoming scheduled appointments
    $appointment_sql = "
        SELECT 
            a.id,
            CONCAT('Appointment: ', COALESCE(a.title, 'Consultation'), ' - ', COALESCE(c.title, CONCAT('Case #', a.case_id))) as title,
            COALESCE(u.name, u2.name, 'Unknown Client') as client_name,
            a.created_at,
            'appointment_request' as type,
            a.case_id,
            a.start_time,
            a.status,
            a.location
        FROM appointments a
        LEFT JOIN cases c ON a.case_id = c.id
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN users u2 ON a.created_by = u2.id
        WHERE a.status = 'scheduled' 
        AND a.start_time >= NOW() 
        AND a.start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    ";
    if ($case_filter_sql) {
        $appointment_sql .= $case_filter_sql;
    }
    $appointment_sql .= " ORDER BY a.start_time ASC LIMIT 5";
    
    $stmt = $pdo->prepare($appointment_sql);
    $stmt->execute($case_filter_params ?: []);
    $appointment_notifications = $stmt->fetchAll();
    $recent_notifications = array_merge($recent_notifications, $appointment_notifications);
} catch (Exception $e) {
    // Try appointment_requests table if appointments doesn't exist or query fails
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ar.id,
                CONCAT('Appointment Request - ', COALESCE(c.title, CONCAT('Case #', ar.case_id))) as title,
                COALESCE(u.name, ar.requestor_name, 'Unknown Client') as client_name,
                ar.created_at,
                'appointment_request' as type,
                ar.case_id,
                ar.preferred_date as start_time,
                ar.status,
                ar.location
            FROM appointment_requests ar
            LEFT JOIN cases c ON ar.case_id = c.id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE ar.status = 'pending'
            ORDER BY ar.preferred_date ASC
            LIMIT 5
        ");
        $stmt->execute();
        $recent_notifications = array_merge($recent_notifications, $stmt->fetchAll());
    } catch (Exception $e2) {
        // Appointments tables might not exist
    }
}

// Sort all notifications by date
usort($recent_notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Limit to 10 most recent
$recent_notifications = array_slice($recent_notifications, 0, 10);

if (is_post()) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create_template') {
            $template_name = trim($_POST['template_name'] ?? '');
            $template_type = $_POST['template_type'] ?? 'email';
            $content = trim($_POST['content'] ?? '');
            $is_html = isset($_POST['is_html']) ? 1 : 0;
            if ($template_name === '' || $content === '') { 
                throw new Exception('Template name and content are required'); 
            }
            $stmt = $pdo->prepare("INSERT INTO notification_templates (template_name, template_type, subject, content, variables, is_html, is_active, created_by, created_at) VALUES (?, ?, NULL, ?, NULL, ?, 1, ?, NOW())");
            $stmt->execute([$template_name, $template_type, $content, $is_html, get_user_id()]);
            $messages[] = 'Template created.';
        }
        if ($action === 'create_campaign') {
            $campaign_name = trim($_POST['campaign_name'] ?? '');
            $template_id = (int)($_POST['template_id'] ?? 0);
            $target_type = $_POST['target_type'] ?? 'all_users';
            $scheduled_at = trim($_POST['scheduled_at'] ?? '') ?: null;
            if ($campaign_name === '' || $template_id <= 0) { 
                throw new Exception('Campaign name and template are required'); 
            }
            $stmt = $pdo->prepare("INSERT INTO notification_campaigns (campaign_name, template_id, target_type, target_criteria, status, scheduled_at, created_by, created_at) VALUES (?, ?, ?, NULL, 'scheduled', ?, ?, NOW())");
            $stmt->execute([$campaign_name, $template_id, $target_type, $scheduled_at, get_user_id()]);
            $messages[] = 'Campaign scheduled.';
        }
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }
}

// Build notifications array - show all types that have counts > 0
$notifications = [];

// Service Requests
if ($notification_counts['service_requests'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['service_requests'],
        'type' => 'service_requests',
        'title' => 'New Service Requests',
        'description' => 'Pending service requests requiring review'
    ];
}

// Case Submissions
if ($notification_counts['case_submissions'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['case_submissions'],
        'type' => 'new_cases',
        'title' => 'New Case Submissions',
        'description' => 'Recently submitted cases awaiting review'
    ];
}

// Messages
if ($notification_counts['messages'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['messages'],
        'type' => 'urgent_messages',
        'title' => 'Unread Messages',
        'description' => 'Unread messages requiring attention'
    ];
}

// Appointments
if ($notification_counts['appointments'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['appointments'],
        'type' => 'appointment_requests',
        'title' => 'Appointment Requests',
        'description' => 'Pending appointment requests'
    ];
}

// Compliance Alerts
if ($notification_counts['compliance_alerts'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['compliance_alerts'],
        'type' => 'compliance_alerts',
        'title' => 'Compliance Alerts',
        'description' => 'Pending compliance requests'
    ];
}

// System Alerts
if ($notification_counts['system_alerts'] > 0) {
    $notifications[] = [
        'count' => $notification_counts['system_alerts'],
        'type' => 'system_alerts',
        'title' => 'System Alerts',
        'description' => 'System notifications requiring attention'
    ];
}

// Debug information (remove in production)
$debug_info = "";
if (isset($_GET['debug'])) {
    $debug_info = "
    <div class='alert alert-info'>
        <strong>Debug Info:</strong><br>
        User Role: {$user_role}<br>
        Case Submissions Count: {$notification_counts['case_submissions']}<br>
        Case Filter SQL: " . ($case_filter_sql ?: 'None') . "<br>
        Case Filter Params: " . implode(', ', $case_filter_params) . "
    </div>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Notifications | Med Attorneys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container my-4">
    <h1 class="h3 mb-3"><i class="fas fa-bell me-2"></i>Admin Notifications</h1>
    <p class="text-muted">Important notifications requiring your attention</p>
    
    <?= $debug_info ?>
    
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <?php if ($messages): ?>
        <div class="alert alert-success"><?= e(implode(' ', $messages)) ?></div>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h3>All Caught Up!</h3>
                <p class="text-muted">No urgent notifications at this time. Everything is running smoothly.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($notifications as $notification): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-<?= $notification['count'] > 5 ? 'danger' : ($notification['count'] > 2 ? 'warning' : 'info') ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="notification-icon">
                                    <?php
                                    $icon = match($notification['type']) {
                                        'service_requests' => 'fas fa-clipboard-list',
                                        'new_cases' => 'fas fa-folder-plus',
                                        'urgent_messages' => 'fas fa-exclamation-triangle',
                                        'appointment_requests' => 'fas fa-calendar-plus',
                                        'document_uploads' => 'fas fa-file-upload',
                                        default => 'fas fa-bell'
                                    };
                                    ?>
                                    <i class="<?= $icon ?> fa-2x text-<?= $notification['count'] > 5 ? 'danger' : ($notification['count'] > 2 ? 'warning' : 'info') ?>"></i>
                                </div>
                                <span class="badge bg-<?= $notification['count'] > 5 ? 'danger' : ($notification['count'] > 2 ? 'warning' : 'info') ?> fs-6">
                                    <?= $notification['count'] ?>
                                </span>
                            </div>
                            <h5 class="card-title"><?= e($notification['title']) ?></h5>
                            <p class="card-text text-muted"><?= e($notification['description']) ?></p>
                            <div class="mt-auto">
                                <?php
                                $action_url = match($notification['type']) {
                                    'service_requests' => 'service-requests.php?status=pending',
                                    'new_cases' => 'cases.php?status=under_review', // FIXED: Changed from pending_review to under_review
                                    'urgent_messages' => 'messages.php?filter=unread',
                                    'appointment_requests' => 'appointments.php?status=pending',
                                    'document_uploads' => 'cases.php?filter=documents',
                                    'compliance_alerts' => 'compliance.php?status=pending',
                                    default => '#'
                                };
                                ?>
                                <button type="button" class="btn btn-<?= $notification['count'] > 5 ? 'danger' : ($notification['count'] > 2 ? 'warning' : 'info') ?> btn-sm" onclick="showNotificationDetails('<?= $notification['type'] ?>', <?= $notification['count'] ?>, '<?= e($notification['title']) ?>', '<?= e($notification['description']) ?>')">
                                    <i class="fas fa-info-circle me-1"></i>View Details
                                </button>
                                <a href="<?= $action_url ?>" class="btn btn-outline-<?= $notification['count'] > 5 ? 'danger' : ($notification['count'] > 2 ? 'warning' : 'info') ?> btn-sm ms-2">
                                    <i class="fas fa-arrow-right me-1"></i>Go to Page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="service-requests.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-clipboard-list me-2"></i>Service Requests
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="cases.php?status=under_review" class="btn btn-outline-success w-100"> <!-- FIXED: Changed from pending_review to under_review -->
                                    <i class="fas fa-folder-open me-2"></i>Pending Cases
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="messages.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-comments me-2"></i>Messages
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="appointments.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-calendar me-2"></i>Appointments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Recent Notifications List -->
    <?php if (!empty($recent_notifications)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach (array_slice($recent_notifications, 0, 10) as $notif): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= e($notif['title'] ?? 'Notification') ?></h6>
                                    <p class="mb-1 text-muted"><?= e($notif['client_name'] ?? 'System') ?></p>
                                    <small class="text-muted"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-secondary"><?= e($notif['type'] ?? 'notification') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationDetailsModal" tabindex="-1" aria-labelledby="notificationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationDetailsModalLabel">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="notificationDetailsAction" class="btn btn-primary">View Full Details</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showNotificationDetails(type, count, title, description) {
    const modal = new bootstrap.Modal(document.getElementById('notificationDetailsModal'));
    const content = document.getElementById('notificationDetailsContent');
    const actionBtn = document.getElementById('notificationDetailsAction');
    
    // Build details content based on type
    let detailsHtml = `
        <div class="mb-3">
            <h6>${title}</h6>
            <p class="text-muted">${description}</p>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Type:</strong> ${type.replace('_', ' ').toUpperCase()}
            </div>
            <div class="col-md-6">
                <strong>Count:</strong> ${count} item(s)
            </div>
        </div>
    `;
    
    // Add type-specific details
    switch(type) {
        case 'service_requests':
            detailsHtml += `
                <hr>
                <h6>Service Request Details</h6>
                <p>There are ${count} pending service requests that require your review and approval.</p>
                <ul>
                    <li>Review each request for completeness</li>
                    <li>Approve or reject based on case requirements</li>
                    <li>Contact clients if additional information is needed</li>
                </ul>
            `;
            actionBtn.href = 'service-requests.php?status=pending';
            break;
        case 'urgent_messages':
            detailsHtml += `
                <hr>
                <h6>Message Details</h6>
                <p>You have ${count} unread message(s) that require your attention.</p>
                <ul>
                    <li>Respond to client inquiries promptly</li>
                    <li>Review case-related communications</li>
                    <li>Update case notes if needed</li>
                </ul>
            `;
            actionBtn.href = 'messages.php?filter=unread';
            break;
        case 'appointment_requests':
            detailsHtml += `
                <hr>
                <h6>Appointment Details</h6>
                <p>You have ${count} upcoming appointment(s) in the next 7 days.</p>
                <ul>
                    <li>Review appointment schedules</li>
                    <li>Confirm appointments with clients</li>
                    <li>Prepare necessary documents</li>
                </ul>
            `;
            actionBtn.href = 'appointments.php?status=scheduled';
            break;
        case 'new_cases':
            detailsHtml += `
                <hr>
                <h6>Case Submission Details</h6>
                <p>There are ${count} new case submission(s) awaiting review.</p>
                <ul>
                    <li>Review case documentation</li>
                    <li>Assign cases to appropriate attorneys</li>
                    <li>Set initial case status</li>
                </ul>
            `;
            actionBtn.href = 'cases.php?status=under_review'; // FIXED: Changed from pending_review to under_review
            break;
        default:
            detailsHtml += `
                <hr>
                <p>Click "View Full Details" to see all related items.</p>
            `;
            actionBtn.href = '#';
    }
    
    content.innerHTML = detailsHtml;
    document.getElementById('notificationDetailsModalLabel').textContent = title;
    modal.show();
}
</script>
</body>
</html>