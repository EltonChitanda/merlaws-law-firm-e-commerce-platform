<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('settings:manage');

$pdo = db();
$errors = [];
$success = '';

$tab = (string)($_GET['tab'] ?? 'services');
$allowedTabs = ['services'];
if (!in_array($tab, $allowedTabs, true)) { $tab = 'services'; }

if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token. Please refresh and try again.';
	} else {
		$action = (string)($_POST['action'] ?? '');
		try {
			switch ($action) {
				case 'save_page': {
					$id = (int)($_POST['id'] ?? 0);
					$title = trim((string)($_POST['title'] ?? ''));
					$slug = trim((string)($_POST['slug'] ?? ''));
					$content = (string)($_POST['content'] ?? '');
					$is_published = isset($_POST['is_published']) ? 1 : 0;
					if ($title === '' || $slug === '') { $errors[] = 'Title and slug are required.'; break; }
					if ($id > 0) {
						$stmt = $pdo->prepare('UPDATE pages SET title = ?, slug = ?, content = ?, is_published = ?, updated_at = NOW() WHERE id = ?');
						$stmt->execute([$title,$slug,$content,$is_published,$id]);
						$success = 'Page updated successfully.';
					} else {
						$stmt = $pdo->prepare('INSERT INTO pages (title, slug, content, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
						$stmt->execute([$title,$slug,$content,$is_published]);
						$success = 'Page created successfully.';
					}
					$tab = 'pages';
					break;
				}
				case 'delete_page': {
					$id = (int)($_POST['id'] ?? 0);
					if ($id > 0) {
						$stmt = $pdo->prepare('DELETE FROM pages WHERE id = ?');
						$stmt->execute([$id]);
						$success = 'Page deleted successfully.';
					}
					$tab = 'pages';
					break;
				}
				case 'save_news': {
					$id = (int)($_POST['id'] ?? 0);
					$title = trim((string)($_POST['title'] ?? ''));
					$body = (string)($_POST['body'] ?? '');
					$published_at = (string)($_POST['published_at'] ?? '');
					if ($title === '') { $errors[] = 'Title is required.'; break; }
					if ($id > 0) {
						$stmt = $pdo->prepare('UPDATE news_articles SET title = ?, body = ?, published_at = NULLIF(?, ""), updated_at = NOW() WHERE id = ?');
						$stmt->execute([$title,$body,$published_at,$id]);
						$success = 'Article updated successfully.';
					} else {
						$stmt = $pdo->prepare('INSERT INTO news_articles (title, body, published_at, created_at, updated_at) VALUES (?, ?, NULLIF(?, ""), NOW(), NOW())');
						$stmt->execute([$title,$body,$published_at]);
						$success = 'Article created successfully.';
					}
					$tab = 'news';
					break;
				}
				case 'delete_news': {
					$id = (int)($_POST['id'] ?? 0);
					if ($id > 0) {
						$stmt = $pdo->prepare('DELETE FROM news_articles WHERE id = ?');
						$stmt->execute([$id]);
						$success = 'Article deleted successfully.';
					}
					$tab = 'news';
					break;
				}
				case 'save_attorney': {
					$id = (int)($_POST['id'] ?? 0);
					$name = trim((string)($_POST['name'] ?? ''));
					$email = trim((string)($_POST['email'] ?? ''));
					$title = trim((string)($_POST['title'] ?? ''));
					$bio = (string)($_POST['bio'] ?? '');
					if ($name === '') { $errors[] = 'Name is required.'; break; }
					if ($id > 0) {
						$stmt = $pdo->prepare('UPDATE attorney_profiles SET name = ?, email = NULLIF(?, ""), title = NULLIF(?, ""), bio = ? WHERE id = ?');
						$stmt->execute([$name,$email,$title,$bio,$id]);
						$success = 'Attorney profile updated successfully.';
					} else {
						$stmt = $pdo->prepare('INSERT INTO attorney_profiles (name, email, title, bio, created_at) VALUES (?, NULLIF(?, ""), NULLIF(?, ""), ?, NOW())');
						$stmt->execute([$name,$email,$title,$bio]);
						$success = 'Attorney profile created successfully.';
					}
					$tab = 'attorneys';
					break;
				}
				case 'delete_attorney': {
					$id = (int)($_POST['id'] ?? 0);
					if ($id > 0) {
						$stmt = $pdo->prepare('DELETE FROM attorney_profiles WHERE id = ?');
						$stmt->execute([$id]);
						$success = 'Attorney profile deleted successfully.';
					}
					$tab = 'attorneys';
					break;
				}
				case 'save_service': {
					$id = (int)($_POST['id'] ?? 0);
					$name = trim((string)($_POST['name'] ?? ''));
					$category = trim((string)($_POST['category'] ?? 'General'));
					$description = (string)($_POST['description'] ?? '');
					$is_active = isset($_POST['is_active']) ? 1 : 0;
					if ($name === '') { $errors[] = 'Name is required.'; break; }
					if ($id > 0) {
						$stmt = $pdo->prepare('UPDATE services SET name = ?, category = ?, description = ?, is_active = ? WHERE id = ?');
						$stmt->execute([$name,$category,$description,$is_active,$id]);
						$success = 'Service updated successfully.';
					} else {
						$stmt = $pdo->prepare('INSERT INTO services (name, category, description, is_active) VALUES (?, ?, ?, ?)');
						$stmt->execute([$name,$category,$description,$is_active]);
						$success = 'Service created successfully.';
					}
					$tab = 'services';
					break;
				}
				case 'delete_service': {
					$id = (int)($_POST['id'] ?? 0);
					if ($id > 0) {
						$stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
						$stmt->execute([$id]);
						$success = 'Service deleted successfully.';
					}
					$tab = 'services';
					break;
				}
				default:
					$errors[] = 'Unknown action.';
			}
		} catch (Throwable $e) {
			$errors[] = 'Operation failed. Please ensure required tables exist.';
		}
	}
}

// Fetch lists (non-fatal if tables missing)
$pages = $news = $attorneyProfiles = $serviceList = [];
try { $pages = $pdo->query('SELECT * FROM pages ORDER BY updated_at DESC')->fetchAll(); } catch (Throwable $e) {}
try { $news = $pdo->query('SELECT * FROM news_articles ORDER BY COALESCE(published_at, created_at) DESC')->fetchAll(); } catch (Throwable $e) {}
try { $attorneyProfiles = $pdo->query('SELECT * FROM attorney_profiles ORDER BY name ASC')->fetchAll(); } catch (Throwable $e) {}
try { $serviceList = $pdo->query('SELECT * FROM services ORDER BY category, name')->fetchAll(); } catch (Throwable $e) {}

// Count statistics
$stats = [
	'pages' => count($pages),
	'published_pages' => count(array_filter($pages, fn($p) => $p['is_published'])),
	'news' => count($news),
	'attorneys' => count($attorneyProfiles),
	'services' => count($serviceList),
	'active_services' => count(array_filter($serviceList, fn($s) => $s['is_active']))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Content Management | Med Attorneys Admin</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
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
		
		.stats-mini-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
			gap: 1rem;
			margin-bottom: 2rem;
		}
		
		.stat-mini-card {
			background: white;
			border-radius: 12px;
			padding: 1.25rem;
			text-align: center;
			box-shadow: 0 2px 8px rgba(0,0,0,0.06);
			border-top: 3px solid var(--merlaws-gold);
			transition: all 0.3s ease;
		}
		
		.stat-mini-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 4px 16px rgba(0,0,0,0.1);
		}
		
		.stat-mini-number {
			font-size: 1.75rem;
			font-weight: 800;
			color: var(--merlaws-gold);
			margin-bottom: 0.25rem;
		}
		
		.stat-mini-label {
			font-size: 0.75rem;
			color: #6b7280;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			font-weight: 600;
		}
		
		.nav-tabs {
			border: none;
			gap: 0.5rem;
			margin-bottom: 2rem;
		}
		
		.nav-tabs .nav-link {
			border: 2px solid transparent;
			border-radius: 12px;
			padding: 0.75rem 1.5rem;
			color: #6b7280;
			font-weight: 600;
			transition: all 0.3s ease;
			background: white;
		}
		
		.nav-tabs .nav-link:hover {
			color: var(--merlaws-gold);
			border-color: var(--merlaws-gold);
		}
		
		.nav-tabs .nav-link.active {
			color: white;
			background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
			border-color: var(--merlaws-gold);
		}
		
		.nav-tabs .nav-link i {
			margin-right: 0.5rem;
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
		
		.form-label {
			font-weight: 600;
			color: #374151;
			margin-bottom: 0.5rem;
			font-size: 0.9rem;
		}
		
		.form-control, .form-select {
			border: 2px solid #e5e7eb;
			border-radius: 10px;
			padding: 0.65rem 1rem;
			transition: all 0.3s ease;
		}
		
		.form-control:focus, .form-select:focus {
			border-color: var(--merlaws-gold);
			box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
		}
		
		textarea.form-control {
			min-height: 120px;
		}
		
		.btn {
			border-radius: 10px;
			padding: 0.65rem 1.5rem;
			font-weight: 600;
			transition: all 0.3s ease;
		}
		
		.btn-primary {
			background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
			border: none;
			color: white;
		}
		
		.btn-primary:hover {
			background: linear-gradient(135deg, #d4af37, var(--merlaws-gold));
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(201, 169, 110, 0.3);
		}
		
		.table-container {
			background: white;
			border-radius: 16px;
			overflow: hidden;
			box-shadow: 0 4px 20px rgba(0,0,0,0.08);
		}
		
		.table {
			margin-bottom: 0;
		}
		
		.table thead th {
			background: linear-gradient(135deg, #f8fafc, #f1f5f9);
			border: none;
			padding: 1rem;
			font-weight: 700;
			color: var(--merlaws-primary);
			text-transform: uppercase;
			font-size: 0.75rem;
			letter-spacing: 0.5px;
		}
		
		.table tbody tr {
			border-bottom: 1px solid #f1f5f9;
			transition: all 0.3s ease;
		}
		
		.table tbody tr:hover {
			background: #f8fafc;
			transform: scale(1.01);
		}
		
		.table tbody td {
			padding: 1rem;
			vertical-align: middle;
		}
		
		.badge {
			padding: 0.4rem 0.9rem;
			border-radius: 8px;
			font-weight: 700;
			font-size: 0.75rem;
			letter-spacing: 0.3px;
		}
		
		.badge.bg-success {
			background: linear-gradient(135deg, var(--success), #059669) !important;
		}
		
		.badge.bg-secondary {
			background: linear-gradient(135deg, #6b7280, #4b5563) !important;
		}
		
		.badge.bg-warning {
			background: linear-gradient(135deg, var(--warning), #d97706) !important;
		}
		
		.btn-group-sm .btn {
			padding: 0.4rem 0.8rem;
			border-radius: 8px;
		}
		
		.btn-outline-primary {
			border: 2px solid var(--merlaws-gold);
			color: var(--merlaws-gold);
		}
		
		.btn-outline-primary:hover {
			background: var(--merlaws-gold);
			color: white;
		}
		
		.btn-outline-danger {
			border: 2px solid var(--danger);
			color: var(--danger);
		}
		
		.btn-outline-danger:hover {
			background: var(--danger);
			color: white;
		}
		
		.alert {
			border: none;
			border-radius: 12px;
			padding: 1rem 1.25rem;
			margin-bottom: 1.5rem;
			border-left: 4px solid;
		}
		
		.alert-success {
			background: #d1fae5;
			color: #065f46;
			border-color: var(--success);
		}
		
		.alert-danger {
			background: #fee2e2;
			color: #991b1b;
			border-color: var(--danger);
		}
		
		.form-check-input {
			width: 1.25rem;
			height: 1.25rem;
			border: 2px solid #d1d5db;
			cursor: pointer;
		}
		
		.form-check-input:checked {
			background-color: var(--merlaws-gold);
			border-color: var(--merlaws-gold);
		}
		
		.empty-state {
			text-align: center;
			padding: 3rem;
			color: #9ca3af;
		}
		
		.empty-state i {
			font-size: 3rem;
			margin-bottom: 1rem;
			opacity: 0.3;
		}
		
		code {
			background: #f1f5f9;
			padding: 0.2rem 0.5rem;
			border-radius: 6px;
			color: #475569;
			font-size: 0.85rem;
		}
		
		@media (max-width: 768px) {
			.page-header {
				padding: 1.5rem 0;
			}
			
			.stats-mini-grid {
				grid-template-columns: repeat(2, 1fr);
			}
			
			.nav-tabs {
				flex-wrap: nowrap;
				overflow-x: auto;
			}
			
			.nav-tabs .nav-link {
				white-space: nowrap;
			}
		}
	</style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="page-header">
	<div class="container">
		<h1 class="mb-2"><i class="fas fa-file-alt me-3"></i>Content Management</h1>
		<p class="mb-0 opacity-75">Manage website pages, news articles, attorney profiles, and services</p>
	</div>
</div>

<div class="container mb-5">
	<?php if ($errors): ?>
		<div class="alert alert-danger">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo e(implode(' ', $errors)); ?>
		</div>
	<?php elseif ($success): ?>
		<div class="alert alert-success">
			<i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
		</div>
	<?php endif; ?>

	<div class="stats-mini-grid">
		<div class="stat-mini-card">
			<div class="stat-mini-number"><?php echo $stats['services']; ?></div>
			<div class="stat-mini-label">Services</div>
		</div>
		<div class="stat-mini-card">
			<div class="stat-mini-number"><?php echo $stats['active_services']; ?></div>
			<div class="stat-mini-label">Active</div>
		</div>
	</div>

	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link active" href="?tab=services">
				<i class="fas fa-briefcase"></i>Services
			</a>
		</li>
	</ul>

	<?php if (false): ?>
	<div class="row g-4">
		<div class="col-12 col-xl-4">
			<div class="content-card h-100">
				<h5 class="section-title">
					<i class="fas fa-plus-circle"></i>
					<?php echo isset($_GET['edit']) ? 'Edit Page' : 'Create New Page'; ?>
				</h5>
				<form method="post">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="save_page">
					<input type="hidden" name="id" id="page_id">
					
					<div class="mb-3">
						<label class="form-label">Title *</label>
						<input name="title" id="page_title" class="form-control" placeholder="Enter page title" required>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Slug *</label>
						<input name="slug" id="page_slug" class="form-control" placeholder="page-url-slug" required>
						<small class="text-muted">URL-friendly identifier</small>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Content</label>
						<textarea name="content" id="page_content" class="form-control" rows="8" placeholder="Page content in HTML or plain text"></textarea>
					</div>
					
					<div class="form-check mb-4">
						<input class="form-check-input" type="checkbox" name="is_published" id="page_published">
						<label class="form-check-label" for="page_published">
							Publish page (make visible on website)
						</label>
					</div>
					
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-2"></i>Save Page
						</button>
						<button type="button" class="btn btn-outline-secondary" onclick="resetPageForm()">
							<i class="fas fa-times me-2"></i>Clear Form
						</button>
					</div>
				</form>
			</div>
		</div>
		
		<div class="col-12 col-xl-8">
			<div class="table-container">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Title</th>
							<th>Slug</th>
							<th>Status</th>
							<th>Updated</th>
							<th style="width:120px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($pages)): ?>
						<tr>
							<td colspan="5">
								<div class="empty-state">
									<i class="fas fa-file-alt"></i>
									<p class="mb-0">No pages yet. Create your first page!</p>
								</div>
							</td>
						</tr>
						<?php else: ?>
						<?php foreach ($pages as $p): ?>
						<tr>
							<td><strong><?php echo e($p['title']); ?></strong></td>
							<td><code><?php echo e($p['slug']); ?></code></td>
							<td>
								<span class="badge bg-<?php echo $p['is_published']?'success':'secondary'; ?>">
									<?php echo $p['is_published']?'Published':'Draft'; ?>
								</span>
							</td>
							<td><?php echo $p['updated_at']? date('M d, Y', strtotime($p['updated_at'])) : '-'; ?></td>
							<td>
								<div class="btn-group btn-group-sm" role="group">
									<button class="btn btn-outline-primary" onclick='fillPageForm(<?php echo json_encode(['id'=>$p['id'],'title'=>$p['title'],'slug'=>$p['slug'],'content'=>$p['content'],'is_published'=>$p['is_published']]); ?>)' title="Edit">
										<i class="fa fa-pen"></i>
									</button>
									<form method="post" class="d-inline" onsubmit="return confirm('Delete this page? This cannot be undone.')">
										<?php echo csrf_field(); ?>
										<input type="hidden" name="action" value="delete_page">
										<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
										<button class="btn btn-outline-danger" title="Delete">
											<i class="fa fa-trash"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if (false): ?>
	<div class="row g-4">
		<div class="col-12 col-xl-4">
			<div class="content-card h-100">
				<h5 class="section-title">
					<i class="fas fa-plus-circle"></i>
					<?php echo isset($_GET['edit']) ? 'Edit Article' : 'Create News Article'; ?>
				</h5>
				<form method="post">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="save_news">
					<input type="hidden" name="id" id="news_id">
					
					<div class="mb-3">
						<label class="form-label">Title *</label>
						<input name="title" id="news_title" class="form-control" placeholder="Article headline" required>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Publish Date</label>
						<input type="date" name="published_at" id="news_published_at" class="form-control">
						<small class="text-muted">Leave empty for current date</small>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Article Body</label>
						<textarea name="body" id="news_body" class="form-control" rows="10" placeholder="Enter article content..."></textarea>
					</div>
					
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-2"></i>Save Article
						</button>
						<button type="button" class="btn btn-outline-secondary" onclick="resetNewsForm()">
							<i class="fas fa-times me-2"></i>Clear Form
						</button>
					</div>
				</form>
			</div>
		</div>
		
		<div class="col-12 col-xl-8">
			<div class="table-container">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Title</th>
							<th>Published</th>
							<th>Updated</th>
							<th style="width:120px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($news)): ?>
						<tr>
							<td colspan="4">
								<div class="empty-state">
									<i class="fas fa-newspaper"></i>
									<p class="mb-0">No news articles yet. Create your first article!</p>
								</div>
							</td>
						</tr>
						<?php else: ?>
						<?php foreach ($news as $n): ?>
						<tr>
							<td><strong><?php echo e($n['title']); ?></strong></td>
							<td><?php echo $n['published_at']? date('M d, Y', strtotime($n['published_at'])) : '—'; ?></td>
							<td><?php echo $n['updated_at']? date('M d, Y', strtotime($n['updated_at'])) : '—'; ?></td>
							<td>
								<div class="btn-group btn-group-sm" role="group">
									<button class="btn btn-outline-primary" onclick='fillNewsForm(<?php echo json_encode(['id'=>$n['id'],'title'=>$n['title'],'published_at'=>$n['published_at'],'body'=>$n['body']]); ?>)' title="Edit">
										<i class="fa fa-pen"></i>
									</button>
									<form method="post" class="d-inline" onsubmit="return confirm('Delete this article?')">
										<?php echo csrf_field(); ?>
										<input type="hidden" name="action" value="delete_news">
										<input type="hidden" name="id" value="<?php echo (int)$n['id']; ?>">
										<button class="btn btn-outline-danger" title="Delete">
											<i class="fa fa-trash"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if (false): ?>
	<div class="row g-4">
		<div class="col-12 col-xl-4">
			<div class="content-card h-100">
				<h5 class="section-title">
					<i class="fas fa-plus-circle"></i>
					<?php echo isset($_GET['edit']) ? 'Edit Attorney' : 'Add Attorney Profile'; ?>
				</h5>
				<form method="post">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="save_attorney">
					<input type="hidden" name="id" id="attorney_id">
					
					<div class="mb-3">
						<label class="form-label">Full Name *</label>
						<input name="name" id="attorney_name" class="form-control" placeholder="Attorney name" required>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Email Address</label>
						<input type="email" name="email" id="attorney_email" class="form-control" placeholder="email@merlaws.com">
					</div>
					
					<div class="mb-3">
						<label class="form-label">Professional Title</label>
						<input name="title" id="attorney_title" class="form-control" placeholder="e.g., Senior Partner, Associate Attorney">
					</div>
					
					<div class="mb-3">
						<label class="form-label">Biography</label>
						<textarea name="bio" id="attorney_bio" class="form-control" rows="8" placeholder="Professional background and qualifications..."></textarea>
					</div>
					
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-2"></i>Save Profile
						</button>
						<button type="button" class="btn btn-outline-secondary" onclick="resetAttorneyForm()">
							<i class="fas fa-times me-2"></i>Clear Form
						</button>
					</div>
				</form>
			</div>
		</div>
		
		<div class="col-12 col-xl-8">
			<div class="table-container">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Title</th>
							<th style="width:120px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($attorneyProfiles)): ?>
						<tr>
							<td colspan="4">
								<div class="empty-state">
									<i class="fas fa-user-tie"></i>
									<p class="mb-0">No attorney profiles yet. Add your first attorney!</p>
								</div>
							</td>
						</tr>
						<?php else: ?>
						<?php foreach ($attorneyProfiles as $ap): ?>
						<tr>
							<td><strong><?php echo e($ap['name'] ?? ''); ?></strong></td>
							<td><?php echo e($ap['email'] ?? ''); ?></td>
							<td><?php echo e($ap['title'] ?? ''); ?></td>
							<td>
								<div class="btn-group btn-group-sm" role="group">
									<button class="btn btn-outline-primary" onclick='fillAttorneyForm(<?php echo json_encode(['id'=>$ap['id'],'name'=>$ap['name'],'email'=>$ap['email'],'title'=>$ap['title'],'bio'=>$ap['bio']]); ?>)' title="Edit">
										<i class="fa fa-pen"></i>
									</button>
									<form method="post" class="d-inline" onsubmit="return confirm('Delete this attorney profile?')">
										<?php echo csrf_field(); ?>
										<input type="hidden" name="action" value="delete_attorney">
										<input type="hidden" name="id" value="<?php echo (int)$ap['id']; ?>">
										<button class="btn btn-outline-danger" title="Delete">
											<i class="fa fa-trash"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($tab === 'services'): ?>
	<div class="row g-4">
		<div class="col-12 col-xl-4">
			<div class="content-card h-100">
				<h5 class="section-title">
					<i class="fas fa-plus-circle"></i>
					<?php echo isset($_GET['edit']) ? 'Edit Service' : 'Add New Service'; ?>
				</h5>
				<form method="post">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="action" value="save_service">
					<input type="hidden" name="id" id="service_id">
					
					<div class="mb-3">
						<label class="form-label">Service Name *</label>
						<input name="name" id="service_name" class="form-control" placeholder="Service title" required>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Category</label>
						<input name="category" id="service_category" class="form-control" value="General" placeholder="e.g., Medical Malpractice, Personal Injury">
					</div>
					
					<div class="mb-3">
						<label class="form-label">Description</label>
						<textarea name="description" id="service_description" class="form-control" rows="8" placeholder="Detailed service description..."></textarea>
					</div>
					
					<div class="form-check mb-4">
						<input class="form-check-input" type="checkbox" name="is_active" id="service_active" checked>
						<label class="form-check-label" for="service_active">
							Active (display on website)
						</label>
					</div>
					
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-2"></i>Save Service
						</button>
						<button type="button" class="btn btn-outline-secondary" onclick="resetServiceForm()">
							<i class="fas fa-times me-2"></i>Clear Form
						</button>
					</div>
				</form>
			</div>
		</div>
		
		<div class="col-12 col-xl-8">
			<div class="table-container">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Service Name</th>
							<th>Category</th>
							<th>Status</th>
							<th style="width:120px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($serviceList)): ?>
						<tr>
							<td colspan="4">
								<div class="empty-state">
									<i class="fas fa-briefcase"></i>
									<p class="mb-0">No services yet. Add your first service!</p>
								</div>
							</td>
						</tr>
						<?php else: ?>
						<?php foreach ($serviceList as $s): ?>
						<tr>
							<td><strong><?php echo e($s['name']); ?></strong></td>
							<td><?php echo e($s['category']); ?></td>
							<td>
								<span class="badge bg-<?php echo $s['is_active']?'success':'secondary'; ?>">
									<?php echo $s['is_active']?'Active':'Inactive'; ?>
								</span>
							</td>
							<td>
								<div class="btn-group btn-group-sm" role="group">
									<button class="btn btn-outline-primary" onclick='fillServiceForm(<?php echo json_encode(['id'=>$s['id'],'name'=>$s['name'],'category'=>$s['category'],'description'=>$s['description'],'is_active'=>$s['is_active']]); ?>)' title="Edit">
										<i class="fa fa-pen"></i>
									</button>
									<form method="post" class="d-inline" onsubmit="return confirm('Delete this service?')">
										<?php echo csrf_field(); ?>
										<input type="hidden" name="action" value="delete_service">
										<input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
										<button class="btn btn-outline-danger" title="Delete">
											<i class="fa fa-trash"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fillPageForm(p){
	document.getElementById('page_id').value = p.id || '';
	document.getElementById('page_title').value = p.title || '';
	document.getElementById('page_slug').value = p.slug || '';
	document.getElementById('page_content').value = p.content || '';
	document.getElementById('page_published').checked = !!p.is_published;
	window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetPageForm(){
	document.getElementById('page_id').value = '';
	document.getElementById('page_title').value = '';
	document.getElementById('page_slug').value = '';
	document.getElementById('page_content').value = '';
	document.getElementById('page_published').checked = false;
}

function fillNewsForm(n){
	document.getElementById('news_id').value = n.id || '';
	document.getElementById('news_title').value = n.title || '';
	document.getElementById('news_published_at').value = (n.published_at||'').substring(0,10);
	document.getElementById('news_body').value = n.body || '';
	window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetNewsForm(){
	document.getElementById('news_id').value = '';
	document.getElementById('news_title').value = '';
	document.getElementById('news_published_at').value = '';
	document.getElementById('news_body').value = '';
}

function fillAttorneyForm(a){
	document.getElementById('attorney_id').value = a.id || '';
	document.getElementById('attorney_name').value = a.name || '';
	document.getElementById('attorney_email').value = a.email || '';
	document.getElementById('attorney_title').value = a.title || '';
	document.getElementById('attorney_bio').value = a.bio || '';
	window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetAttorneyForm(){
	document.getElementById('attorney_id').value = '';
	document.getElementById('attorney_name').value = '';
	document.getElementById('attorney_email').value = '';
	document.getElementById('attorney_title').value = '';
	document.getElementById('attorney_bio').value = '';
}

function fillServiceForm(s){
	document.getElementById('service_id').value = s.id || '';
	document.getElementById('service_name').value = s.name || '';
	document.getElementById('service_category').value = s.category || '';
	document.getElementById('service_description').value = s.description || '';
	document.getElementById('service_active').checked = !!s.is_active;
	window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetServiceForm(){
	document.getElementById('service_id').value = '';
	document.getElementById('service_name').value = '';
	document.getElementById('service_category').value = 'General';
	document.getElementById('service_description').value = '';
	document.getElementById('service_active').checked = true;
}

// Auto-generate slug from title
document.getElementById('page_title')?.addEventListener('input', function(e) {
	const slug = e.target.value
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, '-')
		.replace(/^-+|-+$/g, '');
	document.getElementById('page_slug').value = slug;
});

// Fade in animations
document.addEventListener('DOMContentLoaded', function() {
	const cards = document.querySelectorAll('.content-card, .table-container, .stat-mini-card');
	cards.forEach((card, index) => {
		card.style.opacity = '0';
		card.style.transform = 'translateY(20px)';
		setTimeout(() => {
			card.style.transition = 'all 0.5s ease';
			card.style.opacity = '1';
			card.style.transform = 'translateY(0)';
		}, index * 100);
	});
});
</script>
<script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>