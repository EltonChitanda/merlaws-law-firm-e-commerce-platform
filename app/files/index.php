<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user_id = get_user_id();

// Load user's cases
$stmt = $pdo->prepare('SELECT id, title FROM cases WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

// Filters
$case_id = (int)($_GET['case_id'] ?? 0);
$search = trim((string)($_GET['q'] ?? ''));
$type = trim((string)($_GET['type'] ?? ''));
$sort = (string)($_GET['sort'] ?? 'uploaded_desc');

// Build query
$sql = "SELECT d.*, c.title AS case_title FROM case_documents d JOIN cases c ON d.case_id = c.id WHERE c.user_id = ?";
$params = [$user_id];

if ($case_id > 0) { $sql .= ' AND d.case_id = ?'; $params[] = $case_id; }
if ($type !== '') { $sql .= ' AND d.document_type = ?'; $params[] = $type; }
if ($search !== '') { $sql .= ' AND (d.original_filename LIKE ? OR d.description LIKE ?)'; $like = "%$search%"; $params[] = $like; $params[] = $like; }

switch ($sort) {
	case 'name_asc': $sql .= ' ORDER BY d.original_filename ASC'; break;
	case 'name_desc': $sql .= ' ORDER BY d.original_filename DESC'; break;
	case 'size_desc': $sql .= ' ORDER BY d.file_size DESC'; break;
	case 'size_asc': $sql .= ' ORDER BY d.file_size ASC'; break;
	default: $sql .= ' ORDER BY d.uploaded_at DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Files | Med Attorneys</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) { echo file_get_contents($headerPath); }
?>
<div class="container my-4">
	<h1 class="h3">File Browser</h1>
	<div class="card mb-3">
		<div class="card-body">
			<form class="row g-3" method="get">
				<div class="col-12 col-md-3">
					<label class="form-label">Case</label>
					<select name="case_id" class="form-select" onchange="this.form.submit()">
						<option value="0">All cases</option>
						<?php foreach ($cases as $c): ?>
						<option value="<?php echo (int)$c['id']; ?>" <?php echo $case_id===(int)$c['id']?'selected':''; ?>><?php echo e($c['title']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-12 col-md-3">
					<label class="form-label">Type</label>
					<input name="type" class="form-control" value="<?php echo e($type); ?>" placeholder="e.g. affidavit">
				</div>
				<div class="col-12 col-md-4">
					<label class="form-label">Search</label>
					<input name="q" class="form-control" value="<?php echo e($search); ?>" placeholder="Filename or description">
				</div>
				<div class="col-12 col-md-2">
					<label class="form-label">Sort</label>
					<select name="sort" class="form-select" onchange="this.form.submit()">
						<option value="uploaded_desc" <?php echo $sort==='uploaded_desc'?'selected':''; ?>>Newest</option>
						<option value="name_asc" <?php echo $sort==='name_asc'?'selected':''; ?>>Name A-Z</option>
						<option value="name_desc" <?php echo $sort==='name_desc'?'selected':''; ?>>Name Z-A</option>
						<option value="size_desc" <?php echo $sort==='size_desc'?'selected':''; ?>>Size ↓</option>
						<option value="size_asc" <?php echo $sort==='size_asc'?'selected':''; ?>>Size ↑</option>
					</select>
				</div>
				<div class="col-12 d-flex justify-content-end">
					<button class="btn btn-outline-primary">Apply</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th>File</th>
						<th>Type</th>
						<th>Case</th>
						<th>Size</th>
						<th>Uploaded</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($documents as $d): ?>
					<tr>
						<td><strong><?php echo e($d['original_filename']); ?></strong></td>
						<td><?php echo e($d['document_type'] ?? ''); ?></td>
						<td><?php echo e($d['case_title']); ?></td>
						<td><?php echo number_format((int)$d['file_size']); ?> bytes</td>
						<td><?php echo e($d['uploaded_at']); ?></td>
						<td>
							<a href="../documents/download.php?id=<?php echo (int)$d['id']; ?>" class="btn btn-sm btn-outline-primary">Download</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php 
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($footerPath)) { echo file_get_contents($footerPath); }
?>
<script src="../assets/js/mobile-responsive.js"></script>
</body>
</html> 