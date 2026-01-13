<?php
require __DIR__ . '/../config.php';

require_login();

$user_id = get_user_id();
$pdo = db();

$stmt = $pdo->prepare("SELECT id, title, status FROM cases WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : (count($cases) ? (int)$cases[0]['id'] : 0);
$selectedCase = null;
$activities = [];

if ($caseId) {
	// Get case details
	$stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND user_id = ?");
	$stmt->execute([$caseId, $user_id]);
	$selectedCase = $stmt->fetch();
	
	// Get activities
	$stmt = $pdo->prepare("SELECT a.*, u.name AS user_name FROM case_activities a JOIN users u ON a.user_id = u.id WHERE a.case_id = ? ORDER BY a.created_at DESC");
	$stmt->execute([$caseId]);
	$activities = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Case Progress | Med Attorneys</title>
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
			--merlaws-success: #38a169;
			--merlaws-gray-50: #f7fafc;
			--merlaws-gray-100: #edf2f7;
			--merlaws-gray-200: #e2e8f0;
			--merlaws-gray-300: #cbd5e0;
			--merlaws-gray-500: #718096;
			--merlaws-gray-600: #4a5568;
			--merlaws-gray-800: #1a202c;
			--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
			--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
		}

		body {
			font-family: 'Inter', sans-serif;
			background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
			color: var(--merlaws-gray-800);
			min-height: 100vh;
		}

		.page-header {
			background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
			color: white;
			padding: 3rem 0;
			margin-bottom: 2rem;
			box-shadow: var(--shadow-xl);
			position: relative;
			overflow: hidden;
		}

		.page-header::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
		}

		.page-header .container {
			position: relative;
			z-index: 1;
		}

		.page-title {
			font-family: 'Playfair Display', serif;
			font-size: 2.5rem;
			font-weight: 600;
			margin: 0;
		}

		.page-subtitle {
			opacity: 0.9;
			font-size: 1.1rem;
			margin-top: 0.5rem;
		}

		.card-professional {
			background: white;
			border-radius: 16px;
			box-shadow: var(--shadow-md);
			border: 1px solid var(--merlaws-gray-200);
			transition: all 0.3s ease;
			overflow: hidden;
			position: relative;
		}

		.card-professional::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 4px;
			background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
		}

		.case-selector {
			background: white;
			padding: 1.5rem;
			border-radius: 12px;
			box-shadow: var(--shadow-md);
			margin-bottom: 2rem;
		}

		.case-status-badge {
			padding: 0.5rem 1.25rem;
			border-radius: 20px;
			font-weight: 600;
			font-size: 0.875rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.status-active {
			background: linear-gradient(135deg, #d1fae5, #a7f3d0);
			color: #065f46;
		}

		.status-closed {
			background: linear-gradient(135deg, #e5e7eb, #d1d5db);
			color: #374151;
		}

		.status-under_review {
			background: linear-gradient(135deg, #fef3c7, #fde68a);
			color: #92400e;
		}

		.timeline-container {
			position: relative;
			padding: 2rem;
		}

		.timeline {
			position: relative;
			padding-left: 3rem;
		}

		.timeline::before {
			content: '';
			position: absolute;
			left: 1.5rem;
			top: 0;
			bottom: 0;
			width: 3px;
			background: linear-gradient(to bottom, var(--merlaws-primary), var(--merlaws-gold));
		}

		.timeline-item {
			position: relative;
			padding-bottom: 2.5rem;
			margin-bottom: 1rem;
		}

		.timeline-item:last-child {
			padding-bottom: 0;
		}

		.timeline-marker {
			position: absolute;
			left: -2.35rem;
			top: 0;
			width: 20px;
			height: 20px;
			background: white;
			border: 4px solid var(--merlaws-primary);
			border-radius: 50%;
			box-shadow: 0 0 0 4px rgba(172, 19, 42, 0.1);
			z-index: 2;
		}

		.timeline-content {
			background: var(--merlaws-gray-50);
			padding: 1.5rem;
			border-radius: 12px;
			border-left: 4px solid var(--merlaws-primary);
			box-shadow: var(--shadow-md);
			transition: all 0.3s ease;
		}

		.timeline-content:hover {
			transform: translateX(8px);
			box-shadow: var(--shadow-xl);
		}

		.timeline-title {
			font-weight: 700;
			font-size: 1.1rem;
			color: var(--merlaws-gray-800);
			margin: 0 0 0.75rem 0;
		}

		.timeline-description {
			color: var(--merlaws-gray-600);
			margin: 0 0 1rem 0;
			line-height: 1.6;
		}

		.timeline-meta {
			display: flex;
			gap: 1.5rem;
			font-size: 0.875rem;
			color: var(--merlaws-gray-500);
			flex-wrap: wrap;
		}

		.timeline-meta-item {
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.activity-type-badge {
			display: inline-block;
			padding: 0.25rem 0.75rem;
			border-radius: 12px;
			font-size: 0.75rem;
			font-weight: 600;
			text-transform: uppercase;
		}

		.type-filing {
			background: #dbeafe;
			color: #1e40af;
		}

		.type-meeting {
			background: #fef3c7;
			color: #92400e;
		}

		.type-communication {
			background: #e0e7ff;
			color: #3730a3;
		}

		.type-document {
			background: #f3e8ff;
			color: #6b21a8;
		}

		.type-default {
			background: var(--merlaws-gray-200);
			color: var(--merlaws-gray-600);
		}

		.empty-state {
			text-align: center;
			padding: 4rem 2rem;
		}

		.empty-state i {
			font-size: 4rem;
			color: var(--merlaws-gray-300);
			margin-bottom: 1.5rem;
		}

		.empty-state h3 {
			font-family: 'Playfair Display', serif;
			color: var(--merlaws-gray-600);
			margin-bottom: 1rem;
		}

		@media (max-width: 768px) {
			.timeline {
				padding-left: 2rem;
			}

			.timeline::before {
				left: 0.75rem;
			}

			.timeline-marker {
				left: -1.6rem;
				width: 16px;
				height: 16px;
			}

			.page-title {
				font-size: 2rem;
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
		<h1 class="page-title"><i class="fas fa-chart-line me-3"></i>Case Progress</h1>
		<p class="page-subtitle">Track your case developments, milestones, and activity timeline</p>
	</div>
</div>

<div class="container" style="max-width: 1200px; margin-bottom: 3rem;">
	<?php if (!$cases): ?>
		<div class="card-professional">
			<div class="empty-state">
				<i class="fas fa-briefcase"></i>
				<h3>No Cases Found</h3>
				<p class="text-muted">You don't have any cases yet. Create a case to start tracking progress.</p>
				<a href="../cases/create.php" class="btn btn-primary mt-3" style="background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark)); border: none;">
					<i class="fas fa-plus me-2"></i>Create New Case
				</a>
			</div>
		</div>
	<?php else: ?>
		<!-- Case Selector -->
		<div class="case-selector">
			<form method="get" class="row g-3 align-items-center">
				<div class="col-auto">
					<label for="case_id" class="form-label fw-bold mb-0">
						<i class="fas fa-briefcase me-2"></i>Select Case:
					</label>
				</div>
				<div class="col-md-6">
					<select name="case_id" id="case_id" class="form-select" onchange="this.form.submit()">
						<?php foreach ($cases as $c): ?>
							<option value="<?php echo (int)$c['id']; ?>" <?php echo $caseId===(int)$c['id']?'selected':''; ?>>
								<?php echo e($c['title']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ($selectedCase): ?>
				<div class="col-auto">
					<span class="case-status-badge status-<?php echo e($selectedCase['status'] ?? 'active'); ?>">
						<?php echo ucwords(str_replace('_', ' ', $selectedCase['status'] ?? 'Active')); ?>
					</span>
				</div>
				<?php endif; ?>
			</form>
		</div>

		<!-- Timeline -->
		<div class="card-professional">
			<div class="timeline-container">
				<?php if (!$activities): ?>
					<div class="empty-state">
						<i class="fas fa-clipboard-list"></i>
						<h3>No Activity Recorded</h3>
						<p class="text-muted">There is no recorded activity for this case yet. Activity will appear here as your case progresses.</p>
					</div>
				<?php else: ?>
					<div class="timeline">
						<?php foreach ($activities as $a): ?>
						<div class="timeline-item">
							<div class="timeline-marker"></div>
							<div class="timeline-content">
								<h4 class="timeline-title"><?php echo e($a['title']); ?></h4>
								
								<?php if (!empty($a['description'])): ?>
								<p class="timeline-description"><?php echo e($a['description']); ?></p>
								<?php endif; ?>
								
								<div class="timeline-meta">
									<div class="timeline-meta-item">
										<span class="activity-type-badge type-<?php echo e(strtolower(str_replace(' ', '_', $a['activity_type'] ?? 'default'))); ?>">
											<?php echo e($a['activity_type'] ?? 'General'); ?>
										</span>
									</div>
									<div class="timeline-meta-item">
										<i class="fas fa-user"></i>
										<span><?php echo e($a['user_name']); ?></span>
									</div>
									<div class="timeline-meta-item">
										<i class="fas fa-clock"></i>
										<span><?php echo date('M d, Y g:i A', strtotime($a['created_at'])); ?></span>
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
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
</body>
</html>