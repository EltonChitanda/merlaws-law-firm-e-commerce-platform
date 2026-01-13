<?php
// app/api/availability.php
require __DIR__ . '/../config.php';
require_login();

header('Content-Type: application/json');

$attorney_id = (int)($_GET['attorney_id'] ?? 0);
$date = trim($_GET['date'] ?? '');

if (!$attorney_id || !$date) {
    echo json_encode(['success' => false, 'error' => 'Attorney ID and date are required']);
    exit;
}

try {
    // Load attorney availability from JSON file
    $availabilityFile = __DIR__ . '/../../storage/availability/availability_' . $attorney_id . '.json';
    
    if (!file_exists($availabilityFile)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Attorney has not uploaded their availability schedule'
        ]);
        exit;
    }
    
    $availabilityData = json_decode(file_get_contents($availabilityFile), true);
    
    if (!$availabilityData || !isset($availabilityData['entries'])) {
        echo json_encode([
            'success' => false, 
            'error' => 'Invalid availability data'
        ]);
        exit;
    }
    
    // Parse availability entries for the selected date
    $timeSlots = [];
    $selectedDate = date('Y-m-d', strtotime($date));
    
    foreach ($availabilityData['entries'] as $entry) {
        $startTime = strtotime($entry['start_at']);
        $endTime = strtotime($entry['end_at']);
        
        if (!$startTime || !$endTime) {
            continue;
        }
        
        $entryDate = date('Y-m-d', $startTime);
        
        // Check if this entry is for the selected date
        if ($entryDate === $selectedDate) {
            // Generate time slots (30-minute intervals)
            $currentTime = $startTime;
            while ($currentTime < $endTime) {
                $slotTime = date('H:i:s', $currentTime);
                $slotDisplay = date('g:i A', $currentTime);
                
                // Check if this time slot is not blocked
                if (empty($entry['block_reason'])) {
                    $timeSlots[] = [
                        'time' => $slotTime,
                        'display' => $slotDisplay
                    ];
                }
                
                $currentTime += 1800; // Add 30 minutes
            }
        }
    }
    
    // Check for existing appointments that might block time slots
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT start_time, end_time 
        FROM appointments 
        WHERE assigned_to = ? 
        AND DATE(start_time) = ? 
        AND status IN ('scheduled', 'confirmed')
    ");
    $stmt->execute([$attorney_id, $selectedDate]);
    $existingAppointments = $stmt->fetchAll();
    
    // Remove time slots that conflict with existing appointments
    $availableSlots = [];
    foreach ($timeSlots as $slot) {
        $slotDateTime = $selectedDate . ' ' . $slot['time'];
        $slotTimestamp = strtotime($slotDateTime);
        $slotEndTimestamp = $slotTimestamp + 1800; // 30 minutes duration
        
        $isAvailable = true;
        foreach ($existingAppointments as $appointment) {
            $apptStart = strtotime($appointment['start_time']);
            $apptEnd = strtotime($appointment['end_time']);
            
            // Check for overlap
            if (($slotTimestamp < $apptEnd) && ($slotEndTimestamp > $apptStart)) {
                $isAvailable = false;
                break;
            }
        }
        
        if ($isAvailable) {
            $availableSlots[] = $slot;
        }
    }
    
    echo json_encode([
        'success' => true,
        'timeSlots' => $availableSlots,
        'date' => $selectedDate,
        'attorney_id' => $attorney_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load availability: ' . $e->getMessage()
    ]);
}
?>
