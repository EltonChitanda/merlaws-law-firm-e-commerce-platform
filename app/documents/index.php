<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

require_login();

$pdo = db();
$userId = get_user_id();

// Fetch cases that belong to the logged-in user
$caseStmt = $pdo->prepare("SELECT id, title FROM cases WHERE user_id = ? ORDER BY updated_at DESC");
$caseStmt->execute([$userId]);
$userCases = $caseStmt->fetchAll();

$selectedCaseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : (count($userCases) ? (int)$userCases[0]['id'] : 0);
$searchTerm     = trim((string)($_GET['q'] ?? ''));
$typeFilter     = trim((string)($_GET['type'] ?? ''));

$documents = [];
if ($selectedCaseId) {
    $sql = "SELECT * FROM case_documents WHERE case_id = ?";
    $params = [$selectedCaseId];

    if ($typeFilter !== '') {
        $sql .= " AND document_type = ?";
        $params[] = $typeFilter;
    }

    if ($searchTerm !== '') {
        $sql .= " AND (original_filename LIKE ? OR description LIKE ?)";
        $like = "%$searchTerm%";
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY uploaded_at DESC";
    $docStmt = $pdo->prepare($sql);
    $docStmt->execute($params);
    $documents = $docStmt->fetchAll();
}

$uploadErrors  = $_SESSION['upload_errors'] ?? [];
$uploadSuccess = $_SESSION['upload_success'] ?? '';
unset($_SESSION['upload_errors'], $_SESSION['upload_success']);

$documentCount = count($documents);
$totalSize     = array_sum(array_map(fn($d) => (int)$d['file_size'], $documents));
$totalSizeMb   = $totalSize ? number_format($totalSize / (1024 * 1024), 2) : '0.00';
$recentUpload  = $documentCount ? date('M d, Y', strtotime($documents[0]['uploaded_at'])) : '—';
$uniqueTypes   = array_unique(array_filter(array_map(fn($d) => trim((string)($d['document_type'] ?? '')), $documents)));
$typeCount     = count($uniqueTypes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Center | Med Attorneys</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-secondary: #193256;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border-color: #e2e8f0;
            --muted: #64748b;
            --shadow-soft: 0 18px 45px rgba(15, 23, 42, 0.12);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, rgba(172, 19, 42, 0.08), transparent 45%),
                        linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            color: #0f172a;
        }

        .page-shell {
            max-width: 1200px;
            margin: 0 auto 4rem;
        }

        .hero {
            padding: 3rem 0 2rem;
        }

        .hero h1 {
            font-size: clamp(2.2rem, 3vw, 2.9rem);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .hero p {
            max-width: 640px;
            color: var(--muted);
        }

        .surface {
            background: var(--surface);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .stat-card {
            border-radius: 18px;
            padding: 1.35rem;
            background: var(--surface);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            height: 100%;
        }

        .stat-card small {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            color: var(--muted);
        }

        .stat-card h3 {
            font-size: clamp(1.9rem, 2.5vw, 2.3rem);
            font-weight: 700;
            margin: 0.3rem 0 0.4rem;
        }

        .filter-panel {
            padding: 1.8rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.9), rgba(241, 245, 249, 0.7));
            border-radius: 20px 20px 0 0;
        }

        .filter-panel label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .filter-panel .form-control,
        .filter-panel .form-select {
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 0.65rem 0.85rem;
        }

        .documents-table thead {
            background: var(--surface-soft);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            border-top: none;
        }

        .documents-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background 0.15s ease;
        }

        .documents-table tbody tr:hover {
            background: rgba(15, 23, 42, 0.03);
        }

        .document-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(172, 19, 42, 0.08);
            color: var(--merlaws-primary);
            font-size: 1.35rem;
            margin-right: 14px;
        }

        .document-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.15rem;
        }

        .document-meta {
            font-size: 0.8rem;
            color: var(--muted);
        }

        .action-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.45rem 0.95rem;
            border-radius: 50px;
            font-size: 0.82rem;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #ffffff;
            color: #1e293b;
            transition: all 0.2s ease;
        }

        .action-chip:hover {
            border-color: var(--merlaws-primary);
            color: var(--merlaws-primary);
            box-shadow: 0 10px 25px rgba(172, 19, 42, 0.12);
        }

        .action-chip.primary {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-secondary));
            color: #ffffff;
            border: none;
        }

        .action-chip.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(172, 19, 42, 0.2);
        }

        .upload-card {
            border: 1px dashed rgba(148, 163, 184, 0.65);
            border-radius: 18px;
            padding: 1.75rem;
            background: rgba(248, 250, 252, 0.92);
        }

        #drop-zone {
            border: 2px dashed rgba(148, 163, 184, 0.6);
            border-radius: 16px;
            padding: 2rem;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #drop-zone.drag-over {
            border-color: var(--merlaws-primary);
            background: rgba(172, 19, 42, 0.05);
        }

        #file-list {
            list-style: none;
            padding-left: 0;
            margin-top: 1rem;
        }

        #file-list li {
            background: rgba(241, 245, 249, 0.85);
            padding: 0.55rem 0.9rem;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        #document-preview {
            display: none;
        }

        .preview-frame {
            width: 100%;
            height: 380px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .preview-meta {
            background: rgba(248, 250, 252, 0.9);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.83rem;
        }

        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            border-radius: 18px;
            border: 1px dashed rgba(148, 163, 184, 0.4);
            background: rgba(248, 250, 252, 0.94);
        }

        .empty-state i {
            font-size: 2.8rem;
            color: rgba(148, 163, 184, 0.6);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .page-shell {
                padding: 1rem 0.75rem 2rem;
            }

            .hero {
                padding: 2rem 0 1.5rem;
            }

            .hero h1 {
                font-size: 1.75rem;
            }

            .hero p {
                font-size: 0.95rem;
            }

            .stat-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .surface {
                border-radius: 16px;
                padding: 1.25rem;
            }

            .filter-panel {
                padding: 1.25rem;
            }

            .filter-panel .form-label {
                font-size: 0.9rem;
                font-weight: 600;
            }

            .filter-panel .form-control,
            .filter-panel .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .filter-panel .btn {
                min-height: 48px;
                width: 100%;
            }

            .documents-table {
                font-size: 0.9rem;
            }

            .documents-table th {
                font-size: 0.8rem;
                padding: 0.75rem 0.5rem;
            }

            .documents-table td {
                padding: 1rem 0.5rem;
            }

            .document-title {
                font-size: 0.95rem;
            }

            .document-meta {
                font-size: 0.8rem;
            }

            .action-chip {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                min-height: 44px;
            }

            .upload-card {
                padding: 1.25rem;
            }

            #drop-zone {
                padding: 1.5rem;
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

            .empty-state {
                padding: 2rem 1.5rem;
            }

            .empty-state i {
                font-size: 2.5rem;
            }

            .empty-state h4 {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 480px) {
            .page-shell {
                padding: 0.75rem 0.5rem 1.5rem;
            }

            .hero {
                padding: 1.5rem 0 1rem;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-card h3 {
                font-size: 1.25rem;
            }

            .surface {
                padding: 1rem;
            }

            .filter-panel {
                padding: 1rem;
            }

            .documents-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .documents-table thead {
                display: none;
            }

            .documents-table tbody,
            .documents-table tr,
            .documents-table td {
                display: block;
                width: 100%;
            }

            .documents-table tr {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
            }

            .documents-table td {
                border: none;
                padding: 0.5rem 0;
                text-align: left !important;
            }

            .documents-table td:before {
                content: attr(data-label);
                font-weight: 600;
                display: block;
                margin-bottom: 0.25rem;
                color: #64748b;
                font-size: 0.8rem;
            }

            .action-chip {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.5rem;
            }

            .upload-card {
                padding: 1rem;
            }

            #drop-zone {
                padding: 1.25rem;
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

<div class="page-shell">
    <div class="hero">
        <span class="badge bg-light text-dark mb-3"><i class="fas fa-folder-open me-2"></i>Client Workspace</span>
        <h1>Document Center</h1>
        <p>Browse every file associated with your case, preview uploads before sending them, and access approved documents instantly.</p>
    </div>

    <?php if ($uploadErrors): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars(implode(' ', $uploadErrors)) ?>
        </div>
    <?php elseif ($uploadSuccess): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($uploadSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!$userCases): ?>
        <div class="empty-state surface">
            <i class="fas fa-briefcase"></i>
            <h4 class="fw-semibold mb-2">No cases yet</h4>
            <p class="text-muted mb-3">Create your first case to start uploading and sharing documents securely.</p>
            <a href="../cases/create.php" class="btn btn-primary rounded-pill px-4"><i class="fas fa-plus me-2"></i>Create a Case</a>
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <small><i class="fas fa-file-alt me-1"></i>Total Documents</small>
                    <h3><?= $documentCount ?></h3>
                    <div class="stat-meta">Across the selected case</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <small><i class="fas fa-layer-group me-1"></i>Document Types</small>
                    <h3><?= $typeCount ?></h3>
                    <div class="stat-meta">Categories represented</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <small><i class="fas fa-database me-1"></i>Total Size</small>
                    <h3><?= $totalSizeMb ?> MB</h3>
                    <div class="stat-meta">Combined attachments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <small><i class="fas fa-clock me-1"></i>Last Upload</small>
                    <h3><?= $recentUpload ?></h3>
                    <div class="stat-meta">Most recent submission</div>
                </div>
            </div>
        </div>

        <div class="surface">
            <div class="filter-panel">
                <form class="row g-3 align-items-end" method="get">
                    <div class="col-lg-4">
                        <label for="case_id" class="form-label"><i class="fas fa-briefcase me-2"></i>Case</label>
                        <select class="form-select" id="case_id" name="case_id" onchange="this.form.submit()">
                            <?php foreach ($userCases as $case): ?>
                                <option value="<?= (int)$case['id'] ?>" <?= $selectedCaseId === (int)$case['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($case['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="type" class="form-label"><i class="fas fa-filter me-2"></i>Type</label>
                        <input type="text" class="form-control" id="type" name="type" value="<?= htmlspecialchars($typeFilter) ?>" placeholder="e.g., Affidavit">
                    </div>
                    <div class="col-lg-4">
                        <label for="q" class="form-label"><i class="fas fa-search me-2"></i>Search</label>
                        <input type="text" class="form-control" id="q" name="q" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Filename or description">
                    </div>
                    <div class="col-lg-1 text-lg-end">
                        <button type="submit" class="btn rounded-pill w-100" style="background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-secondary)); color: #fff;">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-4 pb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-semibold mb-0"><i class="fas fa-folder-open me-2 text-primary"></i>Documents</h2>
                    <?php if ($documentCount): ?>
                        <span class="badge rounded-pill bg-light text-dark">Latest: <?= htmlspecialchars($documents[0]['original_filename']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!$documentCount): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-circle-exclamation"></i>
                        <h4 class="fw-semibold">No documents yet</h4>
                        <p class="text-muted mb-3">As soon as you upload a file it will appear here with viewing and download options.</p>
                        <a href="#upload-area" class="btn btn-outline-primary rounded-pill px-4"><i class="fas fa-upload me-2"></i>Upload your first document</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table documents-table align-middle">
                            <thead class="text-uppercase text-muted">
                                <tr>
                                    <th scope="col">Document</th>
                                    <th scope="col" class="text-center">Type</th>
                                    <th scope="col" class="text-center">Uploaded</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="document-icon">
                                                    <i class="fas fa-file"></i>
                                                </div>
                                                <div>
                                                    <div class="document-title"><?= htmlspecialchars($doc['original_filename']) ?></div>
                                                    <div class="document-meta">
                                                        <?= number_format((int)$doc['file_size'] / 1024, 2) ?> KB •
                                                        <?= $doc['description'] ? htmlspecialchars($doc['description']) : 'No description added' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($doc['document_type'])): ?>
                                                <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold px-3 py-2">
                                                    <?= htmlspecialchars($doc['document_type']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="document-meta"><?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="view.php?id=<?= (int)$doc['id'] ?>" target="_blank" class="action-chip">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="download.php?id=<?= (int)$doc['id'] ?>" class="action-chip primary">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-4 pt-0 pb-4 border-top" id="upload-area">
                <div class="mb-4">
                    <h2 class="h5 fw-semibold"><i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload new document</h2>
                    <p class="small text-muted mb-4">Supported formats are PDF and JPEG/PNG images up to 100 MB. You can preview the file before confirming the upload.</p>
                </div>
                <div class="upload-card">
                    <form id="uploadForm" method="post" action="upload.php" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="case_id" value="<?= (int)$selectedCaseId ?>">
                        <div class="mb-3">
                            <div id="drop-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <h6 class="fw-semibold">Drop files here or click to browse</h6>
                                <p class="text-muted small mb-0">PDF, JPEG or PNG • Max size 100 MB</p>
                            </div>
                            <input type="file" id="document" name="document" class="d-none" required accept=".pdf,.jpeg,.jpg,.png">
                            <ul id="file-list" class="mt-3"></ul>

                            <div id="document-preview" class="mt-3">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-4">
                                        <span class="fw-semibold"><i class="fas fa-eye me-2"></i>File preview</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearPreview()"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="card-body p-3">
                                        <div id="preview-content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="document_type" class="form-label fw-semibold">Document type</label>
                                <input type="text" class="form-control" id="document_type" name="document_type" placeholder="e.g. Medical report, Affidavit">
                            </div>
                            <div class="col-md-6">
                                <label for="description" class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Explain the context for the legal team"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex flex-wrap gap-3 align-items-center">
                            <button type="submit" id="uploadBtn" disabled class="btn rounded-pill px-4" style="background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-secondary)); color: #fff;">
                                <i class="fas fa-upload me-2"></i>Upload document
                            </button>
                            <div class="text-muted small">
                                Need help? <a href="../support/contact.php" class="text-decoration-none">Contact support</a>
                            </div>
                        </div>
                    </form>
                </div>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropZone     = document.getElementById('drop-zone');
    const fileInput    = document.getElementById('document');
    const fileList     = document.getElementById('file-list');
    const uploadBtn    = document.getElementById('uploadBtn');
    const previewShell = document.getElementById('document-preview');
    const previewBody  = document.getElementById('preview-content');

    if (!dropZone || !fileInput || !fileList || !uploadBtn) return;

    const showPreview = (file) => {
        if (!previewShell || !previewBody) return;
        previewShell.style.display = 'block';
        previewBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>';

        const reader = new FileReader();
        reader.onload = (e) => {
            const meta = `<div class=\"preview-meta mb-3\"><strong>${file.name}</strong><br><span class=\"text-muted\">${(file.size / (1024 * 1024)).toFixed(2)} MB • ${file.type || 'Unknown type'}</span></div>`;

            if (file.type.startsWith('image/')) {
                previewBody.innerHTML = meta + `<img src="${e.target.result}" class="img-fluid rounded-4 shadow-sm">`;
            } else if (file.type === 'application/pdf') {
                previewBody.innerHTML = meta + `<iframe class="preview-frame" src="${e.target.result}" title="PDF preview"></iframe>`;
            } else {
                previewBody.innerHTML = meta + `<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Preview not available for this format. You can still upload the file and download it later.</div>`;
            }
        };
        reader.readAsDataURL(file);
    };

    const updateFiles = (fileListData) => {
        fileList.innerHTML = '';
        previewShell.style.display = 'none';
        uploadBtn.disabled = true;

        if (fileListData.length === 1) {
            const file = fileListData[0];
            const listItem = document.createElement('li');
            listItem.innerHTML = `<i class="fas fa-file me-2 text-primary"></i>${file.name} <span class=\"text-muted\">${(file.size / (1024 * 1024)).toFixed(2)} MB</span>`;
            fileList.appendChild(listItem);
            uploadBtn.disabled = false;
            showPreview(file);
        }
    };

    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length > 1) {
            alert('Please upload one file at a time.');
            return;
        }
        fileInput.files = e.dataTransfer.files;
        updateFiles(fileInput.files);
    });

    fileInput.addEventListener('change', () => updateFiles(fileInput.files));
});

function clearPreview() {
    const fileInput    = document.getElementById('document');
    const fileList     = document.getElementById('file-list');
    const uploadBtn    = document.getElementById('uploadBtn');
    const previewShell = document.getElementById('document-preview');

    if (fileInput) fileInput.value = '';
    if (fileList) fileList.innerHTML = '';
    if (uploadBtn) uploadBtn.disabled = true;
    if (previewShell) previewShell.style.display = 'none';
}
</script>
</body>
</html>
