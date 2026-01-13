<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }
if (!csrf_validate()) { echo json_encode(['success'=>false,'error'=>'Invalid token']); exit; }
if (!is_logged_in()) { echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }

$pdo = db();
$action = (string)($_POST['action'] ?? '');
$appointmentId = (int)($_POST['appointment_id'] ?? 0);
$caseId = (int)($_POST['case_id'] ?? 0);
$startAt = trim((string)($_POST['start_at'] ?? ''));
$endAt = trim((string)($_POST['end_at'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));

// Scheduling policies
$MIN_NOTICE_MINUTES = 60; // at least 60 minutes from now
$BUFFER_MINUTES = 15; // 15 min buffer before/after

function load_admin_availability(int $adminId): array {
    $path = __DIR__ . '/../../storage/availability/availability_' . $adminId . '.json';
    if (!file_exists($path)) { return []; }
    $data = json_decode((string)file_get_contents($path), true);
    if (!is_array($data) || !isset($data['entries']) || !is_array($data['entries'])) { return []; }
    return $data['entries'];
}

function overlaps(string $aStart, string $aEnd, string $bStart, string $bEnd): bool {
    $as = strtotime($aStart); $ae = strtotime($aEnd); $bs = strtotime($bStart); $be = strtotime($bEnd);
    if (!$as || !$ae || !$bs || !$be) { return false; }
    return ($as < $be) && ($bs < $ae);
}

function within_any_availability(string $startAt, string $endAt, array $entries): bool {
    // Basic: allow if falls entirely within at least one provided interval (ignores RRULE expansion)
    foreach ($entries as $e) {
        if (!empty($e['start_at']) && !empty($e['end_at'])) {
            $s = strtotime($e['start_at']);
            $en = strtotime($e['end_at']);
            $rs = strtotime($startAt);
            $re = strtotime($endAt);
            if ($s !== false && $en !== false && $rs !== false && $re !== false) {
                if ($rs >= $s && $re <= $en) { return true; }
            }
        }
    }
    return empty($entries) ? true : false; // if no availability uploaded, don't block
}

function add_minutes(string $ts, int $minutes): string { $t = strtotime($ts); return $t? date('Y-m-d H:i:s', $t + ($minutes*60)) : $ts; }

function check_conflicts(PDO $pdo, int $adminId, string $startAt, string $endAt): array {
    global $MIN_NOTICE_MINUTES, $BUFFER_MINUTES;
    // 0) Minimum notice
    $nowPlus = time() + ($MIN_NOTICE_MINUTES * 60);
    if (strtotime($startAt) !== false && strtotime($startAt) < $nowPlus) {
        return ['ok' => false, 'error' => 'Appointment does not meet the minimum notice requirement.'];
    }
    // Apply buffer around requested window
    $startWithBuffer = add_minutes($startAt, -$BUFFER_MINUTES);
    $endWithBuffer = add_minutes($endAt, $BUFFER_MINUTES);
    // 1) Check availability intervals
    $entries = load_admin_availability($adminId);
    if (!within_any_availability($startAt, $endAt, $entries)) {
        return ['ok' => false, 'error' => 'Selected time is outside uploaded availability.'];
    }
    // 2) Check existing confirmed appointments assigned to this admin
    $stmt = $pdo->prepare('SELECT start_time, end_time FROM appointments WHERE assigned_to = ? AND status IN ("confirmed","scheduled") LIMIT 2000');
    $stmt->execute([$adminId]);
    foreach ($stmt->fetchAll() as $row) {
        if (overlaps($startWithBuffer, $endWithBuffer, (string)$row['start_time'], (string)$row['end_time'])) {
            return ['ok' => false, 'error' => 'Time conflicts with an existing appointment.'];
        }
    }
    return ['ok' => true];
}

function generate_ics_for_appt(PDO $pdo, int $appointmentId): ?string {
    try {
        $stmt = $pdo->prepare('SELECT a.*, u.email AS client_email, u.name AS client_name FROM appointments a JOIN cases c ON a.case_id = c.id JOIN users u ON c.user_id = u.id WHERE a.id = ?');
        $stmt->execute([$appointmentId]);
        $a = $stmt->fetch();
        if (!$a) { return null; }
        $uid = 'appt-' . $appointmentId . '@merlaws';
        $dtStart = gmdate('Ymd\THis\Z', strtotime((string)$a['start_time']));
        $dtEnd = gmdate('Ymd\THis\Z', strtotime((string)$a['end_time']));
        $summary = ($a['title'] ?: 'Appointment');
        $location = ($a['location'] ?: '');
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//MerLaws//Appointments//EN\r\nBEGIN:VEVENT\r\nUID:$uid\r\nDTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\nDTSTART:$dtStart\r\nDTEND:$dtEnd\r\nSUMMARY:" . addcslashes($summary, "\\,;") . "\r\nLOCATION:" . addcslashes($location, "\\,;") . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
        $dir = __DIR__ . '/../../storage/ics/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $path = $dir . 'appointment_' . $appointmentId . '.ics';
        file_put_contents($path, $ics);
        return '/storage/ics/appointment_' . $appointmentId . '.ics';
    } catch (Throwable $e) { return null; }
}

try {
    // Fetch appointment context when relevant
    $appt = null;
    if (in_array($action, ['accept','decline','propose'], true) && $appointmentId > 0) {
        $get = $pdo->prepare('SELECT a.*, c.user_id AS client_id FROM appointments a JOIN cases c ON a.case_id = c.id WHERE a.id = ?');
        $get->execute([$appointmentId]);
        $appt = $get->fetch();
        if (!$appt) { echo json_encode(['success'=>false,'error'=>'Appointment not found']); exit; }
    }

    if ($action === 'accept' && $appointmentId > 0) {
        // Admin accepts
        require_permission('appointment:update');
        $adminId = get_user_id();
        $conf = check_conflicts($pdo, $adminId, (string)$appt['start_time'], (string)$appt['end_time']);
        if (!$conf['ok']) { echo json_encode(['success'=>false,'error'=>$conf['error']]); exit; }
        $pdo->prepare('UPDATE appointments SET status = "confirmed", assigned_to = ?, updated_at = NOW() WHERE id = ?')->execute([$adminId, $appointmentId]);
        // Log & notify
        log_case_activity((int)$appt['case_id'], $adminId, 'admin_action', 'Appointment Confirmed', 'Appointment confirmed by admin');
        $icsUrl = generate_ics_for_appt($pdo, $appointmentId);
        $msg = 'Your appointment has been confirmed.' . ($icsUrl ? "\nDownload calendar invite: $icsUrl" : '');
        create_user_notification((int)$appt['client_id'], 'appointment_confirmed', 'Appointment Confirmed', $msg, '/app/cases/view.php?id=' . (int)$appt['case_id']);
        echo json_encode(['success'=>true]); exit;
    }
    if ($action === 'decline' && $appointmentId > 0) {
        require_permission('appointment:update');
        $adminId = get_user_id();
        $pdo->prepare('UPDATE appointments SET status = "cancelled", updated_at = NOW() WHERE id = ?')->execute([$appointmentId]);
        log_case_activity((int)$appt['case_id'], $adminId, 'admin_action', 'Appointment Declined', $reason ?: '');
        create_user_notification((int)$appt['client_id'], 'appointment_declined', 'Appointment Declined', 'Your appointment request was declined.' . ($reason? (' Reason: ' . $reason) : ''), '/app/cases/view.php?id=' . (int)$appt['case_id']);
        echo json_encode(['success'=>true]); exit;
    }
    if ($action === 'propose' && $appointmentId > 0 && $startAt !== '' && $endAt !== '') {
        // Admin proposes new time; check conflicts against admin availability
        $actorId = get_user_id();
        $conf = check_conflicts($pdo, $actorId, $startAt, $endAt);
        if (!$conf['ok']) { echo json_encode(['success'=>false,'error'=>$conf['error']]); exit; }
        $stmt = $pdo->prepare('UPDATE appointments SET status = "proposed", start_time = ?, end_time = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$startAt, $endAt, $appointmentId]);
        log_case_activity((int)$appt['case_id'], $actorId, 'admin_action', 'Appointment Proposed', 'Proposed new time window');
        create_user_notification((int)$appt['client_id'], 'appointment_proposed', 'New Appointment Time Proposed', 'A new time has been proposed for your appointment.', '/app/cases/view.php?id=' . (int)$appt['case_id']);
        echo json_encode(['success'=>true]); exit;
    }
    if ($action === 'request' && $caseId > 0 && $startAt !== '' && $endAt !== '') {
        // Client requesting a new appointment (no conflict enforcement yet; admin confirms)
        $stmt = $pdo->prepare('INSERT INTO appointments (case_id, created_by, title, start_time, end_time, status, updated_at) VALUES (?, ?, ?, ?, ?, "pending", NOW())');
        $stmt->execute([$caseId, get_user_id(), ($reason?:'Appointment Request'), $startAt, $endAt]);
        // Notify admin-like roles (optional: could queue or assign later)
        log_case_activity($caseId, get_user_id(), 'service_request', 'Appointment Requested', 'Client requested an appointment');
        echo json_encode(['success'=>true]); exit;
    }

    // Client accepts proposed time -> still not final until admin confirms; flip to pending
    if ($action === 'client_accept' && $appointmentId > 0) {
        $pdo->prepare('UPDATE appointments SET status = "pending", updated_at = NOW() WHERE id = ?')->execute([$appointmentId]);
        if ($appt) {
            log_case_activity((int)$appt['case_id'], get_user_id(), 'service_request', 'Client Accepted Proposed Time', 'Client accepted the proposed time');
        }
        echo json_encode(['success'=>true]); exit;
    }
    // Client proposes a new time (counter)
    if ($action === 'client_propose' && $appointmentId > 0 && $startAt !== '' && $endAt !== '') {
        $stmt = $pdo->prepare('UPDATE appointments SET status = "proposed", start_time = ?, end_time = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$startAt, $endAt, $appointmentId]);
        if ($appt) {
            log_case_activity((int)$appt['case_id'], get_user_id(), 'service_request', 'Client Proposed New Time', 'Client proposed new appointment time');
        }
        echo json_encode(['success'=>true]); exit;
    }
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'error'=>'Operation failed']);
}

