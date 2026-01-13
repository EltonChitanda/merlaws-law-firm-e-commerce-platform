<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('user:update');

$pdo = db();

$errors = [];
$success = '';

// Handle POST actions
if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        try {
            switch ($action) {
                // Permission-aware actions
                case 'create_user': {
                    require_permission('user:create');
                    $name = trim((string)($_POST['name'] ?? ''));
                    $email = trim((string)($_POST['email'] ?? ''));
                    $role = trim((string)($_POST['role'] ?? 'client'));
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    $password = (string)($_POST['password'] ?? '');
                    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                        $errors[] = 'Name, valid email and password are required.';
                        break;
                    }
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([$name, $email, $password_hash, $role, $is_active]);
                    $success = 'User created successfully.';
                    break;
                }
                case 'update_user': {
                    require_permission('user:update');
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $name = trim((string)($_POST['name'] ?? ''));
                    $email = trim((string)($_POST['email'] ?? ''));
                    $role = trim((string)($_POST['role'] ?? 'client'));
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    if ($user_id <= 0 || $name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'User ID, name and valid email are required.';
                        break;
                    }
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $role, $is_active, $user_id]);
                    $success = 'User updated successfully.';
                    break;
                }
                case 'toggle_status': {
                    require_permission('user:update');
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $new_status = (int)($_POST['new_status'] ?? 0);
                    if ($user_id <= 0) { $errors[] = 'Invalid user.'; break; }
                    $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
                    $stmt->execute([$new_status, $user_id]);
                    $success = $new_status ? 'User activated.' : 'User deactivated.';
                    break;
                }
                case 'change_role': {
                    require_permission('role:assign');
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $new_role = trim((string)($_POST['role'] ?? 'client'));
                    if ($user_id <= 0) { $errors[] = 'Invalid user.'; break; }
                    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                    $stmt->execute([$new_role, $user_id]);
                    $success = 'Role updated.';
                    break;
                }
                case 'reset_password': {
                    require_permission('user:update');
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $new_password = (string)($_POST['new_password'] ?? '');
                    if ($user_id <= 0 || $new_password === '') { $errors[] = 'Invalid user or password.'; break; }
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $stmt->execute([$password_hash, $user_id]);
                    $success = 'Password reset successfully.';
                    break;
                }
                default:
                    $errors[] = 'Unknown action.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Operation failed. Please try again.';
        }
    }
}

// Filters
$filter_role = trim((string)($_GET['role'] ?? ''));
$filter_status = trim((string)($_GET['status'] ?? ''));
$filter_start = trim((string)($_GET['start'] ?? ''));
$filter_end = trim((string)($_GET['end'] ?? ''));
$filter_q = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT u.*, COUNT(DISTINCT c.id) AS case_count, COUNT(DISTINCT cd.id) AS document_count
    FROM users u
    LEFT JOIN cases c ON u.id = c.user_id
    LEFT JOIN case_documents cd ON c.id = cd.case_id
    WHERE 1=1";
$params = [];

if ($filter_role !== '') { $sql .= " AND u.role = ?"; $params[] = $filter_role; }
if ($filter_status !== '') { $sql .= " AND u.is_active = ?"; $params[] = $filter_status === 'active' ? 1 : 0; }
if ($filter_start !== '' && $filter_end !== '') { $sql .= " AND DATE(u.created_at) BETWEEN ? AND ?"; $params[] = $filter_start; $params[] = $filter_end; }
if ($filter_q !== '') { $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)"; $like = "%$filter_q%"; $params[] = $like; $params[] = $like; }

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$roles = ['client','super_admin','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | Med Attorneys Admin</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --merlaws-dark: #0d1117;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --neutral-gray: #6b7280;
            --light-bg: #f8fafc;
            --card-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }
        
        body {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-dark) 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--card-shadow);
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
        
        .page-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.02em;
        }
        
        .page-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        /* Filter Section */
        .filter-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .filter-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .filter-title i {
            color: var(--merlaws-gold);
        }
        
        /* Action Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border: none;
            color: var(--merlaws-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(201, 169, 110, 0.3);
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #d4af37, var(--merlaws-gold));
            color: var(--merlaws-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(201, 169, 110, 0.4);
        }
        
        .btn-outline-custom {
            border: 2px solid var(--merlaws-gold);
            color: var(--merlaws-gold);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background: var(--merlaws-gold);
            color: white;
            transform: translateY(-1px);
        }
        
        /* Table Styling */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-dark));
            color: white;
            padding: 1.5rem 2rem;
        }
        
        .table-header h4 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .table-header i {
            color: var(--merlaws-gold);
        }
        
        .table-modern {
            margin: 0;
            background: white;
        }
        
        .table-modern th {
            background: #f8fafc;
            color: var(--merlaws-primary);
            font-weight: 700;
            padding: 1.25rem;
            border: none;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-modern td {
            padding: 1.5rem 1.25rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
        }
        
        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            transform: scale(1.01);
        }
        
        /* User Info Cell */
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .user-details h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 700;
            color: var(--merlaws-primary);
        }
        
        .user-details small {
            color: var(--neutral-gray);
        }
        
        /* Role Badges */
        .role-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .role-badge.admin { background: linear-gradient(135deg, var(--danger-red), #dc2626); color: white; }
        .role-badge.super_admin { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .role-badge.attorney { background: linear-gradient(135deg, var(--merlaws-gold), #d4af37); color: var(--merlaws-dark); }
        .role-badge.client { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
        .role-badge { background: linear-gradient(135deg, var(--neutral-gray), #4b5563); color: white; }
        
        /* Status Badges */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-badge.active {
            background: linear-gradient(135deg, var(--success-green), #059669);
            color: white;
        }
        
        .status-badge.inactive {
            background: linear-gradient(135deg, var(--neutral-gray), #4b5563);
            color: white;
        }
        
        /* Action Buttons */
        .action-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .action-btn.edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .action-btn.toggle {
            background: linear-gradient(135deg, var(--warning-orange), #d97706);
            color: white;
        }
        
        .action-btn.delete {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        /* Modals */
        .modal-content-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .modal-header-custom {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-dark));
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .modal-title-custom {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modal-title-custom i {
            color: var(--merlaws-gold);
        }
        
        .modal-body-custom {
            padding: 2rem;
        }
        
        .form-label-custom {
            font-weight: 600;
            color: var(--merlaws-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-control-custom {
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 0.2rem rgba(201, 169, 110, 0.25);
        }
        
        /* Alert Styling */
        .alert-custom {
            border-radius: 12px;
            padding: 1rem 1.5rem;
            border: none;
            margin-bottom: 2rem;
        }
        
        .alert-success-custom {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            color: var(--success-green);
            border-left: 4px solid var(--success-green);
        }
        
        .alert-danger-custom {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .content-card,
            .filter-card {
                padding: 1.5rem;
            }
            
            .table-modern th,
            .table-modern td {
                padding: 1rem 0.75rem;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .action-group {
                flex-wrap: wrap;
            }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Comprehensive user administration and role management</p>
        </div>
    </div>

    <div class="container">
        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Manage Users</h4>
                <small class="text-muted">Total users: <?php echo count($users); ?></small>
            </div>
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-2"></i>Create New User
            </button>
        </div>

        <!-- Alert Messages -->
        <?php if ($errors): ?>
            <div class="alert alert-danger-custom alert-custom">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo e(implode(' ', $errors)); ?>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success-custom alert-custom">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <!-- Filter Card -->
        <div class="filter-card">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter & Search Users
            </div>
            <form class="row g-3" method="get">
                <div class="col-md-2">
                    <label class="form-label-custom">Role</label>
                    <select name="role" class="form-select form-control-custom">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo e($r); ?>" <?php echo $filter_role === $r ? 'selected' : ''; ?>><?php echo e(ucfirst(str_replace('_', ' ', $r))); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-select form-control-custom">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filter_status==='active'?'selected':''; ?>>Active</option>
                        <option value="inactive" <?php echo $filter_status==='inactive'?'selected':''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">From Date</label>
                    <input type="date" name="start" value="<?php echo e($filter_start); ?>" class="form-control form-control-custom">
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">To Date</label>
                    <input type="date" name="end" value="<?php echo e($filter_end); ?>" class="form-control form-control-custom">
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom">Search</label>
                    <input type="text" name="q" value="<?php echo e($filter_q); ?>" class="form-control form-control-custom" placeholder="Name or email...">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-custom me-2" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="/app/admin/users.php" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-users"></i>User Directory</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>User Information</th>
                            <th>Role</th>
                            <th>Cases</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                    </div>
                                    <div class="user-details">
                                        <h6><?php echo e($u['name']); ?></h6>
                                        <small><?php echo e($u['email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge <?php echo $u['role']; ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $u['role']))); ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo (int)$u['case_count']; ?></span>
                                <small class="text-muted d-block">cases</small>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo (int)$u['document_count']; ?></span>
                                <small class="text-muted d-block">documents</small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $u['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo $u['created_at'] ? date('M d, Y', strtotime($u['created_at'])) : '-'; ?></span>
                                <small class="text-muted d-block"><?php echo $u['created_at'] ? date('g:i A', strtotime($u['created_at'])) : ''; ?></small>
                            </td>
                            <td>
                                <div class="action-group">
                                    <button class="action-btn edit" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                            data-user='{"id":<?php echo (int)$u['id']; ?>,"name":"<?php echo e($u['name']); ?>","email":"<?php echo e($u['email']); ?>","role":"<?php echo e($u['role']); ?>","is_active":<?php echo (int)$u['is_active']; ?>}'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="post" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $u['is_active'] ? 0 : 1; ?>">
                                        <button class="action-btn toggle" type="submit" 
                                                onclick="return confirm('<?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?> this user?')">
                                            <i class="fas <?php echo $u['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    
                                    <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                            data-user-id="<?php echo (int)$u['id']; ?>">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-custom">
                <form method="post">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title modal-title-custom">
                            <i class="fas fa-user-plus"></i>
                            Create New User
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body modal-body-custom">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="create_user">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Full Name</label>
                                <input type="text" name="name" class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Password</label>
                                <input type="password" name="password" class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">User Role</label>
                                <select name="role" class="form-select form-control-custom">
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo e($r); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $r))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="createActive" checked>
                                    <label class="form-check-label form-label-custom" for="createActive">
                                        Activate user account immediately
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-custom">
                <form method="post">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title modal-title-custom">
                            <i class="fas fa-user-edit"></i>
                            Edit User Information
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body modal-body-custom">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Full Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Email Address</label>
                                <input type="email" name="email" id="edit_email" class="form-control form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">User Role</label>
                                <select name="role" id="edit_role" class="form-select form-control-custom">
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo e($r); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $r))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                    <label class="form-check-label form-label-custom" for="edit_is_active">
                                        Account is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-content-custom">
                <form method="post" onsubmit="return validateResetPassword()">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title modal-title-custom">
                            <i class="fas fa-key"></i>
                            Reset User Password
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body modal-body-custom">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="user_id" id="reset_user_id">
                        
                        <div class="mb-3">
                            <label class="form-label form-label-custom">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control form-control-custom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-label-custom">Confirm New Password</label>
                            <input type="password" id="confirm_password" class="form-control form-control-custom" required>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            The user will be notified of this password change via email.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle Edit User Modal
        const editUserModal = document.getElementById('editUserModal');
        editUserModal && editUserModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const data = button.getAttribute('data-user');
            if (!data) return;
            try {
                const user = JSON.parse(data);
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_name').value = user.name;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_is_active').checked = !!user.is_active;
            } catch (e) {
                console.error('Error parsing user data:', e);
            }
        });

        // Handle Reset Password Modal
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        resetPasswordModal && resetPasswordModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const userId = button.getAttribute('data-user-id');
            document.getElementById('reset_user_id').value = userId;
            
            // Clear previous values
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('confirm_password').classList.remove('is-invalid');
        });

        // Validate Password Reset Form
        function validateResetPassword() {
            const p1 = document.getElementById('new_password');
            const p2 = document.getElementById('confirm_password');
            if (!p1 || !p2) return true;
            
            if (p1.value !== p2.value) {
                p2.classList.add('is-invalid');
                return false;
            }
            
            if (p1.value.length < 8) {
                p1.classList.add('is-invalid');
                showToast('Password must be at least 8 characters long', 'error');
                return false;
            }
            
            p2.classList.remove('is-invalid');
            p1.classList.remove('is-invalid');
            return true;
        }

        // Real-time password validation
        document.addEventListener('DOMContentLoaded', function() {
            const confirmPassword = document.getElementById('confirm_password');
            const newPassword = document.getElementById('new_password');
            
            if (confirmPassword && newPassword) {
                confirmPassword.addEventListener('input', function() {
                    if (this.value !== newPassword.value) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });

        // Toast notification system
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            
            const bgClass = type === 'success' ? 'bg-success' : 
                           type === 'error' ? 'bg-danger' : 'bg-info';
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white ${bgClass} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 4000
            });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-custom');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
    <script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>