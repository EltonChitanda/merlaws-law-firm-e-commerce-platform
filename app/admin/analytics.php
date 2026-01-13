<?php
// app/admin/analytics.php
require __DIR__ . '/../config.php';
require_permission('report:view');

$pdo = db();
$user_id = get_user_id();
$user_role = get_user_role();

// Role-based data filtering
$case_filter_sql = "";
$case_filter_params = [];

if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see their assigned cases
    $case_filter_sql = " AND c.assigned_to = ?";
    $case_filter_params[] = $user_id;
} elseif ($user_role === 'billing') {
    // Billing sees cases with financial activity
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM service_requests sr WHERE sr.case_id = c.id AND sr.status != 'cart')";
} elseif ($user_role === 'doc_specialist') {
    // Document specialists see cases with document activity
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM case_documents cd WHERE cd.case_id = c.id)";
} elseif ($user_role === 'receptionist') {
    // Receptionists see cases with appointments
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM appointments a WHERE a.case_id = c.id)";
} elseif ($user_role === 'compliance') {
    // Compliance sees cases with compliance requests
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM compliance_requests cr WHERE cr.case_id = c.id)";
}
// super_admin, partner, case_manager, office_admin see all cases (no additional filter)

// Get role-based metrics
$metrics = [];

// Total Users (only for management roles)
if (in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $metrics['total_users'] = $stmt->fetchColumn();
} else {
    $metrics['total_users'] = 0; // Hide for non-management roles
}

// Active Cases
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM cases c 
    WHERE c.status IN ('active', 'under_review')" . $case_filter_sql
);
$stmt->execute($case_filter_params);
$metrics['active_cases'] = $stmt->fetchColumn();

// Pending Requests
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM service_requests sr 
    JOIN cases c ON sr.case_id = c.id 
    WHERE sr.status = 'pending'" . $case_filter_sql
);
$stmt->execute($case_filter_params);
$metrics['pending_requests'] = $stmt->fetchColumn();

// Average Processing Time (for cases user has access to)
$stmt = $pdo->prepare("
    SELECT AVG(TIMESTAMPDIFF(HOUR, c.created_at, c.updated_at)) 
    FROM cases c 
    WHERE c.status IN ('resolved', 'closed')" . $case_filter_sql
);
$stmt->execute($case_filter_params);
$avg_hours = $stmt->fetchColumn();
$metrics['avg_processing_time'] = $avg_hours ? round($avg_hours) : 0;

// Get case trends data for charts
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(c.created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM cases c
    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)" . $case_filter_sql . "
    GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute($case_filter_params);
$case_trends = $stmt->fetchAll();

// Get case types data
$stmt = $pdo->prepare("
    SELECT c.case_type, COUNT(*) as count
    FROM cases c
    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)" . $case_filter_sql . "
    GROUP BY c.case_type
    ORDER BY count DESC
");
$stmt->execute($case_filter_params);
$case_types = $stmt->fetchAll();

// Get financial data (only for billing, partners, super_admin)
$financial_data = [];
if (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
    $where_clause = " WHERE i.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
    if ($case_filter_sql) {
        $where_clause .= $case_filter_sql;
    }

    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN i.status = 'paid' THEN i.amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN i.status = 'pending' THEN i.amount ELSE 0 END) as pending_amount,
            COUNT(CASE WHEN i.status = 'overdue' THEN 1 END) as overdue_count
        FROM invoices i
        JOIN cases c ON i.case_id = c.id
        $where_clause
    ");
    $stmt->execute($case_filter_params);
    $financial_data = $stmt->fetch();
}

// Get document metrics (for doc_specialist and management)
$document_metrics = [];
if (in_array($user_role, ['doc_specialist', 'super_admin', 'partner', 'office_admin'])) {
    $where_clause = "";
    if ($case_filter_sql) {
        $where_clause = " WHERE 1=1" . $case_filter_sql;
    }

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_documents,
            COUNT(CASE WHEN cd.uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_uploads
        FROM case_documents cd
        JOIN cases c ON cd.case_id = c.id
        $where_clause
    ");
    $stmt->execute($case_filter_params);
    $document_metrics = $stmt->fetch();
}

// Get appointment metrics (for receptionist and management)
$appointment_metrics = [];
if (in_array($user_role, ['receptionist', 'super_admin', 'partner', 'office_admin'])) {
    $where_clause = "";
    if ($case_filter_sql) {
        $where_clause = " WHERE 1=1" . $case_filter_sql;
    }

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_appointments,
            COUNT(CASE WHEN a.start_time >= NOW() AND a.start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 END) as upcoming_appointments
        FROM appointments a
        JOIN cases c ON a.case_id = c.id
        $where_clause
    ");
    $stmt->execute($case_filter_params);
    $appointment_metrics = $stmt->fetch();
}

// Get service performance data for Top Services section
$service_performance = [];
try {
    $stmt = $pdo->query("
        SELECT 
            s.name,
            s.category,
            COUNT(sr.id) as total_requests,
            COUNT(CASE WHEN sr.status = 'approved' THEN 1 END) as approved,
            COUNT(CASE WHEN sr.status = 'rejected' THEN 1 END) as rejected,
            AVG(CASE WHEN sr.processed_at IS NOT NULL 
                THEN TIMESTAMPDIFF(HOUR, sr.requested_at, sr.processed_at) END) as avg_hours
        FROM services s
        LEFT JOIN service_requests sr ON s.id = sr.service_id
            AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        GROUP BY s.id, s.name, s.category
        HAVING total_requests > 0
        ORDER BY total_requests DESC
        LIMIT 10
    ");
    $service_performance = $stmt->fetchAll();
} catch (Exception $e) {
    $service_performance = [];
}

// Get monthly trends data for Monthly Growth chart
$monthly_trends = [];
try {
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(u.created_at, '%Y-%m') as month,
            COUNT(DISTINCT CASE WHEN u.role = 'client' THEN u.id END) as new_users,
            (SELECT COUNT(*) FROM cases c WHERE DATE_FORMAT(c.created_at, '%Y-%m') = DATE_FORMAT(u.created_at, '%Y-%m')) as new_cases
        FROM users u
        WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthly_trends = $stmt->fetchAll();
} catch (Exception $e) {
    $monthly_trends = [];
}

// Get user activity data for User Activity Trends chart
$user_activity_data = [];
try {
    // Get login data from audit_logs
    $stmt = $pdo->prepare("
        SELECT 
            DATE(al.created_at) as date,
            'login' as event_type,
            COUNT(*) as count
        FROM audit_logs al
        WHERE al.event_type = 'login' 
        AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(al.created_at)
        ORDER BY date ASC
    ");
    $stmt->execute();
    $login_data = $stmt->fetchAll();
    $user_activity_data = array_merge($user_activity_data, $login_data);
} catch (Exception $e) {
    // Fallback to security_logs
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(sl.created_at) as date,
                'login' as event_type,
                COUNT(*) as count
            FROM security_logs sl
            WHERE sl.event_type = 'login' 
            AND sl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(sl.created_at)
            ORDER BY date ASC
        ");
        $stmt->execute();
        $login_data = $stmt->fetchAll();
        $user_activity_data = array_merge($user_activity_data, $login_data);
    } catch (Exception $e2) {
        // Continue without login data
    }
}

// Get case creation data for User Activity Trends
try {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(c.created_at) as date,
            'case_created' as event_type,
            COUNT(*) as count
        FROM cases c
        WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $case_filter_sql . "
        GROUP BY DATE(c.created_at)
        ORDER BY date ASC
    ");
    $params = array_merge([], $case_filter_params);
    $stmt->execute($params);
    $case_data = $stmt->fetchAll();
    $user_activity_data = array_merge($user_activity_data, $case_data);
} catch (Exception $e) {
    // Continue without case data
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Analytics & Reports | Med Attorneys Admin</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --admin-primary: #1e40af;
            --admin-secondary: #3b82f6;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .analytics-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        
        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: transform 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
        }
        
        .metric-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-controls {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #333;
        }
        
        .badge-metric {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="mb-0">Analytics & Reports</h1>
            <p class="mb-0 mt-2">Comprehensive insights into system performance and user activity</p>
        </div>
    </div>

    <div class="container">
        <!-- Key Metrics -->
        <div class="row mb-4" id="keyMetrics">
            <?php if (in_array($user_role, ['super_admin', 'partner', 'office_admin'])): ?>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-primary"><?php echo $metrics['total_users']; ?></div>
                    <div class="metric-label">Total Users</div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-success"><?php echo $metrics['active_cases']; ?></div>
                    <div class="metric-label">Active Cases</div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-warning"><?php echo $metrics['pending_requests']; ?></div>
                    <div class="metric-label">Pending Requests</div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-info"><?php echo $metrics['avg_processing_time']; ?>h</div>
                    <div class="metric-label">Avg Processing Time</div>
                </div>
            </div>
            
            <?php if (in_array($user_role, ['billing', 'partner', 'super_admin']) && !empty($financial_data)): ?>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-success">R <?php echo number_format($financial_data['total_revenue'] ?? 0, 0); ?></div>
                    <div class="metric-label">Total Revenue</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['doc_specialist', 'super_admin', 'partner', 'office_admin']) && !empty($document_metrics)): ?>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-info"><?php echo $document_metrics['total_documents']; ?></div>
                    <div class="metric-label">Total Documents</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['receptionist', 'super_admin', 'partner', 'office_admin']) && !empty($appointment_metrics)): ?>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-number text-warning"><?php echo $appointment_metrics['upcoming_appointments']; ?></div>
                    <div class="metric-label">Upcoming Appointments</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <select class="form-select" id="dateRange">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="reportType" class="form-label">Report Type</label>
                    <select class="form-select" id="reportType">
                        <option value="overview" selected>Overview</option>
                        <option value="users">User Activity</option>
                        <option value="cases">Case Statistics</option>
                        <option value="services">Service Performance</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary me-2" onclick="refreshData()">
                        Refresh
                    </button>
                    <?php if (has_permission('report:export')): ?>
                    <button class="btn btn-success" onclick="exportReport()">
                        Export
                    </button>
                    <?php endif; ?>
                    <?php if (has_permission('report:create')): ?>
                    <button class="btn btn-info ms-2" onclick="generateCustomReport()">
                        Custom Report
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Charts -->
            <div class="col-lg-8">
                <!-- User Activity Chart -->
                <div class="analytics-card">
                    <h3 class="section-title">
                        User Activity Trends
                    </h3>
                    <div class="chart-container">
                        <canvas id="userActivityChart"></canvas>
                    </div>
                </div>

                <!-- Case Statistics Chart -->
                <div class="analytics-card">
                    <h3 class="section-title">
                        Case Distribution
                    </h3>
                    <div class="chart-container">
                        <canvas id="caseStatsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Reports Sidebar -->
            <div class="col-lg-4">
                <!-- Service Performance -->
                <div class="analytics-card">
                    <h5 class="section-title">
                        Top Services
                    </h5>
                    <div id="servicePerformance">
                        <div class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends -->
                <div class="analytics-card">
                    <h5 class="section-title">
                        Monthly Growth
                    </h5>
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="analytics-card">
                    <h5 class="section-title">
                        Quick Reports
                    </h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="generateReport('user_summary')">
                            User Summary
                        </button>
                        <button class="btn btn-outline-success" onclick="generateReport('case_outcomes')">
                            Case Outcomes
                        </button>
                        <button class="btn btn-outline-warning" onclick="generateReport('financial')">
                            Financial Report
                        </button>
                        <button class="btn btn-outline-info" onclick="generateReport('system_health')">
                            System Health
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports Table -->
        <div class="analytics-card">
            <h3 class="section-title">
                Detailed Analytics
            </h3>
            <div class="table-responsive">
                <table class="table table-hover" id="detailedTable">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            <th>Change</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody id="detailedTableBody">
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Report Preview Modal (will be created dynamically) -->
    
    <script>
        // Chart instances
        let userActivityChart, caseStatsChart, monthlyTrendsChart;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            
            // Load data from PHP (static data)
            const caseTrendsData = <?php echo json_encode($case_trends); ?>;
            const caseTypesData = <?php echo json_encode($case_types); ?>;
            const userActivityData = <?php echo json_encode($user_activity_data); ?>;
            const servicePerformanceData = <?php echo json_encode($service_performance); ?>;
            const monthlyTrendsData = <?php echo json_encode($monthly_trends); ?>;
            
            // Update charts with static data immediately
            if (userActivityData && userActivityData.length > 0) {
                updateUserActivityChart(userActivityData);
            } else if (caseTrendsData && caseTrendsData.length > 0) {
                // Fallback to case trends if no user activity data
                updateUserActivityChart(caseTrendsData);
            }
            
            if (caseTypesData && caseTypesData.length > 0) {
                updateCaseStatsChart(caseTypesData);
            }
            
            if (servicePerformanceData && servicePerformanceData.length > 0) {
                updateServicePerformance(servicePerformanceData);
            }
            
            if (monthlyTrendsData && monthlyTrendsData.length > 0) {
                updateMonthlyTrendsChart(monthlyTrendsData);
            }
            
            // Update detailed table immediately with PHP data
            updateDetailedTable({});
            
            // Load dynamic dashboard data (for refresh functionality)
            loadDashboardData();
            
            // Event listeners
            document.getElementById('dateRange').addEventListener('change', refreshData);
            document.getElementById('reportType').addEventListener('change', refreshData);
        });
        
        function initializeCharts() {
            // User Activity Chart
            const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
            userActivityChart = new Chart(userActivityCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Logins',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Cases Created',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Case Statistics Chart
            const caseStatsCtx = document.getElementById('caseStatsChart').getContext('2d');
            caseStatsChart = new Chart(caseStatsCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Monthly Trends Chart
            const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'New Users',
                        data: [],
                        backgroundColor: '#3b82f6'
                    }, {
                        label: 'New Cases',
                        data: [],
                        backgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        async function loadDashboardData() {
            try {
                const dateRange = document.getElementById('dateRange').value;
                
                // Load key metrics
                const statsResponse = await fetch('../api/admin-analytics.php?action=dashboard_stats');
                const statsData = await statsResponse.json();
                
                // Update detailed table with latest metrics
                if (statsData.success) {
                    updateDetailedTable(statsData.data || {});
                }
                
                // Load user activity data
                const activityResponse = await fetch(`../api/admin-analytics.php?action=user_activity&days=${dateRange}`);
                const activityData = await activityResponse.json();
                
                if (activityData.success && activityData.data && activityData.data.length > 0) {
                    updateUserActivityChart(activityData.data);
                }
                
                // Load case statistics
                const caseResponse = await fetch('../api/admin-analytics.php?action=case_statistics');
                const caseData = await caseResponse.json();
                
                if (caseData.success && caseData.data && caseData.data.length > 0) {
                    updateCaseStatsChart(caseData.data);
                }
                
                // Load service performance
                const serviceResponse = await fetch('../api/admin-analytics.php?action=service_performance');
                const serviceData = await serviceResponse.json();
                
                if (serviceData.success && serviceData.data && serviceData.data.length > 0) {
                    updateServicePerformance(serviceData.data);
                } else {
                    // Check if we have PHP-loaded data
                    const phpServiceData = <?php echo json_encode($service_performance); ?>;
                    if (phpServiceData && phpServiceData.length > 0) {
                        updateServicePerformance(phpServiceData);
                    } else {
                        document.getElementById('servicePerformance').innerHTML = '<p class="text-muted text-center">No service data available</p>';
                    }
                }
                
                // Load monthly trends
                const trendsResponse = await fetch('../api/admin-analytics.php?action=monthly_trends');
                const trendsData = await trendsResponse.json();
                
                if (trendsData.success && trendsData.data && trendsData.data.length > 0) {
                    updateMonthlyTrendsChart(trendsData.data);
                } else {
                    // Use PHP-loaded data as fallback
                    const phpTrendsData = <?php echo json_encode($monthly_trends); ?>;
                    if (phpTrendsData && phpTrendsData.length > 0) {
                        updateMonthlyTrendsChart(phpTrendsData);
                    }
                }
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                // Don't show alert on initial load to avoid annoying users
            }
        }
        
        function updateDetailedTable(data) {
            const tbody = document.getElementById('detailedTableBody');
            if (!tbody) return;
            
            // Get metrics from PHP (already loaded on page) - use actual values
            const phpMetrics = {
                total_users: <?php echo (int)$metrics['total_users']; ?>,
                active_cases: <?php echo (int)$metrics['active_cases']; ?>,
                pending_requests: <?php echo (int)$metrics['pending_requests']; ?>,
                avg_processing_time: <?php echo (int)$metrics['avg_processing_time']; ?>
            };
            
            // Merge with API data if available, but prioritize PHP data which is already loaded
            const metrics = {
                total_users: data.total_users !== undefined ? data.total_users : phpMetrics.total_users,
                active_cases: data.active_cases !== undefined ? data.active_cases : phpMetrics.active_cases,
                pending_requests: data.pending_requests !== undefined ? data.pending_requests : phpMetrics.pending_requests,
                avg_processing_hours: data.avg_processing_hours !== undefined ? data.avg_processing_hours : phpMetrics.avg_processing_time,
                active_users_today: data.active_users_today || 0,
                active_users_week: data.active_users_week || 0
            };
            
            const rows = [
                ['Total Active Users', metrics.total_users || 0, 'N/A', 'stable'],
                ['Active Cases', metrics.active_cases || 0, 'N/A', 'stable'],
                ['Pending Requests', metrics.pending_requests || 0, 'N/A', 'stable'],
                ['Avg Processing Time', (metrics.avg_processing_hours ? Math.round(metrics.avg_processing_hours) + 'h' : '0h'), 'N/A', 'stable'],
                ['Active Users Today', metrics.active_users_today || 0, 'N/A', 'stable'],
                ['Active Users (7 days)', metrics.active_users_week || 0, 'N/A', 'stable']
            ];
            
            tbody.innerHTML = rows.map(row => `
                <tr>
                    <td><strong>${row[0]}</strong></td>
                    <td>${row[1]}</td>
                    <td><span class="text-muted">${row[2]}</span></td>
                    <td><span class="badge bg-secondary">${row[3]}</span></td>
                </tr>
            `).join('');
        }
        
        function updateUserActivityChart(data) {
            if (!data || data.length === 0) {
                // Show empty state
                userActivityChart.data.labels = [];
                userActivityChart.data.datasets[0].data = [];
                userActivityChart.data.datasets[1].data = [];
                userActivityChart.update();
                return;
            }
            
            const loginData = {};
            const caseData = {};
            
            // Process data - handle both date format (YYYY-MM-DD) and month format (YYYY-MM)
            data.forEach(item => {
                const dateKey = item.date || item.month;
                if (!dateKey) return;
                
                if (item.event_type === 'login' || item.event_type === 'user_login') {
                    loginData[dateKey] = (loginData[dateKey] || 0) + parseInt(item.count || 0);
                } else if (item.event_type === 'case_created' || item.event_type === 'create') {
                    caseData[dateKey] = (caseData[dateKey] || 0) + parseInt(item.count || 0);
                }
            });
            
            // Get all unique dates and sort them
            const allDates = [...new Set([...Object.keys(loginData), ...Object.keys(caseData)])].sort();
            
            if (allDates.length === 0) {
                // Fallback to case trends if available
                const caseTrendsData = <?php echo json_encode($case_trends); ?>;
                if (caseTrendsData && caseTrendsData.length > 0) {
                    const months = caseTrendsData.map(item => item.month);
                    const counts = caseTrendsData.map(item => parseInt(item.count));
                    userActivityChart.data.labels = months;
                    userActivityChart.data.datasets[0].data = counts;
                    userActivityChart.data.datasets[1].data = counts.map(() => 0);
                    userActivityChart.update();
                }
                return;
            }
            
            userActivityChart.data.labels = allDates;
            userActivityChart.data.datasets[0].data = allDates.map(date => loginData[date] || 0);
            userActivityChart.data.datasets[1].data = allDates.map(date => caseData[date] || 0);
            userActivityChart.update();
        }
        
        function updateCaseStatsChart(data) {
            const caseTypes = {};
            
            data.forEach(item => {
                if (!caseTypes[item.case_type]) {
                    caseTypes[item.case_type] = 0;
                }
                caseTypes[item.case_type] += parseInt(item.count);
            });
            
            caseStatsChart.data.labels = Object.keys(caseTypes);
            caseStatsChart.data.datasets[0].data = Object.values(caseTypes);
            caseStatsChart.update();
        }
        
        function updateServicePerformance(data) {
            const container = document.getElementById('servicePerformance');
            
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No service data available</p>';
                return;
            }
            
            let html = '';
            data.slice(0, 5).forEach(service => {
                const approvalRate = service.total_requests > 0 
                    ? Math.round((service.approved / service.total_requests) * 100) 
                    : 0;
                
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="mb-1">${service.name}</h6>
                            <small class="text-muted">${service.category}</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary">${service.total_requests} requests</div>
                            <div class="small text-muted mt-1">${approvalRate}% approved</div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function updateMonthlyTrendsChart(data) {
            const months = data.map(item => item.month).reverse();
            const newUsers = data.map(item => parseInt(item.new_users)).reverse();
            const newCases = data.map(item => parseInt(item.new_cases)).reverse();
            
            monthlyTrendsChart.data.labels = months;
            monthlyTrendsChart.data.datasets[0].data = newUsers;
            monthlyTrendsChart.data.datasets[1].data = newCases;
            monthlyTrendsChart.update();
        }
        
        function refreshData() {
            loadDashboardData();
        }
        
        function exportReport() {
            <?php if (!has_permission('report:export')): ?>
            showAlert('You do not have permission to export reports.', 'error');
            return;
            <?php endif; ?>
            
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            
            const exportUrl = `../api/export-report.php?type=${reportType}&days=${dateRange}`;
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = `merlaws-report-${reportType}-${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        }
        
        function generateCustomReport() {
            <?php if (!has_permission('report:create')): ?>
            showAlert('You do not have permission to create custom reports.', 'error');
            return;
            <?php endif; ?>
            
            // Open modal or redirect to custom report builder
            const reportTypes = {
                'user_summary': 'users',
                'case_outcomes': 'cases',
                'financial': 'financial',
                'system_health': 'system_health'
            };
            
            // For now, show options
            const selectedType = prompt('Select report type:\n1. User Summary\n2. Case Outcomes\n3. Financial\n4. System Health\n\nEnter number (1-4):');
            const typeMap = {'1': 'user_summary', '2': 'case_outcomes', '3': 'financial', '4': 'system_health'};
            const selected = typeMap[selectedType];
            
            if (selected) {
                generateReport(selected);
            }
        }
        
        async function generateReport(type) {
            const reportTypes = {
                'user_summary': 'users',
                'case_outcomes': 'cases',
                'financial': 'financial',
                'system_health': 'system_health'
            };
            
            const reportType = reportTypes[type] || type;
            const dateRange = document.getElementById('dateRange').value;
            
            showAlert(`Generating ${type.replace('_', ' ')} report...`, 'info');
            
            try {
                // First, fetch JSON version to show preview
                const previewUrl = `../api/export-report.php?type=${reportType}&days=${dateRange}&format=json`;
                const response = await fetch(previewUrl);
                
                if (response.ok) {
                    const reportData = await response.json();
                    
                    // Show preview modal
                    showReportPreview(reportData, type, reportType, dateRange);
                } else {
                    // If JSON fails, just export CSV
                    exportReportCSV(reportType, dateRange);
                }
            } catch (error) {
                // If preview fails, just export CSV
                exportReportCSV(reportType, dateRange);
            }
        }
        
        function exportReportCSV(reportType, dateRange) {
            const exportUrl = `../api/export-report.php?type=${reportType}&days=${dateRange}&format=csv`;
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = `merlaws-report-${reportType}-${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            
            setTimeout(() => {
                showAlert(`Report exported successfully!`, 'success');
            }, 1000);
        }
        
        function showReportPreview(reportData, type, reportType, dateRange) {
            // Create or get modal
            let modal = document.getElementById('reportPreviewModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'reportPreviewModal';
                modal.innerHTML = `
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Report Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="reportPreviewContent">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success" onclick="exportReportCSV('${reportType}', ${dateRange})">
                                    <i class="fas fa-download me-2"></i>Export to CSV
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            
            const content = document.getElementById('reportPreviewContent');
            const headers = reportData.headers || [];
            const data = reportData.data || [];
            
            let html = `
                <div class="mb-3">
                    <h6>Report: ${type.replace('_', ' ').toUpperCase()}</h6>
                    <p class="text-muted">Date Range: ${reportData.date_range?.from || 'N/A'} to ${reportData.date_range?.to || 'N/A'} (${reportData.date_range?.days || 0} days)</p>
                    <p class="text-muted">Generated: ${reportData.generated_at || 'N/A'}</p>
                    <p class="text-muted">Total Records: ${data.length}</p>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
            `;
            
            headers.forEach(header => {
                html += `<th>${header}</th>`;
            });
            
            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (data.length === 0) {
                html += `<tr><td colspan="${headers.length}" class="text-center text-muted">No data available</td></tr>`;
            } else {
                data.slice(0, 100).forEach(row => {
                    html += '<tr>';
                    row.forEach(cell => {
                        html += `<td>${cell || ''}</td>`;
                    });
                    html += '</tr>';
                });
                if (data.length > 100) {
                    html += `<tr><td colspan="${headers.length}" class="text-center text-muted"><em>... and ${data.length - 100} more rows (export CSV to see all)</em></td></tr>`;
                }
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            content.innerHTML = html;
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
        
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
    <script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>