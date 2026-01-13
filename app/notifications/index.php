<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user_id = get_user_id();

// Handle mark as read action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    try {
        $notification_id = (int)$_POST['notification_id'];
        
        // Verify the notification belongs to the current user
        $stmt = $pdo->prepare('SELECT id FROM user_notifications WHERE id = ? AND user_id = ?');
        $stmt->execute([$notification_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Update the notification as read
            $update_stmt = $pdo->prepare('UPDATE user_notifications SET is_read = TRUE WHERE id = ? AND user_id = ?');
            $update_stmt->execute([$notification_id, $user_id]);
            
            // Set success message
            $_SESSION['success_message'] = 'Notification marked as read';
        }
        
        // Redirect back to prevent form resubmission
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
        
    } catch (Throwable $e) {
        $_SESSION['error_message'] = 'Failed to mark notification as read';
    }
}

// Handle mark all as read action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    try {
        $update_stmt = $pdo->prepare('UPDATE user_notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE');
        $update_stmt->execute([$user_id]);
        
        $_SESSION['success_message'] = 'All notifications marked as read';
        
        // Redirect back to prevent form resubmission
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
        
    } catch (Throwable $e) {
        $_SESSION['error_message'] = 'Failed to mark all notifications as read';
    }
}

// Helper function to normalize action URLs
function normalize_action_url($url) {
	if (empty($url)) {
		return null;
	}
	
	// Remove domain prefix if present
	$url = preg_replace('#^/www\.merlaws\.com#', '', $url);
	$url = preg_replace('#^https?://[^/]+#', '', $url);
	
	// If it's already a relative path starting with ../, use as is
	if (strpos($url, '../') === 0) {
		return $url;
	}
	
	// Check more specific paths first (before general /app/ check)
	// If it starts with /app/admin/, convert to relative
	if (strpos($url, '/app/admin/') === 0) {
		$url = str_replace('/app/admin/', '../../admin/', $url);
		return $url;
	}
	
	// If it starts with /app/support/, convert to relative
	if (strpos($url, '/app/support/') === 0) {
		$url = str_replace('/app/support/', '../../support/', $url);
		return $url;
	}
	
	// If it starts with /app/, make it relative from notifications directory
	if (strpos($url, '/app/') === 0) {
		// Remove /app/ prefix and make it relative
		$url = str_replace('/app/', '../', $url);
		return $url;
	}
	
	// If it starts with /, make it relative (assuming it's in app directory)
	if (strpos($url, '/') === 0) {
		$url = '..' . $url;
		return $url;
	}
	
	// If it doesn't start with / or ../, assume it's relative and add ../
	if (strpos($url, '/') !== 0 && strpos($url, '../') !== 0 && strpos($url, './') !== 0) {
		$url = '../' . ltrim($url, '/');
	}
	
	return $url;
}

// Counts - all from user_notifications
$counts = [
    'unread_notifications' => 0,
    'unread_messages' => 0,
    'pending_requests' => 0,
    'upcoming_48h' => 0,
];

try {
    // Unread user notifications count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE');
    $stmt->execute([$user_id]);
    $counts['unread_notifications'] = (int)$stmt->fetchColumn();

	// Message-related notifications (info type with message/support keywords, or any message-related)
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE AND (type IN ('message', 'message_reply', 'support_message', 'info') AND (title LIKE '%message%' OR title LIKE '%reply%' OR title LIKE '%support%' OR message LIKE '%message%' OR message LIKE '%reply%' OR message LIKE '%support%'))");
	$stmt->execute([$user_id]);
	$counts['unread_messages'] = (int)$stmt->fetchColumn();
	
	// Service request notifications
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE AND type IN ('service_request', 'service_approved', 'service_rejected')");
	$stmt->execute([$user_id]);
	$counts['pending_requests'] = (int)$stmt->fetchColumn();
	
	// Appointment notifications
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = FALSE AND (type IN ('appointment_confirmed', 'appointment_declined', 'appointment_proposed', 'appointment_update', 'appointment') OR title LIKE '%appointment%' OR message LIKE '%appointment%')");
	$stmt->execute([$user_id]);
	$counts['upcoming_48h'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {}

// All notifications
$recent_notifications = [];
try {
    $stmt = $pdo->prepare('SELECT id, type, title, message, action_url, is_read, created_at FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
    $stmt->execute([$user_id]);
    $recent_notifications = $stmt->fetchAll();
} catch (Throwable $e) {}

// Message-related notifications - show all message-related notifications (read and unread)
$unread = [];
try {
	$stmt = $pdo->prepare("SELECT id, type, title, message, action_url, is_read, created_at FROM user_notifications WHERE user_id = ? AND (type IN ('message', 'message_reply', 'support_message', 'info') AND (title LIKE '%message%' OR title LIKE '%reply%' OR title LIKE '%support%' OR message LIKE '%message%' OR message LIKE '%reply%' OR message LIKE '%support%')) ORDER BY created_at DESC LIMIT 50");
	$stmt->execute([$user_id]);
	$unread = $stmt->fetchAll();
} catch (Throwable $e) {}

// Service request notifications
$pending = [];
try {
	$stmt = $pdo->prepare("SELECT id, type, title, message, action_url, is_read, created_at FROM user_notifications WHERE user_id = ? AND type IN ('service_request', 'service_approved', 'service_rejected') ORDER BY created_at DESC LIMIT 50");
	$stmt->execute([$user_id]);
	$pending = $stmt->fetchAll();
} catch (Throwable $e) {}

// Appointment notifications - show all appointment-related notifications (read and unread)
$upcoming = [];
try {
	$stmt = $pdo->prepare("SELECT id, type, title, message, action_url, is_read, created_at FROM user_notifications WHERE user_id = ? AND (type IN ('appointment_confirmed', 'appointment_declined', 'appointment_proposed', 'appointment_update', 'appointment') OR title LIKE '%appointment%' OR message LIKE '%appointment%') ORDER BY created_at DESC LIMIT 50");
	$stmt->execute([$user_id]);
	$upcoming = $stmt->fetchAll();
} catch (Throwable $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Notifications | MerLaws</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../../css/default.css">
	<style>
		:root {
			--merlaws-primary: #AC132A;
			--merlaws-primary-dark: #8a0f22;
			--merlaws-secondary: #1a365d;
			--merlaws-gold: #d69e2e;
			--merlaws-gray-50: #f7fafc;
			--merlaws-gray-100: #edf2f7;
			--merlaws-gray-200: #e2e8f0;
			--merlaws-gray-600: #4a5568;
			--merlaws-gray-800: #1a202c;
			--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
			--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
			--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
		}

		body {
			font-family: 'Inter', sans-serif;
			background: linear-gradient(135deg, var(--merlaws-gray-50) 0%, var(--merlaws-gray-100) 100%);
			color: var(--merlaws-gray-800);
		}

		.page-header {
			background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
			color: white;
			padding: 3rem 0;
			margin-bottom: 2rem;
			box-shadow: var(--shadow-xl);
		}

		.page-title {
			font-family: 'Playfair Display', serif;
			font-size: 2.75rem;
			font-weight: 600;
		}

		.page-subtitle {
			opacity: 0.9;
			font-size: 1.1rem;
		}

		.stat-card {
			background: white;
			border-radius: 16px;
			padding: 1.5rem;
			box-shadow: var(--shadow-md);
			border: 1px solid var(--merlaws-gray-200);
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			gap: 1rem;
		}

		.stat-card:hover {
			transform: translateY(-5px);
			box-shadow: var(--shadow-lg);
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
		}

		.stat-icon.bg-primary { background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark)); }
		.stat-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
		.stat-icon.bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }

		.stat-info .stat-number {
			font-size: 1.75rem;
			font-weight: 700;
			color: var(--merlaws-gray-800);
			line-height: 1;
		}

		.stat-info .stat-label {
			font-size: 0.875rem;
			color: var(--merlaws-gray-600);
			font-weight: 500;
		}

		.content-card {
			background: white;
			border-radius: 16px;
			padding: 2rem;
			box-shadow: var(--shadow-md);
			border: 1px solid var(--merlaws-gray-200);
		}

		.nav-pills .nav-link {
			border-radius: 12px;
			font-weight: 600;
			padding: 0.75rem 1.5rem;
			color: var(--merlaws-gray-600);
			transition: all 0.3s ease;
		}

		.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
			background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
			color: white;
			box-shadow: 0 4px 12px rgba(172, 19, 42, 0.3);
		}

		.notification-list {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.notification-item {
			display: flex;
			gap: 1rem;
			padding: 1.25rem;
			border-bottom: 1px solid var(--merlaws-gray-100);
			transition: background-color 0.3s ease;
		}

		.notification-item:last-child {
			border-bottom: none;
		}

		.notification-item:hover {
			background-color: var(--merlaws-gray-50);
		}

		.notification-item.fw-bold {
			background-color: var(--merlaws-gray-50);
		}

		.notification-icon {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			flex-shrink: 0;
		}

		.notification-icon.icon-info { background-color: #3b82f6; }
		.notification-icon.icon-message { background-color: #8b5cf6; }
		.notification-icon.icon-request { background-color: #f59e0b; }
		.notification-icon.icon-appointment { background-color: #10b981; }

		.notification-content {
			flex-grow: 1;
		}

		.notification-title {
			font-weight: 600;
			color: var(--merlaws-gray-800);
			margin-bottom: 0.25rem;
		}

		.notification-body {
			color: var(--merlaws-gray-600);
			font-size: 0.9rem;
			margin-bottom: 0.5rem;
		}

		.notification-meta {
			font-size: 0.8rem;
			color: var(--merlaws-gray-600);
		}

		.notification-action .btn {
			font-size: 0.8rem;
			padding: 0.25rem 0.75rem;
			border-radius: 8px;
		}

		.form-check-input:checked {
			background-color: var(--merlaws-primary);
			border-color: var(--merlaws-primary);
		}

		.btn-primary-custom {
			background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
			border: none;
			color: white;
			padding: 0.75rem 1.5rem;
			border-radius: 8px;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.btn-primary-custom:hover {
			background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
			color: white;
		}

		.empty-state {
			text-align: center;
			padding: 3rem;
			color: var(--merlaws-gray-600);
		}

		.empty-state i {
			font-size: 3rem;
			color: var(--merlaws-gray-200);
			margin-bottom: 1rem;
		}

		.mark-read-btn {
			background: linear-gradient(135deg, #10b981, #059669);
			border: none;
			color: white;
			padding: 0.25rem 0.75rem;
			border-radius: 6px;
			font-size: 0.8rem;
			font-weight: 500;
			transition: all 0.3s ease;
			cursor: pointer;
		}

		.mark-read-btn:hover {
			background: linear-gradient(135deg, #059669, #047857);
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
			color: white;
		}

		.mark-read-btn:disabled {
			background: var(--merlaws-gray-400);
			cursor: not-allowed;
			transform: none;
			box-shadow: none;
		}

		.mark-all-read-btn {
			background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
			border: none;
			color: white;
			padding: 0.5rem 1rem;
			border-radius: 8px;
			font-weight: 600;
			transition: all 0.3s ease;
			margin-bottom: 1rem;
		}

		.mark-all-read-btn:hover {
			background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
			color: white;
		}

		.alert {
			border-radius: 12px;
			border: none;
			box-shadow: var(--shadow-md);
		}

		.alert-success {
			background: linear-gradient(135deg, #d1fae5, #a7f3d0);
			color: #065f46;
			border-left: 4px solid #10b981;
		}

		.alert-danger {
			background: linear-gradient(135deg, #fee2e2, #fecaca);
			color: #991b1b;
			border-left: 4px solid #ef4444;
		}
	</style>
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) { echo file_get_contents($headerPath); }
?>

<div class="page-header">
	<div class="container">
		<h1 class="page-title"><i class="fas fa-bell me-3"></i>Notification Center</h1>
		<p class="page-subtitle">All your important updates, messages, and alerts in one place.</p>
	</div>
</div>

<div class="container" style="max-width: 1400px; margin-bottom: 3rem;">
	<!-- Success/Error Messages -->
	<?php if (isset($_SESSION['success_message'])): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<i class="fas fa-check-circle me-2"></i>
			<?php echo $_SESSION['success_message']; ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php unset($_SESSION['success_message']); ?>
	<?php endif; ?>

	<?php if (isset($_SESSION['error_message'])): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<i class="fas fa-exclamation-circle me-2"></i>
			<?php echo $_SESSION['error_message']; ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php unset($_SESSION['error_message']); ?>
	<?php endif; ?>

	<!-- Stats Grid -->
	<div class="row g-3 mb-4">
		<div class="col-md-6 col-lg-3">
			<div class="stat-card">
				<div class="stat-icon bg-primary"><i class="fas fa-envelope-open-text"></i></div>
				<div class="stat-info">
					<div class="stat-number"><?php echo (int)$counts['unread_messages']; ?></div>
					<div class="stat-label">Unread Messages</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-3">
			<div class="stat-card">
				<div class="stat-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
				<div class="stat-info">
					<div class="stat-number"><?php echo (int)$counts['pending_requests']; ?></div>
					<div class="stat-label">Pending Requests</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-3">
			<div class="stat-card">
				<div class="stat-icon bg-info"><i class="fas fa-calendar-check"></i></div>
				<div class="stat-info">
					<div class="stat-number"><?php echo (int)$counts['upcoming_48h']; ?></div>
					<div class="stat-label">Upcoming Appointments</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-lg-3">
			<div class="stat-card">
				<div class="stat-icon bg-primary"><i class="fas fa-bell"></i></div>
				<div class="stat-info">
					<div class="stat-number"><?php echo (int)$counts['unread_notifications']; ?></div>
					<div class="stat-label">General Notifications</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-4">
		<!-- Main Notifications Panel -->
		<div class="col-lg-12">
			<div class="content-card">
				<div class="d-flex justify-content-between align-items-center mb-4">
					<ul class="nav nav-pills" id="notificationsTab" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all-content" type="button">All</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="messages-tab" data-bs-toggle="pill" data-bs-target="#messages-content" type="button">Messages</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="requests-tab" data-bs-toggle="pill" data-bs-target="#requests-content" type="button">Requests</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="appointments-tab" data-bs-toggle="pill" data-bs-target="#appointments-content" type="button">Appointments</button>
						</li>
					</ul>
					
					<?php if ($counts['unread_notifications'] > 0): ?>
					<form method="POST" class="d-inline">
						<input type="hidden" name="mark_all_read" value="1">
						<button type="submit" class="mark-all-read-btn">
							<i class="fas fa-check-double me-2"></i>Mark All as Read
						</button>
					</form>
					<?php endif; ?>
				</div>

				<div class="tab-content" id="notificationsTabContent">
					<!-- All Notifications -->
					<div class="tab-pane fade show active" id="all-content" role="tabpanel">
						<ul class="notification-list">
							<?php if (empty($recent_notifications)): ?>
								<li class="empty-state"><i class="fas fa-bell-slash"></i><p>No notifications to display.</p></li>
							<?php else: ?>
								<?php foreach ($recent_notifications as $n): 
									$icon_class = 'icon-info';
									$icon_fa = 'fa-info-circle';
									if (in_array($n['type'], ['service_request', 'service_approved', 'service_rejected'])) {
										$icon_class = 'icon-request';
										$icon_fa = 'fa-clipboard-list';
									} elseif (in_array($n['type'], ['appointment_confirmed', 'appointment_declined', 'appointment_proposed', 'appointment_update'])) {
										$icon_class = 'icon-appointment';
										$icon_fa = 'fa-calendar-check';
									} elseif (strpos(strtolower($n['title']), 'message') !== false || strpos(strtolower($n['title']), 'reply') !== false || strpos(strtolower($n['title']), 'support') !== false) {
										$icon_class = 'icon-message';
										$icon_fa = 'fa-envelope';
									} elseif ($n['type'] === 'success') {
										$icon_class = 'icon-info';
										$icon_fa = 'fa-check-circle';
									} elseif ($n['type'] === 'warning') {
										$icon_class = 'icon-request';
										$icon_fa = 'fa-exclamation-triangle';
									} elseif ($n['type'] === 'error') {
										$icon_class = 'icon-request';
										$icon_fa = 'fa-times-circle';
									}
								?>
								<li class="notification-item <?php echo $n['is_read'] ? '' : 'fw-bold'; ?>">
									<div class="notification-icon <?php echo $icon_class; ?>"><i class="fas <?php echo $icon_fa; ?>"></i></div>
									<div class="notification-content">
										<div class="notification-title"><?php echo e($n['title']); ?></div>
										<div class="notification-body"><?php echo e(mb_strimwidth($n['message'] ?? '', 0, 150, '...')); ?></div>
										<div class="notification-meta"><?php echo e(date('M d, Y g:i A', strtotime($n['created_at']))); ?></div>
									</div>
									<div class="notification-action align-self-center d-flex gap-2">
										<?php if (!$n['is_read']): ?>
										<form method="POST" class="d-inline">
											<input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
											<button type="submit" name="mark_read" class="mark-read-btn" title="Mark as Read">
												<i class="fas fa-check me-1"></i>Read
											</button>
										</form>
										<?php endif; ?>
										<?php 
										// Only show view button for service request notifications in the "All" tab
										$is_service_request = in_array($n['type'], ['service_request', 'service_approved', 'service_rejected']);
										if ($is_service_request) {
											$action_url = normalize_action_url($n['action_url'] ?? '');
											// Add hash anchor for service requests to scroll to Service Requests section
											if (!empty($action_url) && strpos($action_url, 'cases/view.php') !== false) {
												// Check if hash already exists
												if (strpos($action_url, '#') === false) {
													$action_url .= '#service-requests';
												}
											}
											if (!empty($action_url)): ?>
											<a href="<?php echo e($action_url); ?>" class="btn btn-sm btn-outline-primary">View</a>
											<?php endif;
										}
										?>
									</div>
								</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</div>

					<!-- Messages -->
					<div class="tab-pane fade" id="messages-content" role="tabpanel">
						<ul class="notification-list">
							<?php if (empty($unread)): ?>
								<li class="empty-state"><i class="fas fa-comment-slash"></i><p>No message notifications.</p></li>
							<?php else: ?>
								<?php foreach ($unread as $m): ?>
								<li class="notification-item <?php echo $m['is_read'] ? '' : 'fw-bold'; ?>">
									<div class="notification-icon icon-message"><i class="fas fa-envelope"></i></div>
									<div class="notification-content">
										<div class="notification-title"><?php echo e($m['title']); ?></div>
										<div class="notification-body"><?php echo e(mb_strimwidth($m['message'] ?? '', 0, 120, '...')); ?></div>
										<div class="notification-meta"><?php echo e(date('M d, Y g:i A', strtotime($m['created_at']))); ?></div>
									</div>
									<div class="notification-action align-self-center d-flex gap-2">
										<?php if (!$m['is_read']): ?>
										<form method="POST" class="d-inline">
											<input type="hidden" name="notification_id" value="<?php echo $m['id']; ?>">
											<button type="submit" name="mark_read" class="mark-read-btn" title="Mark as Read">
												<i class="fas fa-check me-1"></i>Read
											</button>
										</form>
										<?php endif; ?>
										<?php 
										$action_url = normalize_action_url($m['action_url'] ?? '');
										if (!empty($action_url)): ?>
										<a href="<?php echo e($action_url); ?>" class="btn btn-sm btn-outline-primary">View</a>
										<?php endif; ?>
									</div>
								</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</div>

					<!-- Requests -->
					<div class="tab-pane fade" id="requests-content" role="tabpanel">
						<ul class="notification-list">
							<?php if (empty($pending)): ?>
								<li class="empty-state"><i class="fas fa-clipboard-question"></i><p>No service request notifications.</p></li>
							<?php else: ?>
								<?php foreach ($pending as $p): ?>
								<li class="notification-item <?php echo $p['is_read'] ? '' : 'fw-bold'; ?>">
									<div class="notification-icon icon-request"><i class="fas fa-clipboard-list"></i></div>
									<div class="notification-content">
										<div class="notification-title"><?php echo e($p['title']); ?></div>
										<div class="notification-body"><?php echo e(mb_strimwidth($p['message'] ?? '', 0, 120, '...')); ?></div>
										<div class="notification-meta"><?php echo e(date('M d, Y g:i A', strtotime($p['created_at']))); ?></div>
									</div>
									<div class="notification-action align-self-center d-flex gap-2">
										<?php if (!$p['is_read']): ?>
										<form method="POST" class="d-inline">
											<input type="hidden" name="notification_id" value="<?php echo $p['id']; ?>">
											<button type="submit" name="mark_read" class="mark-read-btn" title="Mark as Read">
												<i class="fas fa-check me-1"></i>Read
											</button>
										</form>
										<?php endif; ?>
										<?php 
										$action_url = normalize_action_url($p['action_url'] ?? '');
										// Add hash anchor for service requests to scroll to Service Requests section
										if (!empty($action_url) && strpos($action_url, 'cases/view.php') !== false) {
											// Check if hash already exists
											if (strpos($action_url, '#') === false) {
												$action_url .= '#service-requests';
											}
										}
										if (!empty($action_url)): ?>
										<a href="<?php echo e($action_url); ?>" class="btn btn-sm btn-outline-primary">View</a>
										<?php endif; ?>
									</div>
								</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</div>

					<!-- Appointments -->
					<div class="tab-pane fade" id="appointments-content" role="tabpanel">
						<ul class="notification-list">
							<?php if (empty($upcoming)): ?>
								<li class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointment notifications.</p></li>
							<?php else: ?>
								<?php foreach ($upcoming as $a): ?>
								<li class="notification-item <?php echo $a['is_read'] ? '' : 'fw-bold'; ?>">
									<div class="notification-icon icon-appointment"><i class="fas fa-calendar-check"></i></div>
									<div class="notification-content">
										<div class="notification-title"><?php echo e($a['title']); ?></div>
										<div class="notification-body"><?php echo e(mb_strimwidth($a['message'] ?? '', 0, 120, '...')); ?></div>
										<div class="notification-meta"><?php echo e(date('M d, Y g:i A', strtotime($a['created_at']))); ?></div>
									</div>
									<div class="notification-action align-self-center d-flex gap-2">
										<?php if (!$a['is_read']): ?>
										<form method="POST" class="d-inline">
											<input type="hidden" name="notification_id" value="<?php echo $a['id']; ?>">
											<button type="submit" name="mark_read" class="mark-read-btn" title="Mark as Read">
												<i class="fas fa-check me-1"></i>Read
											</button>
										</form>
										<?php endif; ?>
										<?php 
										$action_url = normalize_action_url($a['action_url'] ?? '');
										if (!empty($action_url)): ?>
										<a href="<?php echo e($action_url); ?>" class="btn btn-sm btn-outline-primary">View</a>
										<?php endif; ?>
									</div>
								</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($footerPath)) { echo file_get_contents($footerPath); }
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
</body>
</html>