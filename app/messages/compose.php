<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user_id = get_user_id();

// Cases to message within
$stmt = $pdo->prepare('SELECT id, title FROM cases WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$errors = [];
$success = '';

if (is_post()) {
	if (!csrf_validate()) {
		$errors[] = 'Invalid security token.';
	} else {
		$case_id = (int)($_POST['case_id'] ?? 0);
		$subject = trim((string)($_POST['subject'] ?? ''));
		$body = trim((string)($_POST['body'] ?? ''));
		$priority = (string)($_POST['priority'] ?? 'normal'); // low, normal, high, urgent
		$template = (string)($_POST['template'] ?? '');
		if ($body === '' && $template !== '') { $body = $template; }
		if ($case_id <= 0 || $subject === '' || $body === '') { $errors[] = 'Case, subject and message are required.'; }
		
		if (!$errors) {
			try {
				// Ensure thread exists or create
				$stmt = $pdo->prepare('SELECT id FROM message_threads WHERE case_id = ? AND subject = ? LIMIT 1');
				$stmt->execute([$case_id, $subject]);
				$thread = $stmt->fetch();
				$thread_id = 0;
				if ($thread) {
					$thread_id = (int)$thread['id'];
				} else {
					$stmt = $pdo->prepare('INSERT INTO message_threads (case_id, subject, created_at) VALUES (?, ?, NOW())');
					$stmt->execute([$case_id, $subject]);
					$thread_id = (int)$pdo->lastInsertId();
				}
				// Insert message (write to both body and message columns for compatibility)
				$stmt = $pdo->prepare('INSERT INTO messages (thread_id, sender_id, body, message, priority, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
				$stmt->execute([$thread_id, $user_id, $body, $body, $priority]);
				$message_id = (int)$pdo->lastInsertId();
				
				// Optional single attachment
				if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
					$att = $_FILES['attachment'];
					$ext = strtolower(pathinfo($att['name'], PATHINFO_EXTENSION));
					if (in_array($ext, UPLOAD_ALLOWED_TYPES, true) && $att['size'] <= UPLOAD_MAX_SIZE) {
						$dir = __DIR__ . '/../../uploads/messages/';
						if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
						$fname = uniqid() . '_' . time() . '.' . $ext;
						$path = $dir . $fname;
						if (move_uploaded_file($att['tmp_name'], $path)) {
							$stmt = $pdo->prepare('INSERT INTO message_attachments (message_id, filename, original_filename, file_size, mime_type, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())');
							$stmt->execute([$message_id, $fname, $att['name'], $att['size'], $att['type']]);
						}
					}
				}
				$success = 'Message sent.';
			} catch (Throwable $e) {
				$errors[] = 'Failed to send message.';
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Compose Message | Med Attorneys</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../../assets/css/responsive.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		@media (max-width: 768px) {
			.container {
				padding: 1rem 0.75rem;
			}

			.card {
				border-radius: 12px;
			}

			.card-body {
				padding: 1.25rem;
			}

			.form-label {
				font-size: 0.95rem;
				font-weight: 600;
			}

			.form-control,
			.form-select {
				font-size: 16px;
				padding: 12px 16px;
				min-height: 48px;
			}

			.btn {
				width: 100%;
				min-height: 48px;
				font-size: 16px;
				padding: 12px 20px;
			}

			.alert {
				padding: 1rem;
				font-size: 0.95rem;
			}

			h1 {
				font-size: 1.5rem;
			}
		}

		@media (max-width: 480px) {
			.container {
				padding: 0.75rem 0.5rem;
			}

			.card-body {
				padding: 1rem;
			}

			h1 {
				font-size: 1.35rem;
			}
		}
	</style>
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) { echo file_get_contents($headerPath); }
?>
<div class="container my-4" style="max-width: 900px;">
	<h1 class="h3">Compose Message</h1>
	<?php if ($errors): ?><div class="alert alert-danger"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
	<div class="card">
		<div class="card-body">
			<form method="post" enctype="multipart/form-data">
				<?php echo csrf_field(); ?>
				<div class="row g-3">
					<div class="col-12 col-md-6">
						<label class="form-label">Case</label>
						<select name="case_id" class="form-select" required>
							<option value="">Select case</option>
							<?php foreach ($cases as $c): ?>
							<option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-12 col-md-6">
						<label class="form-label">Priority</label>
						<select name="priority" class="form-select">
							<option value="normal">Normal</option>
							<option value="high">High</option>
							<option value="urgent">Urgent</option>
							<option value="low">Low</option>
						</select>
					</div>
					<div class="col-12">
						<label class="form-label">Subject</label>
						<input name="subject" class="form-control" required>
					</div>
					<div class="col-12">
						<label class="form-label">Message</label>
						<textarea name="body" class="form-control" rows="6" placeholder="Write your message..."></textarea>
					</div>
					<div class="col-12 col-md-6">
						<label class="form-label">Attachment</label>
						<input type="file" name="attachment" class="form-control">
					</div>
					<div class="col-12 col-md-6">
						<label class="form-label">Template</label>
						<select name="template" class="form-select">
							<option value="">None</option>
							<option value="Please provide the requested documents at your earliest convenience.">Request documents</option>
							<option value="This is a reminder about your upcoming appointment.">Appointment reminder</option>
							<option value="We have updated your case status. Please log in to view details.">Status update</option>
						</select>
					</div>
					<div class="col-12 d-flex justify-content-end">
						<button class="btn btn-primary">Send</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php 
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($headerPath)) { echo file_get_contents($footerPath); }
?>
<script src="../assets/js/mobile-responsive.js"></script>
</body>
</html> 