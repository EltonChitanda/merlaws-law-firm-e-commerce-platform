<?php
// app/cron/task_generator.php - Automated Task Generation
require __DIR__ . '/../config.php';

// This script should be run via cron job (e.g., every hour)
// Example cron entry: 0 * * * * /usr/bin/php /path/to/app/cron/task_generator.php

echo "Starting automated task generation...\n";

$created_tasks = 0;

try {
    // 1. Create appointment reminder tasks
    echo "Creating appointment reminder tasks...\n";
    $appointment_tasks = auto_create_tasks_from_appointments();
    $created_tasks += $appointment_tasks;
    echo "Created $appointment_tasks appointment reminder tasks\n";
    
    // 2. Create tasks for overdue service requests
    echo "Creating tasks for overdue service requests...\n";
    $service_tasks = create_service_request_tasks();
    $created_tasks += $service_tasks;
    echo "Created $service_tasks service request tasks\n";
    
    // 3. Create tasks for cases with no activity
    echo "Creating tasks for inactive cases...\n";
    $inactive_tasks = create_inactive_case_tasks();
    $created_tasks += $inactive_tasks;
    echo "Created $inactive_tasks inactive case tasks\n";
    
    // 4. Create tasks for upcoming court dates
    echo "Creating tasks for upcoming court dates...\n";
    $court_tasks = create_court_date_tasks();
    $created_tasks += $court_tasks;
    echo "Created $court_tasks court date tasks\n";
    
    // 5. Create tasks for document review deadlines
    echo "Creating tasks for document review deadlines...\n";
    $doc_tasks = create_document_review_tasks();
    $created_tasks += $doc_tasks;
    echo "Created $doc_tasks document review tasks\n";
    
    echo "Task generation completed. Total tasks created: $created_tasks\n";
    
} catch (Exception $e) {
    echo "Error during task generation: " . $e->getMessage() . "\n";
    exit(1);
}

function create_service_request_tasks(): int {
    $pdo = db();
    $created = 0;
    
    // Find service requests that have been pending for more than 3 days
    $stmt = $pdo->query("
        SELECT sr.id, sr.case_id, sr.service_name, sr.requested_at, c.assigned_to
        FROM service_requests sr
        JOIN cases c ON sr.case_id = c.id
        WHERE sr.status = 'pending'
        AND sr.requested_at < DATE_SUB(NOW(), INTERVAL 3 DAY)
        AND NOT EXISTS (
            SELECT 1 FROM tasks t 
            WHERE t.case_id = sr.case_id 
            AND t.task_type = 'service_followup'
            AND t.title LIKE CONCAT('%', sr.service_name, '%')
            AND t.status IN ('pending', 'in_progress')
        )
    ");
    
    $requests = $stmt->fetchAll();
    
    foreach ($requests as $request) {
        if ($request['assigned_to']) {
            $task_data = [
                'case_id' => $request['case_id'],
                'assigned_to' => $request['assigned_to'],
                'title' => 'Follow up on Service Request: ' . $request['service_name'],
                'description' => 'Service request has been pending for more than 3 days. Please review and take action.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'priority' => 'medium',
                'task_type' => 'service_followup'
            ];
            
            create_task($task_data);
            $created++;
        }
    }
    
    return $created;
}

function create_inactive_case_tasks(): int {
    $pdo = db();
    $created = 0;
    
    // Find cases with no activity in the last 7 days
    $stmt = $pdo->query("
        SELECT c.id, c.title, c.assigned_to, c.updated_at
        FROM cases c
        WHERE c.status IN ('active', 'under_review')
        AND c.updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND c.assigned_to IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM case_activities ca 
            WHERE ca.case_id = c.id 
            AND ca.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        )
        AND NOT EXISTS (
            SELECT 1 FROM tasks t 
            WHERE t.case_id = c.id 
            AND t.task_type = 'admin_task'
            AND t.title LIKE '%Case Review%'
            AND t.status IN ('pending', 'in_progress')
            AND t.created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
        )
    ");
    
    $cases = $stmt->fetchAll();
    
    foreach ($cases as $case) {
        $task_data = [
            'case_id' => $case['id'],
            'assigned_to' => $case['assigned_to'],
            'title' => 'Case Review Required: ' . $case['title'],
            'description' => 'This case has had no activity in the last 7 days. Please review and provide an update.',
            'due_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'priority' => 'medium',
            'task_type' => 'admin_task'
        ];
        
        create_task($task_data);
        $created++;
    }
    
    return $created;
}

function create_court_date_tasks(): int {
    $pdo = db();
    $created = 0;
    
    // Find appointments that are court dates within the next 14 days
    $stmt = $pdo->query("
        SELECT a.id, a.case_id, a.title, a.start_time, a.assigned_to, c.title as case_title
        FROM appointments a
        JOIN cases c ON a.case_id = c.id
        WHERE a.type = 'court'
        AND a.start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 14 DAY)
        AND a.status = 'scheduled'
        AND a.assigned_to IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM tasks t 
            WHERE t.case_id = a.case_id 
            AND t.task_type = 'court_date'
            AND t.title LIKE CONCAT('%', a.title, '%')
            AND t.status IN ('pending', 'in_progress')
        )
    ");
    
    $appointments = $stmt->fetchAll();
    
    foreach ($appointments as $apt) {
        $days_until = ceil((strtotime($apt['start_time']) - time()) / 86400);
        
        $task_data = [
            'case_id' => $apt['case_id'],
            'assigned_to' => $apt['assigned_to'],
            'title' => 'Court Date Preparation: ' . $apt['title'],
            'description' => "Court date in $days_until days. Prepare all necessary documents and evidence for case: " . $apt['case_title'],
            'due_date' => date('Y-m-d H:i:s', strtotime($apt['start_time']) - 86400), // 1 day before
            'priority' => $days_until <= 3 ? 'urgent' : 'high',
            'task_type' => 'court_date'
        ];
        
        create_task($task_data);
        $created++;
    }
    
    return $created;
}

function create_document_review_tasks(): int {
    $pdo = db();
    $created = 0;
    
    // Find documents uploaded more than 2 days ago that haven't been reviewed
    $stmt = $pdo->query("
        SELECT cd.id, cd.case_id, cd.original_filename, cd.uploaded_at, c.assigned_to, c.title as case_title
        FROM case_documents cd
        JOIN cases c ON cd.case_id = c.id
        WHERE cd.uploaded_at < DATE_SUB(NOW(), INTERVAL 2 DAY)
        AND c.assigned_to IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM tasks t 
            WHERE t.case_id = cd.case_id 
            AND t.task_type = 'document_review'
            AND t.title LIKE CONCAT('%', cd.original_filename, '%')
            AND t.status IN ('pending', 'in_progress')
        )
    ");
    
    $documents = $stmt->fetchAll();
    
    foreach ($documents as $doc) {
        $task_data = [
            'case_id' => $doc['case_id'],
            'assigned_to' => $doc['assigned_to'],
            'title' => 'Document Review: ' . $doc['original_filename'],
            'description' => 'Review uploaded document for case: ' . $doc['case_title'],
            'due_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'priority' => 'medium',
            'task_type' => 'document_review'
        ];
        
        create_task($task_data);
        $created++;
    }
    
    return $created;
}

// Log the task generation run
$pdo = db();
$stmt = $pdo->prepare("
    INSERT INTO system_logs (type, message, data, created_at) 
    VALUES ('task_generation', 'Automated task generation completed', ?, NOW())
");
$log_data = json_encode([
    'tasks_created' => $created_tasks,
    'appointment_tasks' => $appointment_tasks ?? 0,
    'service_tasks' => $service_tasks ?? 0,
    'inactive_tasks' => $inactive_tasks ?? 0,
    'court_tasks' => $court_tasks ?? 0,
    'doc_tasks' => $doc_tasks ?? 0
]);
$stmt->execute([$log_data]);

echo "Task generation log saved to system_logs\n";
?>
