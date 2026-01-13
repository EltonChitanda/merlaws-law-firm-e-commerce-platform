<?php
// app/documents/view.php - Document Preview/View
require __DIR__ . '/../config.php';
require_login();

$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($docId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid document ID');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT d.*, c.user_id, c.title as case_title FROM case_documents d JOIN cases c ON d.case_id = c.id WHERE d.id = ?");
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

$mime = $doc['mime_type'] ?: 'application/octet-stream';
$isPdf = stripos($mime, 'pdf') !== false;
$isImage = stripos($mime, 'image') !== false;

// If it's a PDF or image, show it inline with a nice interface, otherwise download
if ($isPdf || $isImage) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($doc['original_filename']) ?> | Med Attorneys</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                background: #f8f9fa;
                padding: 1rem;
            }
            .viewer-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 25px rgba(0,0,0,0.1);
                padding: 1.5rem;
                max-width: 1400px;
                margin: 0 auto;
            }
            .viewer-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e9ecef;
            }
            .viewer-content {
                text-align: center;
                min-height: 500px;
            }
            .viewer-content img {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .viewer-content iframe {
                width: 100%;
                min-height: 600px;
                border: 1px solid #dee2e6;
                border-radius: 8px;
            }
            .btn-download {
                background: linear-gradient(135deg, #AC132A, #8a0f22);
                color: white;
                border: none;
            }
            .btn-download:hover {
                background: linear-gradient(135deg, #8a0f22, #6b0f1a);
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="viewer-container">
            <div class="viewer-header">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-file-alt me-2"></i>
                        <?= htmlspecialchars($doc['original_filename']) ?>
                    </h4>
                    <small class="text-muted">
                        Case: <?= htmlspecialchars($doc['case_title']) ?> • 
                        Uploaded: <?= date('M d, Y g:i A', strtotime($doc['uploaded_at'])) ?>
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <a href="download.php?id=<?= $docId ?>" class="btn btn-download">
                        <i class="fas fa-download me-2"></i>Download
                    </a>
                    <button onclick="window.close()" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
            <div class="viewer-content">
                <?php if ($isPdf): ?>
                    <iframe src="view-pdf.php?id=<?= $docId ?>" style="width: 100%; min-height: 600px; border: 1px solid #dee2e6; border-radius: 8px;">
                        <p>Your browser does not support PDFs. <a href="download.php?id=<?= $docId ?>">Download the PDF</a> instead.</p>
                    </iframe>
                <?php elseif ($isImage): ?>
                    <img src="view-image.php?id=<?= $docId ?>" alt="<?= htmlspecialchars($doc['original_filename']) ?>" style="max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <?php endif; ?>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
} else {
    // For other file types, force download
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($doc['original_filename']) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

