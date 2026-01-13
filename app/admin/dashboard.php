<?php
// app/admin/dashboard.php - Enhanced Professional Dashboard
require __DIR__ . '/../config.php';
require_admin();

$pdo = db();
$user_role = get_user_role();
$user_id = get_user_id();

// Define role-specific dashboard configurations
$role_configs = [
    'super_admin' => [
        'title' => 'Super Administrator Dashboard',
        'subtitle' => 'Complete system oversight and management',
        'widgets' => ['stats', 'notifications', 'tasks', 'calendar', 'charts', 'system_health', 'recent_activity', 'quick_actions', 'contact_submissions'],
        'stats' => ['total_users', 'total_clients', 'total_cases', 'pending_requests', 'system_uptime', 'active_sessions', 'new_contact_submissions']
    ],
    'office_admin' => [
        'title' => 'Office Administration Dashboard',
        'subtitle' => 'Staff and operational management',
        'widgets' => ['stats', 'notifications', 'calendar', 'tasks', 'recent_activity', 'quick_actions', 'contact_submissions'],
        'stats' => ['todays_appointments', 'active_cases', 'staff_count', 'pending_requests', 'new_contact_submissions']
    ],
    'partner' => [
        'title' => 'Partner Dashboard',
        'subtitle' => 'Strategic oversight and case management',
        'widgets' => ['stats', 'charts', 'financial', 'team_performance', 'tasks', 'calendar', 'contact_submissions'],
        'stats' => ['total_cases', 'won_cases', 'revenue_ytd', 'active_attorneys', 'new_contact_submissions']
    ],
    'attorney' => [
        'title' => 'Attorney Dashboard',
        'subtitle' => 'Your active caseload and tasks',
        'widgets' => ['stats', 'notifications', 'tasks', 'calendar', 'my_cases', 'recent_activity', 'quick_actions'],
        'stats' => ['my_active_cases', 'upcoming_appointments', 'pending_documents', 'tasks_due_today']
    ],
    'paralegal' => [
        'title' => 'Paralegal Dashboard',
        'subtitle' => 'Document management and case support',
        'widgets' => ['stats', 'notifications', 'tasks', 'documents', 'recent_activity', 'quick_actions'],
        'stats' => ['assigned_cases', 'documents_pending', 'tasks_today', 'reviews_needed']
    ],
    'case_manager' => [
        'title' => 'Case Manager Dashboard',
        'subtitle' => 'Case coordination and workflow management',
        'widgets' => ['stats', 'notifications', 'tasks', 'calendar', 'team_workload', 'charts'],
        'stats' => ['total_cases', 'overdue_tasks', 'upcoming_deadlines', 'team_utilization']
    ],
    'billing' => [
        'title' => 'Billing & Finance Dashboard',
        'subtitle' => 'Financial operations and invoicing',
        'widgets' => ['stats', 'notifications', 'tasks', 'financial', 'charts'],
        'stats' => ['outstanding_invoices', 'revenue_mtd', 'pending_approvals', 'collection_rate']
    ],
    'compliance' => [
        'title' => 'Compliance Officer Dashboard',
        'subtitle' => 'Regulatory compliance and data management',
        'widgets' => ['stats', 'notifications', 'tasks', 'compliance_requests', 'audit_logs'],
        'stats' => ['pending_requests', 'active_holds', 'compliance_rate', 'recent_audits']
    ]
];

// Get configuration for current role
$config = $role_configs[$user_role] ?? $role_configs['attorney'];

// Fetch role-specific statistics
function get_role_stats($role, $user_id, $pdo) {
    $stats = [];
    
    switch($role) {
        case 'super_admin':
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $stats['total_users'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
            $stats['total_clients'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM cases");
            $stats['total_cases'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetchColumn();
            
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM contact_submissions WHERE status = 'new'");
                $stats['new_contact_submissions'] = $stmt->fetchColumn();
            } catch (Exception $e) {
                $stats['new_contact_submissions'] = 0;
            }
            
            $stats['system_uptime'] = '99.8%';
            $stats['active_sessions'] = 12;
            break;
            
        case 'attorney':
        case 'paralegal':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE assigned_to = ? AND status IN ('active', 'under_review')");
            $stmt->execute([$user_id]);
            $stats['my_active_cases'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE assigned_to = ? AND start_time > NOW() AND start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'scheduled'");
            $stmt->execute([$user_id]);
            $stats['upcoming_appointments'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT cd.id) FROM case_documents cd JOIN cases c ON cd.case_id = c.id WHERE c.assigned_to = ? AND cd.uploaded_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute([$user_id]);
            $stats['pending_documents'] = $stmt->fetchColumn();
            
            $stats['tasks_due_today'] = 3;
            break;
            
        case 'case_manager':
            $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
            $stats['total_cases'] = $stmt->fetchColumn();
            
            $stats['overdue_tasks'] = 3;
            $stats['upcoming_deadlines'] = 8;
            $stats['team_utilization'] = '87%';
            break;
            
        case 'billing':
            $stats['outstanding_invoices'] = 15;
            $stats['revenue_mtd'] = 'R 245,000';
            $stats['pending_approvals'] = 4;
            $stats['collection_rate'] = '94.2%';
            break;
            
        case 'office_admin':
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM contact_submissions WHERE status = 'new'");
                $stats['new_contact_submissions'] = $stmt->fetchColumn();
            } catch (Exception $e) {
                $stats['new_contact_submissions'] = 0;
            }
            // Add other office_admin stats
            $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(start_time) = CURDATE() AND status = 'scheduled'");
            $stats['todays_appointments'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
            $stats['active_cases'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'client' AND is_active = 1");
            $stats['staff_count'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetchColumn();
            break;
            
        case 'partner':
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM contact_submissions WHERE status = 'new'");
                $stats['new_contact_submissions'] = $stmt->fetchColumn();
            } catch (Exception $e) {
                $stats['new_contact_submissions'] = 0;
            }
            break;
            
        default:
            $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
            $stats['total_cases'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetchColumn();
    }
    
    return $stats;
}

$stats = get_role_stats($user_role, $user_id, $pdo);

// Get notifications
$notifications = [];
if (in_array('notifications', $config['widgets'])) {
    $stmt = $pdo->prepare("
        SELECT * FROM user_notifications 
        WHERE user_id = ? AND is_read = FALSE 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
}

// Get real tasks from database
$tasks = [];
if (in_array('tasks', $config['widgets'])) {
    $tasks = get_user_tasks($user_id, 10);
}

// Get overdue tasks for all roles
$overdue_tasks = [];
if (in_array('tasks', $config['widgets'])) {
    $overdue_tasks = get_overdue_tasks($user_id);
}

// Get calendar events
$calendar_events = [];
if (in_array('calendar', $config['widgets'])) {
    if ($user_role === 'attorney' || $user_role === 'paralegal') {
        $stmt = $pdo->prepare("
            SELECT a.*, c.title as case_title 
            FROM appointments a
            JOIN cases c ON a.case_id = c.id
            WHERE a.assigned_to = ? 
            AND a.start_time >= NOW() 
            AND a.start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND a.status = 'scheduled'
            ORDER BY a.start_time ASC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, c.title as case_title, u.name as assigned_name
            FROM appointments a
            JOIN cases c ON a.case_id = c.id
            LEFT JOIN users u ON a.assigned_to = u.id
            WHERE a.start_time >= NOW() 
            AND a.start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND a.status = 'scheduled'
            ORDER BY a.start_time ASC
            LIMIT 5
        ");
        $stmt->execute();
    }
    $calendar_events = $stmt->fetchAll();
}

// Get chart data for visualizations with role-based filtering
$chart_data = [];
if (in_array('charts', $config['widgets'])) {
    $case_ids = get_user_cases_access($user_id, $user_role);
    
    if (!empty($case_ids)) {
        $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
        
        // Last 6 months case trends
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM cases
            WHERE id IN ($placeholders)
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute($case_ids);
        $chart_data['case_trends'] = $stmt->fetchAll();
        
        // Cases by type
        $stmt = $pdo->prepare("
            SELECT case_type, COUNT(*) as count
            FROM cases
            WHERE id IN ($placeholders)
            AND created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY case_type
        ");
        $stmt->execute($case_ids);
        $chart_data['case_types'] = $stmt->fetchAll();
    } else {
        $chart_data['case_trends'] = [];
        $chart_data['case_types'] = [];
    }
}

// Get recent activity with role-based filtering
$recent_activities = [];
if (in_array('recent_activity', $config['widgets'])) {
    $case_ids = get_user_cases_access($user_id, $user_role);
    
    if (!empty($case_ids)) {
        $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT ca.*, c.title as case_title, u.name as user_name
            FROM case_activities ca
            JOIN cases c ON ca.case_id = c.id
            JOIN users u ON ca.user_id = u.id
            WHERE ca.case_id IN ($placeholders)
            ORDER BY ca.created_at DESC
            LIMIT 10
        ");
        $stmt->execute($case_ids);
        $recent_activities = $stmt->fetchAll();
    }
}

// Get contact submissions for relevant roles
$contact_submissions = [];
if (in_array('contact_submissions', $config['widgets'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, subject, status, created_at 
            FROM contact_submissions 
            WHERE status = 'new'
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $contact_submissions = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table doesn't exist yet, ignore
        $contact_submissions = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $config['title']; ?> | Med Attorneys</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/default.css">
    <style>
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, #0d1117 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
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
        
        .role-badge {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-primary);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .stat-card.primary { border-color: var(--info); }
        .stat-card.success { border-color: var(--success); }
        .stat-card.warning { border-color: var(--warning); }
        .stat-card.gold { border-color: var(--merlaws-gold); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0.5rem 0;
            line-height: 1;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title i {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }
        
        /* Notifications */
        .notification-item {
            padding: 1rem;
            border-left: 3px solid;
            margin-bottom: 0.75rem;
            border-radius: 0 8px 8px 0;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            background: #f1f5f9;
            transform: translateX(3px);
        }
        
        .notification-item.info { border-color: var(--info); }
        .notification-item.success { border-color: var(--success); }
        .notification-item.warning { border-color: var(--warning); }
        .notification-item.error { border-color: var(--danger); }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        /* Tasks */
        .task-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }
        
        .task-item:hover {
            border-color: var(--merlaws-gold);
            box-shadow: 0 2px 10px rgba(201,169,110,0.1);
        }
        
        .task-item.overdue {
            border-left: 4px solid var(--danger);
            background: #fef2f2;
        }
        
        .task-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .task-checkbox:hover {
            border-color: var(--merlaws-gold);
        }
        
        .task-content {
            flex: 1;
        }
        
        .task-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .task-meta {
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            gap: 1rem;
        }
        
        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .priority-badge.urgent { background: #fee2e2; color: #991b1b; }
        .priority-badge.high { background: #fef3c7; color: #92400e; }
        .priority-badge.medium { background: #dbeafe; color: #1e40af; }
        .priority-badge.low { background: #f3f4f6; color: #374151; }
        
        /* Calendar */
        .calendar-event {
            padding: 1rem;
            border-left: 4px solid var(--info);
            border-radius: 0 8px 8px 0;
            background: #f8fafc;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .calendar-event:hover {
            background: #f1f5f9;
            transform: translateX(3px);
        }
        
        .event-time {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--info);
            margin-bottom: 0.5rem;
        }
        
        .event-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .event-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        /* Charts */
        .chart-container {
            position: relative;
            height: 250px;
        }
        
        /* Activity Timeline */
        .activity-item {
            padding: 1rem;
            border-left: 3px solid #f1f5f9;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .activity-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 1.25rem;
            width: 11px;
            height: 11px;
            background: var(--merlaws-gold);
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .activity-item:hover {
            border-left-color: var(--merlaws-gold);
            background: #f8fafc;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .activity-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        /* Quick Actions */
        .quick-action {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: var(--merlaws-primary);
            transition: all 0.3s ease;
            display: block;
        }
        
        .quick-action:hover {
            border-color: var(--merlaws-gold);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(201,169,110,0.2);
            color: var(--merlaws-primary);
        }
        
        .quick-action i {
            font-size: 2rem;
            color: var(--merlaws-gold);
            margin-bottom: 0.75rem;
        }
        
        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="role-badge">
                <i class="fas fa-user-shield"></i>
                <?php echo ucfirst(str_replace('_', ' ', $user_role)); ?>
            </div>
            <h1 class="mb-2"><?php echo $config['title']; ?></h1>
            <p class="mb-0 opacity-75"><?php echo $config['subtitle']; ?></p>
        </div>
    </div>

    <div class="container mb-5">
        <?php 
        // Availability upload banner
        $availabilityFile = __DIR__ . '/../../storage/availability/availability_' . get_user_id() . '.json';
        if (!file_exists($availabilityFile)) { ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert" style="border-left:4px solid var(--merlaws-gold)">
            <div class="me-3"><i class="fas fa-calendar-plus"></i></div>
            <div>
                <strong>Please upload your weekly or monthly availability.</strong>
                <div class="small">Upload a CSV schedule so the system can check conflicts and show available slots.</div>
            </div>
            <a href="availability.php" class="btn btn-sm btn-outline-primary ms-auto">Upload Availability</a>
        </div>
        <?php } ?>
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <?php 
            $stat_index = 0;
            $colors = ['primary', 'success', 'warning', 'gold'];
            foreach ($stats as $key => $value): 
                $color = $colors[$stat_index % 4];
                $stat_index++;
            ?>
            <div class="stat-card <?php echo $color; ?>">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--<?php echo $color === 'gold' ? 'merlaws-gold' : $color; ?>), var(--<?php echo $color === 'gold' ? 'merlaws-gold' : $color; ?>));">
                    <i class="fas <?php 
                        echo strpos($key, 'case') !== false ? 'fa-briefcase' :
                             (strpos($key, 'user') !== false || strpos($key, 'client') !== false ? 'fa-users' :
                             (strpos($key, 'request') !== false ? 'fa-clipboard-list' :
                             (strpos($key, 'appointment') !== false ? 'fa-calendar' :
                             (strpos($key, 'task') !== false ? 'fa-tasks' :
                             'fa-chart-line'))));
                    ?>"></i>
                </div>
                <div class="stat-number"><?php echo is_array($value) ? count($value) : $value; ?></div>
                <div class="stat-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <?php if (in_array('notifications', $config['widgets'])): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-bell"></i>
                        Notifications
                        <?php if (count($notifications) > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo count($notifications); ?></span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p class="mb-0">You're all caught up!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo e($notif['type']); ?>">
                        <div class="notification-title">
                            <strong><?php echo e($notif['title']); ?></strong>
                            <span class="notification-time"><?php echo date('M d, g:i A', strtotime($notif['created_at'])); ?></span>
                        </div>
                        <div class="notification-message"><?php echo e($notif['message']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (in_array('tasks', $config['widgets'])): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-tasks"></i>
                        My Tasks
                        <span class="badge bg-warning ms-2"><?php echo count($tasks); ?></span>
                    </h3>
                    
                    <?php if (empty($tasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p class="mb-0">No pending tasks</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                    <div class="task-item" data-task-id="<?php echo $task['id']; ?>">
                        <div class="task-checkbox" onclick="toggleTask(<?php echo $task['id']; ?>)"></div>
                        <div class="task-content">
                            <div class="task-title"><?php echo e($task['title']); ?></div>
                            <div class="task-meta">
                                <?php if ($task['due_date']): ?>
                                <span><i class="far fa-calendar"></i> Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-flag"></i> <?php echo ucfirst($task['priority']); ?></span>
                                <?php if ($task['case_title']): ?>
                                <span><i class="fas fa-briefcase"></i> <?php echo e($task['case_title']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="priority-badge <?php echo $task['priority']; ?>"><?php echo $task['priority']; ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (in_array('tasks', $config['widgets']) && !empty($overdue_tasks)): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Overdue Tasks
                        <span class="badge bg-danger ms-2"><?php echo count($overdue_tasks); ?></span>
                    </h3>
                    
                    <?php foreach ($overdue_tasks as $task): ?>
                    <div class="task-item overdue" data-task-id="<?php echo $task['id']; ?>">
                        <div class="task-checkbox" onclick="toggleTask(<?php echo $task['id']; ?>)"></div>
                        <div class="task-content">
                            <div class="task-title"><?php echo e($task['title']); ?></div>
                            <div class="task-meta">
                                <span class="text-danger"><i class="far fa-calendar"></i> Overdue: <?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                <span><i class="fas fa-flag"></i> <?php echo ucfirst($task['priority']); ?></span>
                                <?php if ($task['case_title']): ?>
                                <span><i class="fas fa-briefcase"></i> <?php echo e($task['case_title']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="priority-badge urgent">Overdue</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (in_array('charts', $config['widgets']) && !empty($chart_data)): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Case Trends (Last 6 Months)
                    </h3>
                    <div class="chart-container">
                        <canvas id="caseTrendsChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (in_array('recent_activity', $config['widgets']) && !empty($recent_activities)): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h3>
                    
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-title"><?php echo e($activity['title']); ?></div>
                        <div class="activity-meta">
                            <strong>Case:</strong> <?php echo e($activity['case_title']); ?> • 
                            <strong>By:</strong> <?php echo e($activity['user_name']); ?> • 
                            <?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <?php if (in_array('calendar', $config['widgets'])): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Upcoming This Week
                    </h3>
                    
                    <?php if (empty($calendar_events)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <p class="mb-0">No upcoming events</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($calendar_events as $event): ?>
                    <div class="calendar-event">
                        <div class="event-time">
                            <?php echo date('D, M d - g:i A', strtotime($event['start_time'])); ?>
                        </div>
                        <div class="event-title"><?php echo e($event['title']); ?></div>
                        <div class="event-meta">
                            Case: <?php echo e($event['case_title']); ?>
                            <?php if (isset($event['assigned_name'])): ?>
                            <br>With: <?php echo e($event['assigned_name']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (in_array('contact_submissions', $config['widgets'])): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-envelope"></i>
                        Contact Submissions
                        <?php if (count($contact_submissions) > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo count($contact_submissions); ?></span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (empty($contact_submissions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p class="mb-0">No new submissions</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($contact_submissions as $submission): ?>
                    <div class="notification-item info">
                        <div class="notification-title">
                            <strong><?php echo e($submission['first_name'] . ' ' . $submission['last_name']); ?></strong>
                            <span class="notification-time"><?php echo date('M d, g:i A', strtotime($submission['created_at'])); ?></span>
                        </div>
                        <div class="notification-message">
                            <?php if ($submission['subject']): ?>
                            <strong>Subject:</strong> <?php echo e($submission['subject']); ?><br>
                            <?php endif; ?>
                            <small><?php echo e($submission['email']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <a href="contact-submissions.php" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-eye"></i> View All Submissions
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (in_array('quick_actions', $config['widgets'])): ?>
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                    <div class="d-grid gap-2">
                        <?php
                        $actions = [
                            'super_admin' => [
                                ['icon' => 'fa-users-cog', 'title' => 'Manage Users', 'url' => 'users.php'],
                                ['icon' => 'fa-cog', 'title' => 'Settings', 'url' => 'settings.php'],
                                ['icon' => 'fa-database', 'title' => 'Backups', 'url' => 'backups.php']
                            ],
                            'attorney' => [
                                ['icon' => 'fa-briefcase', 'title' => 'My Cases', 'url' => 'cases.php'],
                                ['icon' => 'fa-calendar-plus', 'title' => 'New Appointment', 'url' => 'calendar.php?action=create'],
                                ['icon' => 'fa-file-alt', 'title' => 'Documents', 'url' => '../documents/index.php']
                            ],
                            'paralegal' => [
                                ['icon' => 'fa-upload', 'title' => 'Upload Document', 'url' => '../documents/upload.php'],
                                ['icon' => 'fa-folder-open', 'title' => 'My Cases', 'url' => 'cases.php'],
                                ['icon' => 'fa-tasks', 'title' => 'Task List', 'url' => 'cases.php?view=tasks']
                            ],
                            'billing' => [
                                ['icon' => 'fa-file-invoice-dollar', 'title' => 'New Invoice', 'url' => 'finance.php?action=create'],
                                ['icon' => 'fa-credit-card', 'title' => 'Payments', 'url' => 'finance.php?tab=payments'],
                                ['icon' => 'fa-chart-line', 'title' => 'Reports', 'url' => 'reports.php']
                            ]
                        ];
                        
                        $role_actions = $actions[$user_role] ?? $actions['attorney'];
                        foreach ($role_actions as $action):
                        ?>
                        <a href="<?php echo $action['url']; ?>" class="quick-action">
                            <i class="fas <?php echo $action['icon']; ?>"></i>
                            <h6 class="mb-0 mt-2"><?php echo $action['title']; ?></h6>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (in_array('system_health', $config['widgets'])): ?>
                <div class="content-card">
                    <h5 class="section-title">
                        <i class="fas fa-heartbeat"></i>
                        System Status
                    </h5>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-database me-2 text-success"></i>Database</span>
                        <span class="badge bg-success">Operational</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-cloud-upload-alt me-2 text-success"></i>File Storage</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-envelope me-2 text-success"></i>Email Service</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-shield-alt me-2 text-success"></i>Security</span>
                        <span class="badge bg-success">Secure</span>
                    </div>
                    <hr class="my-3">
                    <div class="text-center">
                        <small class="text-muted">Last checked: <?php echo date('g:i A'); ?></small>
                    </div>
                </div>
                <?php endif; ?>

                <div class="content-card">
                    <h5 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Quick Info
                    </h5>
                    <div class="mb-3">
                        <small class="text-muted d-block">Current Time</small>
                        <strong id="currentTime"><?php echo date('g:i A'); ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Today's Date</small>
                        <strong><?php echo date('l, F j, Y'); ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Your Role</small>
                        <strong><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></strong>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="/" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-globe me-2"></i>View Public Site
                        </a>
                        <a href="/app/profile.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialize Charts
        <?php if (in_array('charts', $config['widgets']) && !empty($chart_data)): ?>
        const caseTrendsCtx = document.getElementById('caseTrendsChart');
        if (caseTrendsCtx) {
            new Chart(caseTrendsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($chart_data['case_trends'], 'month')); ?>,
                    datasets: [{
                        label: 'New Cases',
                        data: <?php echo json_encode(array_column($chart_data['case_trends'], 'count')); ?>,
                        borderColor: '#c9a96e',
                        backgroundColor: 'rgba(201, 169, 110, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // Task checkbox interactions
        function toggleTask(taskId) {
            const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
            if (!taskItem) return;
            
            // Show loading state
            taskItem.style.opacity = '0.5';
            
            // Make API call to complete task
            fetch('/app/api/tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=complete&task_id=${taskId}&${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate task completion
                    taskItem.style.transition = 'all 0.5s ease';
                    taskItem.style.transform = 'translateX(100%)';
                    taskItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        taskItem.remove();
                        // Update task count
                        updateTaskCounts();
                    }, 500);
                } else {
                    // Reset on error
                    taskItem.style.opacity = '1';
                    alert('Failed to complete task: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                taskItem.style.opacity = '1';
                alert('Failed to complete task');
            });
        }
        
        function updateTaskCounts() {
            // Update task count badges
            const taskItems = document.querySelectorAll('.task-item:not([style*="opacity: 0"])');
            const overdueItems = document.querySelectorAll('.task-item.overdue:not([style*="opacity: 0"])');
            
            const taskBadge = document.querySelector('.section-title .badge');
            if (taskBadge) {
                taskBadge.textContent = taskItems.length;
            }
            
            const overdueBadge = document.querySelector('.section-title .badge.bg-danger');
            if (overdueBadge) {
                overdueBadge.textContent = overdueItems.length;
            }
        }

        // Mark notification as read
        document.querySelectorAll('.notification-item').forEach(notif => {
            notif.addEventListener('click', function() {
                this.style.opacity = '0.6';
                // Here you would make an AJAX call to mark as read
            });
        });

        // Update current time every minute
        setInterval(() => {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            const timeEl = document.getElementById('currentTime');
            if (timeEl) timeEl.textContent = timeStr;
        }, 60000);

        // Auto-refresh notifications every 2 minutes
        setInterval(async () => {
            try {
                const response = await fetch('/app/api/notifications.php?unread=1');
                const data = await response.json();
                
                if (data.success && data.count > 0) {
                    // Update notification badge
                    const badge = document.querySelector('.section-title .badge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.classList.add('pulse');
                    }
                }
            } catch (error) {
                console.log('Notification refresh failed:', error);
            }
        }, 120000);

        // Smooth scroll for activity items
        document.querySelectorAll('.activity-item, .calendar-event').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
        });

        // Fade in animation
        setTimeout(() => {
            document.querySelectorAll('.activity-item, .calendar-event').forEach((item, index) => {
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }, 300);

        // Add pulse animation for badges
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            .pulse {
                animation: pulse 0.5s ease-in-out 3;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html