<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user_id = get_user_id();

$stmt = $pdo->prepare('SELECT id, title, status, created_at, updated_at FROM cases WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$case_id = (int)($_GET['case_id'] ?? (count($cases) ? (int)$cases[0]['id'] : 0));

$metrics = [
    'document_count' => 0,
    'pending_requests' => 0,
    'completed_requests' => 0,
    'appointments' => 0,
];
$documents = $activity = [];

if ($case_id) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM case_documents WHERE case_id = ?');
        $stmt->execute([$case_id]);
        $metrics['document_count'] = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT SUM(sr.status = 'pending') AS pending, SUM(sr.status = 'completed') AS completed FROM service_requests sr WHERE sr.case_id = ?");
        $stmt->execute([$case_id]);
        $sr = $stmt->fetch();
        $metrics['pending_requests'] = (int)($sr['pending'] ?? 0);
        $metrics['completed_requests'] = (int)($sr['completed'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE case_id = ? AND status = 'scheduled' AND start_time >= NOW()");
        $stmt->execute([$case_id]);
        $metrics['appointments'] = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare('SELECT id, original_filename, uploaded_at, document_type, file_size FROM case_documents WHERE case_id = ? ORDER BY uploaded_at DESC LIMIT 50');
        $stmt->execute([$case_id]);
        $documents = $stmt->fetchAll();
        
        $stmt = $pdo->prepare('SELECT ca.*, u.name AS user_name FROM case_activities ca JOIN users u ON ca.user_id = u.id WHERE ca.case_id = ? ORDER BY ca.created_at DESC LIMIT 50');
        $stmt->execute([$case_id]);
        $activity = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

if (isset($_GET['export']) && $case_id) {
    $format = (string)($_GET['export'] ?? 'csv');
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="case-report-' . $case_id . '.' . ($format === 'json' ? 'json' : 'csv') . '"');
    if ($format === 'json') {
        echo json_encode(['metrics' => $metrics, 'documents' => $documents, 'activity' => $activity]);
        exit;
    }
    echo "Section,Field,Value\n";
    echo "Metrics,Documents,{$metrics['document_count']}\n";
    echo "Metrics,Pending Requests,{$metrics['pending_requests']}\n";
    echo "Metrics,Completed Requests,{$metrics['completed_requests']}\n";
    echo "Metrics,Upcoming Appointments,{$metrics['appointments']}\n";
    echo "\nDocuments,Filename,Uploaded At,Type,Size\n";
    foreach ($documents as $d) {
        echo 'Documents,"' . str_replace('"','""',$d['original_filename']) . '",' . $d['uploaded_at'] . ',"' . ($d['document_type'] ?? '') . '",' . (int)$d['file_size'] . "\n";
    }
    echo "\nActivity,Type,Title,User,At\n";
    foreach ($activity as $a) {
        echo 'Activity,' . ($a['activity_type'] ?? '') . ',"' . str_replace('"','""',$a['title'] ?? '') . '","' . str_replace('"','""',$a['user_name'] ?? '') . '",' . ($a['created_at'] ?? '') . "\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Case Reports | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-accent: #f7fafc;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
            --merlaws-warning: #ed8936;
            --merlaws-danger: #e53e3e;
            --merlaws-info: #3182ce;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e0;
            --merlaws-gray-400: #a0aec0;
            --merlaws-gray-500: #718096;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-700: #2d3748;
            --merlaws-gray-800: #1a202c;
            --merlaws-gray-900: #171923;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .reports-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Professional Reports Header */
        .reports-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .reports-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .reports-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 600;
            margin: 0 0 1rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .reports-header p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 300;
        }

        /* Control Panel Card */
        .control-panel {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .control-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .control-panel-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .control-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 1rem;
            background: linear-gradient(135deg, var(--merlaws-info), #4299e1);
            box-shadow: 0 4px 15px rgba(49, 130, 206, 0.3);
        }

        .control-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0;
        }

        .form-select-custom {
            border: 2px solid var(--merlaws-gray-200);
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-select-custom:focus {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 0.25rem rgba(172, 19, 42, 0.15);
            outline: none;
        }

        .export-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            padding: 0.875rem 1.75rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid;
            text-decoration: none;
        }

        .btn-export-csv {
            background: white;
            color: var(--merlaws-success);
            border-color: var(--merlaws-success);
        }

        .btn-export-csv:hover {
            background: var(--merlaws-success);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(56, 161, 105, 0.3);
        }

        .btn-export-json {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-color: var(--merlaws-primary);
        }

        .btn-export-json:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
        }

        /* Metrics Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .metric-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .metric-card.documents::before { background: linear-gradient(90deg, var(--merlaws-info), #4299e1); }
        .metric-card.pending::before { background: linear-gradient(90deg, var(--merlaws-warning), #f6ad55); }
        .metric-card.completed::before { background: linear-gradient(90deg, var(--merlaws-success), #48bb78); }
        .metric-card.appointments::before { background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold)); }

        .metric-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .metric-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: var(--shadow-md);
        }

        .metric-icon.documents { background: linear-gradient(135deg, var(--merlaws-info), #4299e1); }
        .metric-icon.pending { background: linear-gradient(135deg, var(--merlaws-warning), #f6ad55); }
        .metric-icon.completed { background: linear-gradient(135deg, var(--merlaws-success), #48bb78); }
        .metric-icon.appointments { background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold)); }

        .metric-content {
            text-align: left;
        }

        .metric-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--merlaws-gray-800);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: var(--merlaws-gray-600);
            font-weight: 500;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Data Cards */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 2rem;
        }

        .data-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .data-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .data-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--merlaws-gray-100);
        }

        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .card-icon.documents-icon { background: linear-gradient(135deg, var(--merlaws-info), #4299e1); }
        .card-icon.activity-icon { background: linear-gradient(135deg, var(--merlaws-gold), #ecc94b); }

        .card-title-custom {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0;
        }

        /* Table Styles */
        .table-responsive-custom {
            max-height: 500px;
            overflow-y: auto;
            border-radius: 12px;
        }

        .table-custom {
            width: 100%;
            margin: 0;
        }

        .table-custom thead {
            position: sticky;
            top: 0;
            background: var(--merlaws-gray-50);
            z-index: 10;
        }

        .table-custom thead th {
            padding: 1rem;
            font-weight: 600;
            color: var(--merlaws-gray-700);
            border-bottom: 2px solid var(--merlaws-gray-200);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-custom tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--merlaws-gray-100);
        }

        .table-custom tbody tr:hover {
            background: var(--merlaws-gray-50);
            transform: scale(1.01);
        }

        .table-custom tbody td {
            padding: 1rem;
            color: var(--merlaws-gray-700);
            vertical-align: middle;
        }

        /* Activity List */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 500px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: start;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: var(--merlaws-gray-50);
            border-left: 4px solid var(--merlaws-primary);
            border-radius: 0 12px 12px 0;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            transform: translateX(8px);
            box-shadow: var(--shadow-md);
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }

        .activity-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.875rem;
            color: var(--merlaws-gray-600);
        }

        .activity-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .activity-time {
            text-align: right;
            font-size: 0.875rem;
            color: var(--merlaws-gray-500);
            white-space: nowrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--merlaws-gray-500);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h5 {
            color: var(--merlaws-gray-600);
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .reports-container {
                padding: 1rem;
            }

            .reports-header {
                padding: 2rem;
            }

            .reports-header h1 {
                font-size: 2rem;
            }

            .control-panel {
                padding: 1.5rem;
            }

            .export-buttons {
                flex-direction: column;
            }

            .btn-export {
                width: 100%;
                justify-content: center;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .data-grid {
                grid-template-columns: 1fr;
            }

            .data-card {
                padding: 1.5rem;
            }

            .activity-item {
                flex-direction: column;
                gap: 1rem;
            }

            .activity-time {
                text-align: left;
            }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) { 
    echo file_get_contents($headerPath); 
}
?>

<div class="reports-container">
    <!-- Professional Reports Header -->
    <div class="reports-header">
        <div class="header-content">
            <h1><i class="fas fa-chart-line me-3"></i>Case Reports & Analytics</h1>
            <p>Comprehensive insights and detailed analysis of your case progress</p>
        </div>
    </div>

    <!-- Control Panel -->
    <div class="control-panel">
        <div class="control-panel-header">
            <div class="control-icon">
                <i class="fas fa-sliders-h"></i>
            </div>
            <h3 class="control-title">Report Controls</h3>
        </div>
        
        <form class="row g-4 align-items-end" method="get">
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-briefcase me-2"></i>Select Case
                </label>
                <select name="case_id" class="form-select-custom w-100" onchange="this.form.submit()">
                    <?php if (empty($cases)): ?>
                        <option value="">No cases available</option>
                    <?php else: ?>
                        <?php foreach ($cases as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo $case_id===(int)$c['id']?'selected':''; ?>>
                            <?php echo htmlspecialchars($c['title']); ?> 
                            (<?php echo ucfirst($c['status']); ?>)
                        </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-download me-2"></i>Export Options
                </label>
                <div class="export-buttons">
                    <a class="btn-export btn-export-csv" href="?case_id=<?php echo (int)$case_id; ?>&export=csv">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                    <a class="btn-export btn-export-json" href="?case_id=<?php echo (int)$case_id; ?>&export=json">
                        <i class="fas fa-file-code me-2"></i>Export JSON
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if (!$case_id || empty($cases)): ?>
        <div class="data-card">
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h5>No Cases Available</h5>
                <p>You don't have any cases yet. Once cases are created, you'll be able to view detailed reports here.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card documents">
                <div class="metric-header">
                    <div class="metric-content">
                        <div class="metric-number"><?php echo (int)$metrics['document_count']; ?></div>
                        <div class="metric-label">Documents</div>
                    </div>
                    <div class="metric-icon documents">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>

            <div class="metric-card pending">
                <div class="metric-header">
                    <div class="metric-content">
                        <div class="metric-number"><?php echo (int)$metrics['pending_requests']; ?></div>
                        <div class="metric-label">Pending Requests</div>
                    </div>
                    <div class="metric-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="metric-card completed">
                <div class="metric-header">
                    <div class="metric-content">
                        <div class="metric-number"><?php echo (int)$metrics['completed_requests']; ?></div>
                        <div class="metric-label">Completed Requests</div>
                    </div>
                    <div class="metric-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="metric-card appointments">
                <div class="metric-header">
                    <div class="metric-content">
                        <div class="metric-number"><?php echo (int)$metrics['appointments']; ?></div>
                        <div class="metric-label">Upcoming Appointments</div>
                    </div>
                    <div class="metric-icon appointments">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Grid -->
        <div class="data-grid">
            <!-- Latest Documents -->
            <div class="data-card">
                <div class="card-header-custom">
                    <div class="card-icon documents-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="card-title-custom">Latest Documents</h3>
                </div>
                
                <?php if ($documents): ?>
                <div class="table-responsive-custom">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file me-2"></i>Filename</th>
                                <th><i class="fas fa-tag me-2"></i>Type</th>
                                <th><i class="fas fa-calendar me-2"></i>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($documents as $d): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($d['original_filename']); ?></strong>
                                    <?php if (isset($d['file_size'])): ?>
                                    <div class="small text-muted">
                                        <?php echo number_format((int)$d['file_size'] / 1024, 2); ?> KB
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($d['document_type'] ?? 'General'); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M j, Y', strtotime($d['uploaded_at'])); ?>
                                    <div><?php echo date('g:i A', strtotime($d['uploaded_at'])); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h5>No Documents</h5>
                    <p>No documents have been uploaded for this case yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="data-card">
                <div class="card-header-custom">
                    <div class="card-icon activity-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="card-title-custom">Recent Activity</h3>
                </div>
                
                <?php if ($activity): ?>
                <ul class="activity-list">
                    <?php foreach ($activity as $a): ?>
                    <li class="activity-item">
                        <div class="activity-details">
                            <h6 class="activity-title">
                                <?php 
                                $activityType = $a['activity_type'] ?? 'update';
                                $icon = match($activityType) {
                                    'document_upload' => 'fas fa-file-upload',
                                    'status_change' => 'fas fa-sync-alt',
                                    'comment' => 'fas fa-comment',
                                    'appointment' => 'fas fa-calendar',
                                    default => 'fas fa-circle'
                                };
                                ?>
                                <i class="<?php echo $icon; ?> me-2"></i>
                                <?php echo htmlspecialchars($a['title'] ?? ucfirst(str_replace('_', ' ', $activityType))); ?>
                            </h6>
                            <?php if (!empty($a['description'])): ?>
                            <div class="text-muted small mb-2">
                                <?php echo htmlspecialchars($a['description']); ?>
                            </div>
                            <?php endif; ?>
                            <div class="activity-meta">
                                <span>
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($a['user_name'] ?? 'System'); ?>
                                </span>
                                <span>
                                    <i class="fas fa-tag"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $activityType)); ?>
                                </span>
                            </div>
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('M j, Y', strtotime($a['created_at'] ?? 'now')); ?>
                            <div><?php echo date('g:i A', strtotime($a['created_at'] ?? 'now')); ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h5>No Recent Activity</h5>
                    <p>There is no recent activity to display for this case.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Full Width Summary Card -->
        <div class="data-card" style="grid-column: 1 / -1;">
            <div class="card-header-custom">
                <div class="card-icon" style="background: linear-gradient(135deg, var(--merlaws-secondary), var(--merlaws-primary));">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <h3 class="card-title-custom">Case Summary</h3>
                    <p class="text-muted mb-0">Comprehensive overview of case metrics and progress</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="h2 fw-bold text-primary mb-1"><?php echo (int)$metrics['document_count']; ?></div>
                        <div class="text-muted small">Total Documents</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="h2 fw-bold text-warning mb-1"><?php echo (int)$metrics['pending_requests']; ?></div>
                        <div class="text-muted small">Pending Requests</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="h2 fw-bold text-success mb-1"><?php echo (int)$metrics['completed_requests']; ?></div>
                        <div class="text-muted small">Completed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="h2 fw-bold text-danger mb-1"><?php echo (int)$metrics['appointments']; ?></div>
                        <div class="text-muted small">Appointments</div>
                    </div>
                </div>
            </div>

            <?php 
            $totalRequests = (int)$metrics['pending_requests'] + (int)$metrics['completed_requests'];
            $completionRate = $totalRequests > 0 ? round(((int)$metrics['completed_requests'] / $totalRequests) * 100) : 0;
            ?>

            <?php if ($totalRequests > 0): ?>
            <div class="mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">Completion Rate</span>
                    <span class="fw-bold text-primary"><?php echo $completionRate; ?>%</span>
                </div>
                <div class="progress" style="height: 20px; border-radius: 10px;">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: <?php echo $completionRate; ?>%; background: linear-gradient(90deg, var(--merlaws-success), var(--merlaws-info));" 
                         aria-valuenow="<?php echo $completionRate; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
<script src="../assets/js/mobile-responsive.js"></script>

<script>
    // Enhanced dashboard interactions
    document.addEventListener('DOMContentLoaded', function() {
        initializeReports();
    });

    function initializeReports() {
        animateMetrics();
        setupCardInteractions();
        initializeTooltips();
        animateProgressBars();
    }

    function animateMetrics() {
        const metricNumbers = document.querySelectorAll('.metric-number');
        metricNumbers.forEach((metric, index) => {
            const finalValue = parseInt(metric.textContent);
            metric.textContent = '0';
            
            setTimeout(() => {
                animateCounter(metric, finalValue, 1200);
            }, index * 100);
        });
    }

    function animateCounter(element, target, duration) {
        const start = 0;
        const startTime = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeOut);
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target;
            }
        }
        
        requestAnimationFrame(update);
    }

    function setupCardInteractions() {
        const cards = document.querySelectorAll('.metric-card, .data-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Add click animation to export buttons
        const exportButtons = document.querySelectorAll('.btn-export');
        exportButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    }

    function initializeTooltips() {
        // Add hover effects to table rows
        const tableRows = document.querySelectorAll('.table-custom tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.cursor = 'pointer';
            });
        });
    }

    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1.5s ease-out';
                bar.style.width = targetWidth;
            }, 300);
        });
    }

    // Auto-refresh data every 5 minutes
    setInterval(function() {
        const currentUrl = window.location.href;
        if (!currentUrl.includes('export=')) {
            // Optionally reload the page to refresh data
            // window.location.reload();
        }
    }, 300000);

    // Format file sizes dynamically
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Print functionality
    function printReport() {
        window.print();
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printReport();
        }
        
        // Ctrl/Cmd + E for CSV export
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            const csvBtn = document.querySelector('.btn-export-csv');
            if (csvBtn) csvBtn.click();
        }
    });

    // Lazy loading for images/icons if needed
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.data-card').forEach(card => {
            observer.observe(card);
        });
    }
</script>

<style>
    /* Print Styles */
    @media print {
        .reports-header,
        .control-panel,
        nav,
        footer,
        .btn-export {
            display: none !important;
        }

        .data-card,
        .metric-card {
            page-break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        body {
            background: white !important;
        }
    }

    /* Animation classes */
    .data-card {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .data-card.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Custom scrollbar for tables and lists */
    .table-responsive-custom::-webkit-scrollbar,
    .activity-list::-webkit-scrollbar,
    .message-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive-custom::-webkit-scrollbar-track,
    .activity-list::-webkit-scrollbar-track,
    .message-container::-webkit-scrollbar-track {
        background: var(--merlaws-gray-100);
        border-radius: 10px;
    }

    .table-responsive-custom::-webkit-scrollbar-thumb,
    .activity-list::-webkit-scrollbar-thumb,
    .message-container::-webkit-scrollbar-thumb {
        background: var(--merlaws-gray-300);
        border-radius: 10px;
    }

    .table-responsive-custom::-webkit-scrollbar-thumb:hover,
    .activity-list::-webkit-scrollbar-thumb:hover,
    .message-container::-webkit-scrollbar-thumb:hover {
        background: var(--merlaws-primary);
    }

    /* Loading animation */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .loading {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
</body>
</html>