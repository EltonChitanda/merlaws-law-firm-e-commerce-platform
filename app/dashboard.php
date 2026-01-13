<?php
/**
 * Client Dashboard - CLIENTS ONLY
 * app/dashboard.php
 * 
 * This dashboard is exclusively for client role users.
 * All admin roles are redirected to their admin dashboard.
 */

require __DIR__ . '/config.php';

// CRITICAL: Enforce client-only access
require_login();

// Get user role - default to 'client' if not set
$role = get_user_role();

// Define all admin roles (from your config.php)
$admin_roles = [
    'super_admin', 'admin', 'manager', 'office_admin', 'partner', 
    'attorney', 'paralegal', 'intake', 'case_manager', 'billing', 
    'doc_specialist', 'it_admin', 'compliance', 'receptionist'
];

// REDIRECT ANY ADMIN TO THEIR ADMIN DASHBOARD
if (in_array($role, $admin_roles, true)) {
    header('Location: admin/dashboard.php');
    exit;
}

// ENFORCE CLIENT ROLE ONLY - Extra security layer
if ($role !== 'client') {
    // Log security event
    error_log("Unauthorized access attempt to client dashboard by role: {$role}, User ID: " . get_user_id());
    
    // Clear session and redirect to login
    session_destroy();
    header('Location: login.php?error=unauthorized');
    exit;
}

// At this point, we're guaranteed to have a client user
$name = get_user_name();
$user_id = get_user_id();

// Get client-specific dashboard data
try {
    $pdo = db();
    
    // Initialize statistics
$stats = [
        'active_cases' => 0,
        'total_documents' => 0,
        'upcoming_appointments' => 0,
        'unread_messages' => 0,
        'pending_requests' => 0,
        'completed_cases' => 0
    ];
    
    // Active cases count - only status = 'active'
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $stats['active_cases'] = (int)$stmt->fetchColumn();
    
    // Completed cases count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE user_id = ? AND status = 'closed'");
    $stmt->execute([$user_id]);
    $stats['completed_cases'] = (int)$stmt->fetchColumn();
    
    // Documents count
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cd.id) 
        FROM case_documents cd 
        JOIN cases c ON cd.case_id = c.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats['total_documents'] = (int)$stmt->fetchColumn();
    
    // Upcoming appointments
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments a
        JOIN cases c ON a.case_id = c.id 
        WHERE c.user_id = ? AND a.status = 'scheduled' AND a.start_time > NOW()
    ");
    $stmt->execute([$user_id]);
    $stats['upcoming_appointments'] = (int)$stmt->fetchColumn();
    
    // Unread messages
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.id) FROM messages m
        JOIN message_threads mt ON m.thread_id = mt.id
        JOIN cases c ON mt.case_id = c.id
        WHERE c.user_id = ? AND m.read_at IS NULL AND m.sender_id != ?
    ");
    $stmt->execute([$user_id, $user_id]);
    $stats['unread_messages'] = (int)$stmt->fetchColumn();
    
    // Pending service requests - only truly pending requests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM service_requests sr
        JOIN cases c ON sr.case_id = c.id
        WHERE c.user_id = ? AND sr.status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $stats['pending_requests'] = (int)$stmt->fetchColumn();
    
    // Get recent case activity
    $stmt = $pdo->prepare("
        SELECT ca.*, c.title as case_title, u.name as user_name
        FROM case_activities ca
        JOIN cases c ON ca.case_id = c.id
        JOIN users u ON ca.user_id = u.id
        WHERE c.user_id = ?
        ORDER BY ca.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll();
    
    // Get upcoming appointments in next 48 hours
    $stmt = $pdo->prepare("
        SELECT a.*, c.title as case_title 
        FROM appointments a
        JOIN cases c ON a.case_id = c.id
        WHERE c.user_id = ? AND a.status = 'scheduled' 
        AND a.start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)
        ORDER BY a.start_time ASC
    ");
    $stmt->execute([$user_id]);
    $upcoming_appointments = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Client dashboard error: " . $e->getMessage());
    $stats = [
        'active_cases' => 0,
        'total_documents' => 0,
        'upcoming_appointments' => 0,
        'unread_messages' => 0,
        'pending_requests' => 0,
        'completed_cases' => 0
    ];
    $recent_activities = [];
    $upcoming_appointments = [];
}

// Calculate profile completion
$user_completion_score = 0;
$completion_checks = [
    'has_cases' => $stats['active_cases'] > 0 || $stats['completed_cases'] > 0,
    'has_documents' => $stats['total_documents'] > 0,
    'profile_complete' => !empty($name),
    'has_interactions' => $stats['unread_messages'] > 0 || count($recent_activities) > 0
];

foreach ($completion_checks as $check) {
    if ($check) $user_completion_score += 25;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Portal | MerLaws</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/default.css">
    
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

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Professional Welcome Header */
        .dashboard-welcome {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .dashboard-welcome::before {
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .user-details h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-badge {
            background: var(--merlaws-gold);
            color: var(--merlaws-gray-900);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Enhanced Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: var(--shadow-md);
        }

        .stat-icon.cases { 
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light)); 
        }
        .stat-icon.documents { 
            background: linear-gradient(135deg, var(--merlaws-info), #4299e1); 
        }
        .stat-icon.appointments { 
            background: linear-gradient(135deg, var(--merlaws-success), #48bb78); 
        }
        .stat-icon.messages { 
            background: linear-gradient(135deg, var(--merlaws-warning), #f6ad55); 
        }
        .stat-icon.requests { 
            background: linear-gradient(135deg, var(--merlaws-secondary), #2d5aa0); 
        }
        .stat-icon.completed { 
            background: linear-gradient(135deg, #10b981, #059669); 
        }

        .stat-content {
            text-align: left;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--merlaws-gray-800);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--merlaws-gray-600);
            font-weight: 500;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-trend {
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .trend-up { color: var(--merlaws-success); }
        .trend-down { color: var(--merlaws-danger); }
        .trend-neutral { color: var(--merlaws-gray-500); }

        /* Professional Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-right: 1.5rem;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.5rem 0;
        }

        .card-subtitle {
            color: var(--merlaws-gray-600);
            font-size: 0.9rem;
            margin: 0;
        }

        .card-description {
            color: var(--merlaws-gray-600);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .card-link {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .card-link:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            color: white;
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
        }

        .card-link i {
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .card-link:hover i {
            transform: translateX(3px);
        }

        /* Activity Timeline */
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--merlaws-gray-200);
        }

        .activity-item {
            position: relative;
            padding-bottom: 2rem;
            margin-bottom: 1.5rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0;
            width: 12px;
            height: 12px;
            background: var(--merlaws-primary);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
        }

        .activity-content {
            background: var(--merlaws-gray-50);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--merlaws-primary);
        }

        .activity-title {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.5rem 0;
        }

        .activity-description {
            color: var(--merlaws-gray-600);
            font-size: 0.9rem;
            margin: 0 0 1rem 0;
        }

        .activity-meta {
            font-size: 0.8rem;
            color: var(--merlaws-gray-500);
            display: flex;
            gap: 1rem;
        }

        /* Urgent Alerts */
        .urgent-alerts {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .urgent-alerts h5 {
            color: #92400e;
            font-weight: 600;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .urgent-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            border-left: 4px solid #f59e0b;
        }

        .urgent-item:last-child {
            margin-bottom: 0;
        }

        /* Quick Action Buttons */
        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            background: white;
            color: var(--merlaws-primary);
            border: 2px solid var(--merlaws-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .quick-action-btn:hover {
            background: var(--merlaws-primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .quick-action-btn i {
            margin-right: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0.75rem;
            }

            .dashboard-welcome {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border-radius: 12px;
            }

            .welcome-title {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            .welcome-subtitle {
                font-size: 0.95rem;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stat-card, .dashboard-card {
                padding: 1.25rem;
            }
            
            .stat-card-title {
                font-size: 0.85rem;
            }
            
            .stat-card-value {
                font-size: 1.75rem;
            }
            
            .quick-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .quick-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0.5rem;
            }
            
            .dashboard-welcome {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
            
            .stat-card, .dashboard-card {
                padding: 1rem;
            }
            
            .stat-card-value {
                font-size: 1.5rem;
            }
            
            .card-title {
                font-size: 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Include Standard Header -->
    <?php 
    $headerPath = __DIR__ . '/../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <div class="dashboard-container">
        <!-- Professional Welcome Section -->
        <div class="dashboard-welcome">
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome back, <?php echo e($name); ?></h1>
                <p class="welcome-subtitle">Your legal matters are our priority. Here's your case overview and important updates.</p>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h3><?php echo e($name); ?></h3>
                        <div class="user-badge">Client Account</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Alerts -->
        <?php if (!empty($upcoming_appointments)): ?>
        <div class="urgent-alerts">
            <h5><i class="fas fa-exclamation-triangle"></i> Urgent Reminders</h5>
            <?php foreach ($upcoming_appointments as $appointment): ?>
            <div class="urgent-item">
                <strong><?php echo e($appointment['title']); ?></strong>
                <div class="small text-muted">
                    <i class="fas fa-clock"></i> <?php echo date('M d, Y g:i A', strtotime($appointment['start_time'])); ?>
                    • Case: <?php echo e($appointment['case_title']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Enhanced Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number" id="notifUnread">0</div>
                        <div class="stat-label">Notifications</div>
                    </div>
                    <div class="stat-icon messages">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
                <div id="notifList" class="small" style="max-height:140px; overflow:auto;"></div>
                <div class="mt-2"><a class="card-link" href="notifications/index.php">Open Notifications <i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['active_cases']; ?></div>
                        <div class="stat-label">Active Cases</div>
                    </div>
                    <div class="stat-icon cases">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_documents']; ?></div>
                        <div class="stat-label">Documents</div>
                    </div>
                    <div class="stat-icon documents">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['upcoming_appointments']; ?></div>
                        <div class="stat-label">Appointments</div>
                    </div>
                    <div class="stat-icon appointments">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                        <div class="stat-label">New Messages</div>
                    </div>
                    <div class="stat-icon messages">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['pending_requests']; ?></div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                    <div class="stat-icon requests">
                        <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['completed_cases']; ?></div>
                        <div class="stat-label">Completed Cases</div>
                    </div>
                    <div class="stat-icon completed">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Dashboard Cards -->
        <div class="dashboard-grid">




        </div>

        <!-- Recent Activity Timeline -->
        <?php if (!empty($recent_activities)): ?>
        <div class="dashboard-card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="card-title">Recent Activity</h3>
                    <p class="card-subtitle">Your latest case developments</p>
                </div>
            </div>
            
            <div class="activity-timeline">
                <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-content">
                        <h6 class="activity-title"><?php echo e($activity['title']); ?></h6>
                        <?php if (!empty($activity['description'])): ?>
                        <p class="activity-description"><?php echo e($activity['description']); ?></p>
                        <?php endif; ?>
                        <div class="activity-meta">
                            <span><i class="fas fa-briefcase"></i> <?php echo e($activity['case_title']); ?></span>
                            <span><i class="fas fa-user"></i> <?php echo e($activity['user_name']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Include Standard Footer -->
    <?php 
    $footerPath = __DIR__ . '/../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced dashboard interactions
        document.addEventListener('DOMContentLoaded', function() {
            initializeDashboard();
        });

        function initializeDashboard() {
            animateStatistics();
            setupCardInteractions();
            updateTimestamps();
            loadNotifications();
        }

        function animateStatistics() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach((stat, index) => {
                const finalValue = parseInt(stat.textContent);
                stat.textContent = '0';
                
                setTimeout(() => {
                    animateCounter(stat, finalValue, 1500);
                }, index * 150);
            });
        }

        async function loadNotifications() {
            try {
                const res = await fetch('/app/api/notifications.php?limit=5', { credentials: 'include' });
                if (!res.ok) return;
                const data = await res.json();
                const unread = (data && typeof data.unread === 'number') ? data.unread : 0;
                const items = Array.isArray(data.items) ? data.items : [];
                const unreadEl = document.getElementById('notifUnread');
                const listEl = document.getElementById('notifList');
                if (unreadEl) unreadEl.textContent = unread;
                if (listEl) {
                    if (items.length === 0) {
                        listEl.innerHTML = '<div class="text-muted">No recent notifications.</div>';
                    } else {
                        listEl.innerHTML = items.map(n => (
                            `<div class="mb-2">
                                <strong>${escapeHtml(n.title||'')}</strong>
                                <div class="text-muted">${escapeHtml((n.message||'').substring(0,100))}</div>
                            </div>`
                        )).join('');
                    }
                }
            } catch (e) { /* ignore */ }
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);

        function escapeHtml(s){
            return String(s).replace(/[&<>"]+/g, function(c){
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]);
            });
        }

        function animateCounter(element, target, duration) {
            const start = 0;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (target - start) * easeOut);
                
                element.textContent = current;
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                        } else {
                    element.textContent = target;
                }
            }
            
            requestAnimationFrame(update);
        }

        function setupCardInteractions() {
            const cards = document.querySelectorAll('.dashboard-card, .stat-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.zIndex = '1';
                });
            });
        }

        function updateTimestamps() {
            const timestamps = document.querySelectorAll('[data-timestamp]');
            
            timestamps.forEach(element => {
                const timestamp = element.getAttribute('data-timestamp');
                const timeAgo = getTimeAgo(new Date(timestamp));
                element.textContent = timeAgo;
            });
            
            setTimeout(updateTimestamps, 60000);
        }

        function getTimeAgo(date) {
                const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
            return `${Math.floor(diffInSeconds / 86400)} days ago`;
        }
    </script>
    <script src="assets/js/mobile-responsive.js"></script>
</body>
</html>