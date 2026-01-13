<?php
require __DIR__ . '/../config.php';
require_admin();

// Role-based access - only relevant roles can access
$user_role = get_user_role();
$allowed_roles = ['receptionist', 'intake', 'super_admin', 'office_admin', 'partner'];

if (!in_array($user_role, $allowed_roles)) {
    redirect('dashboard.php?error=insufficient_permissions');
}

$pdo = db();
$user_id = get_user_id();
$errors = [];
$messages = [];

// Handle form submissions
if (is_post()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $submission_id = (int)($_POST['submission_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (in_array($status, ['new', 'contacted', 'resolved', 'archived'])) {
            try {
                $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $submission_id]);
                $messages[] = 'Status updated successfully.';
            } catch (Exception $e) {
                $errors[] = 'Failed to update status.';
            }
        }
    } elseif ($action === 'assign') {
        $submission_id = (int)($_POST['submission_id'] ?? 0);
        $assigned_to = (int)($_POST['assigned_to'] ?? 0);
        
        try {
            $stmt = $pdo->prepare("UPDATE contact_submissions SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$assigned_to ?: null, $submission_id]);
            $messages[] = 'Assignment updated successfully.';
        } catch (Exception $e) {
            $errors[] = 'Failed to update assignment.';
        }
    } elseif ($action === 'add_note') {
        $submission_id = (int)($_POST['submission_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        
        if ($note) {
            try {
                // Get existing notes and append
                $stmt = $pdo->prepare("SELECT notes FROM contact_submissions WHERE id = ?");
                $stmt->execute([$submission_id]);
                $existing = $stmt->fetch();
                $existing_notes = $existing['notes'] ?? '';
                
                $new_note = date('Y-m-d H:i:s') . ' - ' . get_user_name() . ': ' . $note;
                $updated_notes = $existing_notes ? $existing_notes . "\n" . $new_note : $new_note;
                
                $stmt = $pdo->prepare("UPDATE contact_submissions SET notes = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$updated_notes, $submission_id]);
                $messages[] = 'Note added successfully.';
            } catch (Exception $e) {
                $errors[] = 'Failed to add note.';
            }
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where = [];
$params = [];

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($date_from) {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get submissions
try {
    $stmt = $pdo->prepare("
        SELECT cs.*, u.name as assigned_name 
        FROM contact_submissions cs
        LEFT JOIN users u ON cs.assigned_to = u.id
        $where_sql
        ORDER BY cs.created_at DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
} catch (Exception $e) {
    $submissions = [];
    $errors[] = 'Failed to load submissions. Table may not exist yet.';
}

// Get counts by status
$status_counts = ['new' => 0, 'contacted' => 0, 'resolved' => 0, 'archived' => 0];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM contact_submissions GROUP BY status");
    $counts = $stmt->fetchAll();
    foreach ($counts as $count) {
        $status_counts[$count['status']] = (int)$count['count'];
    }
} catch (Exception $e) {
    // Table doesn't exist
}

// Get staff for assignment dropdown
$staff = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role IN ('receptionist', 'intake', 'attorney', 'paralegal', 'super_admin', 'office_admin') AND is_active = 1 ORDER BY name");
    $staff = $stmt->fetchAll();
} catch (Exception $e) {
    // Ignore
}

// Get specific submission if viewing details
$view_submission = null;
if (isset($_GET['id'])) {
    $submission_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("
            SELECT cs.*, u.name as assigned_name 
            FROM contact_submissions cs
            LEFT JOIN users u ON cs.assigned_to = u.id
            WHERE cs.id = ?
        ");
        $stmt->execute([$submission_id]);
        $view_submission = $stmt->fetch();
    } catch (Exception $e) {
        // Ignore
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Submissions | Med Attorneys Admin</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-new { background: #3b82f6; color: white; }
        .status-contacted { background: #f59e0b; color: white; }
        .status-resolved { background: #10b981; color: white; }
        .status-archived { background: #6b7280; color: white; }
        @media (max-width: 768px) {
            .container {
                padding: 1rem 0.75rem;
            }
            h1.h3 {
                font-size: 1.5rem;
            }
            .row.mb-4 {
                margin-bottom: 1.5rem !important;
            }
            .row.mb-4 .col-md-3 {
                margin-bottom: 1rem;
            }
            .card-body {
                padding: 1.25rem;
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
                margin-bottom: 0.5rem;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table {
                font-size: 0.9rem;
            }
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 0.75rem 0.5rem;
            }
            h1.h3 {
                font-size: 1.35rem;
            }
            .card-body {
                padding: 1rem;
            }
            .table {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>
    
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-envelope me-2"></i>Contact Form Submissions
            </h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($messages): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php foreach ($messages as $msg): ?>
                <div><?php echo e($msg); ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Status Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $status_counts['new']; ?></h5>
                        <p class="card-text text-primary mb-0">New</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $status_counts['contacted']; ?></h5>
                        <p class="card-text text-warning mb-0">Contacted</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $status_counts['resolved']; ?></h5>
                        <p class="card-text text-success mb-0">Resolved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $status_counts['archived']; ?></h5>
                        <p class="card-text text-secondary mb-0">Archived</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="contacted" <?php echo $status_filter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                            <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo e($date_from); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo e($date_to); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="contact-submissions.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Submissions List -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No submissions found.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td><?php echo e($sub['first_name'] . ' ' . $sub['last_name']); ?></td>
                                <td><?php echo e($sub['email']); ?></td>
                                <td><?php echo e($sub['phone']); ?></td>
                                <td><?php echo e($sub['subject'] ?: 'No subject'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $sub['status']; ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo e($sub['assigned_name'] ?: 'Unassigned'); ?></td>
                                <td><?php echo date('M d, Y g:i A', strtotime($sub['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $sub['id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?php echo $sub['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Submission Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Name:</strong> <?php echo e($sub['first_name'] . ' ' . $sub['last_name']); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Email:</strong> <a href="mailto:<?php echo e($sub['email']); ?>"><?php echo e($sub['email']); ?></a>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Phone:</strong> <a href="tel:<?php echo e($sub['phone']); ?>"><?php echo e($sub['phone']); ?></a>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Date:</strong> <?php echo date('M d, Y g:i A', strtotime($sub['created_at'])); ?>
                                                </div>
                                            </div>
                                            <?php if ($sub['subject']): ?>
                                            <div class="mb-3">
                                                <strong>Subject:</strong> <?php echo e($sub['subject']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <div class="mb-3">
                                                <strong>Message:</strong>
                                                <div class="border p-3 mt-2" style="background: #f8f9fa; border-radius: 8px;">
                                                    <?php echo nl2br(e($sub['message'])); ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($sub['notes']): ?>
                                            <div class="mb-3">
                                                <strong>Internal Notes:</strong>
                                                <div class="border p-3 mt-2" style="background: #fff3cd; border-radius: 8px; white-space: pre-wrap;"><?php echo e($sub['notes']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <hr>
                                            
                                            <!-- Actions -->
                                            <form method="post" class="mb-3">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Update Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="new" <?php echo $sub['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                            <option value="contacted" <?php echo $sub['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                            <option value="resolved" <?php echo $sub['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                            <option value="archived" <?php echo $sub['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                            <form method="post" class="mb-3">
                                                <input type="hidden" name="action" value="assign">
                                                <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Assign To</label>
                                                        <select name="assigned_to" class="form-select">
                                                            <option value="">Unassigned</option>
                                                            <?php foreach ($staff as $s): ?>
                                                            <option value="<?php echo $s['id']; ?>" <?php echo $sub['assigned_to'] == $s['id'] ? 'selected' : ''; ?>>
                                                                <?php echo e($s['name']); ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="submit" class="btn btn-secondary w-100">Assign</button>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                            <form method="post">
                                                <input type="hidden" name="action" value="add_note">
                                                <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                                <div class="mb-2">
                                                    <label class="form-label">Add Internal Note</label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="Add a note..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-outline-secondary">Add Note</button>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

