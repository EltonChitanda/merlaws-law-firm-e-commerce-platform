<?php
require __DIR__ . '/../config.php';

require_login();

$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($docId <= 0) {
	header('HTTP/1.1 400 Bad Request');
	echo 'Invalid document ID';
	exit;
}

$pdo = db();
$stmt = $pdo->prepare("SELECT d.*, c.user_id FROM case_documents d JOIN cases c ON d.case_id = c.id WHERE d.id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch();

if (!$doc) {
	header('HTTP/1.1 404 Not Found');
	echo 'Document not found';
	exit;
}

if (!is_admin() && (int)$doc['user_id'] !== get_user_id()) {
	header('HTTP/1.1 403 Forbidden');
	echo 'Access denied';
	exit;
}

$storedPath = $doc['file_path'];
$path = is_file($storedPath) ? $storedPath : __DIR__ . '/../../uploads/' . ltrim($storedPath, '/');

if (!is_file($path)) {
	header('HTTP/1.1 404 Not Found');
	echo 'File missing on server';
	exit;
}

$mime = $doc['mime_type'] ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($doc['original_filename']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit; 