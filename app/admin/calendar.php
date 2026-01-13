<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('appointment:view');

$pdo = db();
$user_id = get_user_id();
$user_role = get_user_role();
$errors = [];
$success = '';

// Handle appointment request actions (accept/reject)
if (is_post() && isset($_POST['action']) && isset($_POST['request_id'])) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = $_POST['action'];
        $request_id = (int)$_POST['request_id'];
        $notes = trim($_POST['notes'] ?? '');
        
        try {
            // Check if appointment_requests table exists
            $table_check = $pdo->query("SHOW TABLES LIKE 'appointment_requests'");
            $table_exists = $table_check->rowCount() > 0;
            
            if (!$table_exists) {
                $errors[] = 'Appointment requests feature is not available. Please run the database migration.';
            } else {
                // Get the appointment request
                $stmt = $pdo->prepare("SELECT ar.*, c.id as case_id, c.user_id as client_id, c.title as case_title, c.assigned_to 
                                       FROM appointment_requests ar 
                                       JOIN cases c ON ar.case_id = c.id 
                                       WHERE ar.id = ? AND ar.status = 'pending'");
                $stmt->execute([$request_id]);
                $request = $stmt->fetch();
            
                if (!$request) {
                    $errors[] = 'Appointment request not found or already processed.';
                } else {
                    // Check if user has access to this case
                    $has_access = false;
                    if ($user_role === 'super_admin' || in_array($user_role, ['partner', 'case_manager', 'office_admin'])) {
                        $has_access = true;
                    } elseif ($request['assigned_to'] == $user_id) {
                        $has_access = true;
                    } else {
                        $case_ids = get_user_cases_access($user_id, $user_role);
                        $has_access = in_array($request['case_id'], $case_ids);
                    }
                    
                    if (!$has_access) {
                        $errors[] = 'You do not have permission to process this appointment request.';
                    } else {
                        if ($action === 'accept') {
                            // Create approved appointment
                            // Check if type column exists, if not, don't include it
                            $checkType = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'type'");
                            $hasType = $checkType->rowCount() > 0;
                            
                            if ($hasType) {
                                $stmt = $pdo->prepare("INSERT INTO appointments (case_id, title, type, start_time, end_time, location, status, assigned_to, created_at) 
                                                       VALUES (?, ?, ?, ?, ?, ?, 'approved', ?, NOW())");
                                $stmt->execute([
                                    $request['case_id'],
                                    $request['title'] ?? 'Appointment',
                                    $request['type'] ?? 'consultation',
                                    $request['preferred_date'] . ' ' . ($request['preferred_time'] ?? '09:00:00'),
                                    $request['preferred_date'] . ' ' . ($request['preferred_time_end'] ?? '10:00:00'),
                                    $request['location'] ?? 'TBD',
                                    $request['assigned_to'] ?? $user_id
                                ]);
                            } else {
                                // Fallback if type column doesn't exist
                                $stmt = $pdo->prepare("INSERT INTO appointments (case_id, title, start_time, end_time, location, status, assigned_to, created_at) 
                                                       VALUES (?, ?, ?, ?, ?, 'approved', ?, NOW())");
                                $stmt->execute([
                                    $request['case_id'],
                                    $request['title'] ?? 'Appointment',
                                    $request['preferred_date'] . ' ' . ($request['preferred_time'] ?? '09:00:00'),
                                    $request['preferred_date'] . ' ' . ($request['preferred_time_end'] ?? '10:00:00'),
                                    $request['location'] ?? 'TBD',
                                    $request['assigned_to'] ?? $user_id
                                ]);
                            }
                            
                            // Update request status
                            $stmt = $pdo->prepare("UPDATE appointment_requests SET status = 'approved', processed_by = ?, processed_at = NOW(), admin_notes = ? WHERE id = ?");
                            $stmt->execute([$user_id, $notes, $request_id]);
                            
                        // Notify client
                        $title = 'Appointment Request Approved';
                        $appointmentDate = $request['preferred_date'] ? date('l, F j, Y', strtotime($request['preferred_date'])) : 'scheduled date';
                        $appointmentTime = $request['preferred_time'] ? date('g:i A', strtotime($request['preferred_time'])) : 'scheduled time';
                        $msg = 'Your appointment request for "' . ($request['case_title'] ?? 'Case') . '" has been approved.';
                        $msg .= ' Your appointment is scheduled for ' . $appointmentDate . ' at ' . $appointmentTime . '.';
                        if ($notes) {
                            $msg .= ' Notes: ' . $notes;
                        }
                        $msg .= ' You can view all your scheduled appointments in the Appointments section.';
                        create_user_notification((int)$request['client_id'], 'appointment_update', $title, $msg, '/app/appointments/index.php');
                            
                            // Redirect to refresh the page and show the new appointment
                            $redirectView = $_GET['view'] ?? 'week';
                            $redirectDate = $_GET['date'] ?? date('Y-m-d');
                            header('Location: calendar.php?view=' . urlencode($redirectView) . '&date=' . urlencode($redirectDate));
                            exit;
                        } elseif ($action === 'reject') {
                            // Update request status
                            $stmt = $pdo->prepare("UPDATE appointment_requests SET status = 'rejected', processed_by = ?, processed_at = NOW(), admin_notes = ? WHERE id = ?");
                            $stmt->execute([$user_id, $notes, $request_id]);
                            
                        // Notify client
                        $title = 'Appointment Request Rejected';
                        $msg = 'Your appointment request for "' . ($request['case_title'] ?? 'Case') . '" has been rejected.';
                        if ($notes) {
                            $msg .= ' Reason: ' . $notes;
                        } else {
                            $msg .= ' Please contact your case manager or submit a new appointment request.';
                        }
                        create_user_notification((int)$request['client_id'], 'appointment_update', $title, $msg, '/app/appointments/create.php');
                            
                            $success = 'Appointment request rejected and client notified.';
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Get current date and calculate calendar view
$current_date = date('Y-m-d');
$view_mode = $_GET['view'] ?? 'week'; // day, week, month
$selected_date = $_GET['date'] ?? $current_date;

// Filter by attorney/room if specified
$filter_attorney = $_GET['attorney'] ?? '';
$filter_room = $_GET['room'] ?? '';
$filter_type = $_GET['type'] ?? '';

// Get appointment requests for cases assigned to this admin
$appointment_requests = [];
try {
    // Check if appointment_requests table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'appointment_requests'");
    $table_exists = $table_check->rowCount() > 0;
    
    if ($table_exists) {
        $request_sql = "SELECT ar.*, c.title as case_title, u.name as client_name, u.email as client_email, au.name as assigned_to_name
                        FROM appointment_requests ar
                        JOIN cases c ON ar.case_id = c.id
                        JOIN users u ON c.user_id = u.id
                        LEFT JOIN users au ON c.assigned_to = au.id
                        WHERE ar.status = 'pending'";
        $request_params = [];

        // Filter requests based on role and case assignments
        if (in_array($user_role, ['attorney', 'paralegal'])) {
            $request_sql .= " AND c.assigned_to = ?";
            $request_params[] = $user_id;
        } elseif (!in_array($user_role, ['super_admin', 'partner', 'case_manager', 'office_admin', 'receptionist'])) {
            $case_ids = get_user_cases_access($user_id, $user_role);
            if (!empty($case_ids)) {
                $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
                $request_sql .= " AND ar.case_id IN ($placeholders)";
                $request_params = array_merge($request_params, $case_ids);
            } else {
                $request_sql .= " AND 1=0";
            }
        }

        $request_sql .= " ORDER BY ar.created_at DESC";
        $stmt = $pdo->prepare($request_sql);
        $stmt->execute($request_params);
        $appointment_requests = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Table doesn't exist or error occurred, continue with empty array
    $appointment_requests = [];
    error_log("Appointment requests table error: " . $e->getMessage());
}

// Get real appointments from database with role-based filtering (only approved and upcoming)
// Get ALL upcoming approved appointments for the list view (not filtered by selected_date)
$sql = "SELECT a.*, c.title as case_title, u.name as client_name, au.name as assigned_to_name
        FROM appointments a
        JOIN cases c ON a.case_id = c.id
        JOIN users u ON c.user_id = u.id
        LEFT JOIN users au ON a.assigned_to = au.id
        WHERE a.status = 'approved' AND a.start_time >= NOW()";
$params = [];

// Apply role-based filtering for approved appointments
if (in_array($user_role, ['attorney', 'paralegal'])) {
    // Attorneys and paralegals only see their assigned appointments
    $sql .= " AND a.assigned_to = ?";
    $params[] = $user_id;
} elseif ($user_role === 'receptionist') {
    // Receptionists see all appointments
    // No additional filter needed
} elseif (in_array($user_role, ['super_admin', 'partner', 'case_manager', 'office_admin'])) {
    // Management roles see all appointments
    // No additional filter needed
} else {
    // Other roles see appointments for cases they have access to
    $case_ids = get_user_cases_access($user_id, $user_role);
    if (!empty($case_ids)) {
        $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
        $sql .= " AND a.case_id IN ($placeholders)";
        $params = array_merge($params, $case_ids);
    } else {
        // No access to any cases, return empty result
        $sql .= " AND 1=0";
    }
}

$sql .= " ORDER BY a.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Also get appointments for the selected date (for monthly calendar view)
$appointments_for_date = [];
if ($selected_date) {
    $sql_date = "SELECT a.*, c.title as case_title, u.name as client_name, au.name as assigned_to_name
            FROM appointments a
            JOIN cases c ON a.case_id = c.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN users au ON a.assigned_to = au.id
            WHERE a.status = 'approved' AND DATE(a.start_time) = ?";
    $params_date = [$selected_date];
    
    // Apply same role-based filtering
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $sql_date .= " AND a.assigned_to = ?";
        $params_date[] = $user_id;
    } elseif (!in_array($user_role, ['receptionist', 'super_admin', 'partner', 'case_manager', 'office_admin'])) {
        $case_ids = get_user_cases_access($user_id, $user_role);
        if (!empty($case_ids)) {
            $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
            $sql_date .= " AND a.case_id IN ($placeholders)";
            $params_date = array_merge($params_date, $case_ids);
        } else {
            $sql_date .= " AND 1=0";
        }
    }
    
    $stmt_date = $pdo->prepare($sql_date);
    $stmt_date->execute($params_date);
    $appointments_for_date = $stmt_date->fetchAll();
}

// Transform appointments to expected format
$appointments = array_map(function($apt) {
    $start_time = strtotime($apt['start_time']);
    $end_time = strtotime($apt['end_time']);
    $duration = $end_time ? ($end_time - $start_time) / 60 : 60; // duration in minutes
    
    return [
        'id' => $apt['id'],
        'title' => $apt['title'] ?? 'Untitled Appointment',
        'type' => $apt['type'] ?? 'general',
        'date' => date('Y-m-d', $start_time),
        'time' => date('H:i:s', $start_time),
        'duration' => round($duration),
        'attorney' => $apt['assigned_to_name'] ?? 'Unassigned',
        'room' => $apt['location'] ?? 'TBD',
        'status' => $apt['status'] ?? 'scheduled',
        'case_title' => $apt['case_title'] ?? '',
        'client_name' => $apt['client_name'] ?? ''
    ];
}, $appointments);

// Get all appointment dates for monthly calendar highlighting
$all_appointment_dates = [];
foreach ($appointments as $apt) {
    $all_appointment_dates[$apt['date']] = true;
}

// Get upcoming deadlines as calendar events
$upcoming_deadlines = get_upcoming_deadlines($user_id, $user_role, 14);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Firm Calendar | Med Attorneys Admin</title>
	<link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
	<link rel="manifest" href="../../favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../../css/default.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">

    <style>
        :root {
            --merlaws-primary: #1a1a1a;
            --merlaws-gold: #c9a96e;
            --merlaws-dark: #0d1117;
            --admin-blue: #3b82f6;
            --admin-blue-dark: #2563eb;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --neutral-gray: #6b7280;
            --calendar-today: #fbbf24;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-dark) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
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

        .admin-badge {
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: var(--merlaws-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(201, 169, 110, 0.3);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 1rem 0;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        /* Calendar Controls */
        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .view-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .view-btn {
            background: transparent;
            border: 2px solid #e9ecef;
            color: var(--neutral-gray);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .view-btn:hover, .view-btn.active {
            background: var(--admin-blue);
            border-color: var(--admin-blue);
            color: white;
            text-decoration: none;
        }

        .date-navigation {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-btn {
            background: white;
            border: 2px solid #e9ecef;
            color: var(--neutral-gray);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-btn:hover {
            background: var(--merlaws-gold);
            border-color: var(--merlaws-gold);
            color: white;
            text-decoration: none;
        }

        .current-date {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            min-width: 200px;
            text-align: center;
        }

        /* Calendar Grid */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .calendar-day {
            background: white;
            min-height: 120px;
            padding: 1rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .calendar-day:hover {
            background: #f8fafc;
        }

        .calendar-day.today {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.1));
            border: 2px solid var(--calendar-today);
        }

        .calendar-day.other-month {
            background: #f8fafc;
            color: #9ca3af;
        }

        .calendar-day.has-appointments {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.08));
            border-left: 3px solid var(--admin-blue);
        }

        .calendar-day.has-appointments.today {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.15));
            border-left: 3px solid var(--calendar-today);
        }

        .day-number {
            font-weight: 700;
            color: var(--merlaws-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .appointment-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 8px;
            height: 8px;
            color: var(--admin-blue);
            font-size: 0.5rem;
        }

        .calendar-day.has-appointments .appointment-indicator {
            color: var(--admin-blue);
        }

        .calendar-day.has-appointments.today .appointment-indicator {
            color: var(--calendar-today);
        }

        .day-events {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        /* Event Items */
        .event-item {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .event-item:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .event-consultation {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
            color: var(--admin-blue);
            border-left: 3px solid var(--admin-blue);
        }

        .event-court {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: var(--danger-red);
            border-left: 3px solid var(--danger-red);
        }

        .event-deposition {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: var(--warning-orange);
            border-left: 3px solid var(--warning-orange);
        }

        .event-meeting {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: var(--success-green);
            border-left: 3px solid var(--success-green);
        }

        /* Appointment List */
        .appointment-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .appointment-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .appointment-card.consultation::before { background: var(--admin-blue); }
        .appointment-card.court::before { background: var(--danger-red); }
        .appointment-card.deposition::before { background: var(--warning-orange); }
        .appointment-card.meeting::before { background: var(--success-green); }

        .appointment-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .appointment-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin: 0;
        }

        .appointment-type {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-consultation {
            background: rgba(59, 130, 246, 0.1);
            color: var(--admin-blue);
        }

        .type-court {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-red);
        }

        .type-deposition {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-orange);
        }

        .type-meeting {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item i {
            width: 16px;
            color: var(--merlaws-gold);
        }

        /* Status Indicators */
        .status-confirmed {
            color: var(--success-green);
            background: rgba(16, 185, 129, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-scheduled {
            color: var(--warning-orange);
            background: rgba(245, 158, 11, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Quick Add Button */
        .quick-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--merlaws-gold), #d4af37);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            box-shadow: 0 8px 25px rgba(201, 169, 110, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .quick-add-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(201, 169, 110, 0.6);
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--merlaws-gold);
            box-shadow: 0 0 0 0.2rem rgba(201, 169, 110, 0.25);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
                margin-bottom: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .calendar-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .view-buttons {
                justify-content: center;
            }
            
            .calendar-grid {
                grid-template-columns: 1fr;
            }
            
            .calendar-day {
                min-height: 80px;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }
            
            .content-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="admin-badge">
            <i class="fas fa-calendar-alt"></i>
            Calendar Management System
        </div>
        <h1 class="page-title">Firm Calendar</h1>
        <p class="page-subtitle">Comprehensive scheduling for appointments, court dates, and meetings</p>
    </div>
</div>

<div class="container my-4">
    <!-- Success/Error Messages -->
    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo e(implode('<br>', $errors)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Appointment Requests Section -->
    <?php if (!empty($appointment_requests)): ?>
    <div class="content-card mb-4">
        <h3 class="section-title">
            <i class="fas fa-clock"></i>
            Pending Appointment Requests (<?php echo count($appointment_requests); ?>)
        </h3>
        <div class="appointment-list">
            <?php foreach ($appointment_requests as $req): ?>
            <div class="appointment-card consultation" style="border-left: 4px solid var(--warning-orange);">
                <div class="appointment-header">
                    <h4 class="appointment-title"><?php echo e($req['title'] ?? 'Appointment Request'); ?></h4>
                    <span class="badge bg-warning">Pending</span>
                </div>
                <div class="appointment-details">
                    <div class="detail-item">
                        <i class="fas fa-briefcase"></i>
                        <span><strong>Case:</strong> <?php echo e($req['case_title']); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Client:</strong> <?php echo e($req['client_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-day"></i>
                        <span><strong>Preferred Date:</strong> <?php echo $req['preferred_date'] ? date('l, F j, Y', strtotime($req['preferred_date'])) : 'Not specified'; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><strong>Preferred Time:</strong> <?php echo $req['preferred_time'] ? date('g:i A', strtotime($req['preferred_time'])) : 'Not specified'; ?></span>
                    </div>
                    <?php if ($req['notes']): ?>
                    <div class="detail-item">
                        <i class="fas fa-sticky-note"></i>
                        <span><strong>Notes:</strong> <?php echo e($req['notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-success btn-sm" onclick="showRequestModal(<?php echo (int)$req['id']; ?>, 'accept', '<?php echo e($req['title'] ?? 'Appointment'); ?>')">
                        <i class="fas fa-check me-2"></i>Approve Request
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="showRequestModal(<?php echo (int)$req['id']; ?>, 'reject', '<?php echo e($req['title'] ?? 'Appointment'); ?>')">
                        <i class="fas fa-times me-2"></i>Reject Request
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Schedule Upload Banner -->
    <div class="alert alert-warning d-flex align-items-center" role="alert" style="border-left:4px solid var(--merlaws-gold)">
        <div class="me-3"><i class="fas fa-calendar-plus"></i></div>
        <div>
            <strong>Please upload your weekly or monthly availability.</strong>
            <div class="small">Upload a CSV schedule so the system can check conflicts and show available slots.</div>
        </div>
        <a href="availability.php" class="btn btn-sm btn-outline-primary ms-auto">Upload Availability</a>
    </div>

    <!-- Calendar Controls -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-sliders-h"></i>
            Calendar Controls & Filters
        </h3>
        
        <div class="calendar-controls">
            <div class="view-buttons">
                <a href="?view=day&date=<?php echo $selected_date; ?>" class="view-btn <?php echo $view_mode === 'day' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-day me-2"></i>Day
                </a>
                <a href="?view=week&date=<?php echo $selected_date; ?>" class="view-btn <?php echo $view_mode === 'week' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-week me-2"></i>Week
                </a>
                <a href="?view=month&date=<?php echo $selected_date; ?>" class="view-btn <?php echo $view_mode === 'month' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar me-2"></i>Month
                </a>
            </div>
            
            <div class="date-navigation">
                <a href="?view=<?php echo $view_mode; ?>&date=<?php echo date('Y-m-d', strtotime($selected_date . ' -1 ' . $view_mode)); ?>" class="nav-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <div class="current-date">
                    <?php echo date('F Y', strtotime($selected_date)); ?>
                </div>
                <a href="?view=<?php echo $view_mode; ?>&date=<?php echo date('Y-m-d', strtotime($selected_date . ' +1 ' . $view_mode)); ?>" class="nav-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form class="row g-3" method="get">
            <input type="hidden" name="view" value="<?php echo $view_mode; ?>">
            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
            
            <div class="col-12 col-md-4">
                <label class="form-label">Attorney</label>
                <select name="attorney" class="form-select">
                    <option value="">All Attorneys</option>
                    <option value="dr-roberts" <?php echo $filter_attorney === 'dr-roberts' ? 'selected' : ''; ?>>Dr. Michael Roberts</option>
                    <option value="sarah-williams" <?php echo $filter_attorney === 'sarah-williams' ? 'selected' : ''; ?>>Sarah Williams</option>
                </select>
            </div>
            
            <div class="col-12 col-md-4">
                <label class="form-label">Room/Location</label>
                <select name="room" class="form-select">
                    <option value="">All Locations</option>
                    <option value="conference-a" <?php echo $filter_room === 'conference-a' ? 'selected' : ''; ?>>Conference Room A</option>
                    <option value="conference-b" <?php echo $filter_room === 'conference-b' ? 'selected' : ''; ?>>Conference Room B</option>
                    <option value="court" <?php echo $filter_room === 'court' ? 'selected' : ''; ?>>Court Appearances</option>
                </select>
            </div>
            
            <div class="col-12 col-md-4">
                <label class="form-label">Event Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="consultation" <?php echo $filter_type === 'consultation' ? 'selected' : ''; ?>>Consultations</option>
                    <option value="court" <?php echo $filter_type === 'court' ? 'selected' : ''; ?>>Court Hearings</option>
                    <option value="deposition" <?php echo $filter_type === 'deposition' ? 'selected' : ''; ?>>Depositions</option>
                    <option value="meeting" <?php echo $filter_type === 'meeting' ? 'selected' : ''; ?>>Meetings</option>
                </select>
            </div>
        </form>
    </div>

    <?php if ($view_mode === 'month'): ?>
    <!-- Month Calendar View -->
    <div class="content-card">
        <h3 class="section-title">
            <i class="fas fa-calendar"></i>
            Monthly Calendar View
        </h3>
        
        <div class="calendar-grid">
            <?php
            // Generate calendar days for current month
            $first_day = date('Y-m-01', strtotime($selected_date));
            $days_in_month = date('t', strtotime($selected_date));
            $start_day = date('w', strtotime($first_day));
            
            // Day headers
            $day_headers = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($day_headers as $header): ?>
                <div class="calendar-day" style="min-height: 40px; background: #e5e7eb; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                    <?php echo $header; ?>
                </div>
            <?php endforeach;
            
            // Empty cells for days before month starts
            for ($i = 0; $i < $start_day; $i++): ?>
                <div class="calendar-day other-month"></div>
            <?php endfor;
            
            // Calendar days
            for ($day = 1; $day <= $days_in_month; $day++):
                $current_day = sprintf('%s-%02d', date('Y-m', strtotime($selected_date)), $day);
                $is_today = $current_day === date('Y-m-d');
                $day_appointments = array_filter($appointments, fn($apt) => $apt['date'] === $current_day);
                $has_appointments = !empty($day_appointments) || isset($all_appointment_dates[$current_day]);
            ?>
                <div class="calendar-day <?php echo $is_today ? 'today' : ''; ?> <?php echo $has_appointments ? 'has-appointments' : ''; ?>">
                    <div class="day-number">
                        <?php echo $day; ?>
                        <?php if ($has_appointments): ?>
                            <span class="appointment-indicator" title="<?php echo count($day_appointments); ?> appointment(s)">
                                <i class="fas fa-circle"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="day-events">
                        <?php foreach ($day_appointments as $apt): ?>
                            <div class="event-item event-<?php echo $apt['type']; ?>" title="<?php echo e($apt['title']); ?>">
                                <?php echo e(substr($apt['title'], 0, 20)) . (strlen($apt['title']) > 20 ? '...' : ''); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Appointments List -->
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title mb-0">
                <i class="fas fa-list"></i>
                <?php echo ucfirst($view_mode); ?> Schedule - Approved Appointments
            </h3>
            <button class="btn btn-outline-primary" onclick="openAddAppointment()">
                <i class="fas fa-plus me-2"></i>Add Appointment
            </button>
        </div>
        
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No upcoming approved appointments scheduled.
            </div>
        <?php else: ?>
        <div class="appointment-list">
            <?php foreach ($appointments as $appointment): ?>
            <div class="appointment-card <?php echo $appointment['type']; ?>">
                <div class="appointment-header">
                    <h4 class="appointment-title"><?php echo e($appointment['title']); ?></h4>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="appointment-type type-<?php echo $appointment['type']; ?>">
                            <?php echo ucfirst($appointment['type']); ?>
                        </span>
                        <span class="status-<?php echo $appointment['status']; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="appointment-details">
                    <div class="detail-item">
                        <i class="fas fa-calendar-day"></i>
                        <span><?php echo date('l, F j, Y', strtotime($appointment['date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('g:i A', strtotime($appointment['time'])); ?> (<?php echo $appointment['duration']; ?> min)</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-user-tie"></i>
                        <span><?php echo e($appointment['attorney']); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo e($appointment['room']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Appointment Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="requestModalHeader">
                <h5 class="modal-title" id="requestModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="requestForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="requestAction" name="action">
                <input type="hidden" id="requestId" name="request_id">
                <div class="modal-body">
                    <p id="requestModalMessage"></p>
                    <div class="mb-3">
                        <label for="requestNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="requestNotes" name="notes" rows="3" placeholder="Add any notes or comments..."></textarea>
                        <div class="form-text">These notes will be visible to the client.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="requestConfirmBtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Add Button -->
<button class="quick-add-btn" onclick="openAddAppointment()" title="Add New Appointment">
    <i class="fas fa-plus"></i>
</button>

<?php include __DIR__ . '/_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/mobile-responsive.js"></script>

<script>
function openAddAppointment() {
    // This would open a modal or redirect to add appointment page
    alert('Add Appointment functionality would be implemented here');
}

function showRequestModal(requestId, action, title) {
    const modal = document.getElementById('requestModal');
    const modalHeader = document.getElementById('requestModalHeader');
    const modalTitle = document.getElementById('requestModalTitle');
    const modalMessage = document.getElementById('requestModalMessage');
    const confirmBtn = document.getElementById('requestConfirmBtn');
    
    document.getElementById('requestId').value = requestId;
    document.getElementById('requestAction').value = action;
    document.getElementById('requestNotes').value = '';
    
    if (action === 'accept') {
        modalHeader.className = 'modal-header bg-success text-white';
        modalTitle.textContent = 'Approve Appointment Request';
        modalMessage.innerHTML = `Are you sure you want to <strong>approve</strong> the appointment request for "<em>${title}</em>"?<br><br>The appointment will be added to the schedule and the client will be notified.`;
        confirmBtn.className = 'btn btn-success';
        confirmBtn.textContent = 'Approve';
    } else {
        modalHeader.className = 'modal-header bg-danger text-white';
        modalTitle.textContent = 'Reject Appointment Request';
        modalMessage.innerHTML = `Are you sure you want to <strong>reject</strong> the appointment request for "<em>${title}</em>"?<br><br>The client will be notified of this decision.`;
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.textContent = 'Reject';
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Auto-refresh calendar data every 5 minutes
setInterval(() => {
    // Refresh calendar data
    console.log('Refreshing calendar data...');
}, 300000);
</script>
</body>
</html>