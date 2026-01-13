<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

require_login();

$user_id = get_user_id();
$pdo = db();

// 

$availability = [];
$admin_id = 0;
$primaryAttorney = null;
$availabilitySummary = [];


// 
// HELPER FUNCTIONS 
$add_minutes = fn($ts, $m) => ($t = strtotime($ts)) ? date('Y-m-d H:i:s', $t + ($m * 60)) : $ts;

$overlaps = fn($aStart, $aEnd, $bStart, $bEnd) => 
    ($as = strtotime($aStart)) && ($ae = strtotime($aEnd)) && 
    ($bs = strtotime($bStart)) && ($be = strtotime($bEnd)) 
        ? ($as < $be && $bs < $ae) : false;

$within_any = function($startAt, $endAt, $windows) {
    if (empty($windows)) {
        return false;
    }
    $rs = strtotime($startAt);
    $re = strtotime($endAt);
    if ($rs === false || $re === false) {
        return false;
    }
    foreach ($windows as $window) {
        $s = $window['start_ts'] ?? strtotime($window['start_iso'] ?? '');
        $e = $window['end_ts'] ?? strtotime($window['end_iso'] ?? '');
        if ($s && $e && $rs >= $s && $re <= $e) {
            return true;
        }
    }
    return false;
};

/**
 * Normalize raw availability records into concrete windows between the provided dates.
 *
 * @param array $entries Raw availability entries from JSON storage.
 * @param DateTimeZone $serverTz Timezone used for rendering to clients.
 * @param DateTimeImmutable $windowStart Inclusive lower bound for generated windows.
 * @param DateTimeImmutable $windowEnd Exclusive upper bound for generated windows.
 * @return array Array with keys: windows (array of slot windows) and summary (weekly summary rows).
 */
function normalize_availability_entries(array $entries, DateTimeZone $serverTz, DateTimeImmutable $windowStart, DateTimeImmutable $windowEnd): array
{
    $windows = [];
    $summaryMap = [];
    $allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    $windowEndExclusive = $windowEnd->modify('+1 day');

    foreach ($entries as $entry) {
        $startRaw = trim($entry['start_at'] ?? '');
        $endRaw   = trim($entry['end_at'] ?? '');

        if ($startRaw === '' || $endRaw === '') {
            continue;
        }

        $timezoneName = $entry['timezone'] ?? $serverTz->getName();
        try {
            $entryTz = new DateTimeZone($timezoneName);
        } catch (Exception $e) {
            $entryTz = $serverTz;
        }

        $hasDateComponent = preg_match('/\d{4}-\d{2}-\d{2}/', $startRaw);

        if ($hasDateComponent) {
            try {
                $startLocal = new DateTimeImmutable($startRaw, $entryTz);
                $endLocal   = new DateTimeImmutable($endRaw, $entryTz);
            } catch (Exception $e) {
                continue;
            }

            if ($endLocal <= $startLocal) {
                continue;
            }

            $startServer = $startLocal->setTimezone($serverTz);
            $endServer   = $endLocal->setTimezone($serverTz);

            if ($endServer <= $windowStart || $startServer >= $windowEndExclusive) {
                continue;
            }

            $windows[] = [
                'start_iso' => $startServer->format('Y-m-d H:i:s'),
                'end_iso'   => $endServer->format('Y-m-d H:i:s'),
                'start_local_iso' => $startLocal->format('Y-m-d H:i:s'),
                'end_local_iso'   => $endLocal->format('Y-m-d H:i:s'),
                'start_ts'  => $startServer->getTimestamp(),
                'end_ts'    => $endServer->getTimestamp(),
                'timezone'  => $entryTz->getName(),
                'label'     => $startLocal->format('l, d M Y').' • '.$startLocal->format('H:i').' – '.$endLocal->format('H:i'),
                'day'       => $startLocal->format('l')
            ];

            $dayKey = strtolower($startLocal->format('l'));
            $summaryMap[$dayKey][$startLocal->format('H:i').'-'.$endLocal->format('H:i')] = [
                'day' => ucfirst($dayKey),
                'range' => $startLocal->format('H:i').' – '.$endLocal->format('H:i'),
                'timezone' => $entryTz->getName()
            ];

            continue;
        }

        $days = $entry['days'] ?? $entry['day'] ?? [];
        if (is_string($days)) {
            $days = [$days];
        }
        if (!is_array($days) || empty($days)) {
            $days = $allDays;
        }
        $days = array_map('strtolower', array_map('trim', $days));
        $days = array_values(array_intersect($days, $allDays));
        if (empty($days)) {
            $days = $allDays;
        }

        if (!preg_match('/^\d{1,2}:\d{2}/', $startRaw) || !preg_match('/^\d{1,2}:\d{2}/', $endRaw)) {
            continue;
        }

        $periodStartLocal = $windowStart->setTimezone($entryTz)->setTime(0, 0);
        $periodEndLocal   = $windowEndExclusive->setTimezone($entryTz)->setTime(0, 0);
        $period = new DatePeriod($periodStartLocal, new DateInterval('P1D'), $periodEndLocal);

        foreach ($period as $dayDate) {
            $dayName = strtolower($dayDate->format('l'));
            if (!in_array($dayName, $days, true)) {
                continue;
            }

            $startLocal = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dayDate->format('Y-m-d').' '.$startRaw, $entryTz);
            $endLocal   = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dayDate->format('Y-m-d').' '.$endRaw, $entryTz);

            if (!$startLocal || !$endLocal || $endLocal <= $startLocal) {
                continue;
            }

            $startServer = $startLocal->setTimezone($serverTz);
            $endServer   = $endLocal->setTimezone($serverTz);

            if ($endServer <= $windowStart || $startServer >= $windowEndExclusive) {
                continue;
            }

            $windows[] = [
                'start_iso' => $startServer->format('Y-m-d H:i:s'),
                'end_iso'   => $endServer->format('Y-m-d H:i:s'),
                'start_local_iso' => $startLocal->format('Y-m-d H:i:s'),
                'end_local_iso'   => $endLocal->format('Y-m-d H:i:s'),
                'start_ts'  => $startServer->getTimestamp(),
                'end_ts'    => $endServer->getTimestamp(),
                'timezone'  => $entryTz->getName(),
                'label'     => $startLocal->format('l, d M').' • '.$startLocal->format('H:i').' – '.$endLocal->format('H:i'),
                'day'       => $startLocal->format('l')
            ];

            $summaryMap[$dayName][$startLocal->format('H:i').'-'.$endLocal->format('H:i')] = [
                'day' => ucfirst($dayName),
                'range' => $startLocal->format('H:i').' – '.$endLocal->format('H:i'),
                'timezone' => $entryTz->getName()
            ];
        }
    }

    usort($windows, function ($a, $b) {
        return ($a['start_ts'] ?? 0) <=> ($b['start_ts'] ?? 0);
    });

    $summary = [];
    foreach ($summaryMap as $day => $ranges) {
        foreach ($ranges as $entry) {
            $summary[] = [
                'days' => ucfirst($day),
                'range' => $entry['range'],
                'timezone' => $entry['timezone']
            ];
        }
    }

    return [
        'windows' => $windows,
        'summary' => $summary
    ];
}

/**
 * Load the assigned attorney availability file and normalize the entries.
 */
function load_attorney_availability(int $attorneyId, DateTimeZone $serverTz, DateTimeImmutable $windowStart, DateTimeImmutable $windowEnd): array
{
    $result = [
        'raw' => [],
        'windows' => [],
        'summary' => [],
        'last_updated' => null,
        'file_path' => null
    ];

    if (!$attorneyId) {
        return $result;
    }

    $filePath = __DIR__ . '/../../storage/availability/availability_' . $attorneyId . '.json';
    $result['file_path'] = $filePath;
    if (!file_exists($filePath)) {
        return $result;
    }

    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    if (!$data || !isset($data['entries']) || !is_array($data['entries'])) {
        return $result;
    }

    $result['raw'] = $data['entries'];
    $result['last_updated'] = @filemtime($filePath) ?: null;

    $normalized = normalize_availability_entries($data['entries'], $serverTz, $windowStart, $windowEnd);
    $result['windows'] = $normalized['windows'];
    $result['summary'] = $normalized['summary'];

    return $result;
}

/**
 * Generate discrete appointment start times given availability windows and existing bookings.
 *
 * @param array $windows Normalized availability windows.
 * @param array $bookedAppointments Existing appointments for conflict checking.
 * @param int $slotDurationSeconds Duration of each slot in seconds.
 * @param int $stepSeconds Increment for candidate slots (e.g., 15 minutes).
 * @param int $noticeTimestamp Earliest allowed slot start timestamp.
 * @param callable $overlaps Overlap detection callback.
 * @param callable $add_minutes Helper to adjust times with buffer.
 * @return array Sorted array of slot start timestamps (Y-m-d H:i:s).
 */
function generate_available_slots(array $windows, array $bookedAppointments, int $slotDurationSeconds, int $stepSeconds, int $noticeTimestamp, callable $overlaps, callable $add_minutes): array
{
    $slots = [];
    foreach ($windows as $window) {
        $startTs = $window['start_ts'] ?? null;
        $endTs   = $window['end_ts'] ?? null;
        if (!$startTs || !$endTs || $endTs <= $startTs) {
            continue;
        }

        for ($candidate = $startTs; $candidate + $slotDurationSeconds <= $endTs; $candidate += $stepSeconds) {
            if ($candidate < $noticeTimestamp) {
                continue;
            }
            $candidateStart = date('Y-m-d H:i:s', $candidate);
            $candidateEnd   = date('Y-m-d H:i:s', $candidate + $slotDurationSeconds);

            $bufStart = $add_minutes($candidateStart, -15);
            $bufEnd   = $add_minutes($candidateEnd, 15);

            $isConflict = false;
            foreach ($bookedAppointments as $appt) {
                if (!empty($appt['start_time']) && !empty($appt['end_time']) && $overlaps($bufStart, $bufEnd, $appt['start_time'], $appt['end_time'])) {
                    $isConflict = true;
                    break;
                }
            }

            if (!$isConflict) {
                $slots[] = $candidateStart;
            }
        }
    }

    $slots = array_values(array_unique($slots));
    sort($slots);

    return $slots;
}


// CONFIG

$locations = ['Johannesburg','Pretoria','Bloemfontein','Durban','Middleburg','Nelspruit','Ladysmith','Virtual'];
$durations = [30 => '30 minutes', 60 => '1 hour', 90 => '1.5 hours', 120 => '2 hours'];

$serverTimezone = new DateTimeZone(date_default_timezone_get());

// Get duration from request, default to 60
$slotDurationMinutes = 60;
if (isset($_REQUEST['duration']) && array_key_exists((int)$_REQUEST['duration'], $durations)) {
    $slotDurationMinutes = (int)$_REQUEST['duration'];
}

$slotRangeSeconds = $slotDurationMinutes * 60;
$slotStepMinutes = 15;
$slotStepSeconds = $slotStepMinutes * 60;
$lookaheadDays = 30;
$noticeSeconds = 60 * 60;

$nowDate = new DateTimeImmutable('now', $serverTimezone);
$windowStart = $nowDate;
$windowEnd   = $nowDate->modify('+' . $lookaheadDays . ' days');
$noticeTimestamp = $nowDate->getTimestamp() + $noticeSeconds;


// HANDLE BOOKING

$errors = [];
$success = '';
if (is_post() && ($_POST['action'] ?? '') === 'create') {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token.';
    } else {
        $caseId      = (int)($_POST['case_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $startInput  = trim($_POST['start_time'] ?? '');
        $duration    = (int)($_POST['duration'] ?? 60);
        $location    = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($caseId <= 0) $errors[] = 'Case is required.';
        if ($title === '') $errors[] = 'Title is required.';
        if ($startInput === '') $errors[] = 'Start time is required.';
        if (!in_array($duration, array_keys($durations))) $errors[] = 'Invalid duration.';

        if (!is_admin()) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM cases WHERE id = ? AND user_id = ?');
            $stmt->execute([$caseId, $user_id]);
            if (!$stmt->fetchColumn()) $errors[] = 'Invalid case.';
        }

        $startTs = strtotime($startInput);
        if ($startTs === false || $startTs < time()) $errors[] = 'Start time invalid or in past.';

        $start = date('Y-m-d H:i:s', $startTs);
        $end = date('Y-m-d H:i:s', $startTs + $duration * 60);

        $availabilityWindowsForCheck = [];

        $check_conflicts = function($adminId, $sAt, $eAt) use ($pdo, $add_minutes, $within_any, $overlaps, &$availabilityWindowsForCheck, $noticeSeconds) {
            if (strtotime($sAt) < time() + $noticeSeconds) return ['ok'=>false, 'error'=>'60 min notice required.'];
            $sBuf = $add_minutes($sAt, -15); $eBuf = $add_minutes($eAt, 15);
            if (!$within_any($sAt, $eAt, $availabilityWindowsForCheck)) 
                return ['ok'=>false, 'error'=>'Outside availability.'];
            $q = $pdo->prepare('SELECT start_time, end_time FROM appointments WHERE assigned_to = ? AND status IN ("confirmed","scheduled")');
            $q->execute([$adminId]);
            foreach ($q->fetchAll() as $row) {
                if ($overlaps($sBuf, $eBuf, $row['start_time'], $row['end_time'])) 
                    return ['ok'=>false, 'error'=>'Time slot already booked.'];
            }
            return ['ok'=>true];
        };

        if (!$errors) {
            $stmt = $pdo->prepare('SELECT assigned_to FROM cases WHERE id = ?');
            $stmt->execute([$caseId]);
            $assignedTo = (int)($stmt->fetchColumn() ?: 0);

            if (!$assignedTo) {
                $errors[] = 'An attorney must be assigned to this case before scheduling.';
            } else {
                // Ensure availability reflects the assigned attorney in real-time
                $availabilityData = load_attorney_availability($assignedTo, $serverTimezone, $windowStart, $windowEnd);
                $availabilityWindowsForCheck = $availabilityData['windows'] ?? [];
                $windowOk = $check_conflicts($assignedTo, $start, $end);

                if (!$windowOk['ok']) {
                    $errors[] = $windowOk['error'];
                } else {
                    // Check if appointment_requests table exists
                    $table_check = $pdo->query("SHOW TABLES LIKE 'appointment_requests'");
                    $table_exists = $table_check->rowCount() > 0;
                    
                    if ($table_exists) {
                        // Check for duplicate pending appointment request for the same day
                        $requestDate = date('Y-m-d', $startTs);
                        $checkDuplicate = $pdo->prepare('
                            SELECT COUNT(*) 
                            FROM appointment_requests 
                            WHERE case_id = ? 
                            AND DATE(preferred_date) = ? 
                            AND status = "pending"
                        ');
                        $checkDuplicate->execute([$caseId, $requestDate]);
                        $duplicateCount = (int)$checkDuplicate->fetchColumn();
                        
                        if ($duplicateCount > 0) {
                            $errors[] = 'You already have a pending appointment request for that day. Please wait for admin approval before submitting another request.';
                        } else {
                            // Create appointment request instead of direct appointment
                            $stmt = $pdo->prepare('
                                INSERT INTO appointment_requests 
                                (case_id, title, type, preferred_date, preferred_time, preferred_time_end, location, notes, status, created_at) 
                                VALUES (?, ?, "consultation", DATE(?), TIME(?), TIME(?), ?, ?, "pending", NOW())
                            ');
                            $stmt->execute([
                                $caseId, 
                                $title, 
                                $start, 
                                $start, 
                                $end, 
                                $location ?: null, 
                                $description ?: null
                            ]);
                            $success = "Appointment request submitted for $duration minutes. You will be notified once it's approved.";
                        }
                    } else {
                        // Fallback to old method if table doesn't exist
                        $stmt = $pdo->prepare('
                            INSERT INTO appointments 
                            (case_id, created_by, assigned_to, title, description, location, start_time, end_time, status, updated_at) 
                            VALUES (?, ?, NULLIF(?,0), ?, ?, ?, ?, ?, "pending", NOW())
                        ');
                        $stmt->execute([$caseId, $user_id, $assignedTo, $title, $description ?: null, $location ?: null, $start, $end]);
                        $success = "Appointment booked for $duration minutes. Awaiting confirmation.";
                    }
                }
            }
        }
    }
}


// LOAD CASES & APPOINTMENTS

$stmt = $pdo->prepare("SELECT id, title, assigned_to FROM cases WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$caseIds = array_map(fn($caseRow) => (int)$caseRow['id'], $cases);
$caseId = $cases ? (int)$cases[0]['id'] : 0;

if (isset($_GET['case_id'])) {
    $requestedCaseId = (int)$_GET['case_id'];
    if (in_array($requestedCaseId, $caseIds, true)) {
        $caseId = $requestedCaseId;
    }
}

if (is_post()) {
    $postedCaseId = (int)($_POST['case_id'] ?? 0);
    if ($postedCaseId && in_array($postedCaseId, $caseIds, true)) {
        $caseId = $postedCaseId;
    }
}

$selectedCase = null;
foreach ($cases as $caseRow) {
    if ((int)$caseRow['id'] === $caseId) {
        $selectedCase = $caseRow;
        break;
    }
}

$admin_id = $selectedCase ? (int)($selectedCase['assigned_to'] ?? 0) : 0;
$availability = [];
$availabilityWindows = [];
$availabilitySummary = [];
$availabilityLastUpdated = null;
$primaryAttorney = null;

$availabilityData = load_attorney_availability($admin_id, $serverTimezone, $windowStart, $windowEnd);
$availability = $availabilityData['raw'] ?? [];
$availabilityWindows = $availabilityData['windows'] ?? [];
$availabilityLastUpdated = $availabilityData['last_updated'] ?? null;
$availabilityLastUpdatedLabel = $availabilityLastUpdated
    ? date('D, d M Y \a\t H:i', $availabilityLastUpdated)
    : null;
$availabilityLastUpdatedIso = $availabilityLastUpdated
    ? date(DATE_ATOM, $availabilityLastUpdated)
    : null;

if (!empty($availabilityData['summary'])) {
    $dayOrder = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $tempSummary = [];
    foreach ($availabilityData['summary'] as $summaryRow) {
        $dayLabel = $summaryRow['day'] ?? $summaryRow['days'] ?? null;
        if (!$dayLabel) {
            continue;
        }
        $rangeLabel = $summaryRow['range'] ?? '';
        $tzLabel = $summaryRow['timezone'] ?? '';
        if ($tzLabel) {
            $rangeLabel .= ' (' . $tzLabel . ')';
        }
        $hash = strtolower($dayLabel).'|'.$rangeLabel;
        if (!isset($tempSummary[$hash])) {
            $tempSummary[$hash] = [
                'days' => $dayLabel,
                'range' => $rangeLabel
            ];
        }
    }
    usort($tempSummary, function ($a, $b) use ($dayOrder) {
        $posA = array_search($a['days'], $dayOrder, true);
        $posB = array_search($b['days'], $dayOrder, true);
        $posA = $posA === false ? 99 : $posA;
        $posB = $posB === false ? 99 : $posB;
        if ($posA === $posB) {
            return strcmp($a['range'], $b['range']);
        }
        return $posA <=> $posB;
    });
    $availabilitySummary = array_values($tempSummary);
}

if ($admin_id) {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $primaryAttorney = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$appointments = [];
$attorneyAppointments = [];
if ($caseId) {
    // Load case-specific appointments (optional, kept for context)
    $stmt = $pdo->prepare("SELECT title, start_time, end_time, status, location FROM appointments WHERE case_id = ?");
    $stmt->execute([$caseId]);
    $appointments = $stmt->fetchAll();
}
if ($admin_id) {
    // Load all booked appointments for the assigned attorney to correctly block availability
    $stmt = $pdo->prepare('SELECT title, start_time, end_time, status, location FROM appointments WHERE assigned_to = ? AND status IN ("confirmed","scheduled")');
    $stmt->execute([$admin_id]);
    $attorneyAppointments = $stmt->fetchAll();
}


// GENERATE AVAILABLE SLOTS FOR CALENDAR (using normalized availability windows)

$availableSlots = generate_available_slots(
    $availabilityWindows,
    $attorneyAppointments,
    $slotRangeSeconds,
    $slotStepSeconds,
    $noticeTimestamp,
    $overlaps,
    $add_minutes
);

$upcomingSlots = array_slice($availableSlots, 0, 12);
$min_datetime = date('Y-m-d\TH:i', $noticeTimestamp);
$nowTs = $nowDate->getTimestamp();
$name = $_SESSION['name'] ?? 'User';

$upcomingAttorneyAppointments = array_values(array_filter($attorneyAppointments, function($appt) use ($nowTs) {
    $startTs = strtotime($appt['start_time'] ?? '');
    return $startTs !== false && $startTs >= $nowTs;
}));
usort($upcomingAttorneyAppointments, function($a, $b) {
    return strtotime($a['start_time']) <=> strtotime($b['start_time']);
});

$nextAttorneyBooking = $upcomingAttorneyAppointments[0] ?? null;
$nextAttorneyBookingLabel = $nextAttorneyBooking ? date('D, d M \a\t H:i', strtotime($nextAttorneyBooking['start_time'])) : null;
$upcomingCount = count($upcomingAttorneyAppointments);

$nextAvailableSlot = $upcomingSlots[0] ?? null;
$nextAvailableLabel = $nextAvailableSlot ? date('D, d M \a\t H:i', strtotime($nextAvailableSlot)) : null;

$hasAvailabilityData = !empty($availabilityWindows);
$hasAssignedAttorney = (bool)$primaryAttorney;

$availabilityStatusTone = 'pending';
$availabilityStatusLabel = 'Availability pending upload';
if (!$hasAssignedAttorney) {
    $availabilityStatusTone = 'warning';
    $availabilityStatusLabel = 'Awaiting attorney assignment';
} elseif ($hasAvailabilityData && $nextAvailableSlot) {
    $availabilityStatusTone = 'active';
    $availabilityStatusLabel = 'Accepting new bookings';
} elseif ($hasAvailabilityData && !$nextAvailableSlot) {
    $availabilityStatusTone = 'booked';
    $availabilityStatusLabel = 'Fully booked';
} elseif (!$hasAvailabilityData) {
    $availabilityStatusTone = 'pending';
    $availabilityStatusLabel = 'Availability pending upload';
}

$scheduleDisabledReason = '';
if (!$hasAssignedAttorney) {
    $scheduleDisabledReason = 'A lead attorney has not yet been assigned to this case. Please contact your case manager or support for assistance.';
} elseif (!$hasAvailabilityData) {
    $scheduleDisabledReason = 'This attorney has not published availability yet. Reach out to your case manager to coordinate a time.';
}
$bookingDisabled = $scheduleDisabledReason !== '';

$availabilityNote = 'Availability updates are shared instantly when the attorney opens new times.';
if ($availabilityStatusTone === 'warning') {
    $availabilityNote = 'We will unlock scheduling as soon as your case is paired with an attorney.';
} elseif ($availabilityStatusTone === 'booked') {
    $availabilityNote = 'All published availability has been booked. We are working to release more slots soon.';
} elseif ($availabilityStatusTone === 'active') {
    $availabilityNote = 'Pick any highlighted slot in the calendar or quick list to confirm a request.';
}

$nextAvailableNote = $nextAvailableLabel
    ? 'Tap a slot below and the form will pre-fill the date and time for you.'
    : 'We will notify you the moment new availability is published.';

$nextBookingNote = 'No confirmed sessions yet.';
if ($nextAttorneyBooking) {
    $remaining = max(0, $upcomingCount - 1);
    $nextBookingNote = $remaining > 0
        ? 'This session plus ' . $remaining . ' more upcoming ' . ($remaining > 1 ? 'appointments.' : 'appointment.')
        : 'This is your next confirmed appointment.';
}

$caseTitle = $selectedCase ? ($selectedCase['title'] ?? 'Untitled case') : 'Select a case';
$caseNote = $selectedCase
    ? 'Case ID #' . $caseId . ($hasAssignedAttorney ? ' • Attorney: ' . ($primaryAttorney['name'] ?? 'Assigned attorney') : ' • Awaiting attorney assignment')
    : 'Choose a case to access tailored availability and booking.';

$slotDurationMinutes = 60;
$slotRangeSeconds = $slotDurationMinutes * 60;

$calendarEvents = [];
foreach ($attorneyAppointments as $a) {
    $calendarEvents[] = [
        'title' => ($a['title'] ?? 'Booked') . (!empty($a['location']) ? ' @ ' . $a['location'] : ''),
        'start' => $a['start_time'],
        'end' => $a['end_time'],
        'className' => 'booked'
    ];
}

$availableSlotRanges = [];
foreach ($availableSlots as $slot) {
    $slotTs = strtotime($slot);
    if (!$slotTs) continue;
    $calendarEvents[] = [
        'title' => 'Available',
        'start' => $slot,
        'end' => date('Y-m-d H:i:s', $slotTs + $slotRangeSeconds),
        'className' => 'available',
        'display' => 'background',
        'extendedProps' => [
            'slotValue' => date('Y-m-d\TH:i', $slotTs)
        ]
    ];

    $availableSlotRanges[] = [
        'value' => date('Y-m-d\TH:i', $slotTs),
        'from' => date('Y-m-d H:i', $slotTs),
        'to' => date('Y-m-d H:i', $slotTs + $slotRangeSeconds),
        'dateLabel' => date('l, d M', $slotTs),
        'timeLabel' => date('H:i', $slotTs)
    ];
}

$slotPills = [];
// foreach ($upcomingSlots as $slot) { // OLD: Used only the first 12
foreach ($availableSlots as $slot) { // NEW: Use ALL available slots so JS can filter
    $slotTs = strtotime($slot);
    if (!$slotTs) continue;
    $slotPills[] = [
        'value' => date('Y-m-d\TH:i', $slotTs),
        'label' => date('D d M', $slotTs),
        'timeLabel' => date('H:i', $slotTs)
    ];
}

$dropdownLimit = 75;
$slotDropdownGroups = [];
$countedDropdownOptions = 0;
foreach ($availableSlotRanges as $slot) {
    if ($countedDropdownOptions >= $dropdownLimit) break;
    $dateKey = date('Y-m-d', strtotime($slot['from']));
    if (!isset($slotDropdownGroups[$dateKey])) {
        $slotDropdownGroups[$dateKey] = [
            'label' => $slot['dateLabel'],
            'options' => []
        ];
    }
    $slotDropdownGroups[$dateKey]['options'][] = [
        'value' => $slot['value'],
        'label' => $slot['timeLabel']
    ];
    $countedDropdownOptions++;
}
$slotDropdownGroups = array_values($slotDropdownGroups);

$availabilityPayload = [
    'caseId' => $caseId,
    'statusTone' => $availabilityStatusTone,
    'statusLabel' => $availabilityStatusLabel,
    'availabilityNote' => $availabilityNote,
    'nextAvailableLabel' => $nextAvailableLabel,
    'nextAvailableNote' => $nextAvailableNote,
    'nextBookingLabel' => $nextAttorneyBookingLabel,
    'nextBookingNote' => $nextBookingNote,
    'upcomingCount' => $upcomingCount,
    'availabilitySummary' => $availabilitySummary,
    'slotPills' => $slotPills,
    'slotDropdownGroups' => $slotDropdownGroups,
    'availableSlotRanges' => $availableSlotRanges,
    'calendarEvents' => $calendarEvents,
    'bookingDisabled' => $bookingDisabled,
    'scheduleDisabledReason' => $scheduleDisabledReason,
    'hasAvailabilityData' => $hasAvailabilityData,
    'hasAssignedAttorney' => $hasAssignedAttorney,
    'caseTitle' => $caseTitle,
    'caseNote' => $caseNote,
    'attorney' => [
        'name' => $primaryAttorney['name'] ?? null,
        'email' => $primaryAttorney['email'] ?? null,
        'phone' => $primaryAttorney['phone'] ?? null
    ],
    'updatedAt' => $availabilityLastUpdated ?: $nowDate->getTimestamp(),
    'lastUpdatedLabel' => $availabilityLastUpdatedLabel,
    'lastUpdatedIso' => $availabilityLastUpdatedIso,
    'minSelectable' => date('Y-m-d\TH:i', $noticeTimestamp)
];

if (isset($_GET['format']) && $_GET['format'] === 'availability-json') {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo json_encode($availabilityPayload);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments | Med Attorneys</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />

    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-success: #38a169;
            --merlaws-danger: #e53e3e;
            --merlaws-info: #3182ce;
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: #1a202c;
            min-height: 100vh;
        }
        
        /* --- General Readability Improvements --- */
        
        /* Make all form labels clearer */
        label, .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            display: inline-block;
            font-size: 0.95rem;
        }
        
        /* Improve contrast on helper text */
        small, .text-muted, .insight-note, .page-stats-card span.label {
            color: #4a5568 !important; /* Darker grey for better readability */
            opacity: 1; /* Override any theme opacity */
        }
        
        /* Make subtitles and descriptions clearer */
        .page-title-section p, .text-muted {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        /* --- Booking Guidance Visibility --- */
        .form-info {
            padding: 1.25rem !important;
        }
        .form-info strong {
            font-weight: 600;
            color: #1a202c;
            font-size: 1rem;
            display: block;
            margin-bottom: 0.75rem;
        }
        .form-info ul {
            gap: 0.5rem !important; /* Add more spacing */
        }
        .form-info li {
            font-size: 0.95rem !important; /* Larger font */
            color: #2d3748 !important; /* Darker text */
            line-height: 1.5;
            font-weight: 500;
        }
        /* Ensure disabled/error reason is very clear */
        #schedule-disabled-list-item {
            color: var(--merlaws-danger) !important;
            font-weight: 600 !important;
        }
        
        /* --- Header & Case Selector Readability --- */
        .case-selector-card h3 {
            font-size: 1.5rem; /* Larger title */
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1.25rem;
        }
        
        .page-breadcrumb {
            font-size: 0.8rem; /* Slightly larger */
            font-weight: 500; /* Bolder */
            opacity: 0.85; /* Less dim */
        }
        
        .page-title-section h1 {
            color: #ffffff; /* Ensure pure white */
        }
        
        .page-title-section p {
            font-size: 1.1rem; /* Larger subtitle */
            opacity: 0.9;
        }
        
        .status-pill {
            font-size: 0.85rem; /* Larger pill text */
            padding: 0.5rem 1rem;
        }
        
        .attorney-highlight .availability-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            font-weight: 500;
        }
        
        .attorney-highlight h4 {
            font-size: 1.25rem; /* Larger attorney name */
            font-weight: 600;
            color: #ffffff;
        }
        
        .attorney-highlight span {
            font-size: 1rem; /* Larger email/phone */
            opacity: 0.9;
        }
        
        /* --- Page Stats Readability --- */
        .page-stats {
            gap: 1.5rem; /* More spacing */
        }
        
        .page-stats-card {
            padding: 1.25rem 1.5rem; /* More padding */
        }
        
        .page-stats-card span.label {
            font-size: 0.85rem; /* Larger label */
            font-weight: 500;
            color: #ffffff !important; /* White for contrast on dark bg */
            opacity: 0.7;
        }
        
        .page-stats-card span.value {
            font-size: 1.2rem; /* Larger value */
            font-weight: 600;
            color: #ffffff; /* Pure white */
            margin-top: 0.25rem;
        }
        
        .page-stats-card .insight-note {
            font-size: 0.95rem; /* Larger note */
            color: #ffffff !important;
            opacity: 0.85;
            margin-top: 0.25rem;
        }
        
        .page-stats-card .insight-meta {
            font-size: 0.9rem; /* Larger meta */
            color: #ffffff !important;
            opacity: 0.7;
            margin-top: 0.5rem;
        }
        

        .appointments-container { max-width: 1400px; margin: 0 auto; padding: 2rem 1rem; }

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: stretch;
            justify-content: space-between;
        }

        .page-intro {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 260px;
            flex: 1;
        }

        .page-breadcrumb {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.7;
        }

        .page-header::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><pattern id="g" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M50 0L0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern><rect width="100%" height="100%" fill="url(%23g)"/></svg>');
        }

        .page-title-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title-section p {
            margin: 0.25rem 0 0;
            opacity: 0.85;
            font-size: 1rem;
        }

        .page-title-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.95rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            background: rgba(255,255,255,0.16);
        }

        .status-pill i { font-size: 0.9rem; }

        .status-pill.status-active { background: rgba(56, 161, 105, 0.2); color: #f0fff4; }
        .status-pill.status-booked { background: rgba(229, 62, 62, 0.2); color: #ffe5e5; }
        .status-pill.status-warning { background: rgba(237,146,36,0.25); color: #fff4e6; }
        .status-pill.status-pending { background: rgba(59,130,246,0.2); color: #e0f0ff; }

        .attorney-highlight {
            background: rgba(255,255,255,0.16);
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-width: 260px;
        }

        .attorney-highlight.attorney-highlight--empty {
            border: 1px dashed rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.08);
        }

        .attorney-highlight h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .attorney-highlight span {
            font-size: 0.95rem;
            opacity: 0.85;
        }

        .attorney-highlight .availability-badge {
            align-self: flex-start;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.22);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .alert-custom {
            border: none;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.05rem;
        }

        .alert-error { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; border-left: 4px solid var(--merlaws-danger); }
        .alert-success { 
            background: linear-gradient(135deg, #d1fae5, #a7f3d0); 
            color: #065f46; 
            border-left: 4px solid var(--merlaws-success);
            box-shadow: 0 8px 20px rgba(56, 161, 105, 0.3);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .case-selector-card, .appointments-list-card, .create-appointment-card, .availability-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.875rem 1.125rem;
            border: 2px solid #cbd5e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 4px rgba(172, 19, 42, 0.1);
        }

        .btn-create-appointment {
            width: 100%;
            padding: 1.125rem;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .btn-create-appointment:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(172, 19, 42, 0.4);
        }

        #calendar {
            max-width: 100%;
            margin: 0; /* Removed margin */
            background: white;
            border-radius: 16px;
            /* Padding is now handled by .appointments-list-card */
        }

        .appointments-list-card {
            padding: 2.5rem; /* Add padding back to the card */
        }

        .page-stats {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .page-stats-card {
            background: rgba(255,255,255,0.16);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .page-stats-card span.label {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.7;
        }

        .page-stats-card span.value {
            font-size: 1rem;
            font-weight: 600;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .insight-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .insight-card.highlight {
            border-color: rgba(172, 19, 42, 0.3);
            box-shadow: 0 18px 35px -20px rgba(172,19,42,0.5);
        }

        .insight-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #4a5568;
        }

        .insight-value {
            font-size: 1.15rem;
            font-weight: 600;
            color: #1a202c;
        }

        .insight-note {
            font-size: 0.85rem;
            color: #4a5568;
        }

        .insight-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.25rem;
            font-size: 0.8rem;
            color: #64748b;
        }

        .availability-list {
            display: grid;
            gap: 1rem;
        }

        .availability-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 14px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
        }

        .availability-list-item span {
            font-size: 0.95rem;
            font-weight: 500;
            color: #1a202c;
        }

        .availability-list-item small {
            color: #4a5568;
            font-weight: 500;
        }

        .slot-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .slot-pill {
            border: 1px solid #cbd5e0;
            padding: 0.65rem 0.85rem;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            min-width: 120px;
            text-align: left;
        }

        .slot-pill:hover {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.12);
        }

        .slot-pill.active {
            border-color: var(--merlaws-primary);
            background: rgba(172, 19, 42, 0.08);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.12);
        }

        .slot-pill strong {
            font-size: 0.95rem;
            color: #1a202c;
        }

        .slot-pill small {
            font-size: 0.75rem;
            color: #4a5568;
        }

        .guidance-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        /* Ensure availability card has no margin inside new layout */
        .create-appointment-card .availability-card {
            margin-bottom: 0; /* Remove default bottom margin */
        }

        .guidance-card ul {
            padding-left: 1.2rem;
            margin: 0;
            display: grid;
            gap: 0.35rem;
        }

        .guidance-card li {
            font-size: 0.9rem;
            color: #4a5568;
        }

        .guidance-card .cta {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
            color: var(--merlaws-primary);
            font-weight: 600;
        }

        .fc-event.booked { background: #e53e3e !important; border-color: #e53e3e !important; }
        .fc-event.available { background: #38a169 !important; border-color: #38a169 !important; opacity: 0.3; }

        /* --- FullCalendar Readability Improvements --- */
        
        /* Calendar Title (e.g., "November 2025") */
        .fc-toolbar-title {
            font-size: 1.75rem !important;
            font-weight: 700 !important;
            color: #1a202c !important;
        }

        /* Y-Axis (Time) */
        .fc .fc-timegrid-slot-label-cushion {
            font-size: 0.9rem !important;
            color: #1a202c !important;
            font-weight: 500 !important;
        }
        
        /* X-Axis (Date Headers) */
        .fc .fc-col-header-cell-cushion {
            font-size: 1rem !important;
            color: #1a202c !important;
            font-weight: 600 !important;
            padding: 0.5rem !important;
        }
        
        /* Text inside events */
        .fc-event-title {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
        }
        
        /* Calendar Legend Text */
        .calendar-legend {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem; /* Increased size */
            color: #1a202c; /* Darkened color */
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }
        .calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        /* --- End FullCalendar --- */


        @media (max-width: 1024px) {
            /* Switch new internal booking grid to single column */
            .internal-booking-grid {
                grid-template-columns: 1fr !important;
            }
            .internal-booking-form {
                position: static !important; /* Disable sticky on tablet/mobile */
            }
        }

        @media (max-width: 768px) {
            .appointments-container {
                padding: 1rem 0.75rem;
            }

            .page-header {
                padding: 1.75rem;
                border-radius: 16px;
                margin-bottom: 1.5rem;
            }

            .page-header-content {
                flex-direction: column;
                gap: 1.25rem;
            }

            .page-title-section h1 {
                font-size: 1.75rem;
            }

            .page-title-section p {
                font-size: 0.95rem;
            }

            .page-title-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .status-pill {
                width: 100%;
                justify-content: center;
                min-height: 44px;
            }

            .attorney-highlight {
                width: 100%;
                padding: 1rem 1.25rem;
            }

            .page-stats {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .page-stats-card {
                padding: 1rem 1.25rem;
            }

            .insights-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .case-selector-card,
            .appointments-list-card,
            .create-appointment-card,
            .availability-card {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .case-selector-card h3 {
                font-size: 1.25rem;
            }

            .form-control-custom {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }

            .btn-create-appointment {
                min-height: 48px;
                font-size: 16px;
            }

            .slot-pills {
                flex-direction: column;
            }

            .slot-pill {
                width: 100%;
                min-height: 44px;
            }

            .availability-list-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .internal-booking-grid {
                gap: 1.5rem !important;
            }

            .calendar-legend {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            #calendar {
                font-size: 0.85rem;
            }

            .fc-toolbar-title {
                font-size: 1.25rem !important;
            }

            .form-info {
                padding: 1rem !important;
            }

            .form-info strong {
                font-size: 0.95rem;
            }

            .form-info li {
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 480px) {
            .appointments-container {
                padding: 0.75rem 0.5rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .page-title-section h1 {
                font-size: 1.5rem;
            }

            .case-selector-card,
            .appointments-list-card,
            .create-appointment-card {
                padding: 1.25rem;
            }

            .form-control-custom {
                padding: 10px 14px;
            }

            .btn-create-appointment {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<?php
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) echo file_get_contents($headerPath);
?>

<div class="appointments-container">
    <?php if ($errors): ?>
        <div class="alert-custom alert-error" style="position: sticky; top: 0; z-index: 1000; margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div><strong>Error:</strong> <?php echo e(implode(' ', $errors)); ?></div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-custom alert-success" style="position: sticky; top: 0; z-index: 1000; margin-bottom: 1.5rem; animation: slideDown 0.3s ease-out;">
            <i class="fas fa-check-circle"></i>
            <div><strong>Success:</strong> <?php echo e($success); ?></div>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div class="page-header-content">
            <div class="page-intro">
                <div class="page-breadcrumb">Client Portal / Appointments</div>
            <div class="page-title-section">
                <h1>Appointments</h1>
                    <p>Schedule and manage secure consultations with your legal team.</p>
            </div>
                <div class="page-title-meta">
                    <span class="status-pill <?= 'status-' . e($availabilityStatusTone) ?>" id="statusPill">
                        <i class="fas fa-wave-square"></i><span id="statusPillLabel"><?= e($availabilityStatusLabel) ?></span>
                    </span>
                    <span class="status-pill">
                        <i class="fas fa-folder-open"></i>Case #<?= (int)$caseId ?>
                    </span>
                    <?php if ($upcomingCount): ?>
                        <span class="status-pill">
                            <i class="fas fa-calendar-check"></i><?= $upcomingCount ?> upcoming <?= $upcomingCount > 1 ? 'sessions' : 'session' ?>
                        </span>
                    <?php endif; ?>
            </div>
        </div>
            <div class="attorney-highlight <?= $hasAssignedAttorney ? '' : 'attorney-highlight--empty' ?>" id="attorneyHighlight">
                <div class="availability-badge">
                    <i class="fas fa-user-check"></i>
                    Case attorney
                </div>
                <div id="attorneyAssignedPanel" style="<?= $hasAssignedAttorney ? '' : 'display:none;' ?>">
                    <h4 id="attorneyName"><?= e($primaryAttorney['name'] ?? 'Assigned attorney') ?></h4>
                    <span id="attorneyEmail" style="display:<?= !empty($primaryAttorney['email']) ? 'flex' : 'none' ?>;align-items:center;gap:0.4rem;font-size:0.95rem;opacity:0.85;">
                        <i class="fas fa-envelope me-2"></i><span id="attorneyEmailValue"><?= e($primaryAttorney['email'] ?? '') ?></span>
                    </span>
                    <span id="attorneyPhone" style="display:<?= !empty($primaryAttorney['phone']) ? 'flex' : 'none' ?>;align-items:center;gap:0.4rem;font-size:0.95rem;opacity:0.85;">
                        <i class="fas fa-phone me-2"></i><span id="attorneyPhoneValue"><?= e($primaryAttorney['phone'] ?? '') ?></span>
                    </span>
                    <span style="display:flex;align-items:center;gap:0.4rem;font-size:0.85rem;opacity:0.9;" id="attorneySyncMessage">
                        <i class="fas fa-business-time"></i><span>Availability syncs in real-time with this attorney.</span>
                    </span>
                </div>
                <div id="attorneyUnassignedPanel" style="<?= $hasAssignedAttorney ? 'display:none;' : '' ?>">
                    <h4>Attorney assignment in progress</h4>
                    <p id="attorneyUnassignedCopy" style="font-size:0.9rem;opacity:0.85;margin:0;">
                        Your case manager is pairing you with the best-fit attorney. Scheduling will unlock as soon as the assignment is confirmed.
                    </p>
                    <span style="display:flex;align-items:center;gap:0.4rem;font-size:0.85rem;opacity:0.9;">
                        <i class="fas fa-info-circle"></i>We will send a notification once availability is ready.
                    </span>
                </div>
            </div>
        </div>
        <div class="page-stats">
            <div class="page-stats-card" id="caseStats">
                <span class="label">Case</span>
                <span class="value" id="caseStatsValue"><?= e($caseTitle) ?></span>
                <span class="insight-note" id="caseStatsNote"><?= e($caseNote) ?></span>
            </div>
            <div class="page-stats-card" id="availabilityStatusStats">
                <span class="label">Availability status</span>
                <span class="value" id="availabilityStatusValue"><?= e($availabilityStatusLabel) ?></span>
                <span class="insight-note" id="availabilityStatusNote"><?= e($availabilityNote) ?></span>
                <span class="insight-meta" id="availabilitySyncMeta">
                    <i class="fas fa-clock"></i>
                    <?= $availabilityLastUpdatedLabel ? 'Synced ' . e($availabilityLastUpdatedLabel) : 'Awaiting availability upload' ?>
                </span>
            </div>
            <div class="page-stats-card" id="nextConfirmedSessionStats">
                <span class="label">Next confirmed session</span>
                <span class="value" id="nextConfirmedValue"><?= $nextAttorneyBookingLabel ? e($nextAttorneyBookingLabel) : 'None scheduled' ?></span>
                <span class="insight-note" id="nextConfirmedNote"><?= e($nextBookingNote) ?></span>
            </div>
            <div class="page-stats-card" id="nextAvailableSlotStats">
                <span class="label">Next available slot</span>
                <span class="value" id="nextAvailableValue"><?= $nextAvailableLabel ? e($nextAvailableLabel) : 'No openings yet' ?></span>
                <span class="insight-note" id="nextAvailableNote"><?= e($nextAvailableNote) ?></span>
    </div>

    <?php if (!$cases): ?>
        <div class="no-cases-card text-center p-5" style="background:white;border-radius:24px;box-shadow:var(--shadow-md);">
            <i class="fas fa-folder-open" style="font-size:5rem;color:#cbd5e0;"></i>
            <h3 class="mt-3">No Cases Available</h3>
            <p class="text-muted">Create a case first to schedule appointments.</p>
            <a href="../cases/create.php" class="btn" style="background:linear-gradient(135deg,var(--merlaws-primary),var(--merlaws-primary-dark));color:white;padding:1rem 2rem;border-radius:12px;">
                Create Case
            </a>
        </div>
    <?php else: ?>
        <div class="case-selector-card">
            <h3>Select Case</h3>
            <form method="get">
                <select name="case_id" class="form-control-custom" onchange="this.form.submit()" style="cursor:pointer;">
                    <?php foreach ($cases as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $caseId === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- 
        RESTRUCTURED LAYOUT:
        1. The main 'appointments-grid' is now set to 1 column, stacking the calendar and booking cards.
        2. The calendar card ('appointments-list-card') appears first, full-width.
        3. The booking card ('create-appointment-card') appears second, full-width.
        4. INSIDE the booking card, a new 2-column grid is created.
           - Left Col: Availability info (weekly list, slot pills)
           - Right Col: The booking form (which is sticky)
        -->
        <div class="appointments-grid" style="display:grid; grid-template-columns: 1fr; gap:2rem;">
            
            <!-- Calendar View (Now on top, full-width) -->
            <div class="appointments-list-card">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <h3 style="margin:0;">Attorney Calendar</h3>
                    <div class="calendar-legend">
                        <span><i class="fas fa-square" style="color:#38a169;"></i> Available window</span>
                        <span><i class="fas fa-square" style="color:#e53e3e;"></i> Confirmed booking</span>
                    </div>
                </div>
                <p class="text-muted" style="margin-top:0.75rem;">Select a slot directly on the calendar or use the curated availability list below to pre-fill the form.</p>
                <div id="calendar" style="margin-top: 1.5rem;"></div>
            </div>

            <!-- Create Form Area (Now below calendar, with internal 2-column layout) -->
            <div class="create-appointment-card" style=""> <!-- Removed sticky style from outer card -->
                
                <!-- New Internal 2-Column Grid -->
                <div class="internal-booking-grid" style="display: grid; grid-template-columns: 1fr 420px; gap: 2rem; align-items: start;">
                    
                    <!-- Internal Left Column: Availability Info -->
                    <div class="internal-booking-info">
                        <div class="availability-card" style="padding:0; box-shadow:none; border:none; background:none;">
                            <h5 style="margin-bottom:1rem;">Weekly availability</h5>
                            <div class="availability-list" id="weekly-availability-list"></div>
                            <p class="text-muted mb-0" id="weekly-availability-empty" style="display:none;"></p>
                        </div>

                        <div class="availability-card" style="padding:0; box-shadow:none; border:none; background:none; margin-top: 2.5rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                                <h5 style="margin:0;">Next available slots</h5>
                                <span style="font-size:0.8rem;color:#4a5568;" id="slot-pills-helper">Tap to pre-fill start time</span>
                            </div>
                            <div class="slot-pills" id="slot-pills"></div>
                            <p class="text-muted mb-0" id="slot-pills-empty" style="display:none;"></p>
                        </div>
                    </div>
                    
                    <!-- Internal Right Column: Booking Form (Sticky) -->
                    <div class="internal-booking-form" style="position: sticky; top: 2rem;">
                        <h3>Schedule New</h3>
                        <div class="form-info p-3 mb-3" style="background:#f7fafc;border-left:4px solid var(--merlaws-info);border-radius:12px;">
                            <strong>Booking guidance</strong>
                            <ul style="margin:0.75rem 0 0;padding-left:1.2rem;display:grid;gap:0.35rem;">
                                <li>Minimum 60-minute notice for new appointments.</li>
                                <li>Green windows indicate your attorney’s available time.</li>
                                <li id="schedule-disabled-list-item" style="color:#c53030;font-weight:600;<?= $scheduleDisabledReason ? '' : 'display:none;' ?>"><?= e($scheduleDisabledReason ?: '') ?></li>
                            </ul>
                        </div>

                        <form method="post" id="appointmentForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="case_id" value="<?= $caseId ?>">

                            <div class="form-group mb-3">
                                <label class="form-label" for="form-title">Title *</label>
                                <input type="text" id="form-title" name="title" class="form-control-custom" placeholder="e.g., Initial Consultation" required data-booking-field <?= $bookingDisabled ? 'disabled' : '' ?>>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="start_time">Start Date & Time *</label>
                                <select name="start_time" id="start_time" class="form-control-custom" data-booking-field <?= $bookingDisabled ? 'disabled' : '' ?> required>
                                    <option value="" disabled <?= $bookingDisabled ? 'selected' : '' ?>>Select an available slot...</option>
                                </select>
                                <small id="start-time-helper" class="text-muted d-block mt-2">Only visible times are available to request.</small>
                                <small id="end-time-helper" class="text-muted d-block mt-2" style="font-weight: 600; color: var(--merlaws-primary-dark) !important;"></small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="form-duration">Duration</label>
                                <select name="duration" id="form-duration" class="form-control-custom" data-booking-field <?= $bookingDisabled ? 'disabled' : '' ?>>
                                    <?php foreach ($durations as $mins => $label): ?>
                                        <option value="<?= $mins ?>" <?= $mins === $slotDurationMinutes ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="form-location">Location</label>
                                <select name="location" id="form-location" class="form-control-custom" data-booking-field <?= $bookingDisabled ? 'disabled' : '' ?>>
                                    <option value="">Select location...</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= e($loc) ?>"><?= e($loc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label" for="form-description">Notes</label>
                                <textarea name="description" id="form-description" class="form-control-custom" rows="3" placeholder="Any special requests..." data-booking-field <?= $bookingDisabled ? 'disabled' : '' ?>></textarea>
                            </div>

                            <button type="submit" class="btn-create-appointment" id="schedule-button" <?= $bookingDisabled ? 'disabled' : '' ?>>
                                <?= $bookingDisabled ? 'Scheduling temporarily unavailable' : 'Schedule Appointment' ?>
                            </button>
                            <p class="text-muted text-center mt-3 mb-0" id="schedule-disabled-message" style="font-size:0.85rem;<?= $bookingDisabled ? '' : 'display:none;' ?>">
                                <?= $bookingDisabled ? 'Our team will let you know as soon as booking becomes available.' : '' ?>
                            </p>
                        </form>

                        <div class="guidance-card">
                            <h5 style="margin:0;">Best practices</h5>
                            <ul>
                                <li>Switch cases above to compare schedules for other matters.</li>
                                <li>Click an available slot to auto-fill the start date and time.</li>
                                <li>Use the notes field to highlight priorities or supporting documents.</li>
                            </ul>
                            <div class="cta">
                                <i class="fas fa-headset"></i>
                                Need a bespoke time? Message your case manager from the secure inbox.
                            </div>
                        </div>
                    </div> <!-- End Internal Right Column -->

                </div> <!-- End Internal Grid -->

            </div> <!-- End Create Appointment Card -->
        </div>
    <?php endif; ?>
</div>

<?php
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($footerPath)) echo file_get_contents($footerPath);
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<script>
let availabilityState = <?= json_encode($availabilityPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
let calendar = null;
let startSelect;
let durationSelect;
let endTimeHelper;
let slotPillsContainer;
let slotPillsEmpty;
let slotPillsHelper;
let weeklyList;
let weeklyEmpty;
let scheduleButton;
let scheduleDisabledMessage;
let scheduleDisabledListItem;
let bookingFields = [];
let statusPill;
let statusPillLabel;
let availabilitySyncMeta;
let availabilityStatusValue;
let availabilityStatusNote;
let caseStatsValue;
let caseStatsNote;
let nextConfirmedValue;
let nextConfirmedNote;
let nextAvailableValue;
let nextAvailableNote;
let attorneyHighlight;
let attorneyAssignedPanel;
let attorneyUnassignedPanel;
let attorneyNameEl;
let attorneyEmailEl;
let attorneyEmailValueEl;
let attorneyPhoneEl;
let attorneyPhoneValueEl;
let startTimeHelper;
let selectedSlotValue = '';

const AUTO_REFRESH_INTERVAL = 30000;
const baseAvailabilityUrl = (() => {
    const url = new URL(window.location.href);
    url.searchParams.set('format', 'availability-json');
    url.searchParams.delete('duration'); // Clean it first
    return url.toString(); // Store as string
})();

document.addEventListener('DOMContentLoaded', () => {
    // Scroll to top if there's a success notification
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    startSelect = document.getElementById('start_time');
    slotPillsContainer = document.getElementById('slot-pills');
    slotPillsEmpty = document.getElementById('slot-pills-empty');
    slotPillsHelper = document.getElementById('slot-pills-helper');
    weeklyList = document.getElementById('weekly-availability-list');
    weeklyEmpty = document.getElementById('weekly-availability-empty');
    scheduleButton = document.getElementById('schedule-button');
    scheduleDisabledMessage = document.getElementById('schedule-disabled-message');
    scheduleDisabledListItem = document.getElementById('schedule-disabled-list-item');
    bookingFields = Array.from(document.querySelectorAll('[data-booking-field]'));
    statusPill = document.getElementById('statusPill');
    statusPillLabel = document.getElementById('statusPillLabel');
    availabilitySyncMeta = document.getElementById('availabilitySyncMeta');
    availabilityStatusValue = document.getElementById('availabilityStatusValue');
    availabilityStatusNote = document.getElementById('availabilityStatusNote');
    caseStatsValue = document.getElementById('caseStatsValue');
    caseStatsNote = document.getElementById('caseStatsNote');
    nextConfirmedValue = document.getElementById('nextConfirmedValue');
    nextConfirmedNote = document.getElementById('nextConfirmedNote');
    nextAvailableValue = document.getElementById('nextAvailableValue');
    nextAvailableNote = document.getElementById('nextAvailableNote');
    attorneyHighlight = document.getElementById('attorneyHighlight');
    attorneyAssignedPanel = document.getElementById('attorneyAssignedPanel');
    attorneyUnassignedPanel = document.getElementById('attorneyUnassignedPanel');
    attorneyNameEl = document.getElementById('attorneyName');
    attorneyEmailEl = document.getElementById('attorneyEmail');
    attorneyEmailValueEl = document.getElementById('attorneyEmailValue');
    attorneyPhoneEl = document.getElementById('attorneyPhone');
    attorneyPhoneValueEl = document.getElementById('attorneyPhoneValue');
    startTimeHelper = document.getElementById('start-time-helper');
    durationSelect = document.getElementById('form-duration');
    endTimeHelper = document.getElementById('end-time-helper');

    if (startSelect) {
        selectedSlotValue = startSelect.value;
        startSelect.addEventListener('change', handleStartSelectChange);
        startSelect.addEventListener('change', updateEndTimeDisplay);
    }
    if (durationSelect) {
        durationSelect.addEventListener('change', () => {
            updateEndTimeDisplay();
            refreshAvailability(); // Trigger refresh on change
        });
    }

    renderCalendar(availabilityState.calendarEvents || []);
    applyAvailability(availabilityState, { updateCalendar: false });
    updateEndTimeDisplay(); // Call on load

    setInterval(refreshAvailability, AUTO_REFRESH_INTERVAL);
});

function cloneAvailabilityUrl() {
    const url = new URL(baseAvailabilityUrl); // Re-parse from string
    if (durationSelect) {
        url.searchParams.set('duration', durationSelect.value);
    }
    url.searchParams.set('_', Date.now().toString());
    return url;
}

function renderCalendar(events) {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    if (!calendar) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            height: 'auto',
            nowIndicator: true,
            selectable: true,
            selectMirror: true,
            selectAllow(selection) {
                return selection.start >= new Date(Date.now() + 3600 * 1000);
            },
            select(info) {
                if (!availabilityState.bookingDisabled) {
                    const value = info.start.toISOString().slice(0, 16);
                    selectSlotValue(value);
                }
            },
            eventClick(info) {
                const value = info.event.extendedProps?.slotValue;
                if (value && !availabilityState.bookingDisabled) {
                    selectSlotValue(value);
                }
            },
            datesSet: function() {
                // NEW: When calendar view changes, re-filter pills AND dropdown
                renderSlotPills(availabilityState);
                renderStartOptions(availabilityState);
            }
        });
        calendar.render();
    }

    calendar.removeAllEventSources();
    if (events && events.length) {
        calendar.addEventSource(events);
    }
}

function applyAvailability(state, { updateCalendar = true } = {}) {
    availabilityState = state;
    const { hasOptions, selectedValue } = renderStartOptions(state);
    renderSlotPills(state);
    renderWeeklyAvailability(state);
    updateStatus(state);
    updateAttorney(state);
    setBookingState(state, hasOptions);
    highlightActivePills(selectedValue);
    if (updateCalendar) {
        renderCalendar(state.calendarEvents || []);
    }
}

function renderWeeklyAvailability(state) {
    if (!weeklyList || !weeklyEmpty) return;
    weeklyList.innerHTML = '';
    const summary = state.availabilitySummary || [];

    if (!summary.length) {
        weeklyList.style.display = 'none';
        weeklyEmpty.style.display = '';
        weeklyEmpty.textContent = state.hasAssignedAttorney
            ? 'The assigned attorney has not published availability yet.'
            : 'Availability will appear once an attorney is assigned to your case.';
        return;
    }

    weeklyList.style.display = 'grid';
    weeklyEmpty.style.display = 'none';

    summary.forEach(item => {
        const row = document.createElement('div');
        row.className = 'availability-list-item';

        const label = document.createElement('span');
        label.innerHTML = `<i class="fas fa-calendar-day me-2"></i>${item.days}`;

        const range = document.createElement('small');
        range.textContent = item.range;

        row.appendChild(label);
        row.appendChild(range);
        weeklyList.appendChild(row);
    });
}

function renderSlotPills(state) {
    if (!slotPillsContainer || !slotPillsEmpty) return;

    // --- NEW FILTER LOGIC ---
    const allSlots = state.slotPills || [];
    const PILL_LIMIT = 12; // Max pills to show
    let viewStart = null;
    let viewEnd = null;

    // Get calendar view range IF calendar is initialized
    if (calendar && calendar.view) {
        viewStart = calendar.view.activeStart;
        viewEnd = calendar.view.activeEnd;
    }

    const slots = [];
    for (const slot of allSlots) {
        if (slots.length >= PILL_LIMIT) {
            break; // Stop after finding 12 matching slots
        }

        // If calendar isn't ready, just use the first 12 slots (maintains original behavior)
        if (!viewStart || !viewEnd) {
            slots.push(slot);
            continue;
        }
        
        try {
            const slotDate = new Date(slot.value);
            // Check if slotDate is within the [viewStart, viewEnd) range
            if (slotDate >= viewStart && slotDate < viewEnd) {
                slots.push(slot);
            }
        } catch (e) {
            // Invalid date format, skip
        }
    }
    // --- END NEW FILTER LOGIC ---

    slotPillsContainer.innerHTML = '';
    // const slots = state.slotPills || []; // OLD LINE

    if (!slots.length || state.bookingDisabled) {
        slotPillsContainer.style.display = 'none';
        if (slotPillsHelper) slotPillsHelper.style.display = 'none';
        slotPillsEmpty.style.display = '';
        slotPillsEmpty.textContent = state.bookingDisabled
            ? 'Scheduling is temporarily unavailable.'
            : (viewStart ? 'No open slots in this view. Try another date.' : 'No open slots in the coming weeks. We will notify you as soon as new times become available.'); // Dynamic message
        return;
    }

    slotPillsContainer.style.display = 'flex';
    if (slotPillsHelper) slotPillsHelper.style.display = '';
    slotPillsEmpty.style.display = 'none';

    slots.forEach(slot => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'slot-pill';
        button.dataset.slotValue = slot.value;
        button.innerHTML = `<strong>${slot.label}</strong><small>${slot.timeLabel}</small>`;
        button.addEventListener('click', () => selectSlotValue(slot.value));
        slotPillsContainer.appendChild(button);
    });

    highlightActivePills(selectedSlotValue);
}

function renderStartOptions(state) {
    if (!startSelect) return { hasOptions: false, selectedValue: '' };

    // --- NEW FILTER LOGIC ---
    let viewStart = null;
    let viewEnd = null;
    if (calendar && calendar.view) {
        viewStart = calendar.view.activeStart;
        viewEnd = calendar.view.activeEnd;
    }
    // --- END NEW FILTER LOGIC ---

    const previous = selectedSlotValue || startSelect.value || '';
    const groups = state.slotDropdownGroups || [];
    const fragment = document.createDocumentFragment();
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.disabled = true;
    placeholder.textContent = groups.length ? 'Select an available slot...' : 'No available slots yet';
    // fragment.appendChild(placeholder); // Will append at the end

    const values = [];
    groups.forEach(group => {
        
        // --- FILTERING ---
        let filteredOptions = group.options || [];
        
        if (viewStart && viewEnd) {
            filteredOptions = filteredOptions.filter(opt => {
                try {
                    const slotDate = new Date(opt.value);
                    return slotDate >= viewStart && slotDate < viewEnd;
                } catch(e) { return false; }
            });
        }
        
        // If, after filtering, there are no options, skip this group
        if (filteredOptions.length === 0) {
            return; // Skip this day
        }
        // --- END FILTERING ---

        const optgroup = document.createElement('optgroup');
        optgroup.label = group.label;
        
        // (group.options || []).forEach(opt => { // OLD
        filteredOptions.forEach(opt => { // NEW
            const optionEl = document.createElement('option');
            optionEl.value = opt.value;
            optionEl.textContent = opt.label;
            optgroup.appendChild(optionEl);
            values.push(opt.value);
        });
        fragment.appendChild(optgroup);
    });

    startSelect.innerHTML = ''; // Clear
    startSelect.appendChild(placeholder); // Add placeholder first
    startSelect.appendChild(fragment); // Add all filtered groups

    const hasOptions = values.length > 0;
    let nextValue = '';

    if (hasOptions) {
        if (values.includes(previous)) {
            nextValue = previous;
        } else if (state.slotPills && state.slotPills.length) {
            nextValue = state.slotPills[0].value;
        } else {
            nextValue = values[0];
        }
    }

    if (nextValue) {
        startSelect.value = nextValue;
    } else {
        startSelect.value = '';
        // placeholder.selected = true; // No longer needed
    }

    selectedSlotValue = startSelect.value;

    if (startTimeHelper) {
        if (state.bookingDisabled) {
            startTimeHelper.textContent = 'Scheduling is currently locked. We will notify you when new availability opens.';
        } else if (hasOptions) {
            startTimeHelper.textContent = 'Only visible times are available to request.';
        } else {
            startTimeHelper.textContent = (viewStart && state.hasAssignedAttorney) // Dynamic message
                ? 'No open slots in this view. Try another date.'
                : (state.hasAssignedAttorney ? 'No open slots yet. We will notify you...' : 'Availability will appear once an attorney is assigned.');
        }
    }

    return { hasOptions, selectedValue: selectedSlotValue };
}

function updateStatus(state) {
    const tone = state.statusTone || 'pending';
    if (statusPill) {
        statusPill.className = `status-pill status-${tone}`;
    }
    if (statusPillLabel) {
        statusPillLabel.textContent = state.statusLabel || 'Availability pending upload';
    }
    if (availabilityStatusValue) {
        availabilityStatusValue.textContent = state.statusLabel || 'Availability pending upload';
    }
    if (availabilityStatusNote) {
        availabilityStatusNote.textContent = state.availabilityNote || '';
    }
    if (availabilitySyncMeta) {
        const label = state.lastUpdatedLabel
            ? `Synced ${state.lastUpdatedLabel}`
            : 'Awaiting availability upload';
        availabilitySyncMeta.innerHTML = `<i class="fas fa-clock"></i> ${label}`;
    }
    if (caseStatsValue) caseStatsValue.textContent = state.caseTitle || 'Select a case';
    if (caseStatsNote) caseStatsNote.textContent = state.caseNote || '';
    if (nextConfirmedValue) nextConfirmedValue.textContent = state.nextBookingLabel || 'None scheduled';
    if (nextConfirmedNote) nextConfirmedNote.textContent = state.nextBookingNote || '';
    if (nextAvailableValue) nextAvailableValue.textContent = state.nextAvailableLabel || 'No openings yet';
    if (nextAvailableNote) nextAvailableNote.textContent = state.nextAvailableNote || '';
}

function updateAttorney(state) {
    if (!attorneyHighlight) return;
    attorneyHighlight.classList.toggle('attorney-highlight--empty', !state.hasAssignedAttorney);

    if (state.hasAssignedAttorney) {
        if (attorneyAssignedPanel) attorneyAssignedPanel.style.display = '';
        if (attorneyUnassignedPanel) attorneyUnassignedPanel.style.display = 'none';
        if (attorneyNameEl) attorneyNameEl.textContent = state.attorney?.name || 'Assigned attorney';

        const email = state.attorney?.email || '';
        if (attorneyEmailEl) {
            attorneyEmailEl.style.display = email ? 'flex' : 'none';
        }
        if (attorneyEmailValueEl) attorneyEmailValueEl.textContent = email;

        const phone = state.attorney?.phone || '';
        if (attorneyPhoneEl) {
            attorneyPhoneEl.style.display = phone ? 'flex' : 'none';
        }
        if (attorneyPhoneValueEl) attorneyPhoneValueEl.textContent = phone;
    } else {
        if (attorneyAssignedPanel) attorneyAssignedPanel.style.display = 'none';
        if (attorneyUnassignedPanel) attorneyUnassignedPanel.style.display = '';
    }
}

function setBookingState(state, hasOptions) {
    const shouldDisable = state.bookingDisabled || !hasOptions;
    bookingFields.forEach(field => {
        if (field) field.disabled = shouldDisable;
    });
    if (startSelect) {
        startSelect.disabled = shouldDisable;
        startSelect.required = !shouldDisable;
    }
    if (scheduleButton) scheduleButton.disabled = shouldDisable;

    if (scheduleDisabledMessage) {
        scheduleDisabledMessage.style.display = state.bookingDisabled ? '' : 'none';
    }
    if (scheduleDisabledListItem) {
        if (state.scheduleDisabledReason) {
            scheduleDisabledListItem.style.display = '';
            scheduleDisabledListItem.textContent = state.scheduleDisabledReason;
        } else {
            scheduleDisabledListItem.style.display = 'none';
        }
    }
}

function updateEndTimeDisplay() {
    if (!startSelect || !durationSelect || !endTimeHelper) {
        return;
    }

    const startValue = startSelect.value;
    const durationMinutes = parseInt(durationSelect.value, 10);

    if (!startValue || isNaN(durationMinutes)) {
        endTimeHelper.textContent = '';
        return;
    }

    try {
        const startDate = new Date(startValue);
        const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
        
        // Format as "Ends at: 10:30 on Thu, 20 Nov"
        const endFormatted = endDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
        const dateFormatted = endDate.toLocaleDateString('en-GB', { weekday: 'short', month: 'short', day: 'numeric' });
        
        endTimeHelper.textContent = `Ends at: ${endFormatted} on ${dateFormatted}`;

    } catch (e) {
        console.warn('Error calculating end time', e);
        endTimeHelper.textContent = '';
    }
}

function highlightActivePills(value) {
    if (!slotPillsContainer) return;
    slotPillsContainer.querySelectorAll('.slot-pill').forEach(btn => {
        btn.classList.toggle('active', !!value && btn.dataset.slotValue === value);
    });
}

function selectSlotValue(value) {
    if (!startSelect || !value) return;
    const option = Array.from(startSelect.options).find(opt => opt.value === value);
    if (option) {
        startSelect.value = value;
        selectedSlotValue = value;
        handleStartSelectChange();
        updateEndTimeDisplay(); // Update end time on pill click
    }
}

function handleStartSelectChange() {
    if (!startSelect) return;
    selectedSlotValue = startSelect.value;
    highlightActivePills(selectedSlotValue);
    updateEndTimeDisplay(); // Update end time on dropdown change
}

async function refreshAvailability() {
    try {
        const response = await fetch(cloneAvailabilityUrl(), {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        if (!response.ok) return;
        const data = await response.json();
        applyAvailability(data);
    } catch (error) {
        console.warn('Availability refresh skipped', error);
    }
}
</script>
</body>
</html>