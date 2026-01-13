<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

// Gate access
if (!has_permission('settings:manage')) {
    require_permission('settings:manage');
}

$pdo = db();
$errors = [];
$success = '';

// Known roles (align with system roles)
$roles = ['super_admin','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist','manager','admin','client'];

// Role display configurations
$role_configs = [
    'super_admin' => ['icon' => 'fa-user-shield', 'color' => '#ef4444'],
    'office_admin' => ['icon' => 'fa-user-tie', 'color' => '#f59e0b'],
    'partner' => ['icon' => 'fa-user-crown', 'color' => '#c9a96e'],
    'attorney' => ['icon' => 'fa-balance-scale', 'color' => '#3b82f6'],
    'paralegal' => ['icon' => 'fa-file-contract', 'color' => '#8b5cf6'],
    'intake' => ['icon' => 'fa-user-plus', 'color' => '#06b6d4'],
    'case_manager' => ['icon' => 'fa-tasks', 'color' => '#10b981'],
    'billing' => ['icon' => 'fa-file-invoice-dollar', 'color' => '#14b8a6'],
    'doc_specialist' => ['icon' => 'fa-folder-open', 'color' => '#6366f1'],
    'it_admin' => ['icon' => 'fa-server', 'color' => '#64748b'],
    'compliance' => ['icon' => 'fa-shield-alt', 'color' => '#84cc16'],
    'receptionist' => ['icon' => 'fa-phone', 'color' => '#ec4899'],
    'manager' => ['icon' => 'fa-user-cog', 'color' => '#f97316'],
    'admin' => ['icon' => 'fa-user-lock', 'color' => '#dc2626'],
    'client' => ['icon' => 'fa-user', 'color' => '#9ca3af']
];

// Handle actions
if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        try {
            switch ($action) {
                case 'create_permission': {
                    $name = trim((string)($_POST['name'] ?? ''));
                    if ($name === '') { $errors[] = 'Permission name is required.'; break; }
                    $stmt = $pdo->prepare('INSERT INTO permissions (name) VALUES (?)');
                    $stmt->execute([$name]);
                    $success = 'Permission created successfully.';
                    break;
                }
                case 'delete_permission': {
                    $id = (int)($_POST['permission_id'] ?? 0);
                    if ($id <= 0) { $errors[] = 'Invalid permission.'; break; }
                    $pdo->prepare('DELETE FROM role_permissions WHERE permission_id = ?')->execute([$id]);
                    $pdo->prepare('DELETE FROM permissions WHERE id = ?')->execute([$id]);
                    $success = 'Permission deleted successfully.';
                    break;
                }
                case 'assign_permission': {
                    $role = trim((string)($_POST['role'] ?? ''));
                    $permId = (int)($_POST['permission_id'] ?? 0);
                    if (!in_array($role, $roles, true) || $permId <= 0) { $errors[] = 'Invalid role or permission.'; break; }
                    $stmt = $pdo->prepare('INSERT IGNORE INTO role_permissions (role, permission_id) VALUES (?, ?)');
                    $stmt->execute([$role, $permId]);
                    $success = 'Permission assigned successfully.';
                    break;
                }
                case 'revoke_permission': {
                    $role = trim((string)($_POST['role'] ?? ''));
                    $permId = (int)($_POST['permission_id'] ?? 0);
                    if (!in_array($role, $roles, true) || $permId <= 0) { $errors[] = 'Invalid role or permission.'; break; }
                    $stmt = $pdo->prepare('DELETE FROM role_permissions WHERE role = ? AND permission_id = ?');
                    $stmt->execute([$role, $permId]);
                    $success = 'Permission revoked successfully.';
                    break;
                }
                default:
                    $errors[] = 'Unknown action.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Operation failed. Please ensure the RBAC tables exist.';
        }
    }
}

// Fetch permissions
$permissions = [];
try { $permissions = $pdo->query('SELECT id, name FROM permissions ORDER BY name')->fetchAll(); } catch (Throwable $e) {}

// Fetch role mappings
$roleToPermIds = [];
try {
    $rows = $pdo->query('SELECT role, permission_id FROM role_permissions')->fetchAll();
    foreach ($rows as $row) {
        $r = (string)$row['role'];
        $p = (int)$row['permission_id'];
        if (!isset($roleToPermIds[$r])) { $roleToPermIds[$r] = []; }
        $roleToPermIds[$r][$p] = true;
    }
} catch (Throwable $e) {}

// Count permissions per role
$role_perm_counts = [];
foreach ($roles as $role) {
    $role_perm_counts[$role] = isset($roleToPermIds[$role]) ? count($roleToPermIds[$role]) : 0;
}

// Fetch staff members
$staff_members = [];
try {
    $stmt = $pdo->query('SELECT id, name, email, role FROM users WHERE role != "client" ORDER BY name');
    $staff_members = $stmt->fetchAll();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RBAC Management | Med Attorneys Admin</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
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
        
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .content-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
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
        
        .view-toggle {
            display: flex;
            gap: 0.5rem;
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .view-toggle-btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .view-toggle-btn:hover {
            color: var(--merlaws-gold);
        }
        
        .view-toggle-btn.active {
            background: white;
            color: var(--merlaws-gold);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .staff-selector {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .staff-search {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .staff-search:focus {
            outline: none;
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 4px rgba(201, 169, 110, 0.1);
        }
        
        .staff-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
        }
        
        .staff-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-top: 0.5rem;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }
        
        .staff-dropdown.show {
            display: block;
        }
        
        .staff-item {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .staff-item:last-child {
            border-bottom: none;
        }
        
        .staff-item:hover {
            background: #f8fafc;
        }
        
        .staff-avatar {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .staff-details {
            flex: 1;
        }
        
        .staff-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.15rem;
        }
        
        .staff-email {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .staff-role-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }
        
        .selected-staff-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid var(--merlaws-gold);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .selected-staff-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .selected-staff-info {
            flex: 1;
        }
        
        .selected-staff-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .selected-staff-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .clear-selection-btn {
            background: white;
            border: 2px solid #e5e7eb;
            color: #64748b;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .clear-selection-btn:hover {
            border-color: #dc2626;
            color: #dc2626;
        }
        
        .permission-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 2px solid;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .permission-badge.assigned {
            background: linear-gradient(135deg, #10b981, #059669);
            border-color: #10b981;
            color: white;
        }
        
        .permission-badge.assigned:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .permission-badge.unassigned {
            background: white;
            border-color: #e5e7eb;
            color: #6b7280;
        }
        
        .permission-badge.unassigned:hover {
            border-color: var(--merlaws-gold);
            color: var(--merlaws-gold);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 169, 110, 0.2);
        }
        
        .role-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .role-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .role-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .role-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .role-info {
            flex: 1;
        }
        
        .role-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .role-stats {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .permission-item:hover {
            border-color: var(--merlaws-gold);
            background: #fefce8;
        }
        
        .permission-name {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .permission-name i {
            color: var(--merlaws-gold);
        }
        
        .btn-create {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(201, 169, 110, 0.4);
            color: white;
        }
        
        .btn-delete {
            background: white;
            border: 2px solid #fee2e2;
            color: #dc2626;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        
        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
        }
        
        .stats-badge i {
            color: var(--merlaws-gold);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-custom i {
            font-size: 1.5rem;
        }
        
        .alert-custom.alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }
        
        .alert-custom.alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }
        
        .form-control-modern {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-modern:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 4px rgba(201, 169, 110, 0.1);
        }
        
        .permission-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .role-selector-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .role-selector-card:hover {
            border-color: var(--merlaws-gold);
            box-shadow: 0 4px 12px rgba(201, 169, 110, 0.2);
            transform: translateY(-2px);
        }
        
        .role-selector-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .role-selector-info {
            flex: 1;
        }
        
        .role-selector-name {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .role-selector-stats {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .role-selector-arrow {
            color: #9ca3af;
            font-size: 1rem;
        }
        
        @media (max-width: 768px) {
            .permission-grid {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2">Role-Based Access Control</h1>
                    <p class="mb-0 opacity-75">Manage system permissions and role assignments</p>
                </div>
                <div class="stats-badge">
                    <i class="fas fa-shield-alt"></i>
                    <?php echo count($permissions); ?> Permissions
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($errors): ?>
            <div class="alert-custom alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo e(implode(' ', $errors)); ?></div>
            </div>
        <?php elseif ($success): ?>
            <div class="alert-custom alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo e($success); ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column - Permission Management -->
            <div class="col-lg-4">
                <!-- Create Permission Card -->
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-plus-circle"></i>
                        Create Permission
                    </h3>
                    <form method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="create_permission">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Permission Name</label>
                            <input name="name" class="form-control form-control-modern" 
                                   placeholder="e.g. case:export" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Use format: resource:action
                            </div>
                        </div>
                        <button type="submit" class="btn btn-create w-100">
                            <i class="fas fa-plus me-2"></i>Create Permission
                        </button>
                    </form>
                </div>

                <!-- All Permissions Card -->
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-list"></i>
                        All Permissions
                        <span class="badge bg-primary ms-auto"><?php echo count($permissions); ?></span>
                    </h3>
                    
                    <?php if (empty($permissions)): ?>
                        <div class="empty-state">
                            <i class="fas fa-key"></i>
                            <p class="mb-0">No permissions created yet</p>
                            <small>Create your first permission above</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($permissions as $perm): ?>
                        <div class="permission-item">
                            <div class="permission-name">
                                <i class="fas fa-key"></i>
                                <?php echo e($perm['name']); ?>
                            </div>
                            <form method="post" onsubmit="return confirm('Delete this permission? This will remove it from all roles.')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_permission">
                                <input type="hidden" name="permission_id" value="<?php echo (int)$perm['id']; ?>">
                                <button type="submit" class="btn btn-delete btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Role Permission Assignment -->
            <div class="col-lg-8">
                <div class="content-card">
                    <h3 class="section-title">
                        <i class="fas fa-users-cog"></i>
                        Permission Assignment
                    </h3>
                    
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <button class="view-toggle-btn active" data-view="all">
                            <i class="fas fa-th"></i>
                            All Roles
                        </button>
                        <button class="view-toggle-btn" data-view="staff">
                            <i class="fas fa-user"></i>
                            By Staff Member
                        </button>
                    </div>

                    <!-- By Staff Member View -->
                    <div id="staffView" style="display: none;">
                        <!-- Staff Member Grid -->
                        <div class="row g-3 mb-4">
                            <?php foreach ($staff_members as $staff): 
                                $config = $role_configs[$staff['role']] ?? ['icon' => 'fa-user', 'color' => '#6b7280'];
                                $initials = strtoupper(substr($staff['name'], 0, 1));
                                if (strpos($staff['name'], ' ') !== false) {
                                    $parts = explode(' ', $staff['name']);
                                    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
                                }
                                $permCount = $role_perm_counts[$staff['role']] ?? 0;
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="role-selector-card" style="cursor: pointer;" 
                                     onclick="selectStaffMemberAndScroll('<?php echo e($staff['role']); ?>')"
                                     data-role="<?php echo e($staff['role']); ?>"
                                     data-role-color="<?php echo $config['color']; ?>"
                                     data-role-icon="<?php echo $config['icon']; ?>">
                                    <div class="role-selector-icon" style="background: <?php echo $config['color']; ?>;">
                                        <i class="fas <?php echo $config['icon']; ?>"></i>
                                    </div>
                                    <div class="role-selector-info">
                                        <div class="role-selector-name"><?php echo e($staff['name']); ?></div>
                                        <div class="role-selector-stats">
                                            <i class="fas fa-user-tag me-1"></i><?php echo ucfirst(str_replace('_', ' ', $staff['role'])); ?>
                                            <br>
                                            <i class="fas fa-key me-1"></i><?php echo $permCount; ?> permissions
                                        </div>
                                    </div>
                                    <div class="role-selector-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Selected Staff Member Display -->
                        <div id="selectedRoleCard" style="display: none;">
                            <div class="selected-staff-card">
                                <div class="selected-staff-avatar" id="selectedRoleAvatar"></div>
                                <div class="selected-staff-info">
                                    <div class="selected-staff-name" id="selectedRoleName"></div>
                                    <div class="selected-staff-meta">
                                        <span><i class="fas fa-key me-1"></i><span id="selectedRolePermCount"></span> permissions assigned</span>
                                    </div>
                                </div>
                                <button class="clear-selection-btn" onclick="clearRoleSelection()">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Staff Members
                                </button>
                            </div>

                            <!-- Role Permissions -->
                            <div id="rolePermissionsDisplay"></div>
                        </div>
                    </div>

                    <!-- All Roles View -->
                    <div id="allRolesView">
                        <?php if (empty($permissions)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shield-alt"></i>
                                <p class="mb-0">No permissions available</p>
                                <small>Create permissions first to assign them to roles</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($roles as $role): 
                                $config = $role_configs[$role] ?? ['icon' => 'fa-user', 'color' => '#6b7280'];
                            ?>
                            <div class="role-card" style="border-left-color: <?php echo $config['color']; ?>;" data-role="<?php echo e($role); ?>">
                                <div class="role-header">
                                    <div class="role-icon" style="background: <?php echo $config['color']; ?>;">
                                        <i class="fas <?php echo $config['icon']; ?>"></i>
                                    </div>
                                    <div class="role-info">
                                        <div class="role-name"><?php echo ucfirst(str_replace('_', ' ', $role)); ?></div>
                                        <div class="role-stats">
                                            <i class="fas fa-key"></i>
                                            <?php echo $role_perm_counts[$role]; ?> permissions assigned
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="permission-grid">
                                    <?php foreach ($permissions as $perm): 
                                        $pid = (int)$perm['id'];
                                        $has = isset($roleToPermIds[$role][$pid]);
                                    ?>
                                        <form method="post" style="margin: 0;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="<?php echo $has ? 'revoke_permission' : 'assign_permission'; ?>">
                                            <input type="hidden" name="role" value="<?php echo e($role); ?>">
                                            <input type="hidden" name="permission_id" value="<?php echo $pid; ?>">
                                            <button type="submit" class="permission-badge <?php echo $has ? 'assigned' : 'unassigned'; ?>">
                                                <i class="fas <?php echo $has ? 'fa-check-circle' : 'fa-circle'; ?>"></i>
                                                <?php echo e($perm['name']); ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role configurations from PHP
        const roleConfigs = <?php echo json_encode($role_configs); ?>;
        const roleToPermIds = <?php echo json_encode($roleToPermIds); ?>;
        const permissions = <?php echo json_encode($permissions); ?>;
        
        // View toggle functionality
        const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
        const staffView = document.getElementById('staffView');
        const allRolesView = document.getElementById('allRolesView');
        
        viewToggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewToggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const view = btn.dataset.view;
                if (view === 'staff') {
                    staffView.style.display = 'block';
                    allRolesView.style.display = 'none';
                } else {
                    staffView.style.display = 'none';
                    allRolesView.style.display = 'block';
                }
            });
        });
        
        // Staff search functionality
        const staffSearch = document.getElementById('staffSearch');
        const staffDropdown = document.getElementById('staffDropdown');
        const staffItems = document.querySelectorAll('.staff-item');
        
        staffSearch.addEventListener('focus', () => {
            staffDropdown.classList.add('show');
        });
        
        staffSearch.addEventListener('blur', () => {
            setTimeout(() => {
                staffDropdown.classList.remove('show');
            }, 200);
        });
        
        staffSearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            staffItems.forEach(item => {
                const name = item.dataset.staffName.toLowerCase();
                const email = item.dataset.staffEmail.toLowerCase();
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Staff selection
        staffItems.forEach(item => {
            item.addEventListener('click', () => {
                selectStaff(item);
            });
        });
        
        function selectStaff(item) {
            const staffId = item.dataset.staffId;
            const staffName = item.dataset.staffName;
            const staffEmail = item.dataset.staffEmail;
            const staffRole = item.dataset.staffRole;
            const staffColor = item.dataset.staffColor;
            const staffIcon = item.dataset.staffIcon;
            const staffInitials = item.dataset.staffInitials;
            
            // Update selected staff card
            document.getElementById('selectedAvatar').style.background = staffColor;
            document.getElementById('selectedAvatar').textContent = staffInitials;
            document.getElementById('selectedName').textContent = staffName;
            document.getElementById('selectedEmail').textContent = staffEmail;
            document.getElementById('selectedRole').innerHTML = `<i class="fas ${staffIcon} me-1"></i>${staffRole.replace('_', ' ')}`;
            
            // Show selected card and hide no selection message
            document.getElementById('selectedStaffCard').style.display = 'block';
            document.getElementById('noStaffSelected').style.display = 'none';
            
            // Clear search
            staffSearch.value = '';
            staffDropdown.classList.remove('show');
            
            // Display role permissions
            displayStaffRolePermissions(staffRole, staffColor, staffIcon);
        }
        
        function displayStaffRolePermissions(role, color, icon) {
            const container = document.getElementById('staffRolePermissions');
            const rolePerms = roleToPermIds[role] || {};
            const permCount = Object.keys(rolePerms).length;
            
            let html = `
                <div class="role-card" style="border-left-color: ${color};">
                    <div class="role-header">
                        <div class="role-icon" style="background: ${color};">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="role-info">
                            <div class="role-name">${role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                            <div class="role-stats">
                                <i class="fas fa-key"></i>
                                ${permCount} permissions assigned
                            </div>
                        </div>
                    </div>
                    <div class="permission-grid">
            `;
            
            permissions.forEach(perm => {
                const has = rolePerms[perm.id] !== undefined;
                html += `
                    <form method="post" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name="csrf_token"]').value}">
                        <input type="hidden" name="action" value="${has ? 'revoke_permission' : 'assign_permission'}">
                        <input type="hidden" name="role" value="${role}">
                        <input type="hidden" name="permission_id" value="${perm.id}">
                        <button type="submit" class="permission-badge ${has ? 'assigned' : 'unassigned'}">
                            <i class="fas ${has ? 'fa-check-circle' : 'fa-circle'}"></i>
                            ${perm.name}
                        </button>
                    </form>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        function clearStaffSelection() {
            document.getElementById('selectedStaffCard').style.display = 'none';
            document.getElementById('noStaffSelected').style.display = 'block';
        }
        
        function selectStaffMemberAndScroll(role) {
            // Switch to "All Roles" tab
            const allRolesBtn = document.querySelector('.view-toggle-btn[data-view="all"]');
            if (allRolesBtn) {
                allRolesBtn.click();
            }
            
            // Wait for tab switch, then scroll to role
            setTimeout(() => {
                const roleCard = document.querySelector(`.role-card[data-role="${role}"]`);
                if (roleCard) {
                    roleCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Highlight the role card briefly
                    roleCard.style.transition = 'all 0.3s ease';
                    roleCard.style.boxShadow = '0 0 0 4px rgba(201, 169, 110, 0.5)';
                    roleCard.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        roleCard.style.boxShadow = '';
                        roleCard.style.transform = '';
                    }, 2000);
                }
            }, 100);
        }
        
        function selectStaffMember(role, color, icon, name, permCount) {
            // Hide staff grid, show selected card
            document.querySelector('#staffView .row.g-3').style.display = 'none';
            document.getElementById('selectedRoleCard').style.display = 'block';
            
            // Update selected card
            const avatar = document.getElementById('selectedRoleAvatar');
            avatar.style.background = color;
            avatar.innerHTML = `<i class="fas ${icon}"></i>`;
            document.getElementById('selectedRoleName').textContent = name;
            document.getElementById('selectedRolePermCount').textContent = permCount;
            
            // Display permissions for this role
            displayRolePermissions(role, color, icon);
        }
        
        function clearRoleSelection() {
            document.querySelector('#staffView .row.g-3').style.display = 'grid';
            document.getElementById('selectedRoleCard').style.display = 'none';
        }
        
        function displayRolePermissions(role, color, icon) {
            const container = document.getElementById('rolePermissionsDisplay');
            const rolePerms = roleToPermIds[role] || {};
            const permCount = Object.keys(rolePerms).length;
            
            let html = `
                <div class="role-card" style="border-left-color: ${color};">
                    <div class="role-header">
                        <div class="role-icon" style="background: ${color};">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="role-info">
                            <div class="role-name">${role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                            <div class="role-stats">
                                <i class="fas fa-key"></i>
                                ${permCount} permissions assigned
                            </div>
                        </div>
                    </div>
                    <div class="permission-grid">
            `;
            
            permissions.forEach(perm => {
                const has = rolePerms[perm.id] !== undefined;
                html += `
                    <form method="post" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name="csrf_token"]').value}">
                        <input type="hidden" name="action" value="${has ? 'revoke_permission' : 'assign_permission'}">
                        <input type="hidden" name="role" value="${role}">
                        <input type="hidden" name="permission_id" value="${perm.id}">
                        <button type="submit" class="permission-badge ${has ? 'assigned' : 'unassigned'}">
                            <i class="fas ${has ? 'fa-check-circle' : 'fa-circle'}"></i>
                            ${perm.name}
                        </button>
                    </form>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        // Add smooth fade-in animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.content-card, .role-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Auto-hide success/error alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert-custom');
                alerts.forEach(alert => {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!staffSearch.contains(e.target) && !staffDropdown.contains(e.target)) {
                staffDropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>