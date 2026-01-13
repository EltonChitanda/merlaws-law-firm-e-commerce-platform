<?php
// app/api/export-report.php
// FIXED VERSION - Clean CSV exports without HTML

// Disable all output buffering and error display
while (ob_get_level()) {
    ob_end_clean();
}

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require __DIR__ . '/../config.php';
require_admin();

if (!has_permission('report:export')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    die('Access Denied: You do not have permission to export reports.');
}

$pdo = db();
$user_id = get_user_id();
$user_role = get_user_role();

// Get parameters
$report_type = $_GET['type'] ?? 'overview';
$days = (int)($_GET['days'] ?? 30);
$format = $_GET['format'] ?? 'csv';

// Date range
$date_from = date('Y-m-d', strtotime("-{$days} days"));
$date_to = date('Y-m-d');

// ENHANCED: Comprehensive data cleaning function
function clean_csv_value($value) {
    // Handle null, false, empty
    if ($value === null || $value === false || $value === '') {
        return '';
    }
    
    // Convert to string
    $value = (string)$value;
    
    // Step 1: Strip ALL HTML tags completely
    $value = strip_tags($value);
    
    // Step 2: Decode ALL HTML entities (do this multiple times to catch nested entities)
    for ($i = 0; $i < 3; $i++) {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    // Step 3: Remove any remaining HTML entity patterns
    $value = preg_replace('/&[#a-zA-Z0-9]+;/', '', $value);
    
    // Step 4: Remove XML/HTML-like tags that might have been missed
    $value = preg_replace('/<[^>]*>/', '', $value);
    
    // Step 5: Remove control characters (except newlines and tabs which we'll handle)
    $value = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F-\x9F]/', '', $value);
    
    // Step 6: Replace newlines and tabs with spaces for CSV compatibility
    $value = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $value);
    
    // Step 7: Remove multiple consecutive spaces
    $value = preg_replace('/\s+/', ' ', $value);
    
    // Step 8: Trim whitespace
    $value = trim($value);
    
    // Step 9: Remove any remaining suspicious characters
    $value = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $value);
    
    return $value;
}

// Format currency values
function format_currency($amount) {
    if ($amount === null || $amount === '') {
        return 'R 0.00';
    }
    return 'R ' . number_format((float)$amount, 2, '.', ',');
}

// Format dates consistently
function format_date($date, $include_time = false) {
    if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    try {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return 'N/A';
        }
        return date($include_time ? 'Y-m-d H:i' : 'Y-m-d', $timestamp);
    } catch (Exception $e) {
        return 'N/A';
    }
}

// Role-based filtering
$case_filter_sql = "";
$case_filter_params = [];

if (in_array($user_role, ['attorney', 'paralegal'])) {
    $case_filter_sql = " AND c.assigned_to = ?";
    $case_filter_params[] = $user_id;
} elseif ($user_role === 'billing') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM service_requests sr WHERE sr.case_id = c.id AND sr.status != 'cart')";
} elseif ($user_role === 'doc_specialist') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM case_documents cd WHERE cd.case_id = c.id)";
} elseif ($user_role === 'receptionist') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM appointments a WHERE a.case_id = c.id)";
} elseif ($user_role === 'compliance') {
    $case_filter_sql = " AND EXISTS (SELECT 1 FROM compliance_requests cr WHERE cr.case_id = c.id)";
}

try {
    $data = [];
    $headers = [];
    $report_title = '';
    
    switch ($report_type) {
        case 'overview':
            $report_title = 'System Overview Report';
            $headers = ['Metric', 'Value', 'Period', 'Date Generated'];
            
            // Total users (if allowed)
            if (in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1 AND created_at >= ?");
                $stmt->execute([$date_from]);
                $data[] = [
                    'Total Active Users',
                    clean_csv_value($stmt->fetchColumn()),
                    "Last {$days} days",
                    date('Y-m-d H:i:s')
                ];
            }
            
            // Active cases
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases c WHERE c.status IN ('active', 'under_review') AND c.created_at >= ?" . $case_filter_sql);
            $params = array_merge([$date_from], $case_filter_params);
            $stmt->execute($params);
            $data[] = [
                'Active Cases',
                clean_csv_value($stmt->fetchColumn()),
                "Last {$days} days",
                date('Y-m-d H:i:s')
            ];
            
            // Pending requests
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests sr JOIN cases c ON sr.case_id = c.id WHERE sr.status = 'pending' AND sr.requested_at >= ?" . $case_filter_sql);
            $stmt->execute($params);
            $data[] = [
                'Pending Service Requests',
                clean_csv_value($stmt->fetchColumn()),
                "Last {$days} days",
                date('Y-m-d H:i:s')
            ];
            
            // Completed cases
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases c WHERE c.status IN ('resolved', 'closed') AND c.updated_at >= ?" . $case_filter_sql);
            $stmt->execute($params);
            $data[] = [
                'Completed Cases',
                clean_csv_value($stmt->fetchColumn()),
                "Last {$days} days",
                date('Y-m-d H:i:s')
            ];
            
            // Financial data (if allowed)
            if (in_array($user_role, ['billing', 'partner', 'super_admin'])) {
                $stmt = $pdo->prepare("
                    SELECT 
                        SUM(CASE WHEN i.status = 'paid' THEN i.amount ELSE 0 END) as revenue,
                        SUM(CASE WHEN i.status = 'pending' THEN i.amount ELSE 0 END) as pending,
                        COUNT(CASE WHEN i.status = 'overdue' THEN 1 END) as overdue_count
                    FROM invoices i
                    JOIN cases c ON i.case_id = c.id
                    WHERE i.created_at >= ?
                " . $case_filter_sql);
                $stmt->execute($params);
                $financial = $stmt->fetch();
                
                $data[] = [
                    'Total Revenue (Paid Invoices)',
                    format_currency($financial['revenue'] ?? 0),
                    "Last {$days} days",
                    date('Y-m-d H:i:s')
                ];
                $data[] = [
                    'Pending Payments',
                    format_currency($financial['pending'] ?? 0),
                    "Last {$days} days",
                    date('Y-m-d H:i:s')
                ];
                $data[] = [
                    'Overdue Invoices Count',
                    clean_csv_value($financial['overdue_count'] ?? 0),
                    "Last {$days} days",
                    date('Y-m-d H:i:s')
                ];
            }
            break;
            
        case 'users':
            $report_title = 'User Activity Report';
            $headers = ['User ID', 'Full Name', 'Email Address', 'Role', 'Last Login', 'Total Logins', 'Cases Created', 'Account Status', 'Registration Date'];
            
            $sql = "
                SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.role,
                    u.last_login,
                    (SELECT COUNT(*) FROM audit_logs al WHERE al.user_id = u.id AND al.event_type = 'login' AND al.created_at >= ?) as login_count,
                    (SELECT COUNT(*) FROM cases c WHERE c.created_by = u.id AND c.created_at >= ?) as cases_created,
                    CASE WHEN u.is_active = 1 THEN 'Active' ELSE 'Inactive' END as status,
                    u.created_at
                FROM users u
                WHERE u.created_at >= ? OR u.last_login >= ?
                ORDER BY u.last_login DESC
            ";
            
            if (!in_array($user_role, ['super_admin', 'partner', 'office_admin'])) {
                $sql .= " AND u.id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date_from, $date_from, $date_from, $date_from, $user_id]);
            } else {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date_from, $date_from, $date_from, $date_from]);
            }
            
            while ($row = $stmt->fetch()) {
                $data[] = [
                    clean_csv_value($row['id']),
                    clean_csv_value($row['name']),
                    clean_csv_value($row['email']),
                    ucfirst(str_replace('_', ' ', clean_csv_value($row['role']))),
                    format_date($row['last_login'], true),
                    clean_csv_value($row['login_count'] ?? 0),
                    clean_csv_value($row['cases_created'] ?? 0),
                    clean_csv_value($row['status']),
                    format_date($row['created_at'])
                ];
            }
            break;
            
        case 'cases':
            $report_title = 'Case Statistics Report';
            $headers = ['Case ID', 'Case Title', 'Case Type', 'Status', 'Client Name', 'Assigned Attorney', 'Created Date', 'Last Updated', 'Days Open', 'Priority'];
            
            $sql = "
                SELECT 
                    c.id,
                    c.title,
                    c.case_type,
                    c.status,
                    u1.name as created_by_name,
                    u2.name as assigned_to_name,
                    c.created_at,
                    c.updated_at,
                    TIMESTAMPDIFF(DAY, c.created_at, COALESCE(c.updated_at, NOW())) as days_open,
                    COALESCE(c.priority, 'normal') as priority
                FROM cases c
                LEFT JOIN users u1 ON c.created_by = u1.id
                LEFT JOIN users u2 ON c.assigned_to = u2.id
                WHERE c.created_at >= ?
            " . $case_filter_sql . " ORDER BY c.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$date_from], $case_filter_params);
            $stmt->execute($params);
            
            while ($row = $stmt->fetch()) {
                $data[] = [
                    clean_csv_value($row['id']),
                    clean_csv_value($row['title']),
                    ucfirst(str_replace('_', ' ', clean_csv_value($row['case_type']))),
                    ucfirst(str_replace('_', ' ', clean_csv_value($row['status']))),
                    clean_csv_value($row['created_by_name'] ?? 'Unknown'),
                    clean_csv_value($row['assigned_to_name'] ?? 'Unassigned'),
                    format_date($row['created_at']),
                    format_date($row['updated_at']),
                    clean_csv_value($row['days_open']),
                    ucfirst(clean_csv_value($row['priority']))
                ];
            }
            break;
            
        case 'services':
            $report_title = 'Service Performance Report';
            $headers = ['Service ID', 'Service Name', 'Category', 'Total Requests', 'Approved', 'Rejected', 'Pending', 'Approval Rate', 'Avg Processing Time (Hours)', 'Total Revenue'];
            
            $sql = "
                SELECT 
                    s.id,
                    s.name,
                    s.category,
                    COUNT(sr.id) as total_requests,
                    COUNT(CASE WHEN sr.status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN sr.status = 'rejected' THEN 1 END) as rejected,
                    COUNT(CASE WHEN sr.status = 'pending' THEN 1 END) as pending,
                    AVG(CASE WHEN sr.processed_at IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, sr.requested_at, sr.processed_at) END) as avg_hours,
                    SUM(CASE WHEN i.status = 'paid' THEN i.amount ELSE 0 END) as revenue
                FROM services s
                LEFT JOIN service_requests sr ON s.id = sr.service_id AND sr.requested_at >= ?
                LEFT JOIN invoices i ON sr.id = i.service_request_id
                GROUP BY s.id, s.name, s.category
                HAVING total_requests > 0
                ORDER BY total_requests DESC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_from]);
            
            while ($row = $stmt->fetch()) {
                $total = (int)$row['total_requests'];
                $approved = (int)$row['approved'];
                $approval_rate = $total > 0 ? round(($approved / $total) * 100, 1) . '%' : 'N/A';
                
                $data[] = [
                    clean_csv_value($row['id']),
                    clean_csv_value($row['name']),
                    ucfirst(str_replace('_', ' ', clean_csv_value($row['category']))),
                    clean_csv_value($total),
                    clean_csv_value($approved),
                    clean_csv_value($row['rejected']),
                    clean_csv_value($row['pending']),
                    $approval_rate,
                    $row['avg_hours'] ? round($row['avg_hours'], 1) : 'N/A',
                    format_currency($row['revenue'] ?? 0)
                ];
            }
            break;
            
        case 'financial':
            if (!in_array($user_role, ['billing', 'partner', 'super_admin'])) {
                http_response_code(403);
                die('Access Denied: You do not have permission to export financial reports.');
            }
            
            $report_title = 'Financial Report';
            $headers = ['Invoice ID', 'Case ID', 'Case Title', 'Client Name', 'Amount', 'Status', 'Issue Date', 'Due Date', 'Paid Date', 'Days Overdue', 'Payment Method'];
            
            $sql = "
                SELECT 
                    i.id,
                    i.case_id,
                    c.title as case_title,
                    u.name as client_name,
                    i.amount,
                    i.status,
                    i.created_at as issue_date,
                    i.due_date,
                    i.paid_at,
                    CASE 
                        WHEN i.status = 'overdue' THEN TIMESTAMPDIFF(DAY, i.due_date, NOW())
                        ELSE 0
                    END as days_overdue,
                    i.payment_method
                FROM invoices i
                JOIN cases c ON i.case_id = c.id
                LEFT JOIN users u ON c.created_by = u.id
                WHERE i.created_at >= ?
            " . $case_filter_sql . " ORDER BY i.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$date_from], $case_filter_params);
            $stmt->execute($params);
            
            while ($row = $stmt->fetch()) {
                $data[] = [
                    clean_csv_value($row['id']),
                    clean_csv_value($row['case_id']),
                    clean_csv_value($row['case_title']),
                    clean_csv_value($row['client_name'] ?? 'Unknown'),
                    format_currency($row['amount']),
                    ucfirst(clean_csv_value($row['status'])),
                    format_date($row['issue_date']),
                    format_date($row['due_date']),
                    format_date($row['paid_at']),
                    clean_csv_value($row['days_overdue']),
                    ucfirst(str_replace('_', ' ', clean_csv_value($row['payment_method'] ?? 'N/A')))
                ];
            }
            break;
            
        case 'system_health':
            $report_title = 'System Health Report';
            $headers = ['Metric', 'Value', 'Status', 'Description', 'Last Checked'];
            
            // Database size
            $stmt = $pdo->query("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            $db_size = $stmt->fetchColumn();
            $data[] = [
                'Database Size',
                clean_csv_value($db_size) . ' MB',
                $db_size < 1000 ? 'Healthy' : 'Warning',
                'Total size of all database tables',
                date('Y-m-d H:i:s')
            ];
            
            // Total audit logs
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE created_at >= ?");
                $stmt->execute([$date_from]);
                $audit_count = $stmt->fetchColumn();
                $data[] = [
                    'Audit Logs',
                    clean_csv_value($audit_count),
                    'Healthy',
                    "Total audit entries in last {$days} days",
                    date('Y-m-d H:i:s')
                ];
            } catch (Exception $e) {
                $data[] = ['Audit Logs', 'N/A', 'Error', 'Unable to retrieve audit logs', date('Y-m-d H:i:s')];
            }
            
            // Active users today
            try {
                $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM audit_logs WHERE event_type = 'login' AND DATE(created_at) = CURDATE()");
                $active_today = $stmt->fetchColumn();
                $data[] = [
                    'Active Users Today',
                    clean_csv_value($active_today),
                    'Healthy',
                    'Unique users who logged in today',
                    date('Y-m-d H:i:s')
                ];
            } catch (Exception $e) {
                $data[] = ['Active Users Today', 'N/A', 'Error', 'Unable to retrieve login data', date('Y-m-d H:i:s')];
            }
            
            // Failed logins
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE event_type = 'login' AND status = 'failure' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $failed_logins = $stmt->fetchColumn();
                $data[] = [
                    'Failed Login Attempts (24h)',
                    clean_csv_value($failed_logins),
                    $failed_logins < 10 ? 'Healthy' : 'Warning',
                    'Failed login attempts in last 24 hours',
                    date('Y-m-d H:i:s')
                ];
            } catch (Exception $e) {
                $data[] = ['Failed Login Attempts', 'N/A', 'Error', 'Unable to retrieve login data', date('Y-m-d H:i:s')];
            }
            
            // Total cases
            $stmt = $pdo->query("SELECT COUNT(*) FROM cases");
            $total_cases = $stmt->fetchColumn();
            $data[] = [
                'Total Cases',
                clean_csv_value($total_cases),
                'Info',
                'Total number of cases in system',
                date('Y-m-d H:i:s')
            ];
            break;
            
        default:
            http_response_code(400);
            die('Invalid report type: ' . htmlspecialchars($report_type));
    }
    
    // Log report export
    try {
        log_audit_event('export', 'report_exported', "Report exported: {$report_type}", [
            'category' => 'report',
            'metadata' => [
                'report_type' => $report_type,
                'days' => $days,
                'format' => $format,
                'rows' => count($data),
                'user_role' => $user_role
            ]
        ]);
    } catch (Exception $e) {
        // Continue even if logging fails
    }
    
    // Export based on format
    if ($format === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="merlaws-report-' . $report_type . '-' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode([
            'report_title' => $report_title,
            'report_type' => $report_type,
            'date_range' => [
                'from' => $date_from,
                'to' => $date_to,
                'days' => $days
            ],
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => [
                'user_id' => $user_id,
                'role' => $user_role
            ],
            'headers' => $headers,
            'data' => $data,
            'total_rows' => count($data)
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        // CSV export
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="merlaws-report-' . $report_type . '-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Open output stream
        $out = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write report metadata as comments (CSV-safe)
        fputcsv($out, ['Med Attorneys - ' . $report_title]);
        fputcsv($out, ['Generated: ' . date('Y-m-d H:i:s')]);
        fputcsv($out, ['Period: ' . $date_from . ' to ' . $date_to . ' (' . $days . ' days)']);
        fputcsv($out, ['Total Records: ' . count($data)]);
        fputcsv($out, []); // Empty row
        
        // Write headers
        fputcsv($out, $headers, ',', '"');
        
        // Write data rows
        foreach ($data as $row) {
            fputcsv($out, $row, ',', '"');
        }
        
        // Add footer
        fputcsv($out, []);
        fputcsv($out, ['End of Report']);
        
        fclose($out);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    if ($format === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'error' => 'Report generation failed',
            'message' => $e->getMessage(),
            'report_type' => $report_type
        ]);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Report generation failed: ' . $e->getMessage();
    }
    exit;
}