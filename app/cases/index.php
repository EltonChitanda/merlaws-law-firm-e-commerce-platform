<?php
// app/cases/index.php
require __DIR__ . '/../config.php';
require_login();

$user_id = get_user_id();
$cases = get_user_cases($user_id);

// Filter and sort cases
$filter = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'updated_desc';
$search = trim($_GET['search'] ?? '');

// Apply filters
$filtered_cases = $cases;
if ($filter !== 'all') {
    $filtered_cases = array_filter($cases, fn($c) => $c['status'] === $filter);
}

if ($search) {
    $filtered_cases = array_filter($filtered_cases, fn($c) => 
        stripos($c['title'], $search) !== false || 
        stripos($c['description'], $search) !== false ||
        stripos($c['case_type'], $search) !== false
    );
}

// Apply sorting
switch ($sort) {
    case 'title_asc':
        usort($filtered_cases, fn($a, $b) => strcasecmp($a['title'], $b['title']));
        break;
    case 'title_desc':
        usort($filtered_cases, fn($a, $b) => strcasecmp($b['title'], $a['title']));
        break;
    case 'created_asc':
        usort($filtered_cases, fn($a, $b) => strtotime($a['created_at']) - strtotime($b['created_at']));
        break;
    case 'created_desc':
        usort($filtered_cases, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        break;
    case 'updated_desc':
    default:
        usort($filtered_cases, fn($a, $b) => strtotime($b['updated_at']) - strtotime($a['updated_at']));
        break;
}

$stats = [
    'total' => count($cases),
    'active' => count(array_filter($cases, fn($c) => $c['status'] === 'active')),
    'draft' => count(array_filter($cases, fn($c) => $c['status'] === 'draft')),
    'under_review' => count(array_filter($cases, fn($c) => $c['status'] === 'under_review')),
    'closed' => count(array_filter($cases, fn($c) => $c['status'] === 'closed'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Case Management | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #c8344a;
            --merlaws-secondary: #f8f9fa;
            --merlaws-accent: #ffc107;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        
        /* Header Styles */
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 3rem 0 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .page-header .container {
            position: relative;
            z-index: 2;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }
        
        .page-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-primary-light));
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--merlaws-primary);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin: 0;
        }
        
        .stats-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }
        
        /* Controls Section */
        .controls-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .controls-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .controls-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .btn-new-case {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light));
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(172, 19, 42, 0.3);
        }
        
        .btn-new-case:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(172, 19, 42, 0.4);
            color: white;
        }
        
        /* Filter and Search Controls */
        .controls-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-select, .sort-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: white;
            font-size: 0.95rem;
            min-width: 140px;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus, .sort-select:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
        }
        
        /* Cases List */
        .cases-section {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .cases-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, #fefefe 0%, #f9fafb 100%);
        }
        
        .cases-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .cases-count {
            background: var(--merlaws-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .case-card {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .case-card:last-child {
            border-bottom: none;
        }
        
        .case-card:hover {
            background: linear-gradient(135deg, #fefefe 0%, #f8fafc 100%);
        }
        
        .case-content {
            padding: 2rem;
            position: relative;
        }
        
        .case-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        
        .case-title-section {
            flex: 1;
        }
        
        .case-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        
        .case-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .case-title a:hover {
            color: var(--merlaws-primary);
        }
        
        .case-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            flex-wrap: wrap;
        }
        
        .case-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .case-badges {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        
        .case-description {
            color: #475569;
            margin: 1.5rem 0;
            line-height: 1.7;
            font-size: 0.95rem;
        }
        
        .case-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .case-stats {
            display: flex;
            gap: 2rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .case-stat {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .case-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        
        .btn-case-action {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-primary {
            background: var(--merlaws-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--merlaws-primary-dark);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
        }
        
        .btn-outline:hover {
            background: var(--merlaws-primary);
            color: white;
        }
        
        /* Status and Priority Badges */
        .status-badge, .priority-badge, .type-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border: 2px solid transparent;
        }
        
        /* Status badges */
        .status-draft {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }
        
        .status-active {
            background: #dbeafe;
            color: #1e40af;
            border-color: #3b82f6;
        }
        
        .status-under-review {
            background: #fef3c7;
            color: #92400e;
            border-color: #f59e0b;
        }
        
        .status-closed {
            background: #dcfce7;
            color: #166534;
            border-color: #22c55e;
        }
        
        /* Priority badges */
        .priority-low {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }
        
        .priority-medium {
            background: #fffbeb;
            color: #92400e;
            border-color: #fed7aa;
        }
        
        .priority-high {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .priority-urgent {
            background: #7f1d1d;
            color: white;
            border-color: #dc2626;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .type-badge {
            background: #f8fafc;
            color: #64748b;
            border-color: #cbd5e1;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 2rem;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            font-size: 1rem;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.75rem;
            }
            
            .page-subtitle {
                font-size: 0.95rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stats-card {
                padding: 1.25rem;
            }
            
            .stats-number {
                font-size: 1.75rem;
            }
            
            .stats-label {
                font-size: 0.85rem;
            }
            
            .controls-section {
                padding: 1.25rem;
            }
            
            .controls-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .search-input,
            .filter-select,
            .sort-select {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 12px 16px;
                min-height: 48px;
            }
            
            .cases-header {
                padding: 1.5rem 1.25rem 1rem;
            }
            
            .cases-title {
                font-size: 1.25rem;
            }
            
            .case-content {
                padding: 1.25rem;
            }
            
            .case-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .case-title {
                font-size: 1.15rem;
            }
            
            .case-meta {
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            
            .case-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .case-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            
            .case-action-btn {
                min-height: 44px;
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .btn-new-case {
                width: 100%;
                justify-content: center;
                min-height: 48px;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-card {
                padding: 1rem;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .controls-section {
                padding: 1rem;
            }
            
            .cases-header {
                padding: 1.25rem 1rem 0.75rem;
            }
            
            .case-content {
                padding: 1rem;
            }
            
            .case-title {
                font-size: 1.05rem;
            }
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <!-- Include header -->
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">Case Management</h1>
                    <p class="page-subtitle">Manage and track all your legal matters in one centralized location</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="create.php" class="btn-new-case">
                        <i class="fas fa-plus"></i> New Case
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 2rem; border-left: 4px solid #3182ce; background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e3a8a;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Information:</strong> <?= htmlspecialchars(urldecode($_GET['message'])) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-number"><?php echo $stats['total']; ?></div>
                <p class="stats-label">Total Cases</p>
                <div class="stats-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?php echo $stats['active']; ?></div>
                <p class="stats-label">Active Cases</p>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?php echo $stats['under_review']; ?></div>
                <p class="stats-label">Under Review</p>
                <div class="stats-icon">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?php echo $stats['closed']; ?></div>
                <p class="stats-label">Resolved</p>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Search and Filter Controls -->
        <div class="controls-section">
            <div class="controls-header">
                <h2 class="controls-title">Filter & Search Cases</h2>
            </div>
            
            <form method="get" class="controls-row" id="filterForm">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Search cases by title, description, or type..."
                           value="<?php echo e($search); ?>"
                           id="searchInput">
                </div>
                
                <div class="filter-group">
                    <select name="filter" class="filter-select" id="filterSelect">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="draft" <?php echo $filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="under_review" <?php echo $filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                        <option value="closed" <?php echo $filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                    
                    <select name="sort" class="sort-select" id="sortSelect">
                        <option value="updated_desc" <?php echo $sort === 'updated_desc' ? 'selected' : ''; ?>>Recently Updated</option>
                        <option value="created_desc" <?php echo $sort === 'created_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="created_asc" <?php echo $sort === 'created_asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Cases List -->
        <div class="cases-section">
            <div class="cases-header">
                <h2 class="cases-title">
                    Your Cases
                    <span class="cases-count"><?php echo count($filtered_cases); ?></span>
                </h2>
            </div>
            
            <?php if (empty($filtered_cases) && empty($cases)): ?>
            <!-- Empty State for New Users -->
            <div class="empty-state">
                <i class="fas fa-briefcase empty-state-icon"></i>
                <h3>Welcome to Case Management</h3>
                <p>You haven't created any cases yet. Start by creating your first case to begin managing your legal matters with our comprehensive case management system.</p>
                <a href="create.php" class="btn-new-case">
                    <i class="fas fa-plus"></i> Create Your First Case
                </a>
            </div>
            
            <?php elseif (empty($filtered_cases)): ?>
            <!-- Empty State for Filtered Results -->
            <div class="empty-state">
                <i class="fas fa-search empty-state-icon"></i>
                <h3>No Cases Found</h3>
                <p>No cases match your current search and filter criteria. Try adjusting your filters or search terms.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="clearFilters()" class="btn-case-action btn-outline">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                    <a href="create.php" class="btn-case-action btn-primary">
                        <i class="fas fa-plus"></i> New Case
                    </a>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Cases List -->
            <?php foreach ($filtered_cases as $case): ?>
            <div class="case-card">
                <div class="case-content">
                    <div class="case-header">
                        <div class="case-title-section">
                            <h3 class="case-title">
                                <a href="view.php?id=<?php echo (int)$case['id']; ?>">
                                    <?php echo e($case['title']); ?>
                                </a>
                            </h3>
                            <div class="case-meta">
                                <div class="case-meta-item">
                                    <i class="fas fa-calendar-plus"></i>
                                    Created <?php echo date('M d, Y', strtotime($case['created_at'])); ?>
                                </div>
                                <div class="case-meta-item">
                                    <i class="fas fa-clock"></i>
                                    Updated <?php echo date('M d, Y g:i A', strtotime($case['updated_at'])); ?>
                                </div>
                                <?php if (isset($case['client_name']) && $case['client_name']): ?>
                                <div class="case-meta-item">
                                    <i class="fas fa-user"></i>
                                    <?php echo e($case['client_name']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="case-badges">
                            <?php if (isset($case['status'])): ?>
                            <span class="status-badge status-<?php echo str_replace('_', '-', $case['status']); ?>">
                                <?php echo ucwords(str_replace('_', ' ', $case['status'])); ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (isset($case['priority'])): ?>
                            <span class="priority-badge priority-<?php echo $case['priority']; ?>">
                                <?php echo ucfirst($case['priority']); ?> Priority
                            </span>
                            <?php endif; ?>
                            
                            <span class="type-badge">
                                <?php echo ucwords(str_replace('_', ' ', $case['case_type'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($case['description']) && !empty($case['description'])): ?>
                    <div class="case-description">
                        <?php echo e(substr($case['description'], 0, 280)); ?>
                        <?php if (strlen($case['description']) > 280): ?>...<?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="case-footer">
                        <div class="case-stats">
                            <div class="case-stat">
                                <i class="fas fa-file-alt"></i>
                                <span><?php echo isset($case['document_count']) ? (int)$case['document_count'] : 0; ?> documents</span>
                            </div>
                            <div class="case-stat">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?php echo isset($case['service_count']) ? (int)$case['service_count'] : 0; ?> service requests</span>
                            </div>
                            <?php if (isset($case['last_activity']) && $case['last_activity']): ?>
                            <div class="case-stat">
                                <i class="fas fa-history"></i>
                                <span>Last activity <?php echo date('M d', strtotime($case['last_activity'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="case-actions">
                            <a href="view.php?id=<?php echo (int)$case['id']; ?>" class="btn-case-action btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <?php if (isset($case['status']) && $case['status'] !== 'closed'): ?>
                            <a href="edit.php?id=<?php echo (int)$case['id']; ?>" class="btn-case-action btn-outline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include footer -->
    <?php 
    $footerPath = __DIR__ . '/../../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const filterSelect = document.getElementById('filterSelect');
            const sortSelect = document.getElementById('sortSelect');
            const searchInput = document.getElementById('searchInput');
            
            let searchTimeout;
            
            // Handle filter and sort changes
            if (filterSelect) {
                filterSelect.addEventListener('change', function() {
                    if (filterForm) filterForm.submit();
                });
            }
            
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    if (filterForm) filterForm.submit();
                });
            }
            
            // Handle search with debouncing
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (filterForm) filterForm.submit();
                    }, 500); // Wait 500ms after user stops typing
                });
            }
            
            // Animate stats cards on load
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animate case cards on load
            const caseCards = document.querySelectorAll('.case-card');
            caseCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 200 + (index * 100));
            });
        });
        
        // Clear all filters
        function clearFilters() {
            const url = new URL(window.location);
            url.searchParams.delete('filter');
            url.searchParams.delete('sort');
            url.searchParams.delete('search');
            window.location.href = url.toString();
        }
        
        // Show loading state for form submissions
        function showLoading(element) {
            if (element) {
                const originalText = element.innerHTML;
                element.innerHTML = '<span class="loading"></span> Loading...';
                element.disabled = true;
                
                setTimeout(() => {
                    element.innerHTML = originalText;
                    element.disabled = false;
                }, 2000);
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Ctrl/Cmd + N to create new case
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'create.php';
            }
        });
        
        // Add tooltip for keyboard shortcuts
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.title = 'Press Ctrl+K to focus search';
        }
        
        // Progressive enhancement for better UX
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'slideInUp 0.5s ease forwards';
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.case-card').forEach(card => {
                observer.observe(card);
            });
        }
    </script>
    
    <!-- Add slide animation -->
    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    <script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>