<?php
// app/documents/view-image.php - Image Viewer
require __DIR__ . '/../config.php';
require_login();

$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($docId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid document ID');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT d.*, c.user_id FROM case_documents d JOIN cases c ON d.case_id = c.id WHERE d.id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch();

if (!$doc) {
    header('HTTP/1.1 404 Not Found');
    die('Document not found');
}

if (!is_admin() && (int)$doc['user_id'] !== get_user_id()) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

$storedPath = $doc['file_path'];
$path = is_file($storedPath) ? $storedPath : __DIR__ . '/../../uploads/' . ltrim($storedPath, '/');

if (!is_file($path)) {
    header('HTTP/1.1 404 Not Found');
    die('File missing on server');
}

$mime = $doc['mime_type'] ?: 'image/jpeg';
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($doc['original_filename']) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;

