<?php
// app/api/tasks.php - Task Management API
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

// Only allow admin users
require_admin();

$pdo = db();
$user_id = get_user_id();
$user_role = get_user_role();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $user_id, $user_role, $pdo);
            break;
        case 'POST':
            handlePostRequest($action, $user_id, $user_role, $pdo);
            break;
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGetRequest($action, $user_id, $user_role, $pdo) {
    switch ($action) {
        case 'list':
            getTasks($user_id, $user_role, $pdo);
            break;
        case 'overdue':
            getOverdueTasks($user_id, $user_role, $pdo);
            break;
        case 'upcoming':
            getUpcomingTasks($user_id, $user_role, $pdo);
            break;
        case 'stats':
            getTaskStats($user_id, $user_role, $pdo);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

function handlePostRequest($action, $user_id, $user_role, $pdo) {
    if (!csrf_validate()) {
        throw new Exception('Invalid security token');
    }
    
    switch ($action) {
        case 'create':
            createTask($user_id, $user_role, $pdo);
            break;
        case 'update':
            updateTask($user_id, $user_role, $pdo);
            break;
        case 'complete':
            completeTask($user_id, $user_role, $pdo);
            break;
        case 'delete':
            deleteTask($user_id, $user_role, $pdo);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

function getTasks($user_id, $user_role, $pdo) {
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? null;
    $case_id = $_GET['case_id'] ?? null;
    
    $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name, 
                   u2.name as assigned_to_name
            FROM tasks t 
            LEFT JOIN cases c ON t.case_id = c.id 
            JOIN users u ON t.created_by = u.id 
            JOIN users u2 ON t.assigned_to = u2.id 
            WHERE 1=1";
    $params = [];
    
    // Apply role-based filtering
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $user_id;
    } elseif ($user_role === 'case_manager') {
        // Case managers see tasks for cases they manage
        $sql .= " AND (t.assigned_to = ? OR c.assigned_to = ?)";
        $params[] = $user_id;
        $params[] = $user_id;
    }
    
    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }
    
    if ($case_id) {
        $sql .= " AND t.case_id = ?";
        $params[] = $case_id;
    }
    
    $sql .= " ORDER BY t.due_date ASC, t.priority DESC, t.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'tasks' => $tasks]);
}

function getOverdueTasks($user_id, $user_role, $pdo) {
    $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name,
                   u2.name as assigned_to_name
            FROM tasks t 
            LEFT JOIN cases c ON t.case_id = c.id 
            JOIN users u ON t.created_by = u.id 
            JOIN users u2 ON t.assigned_to = u2.id 
            WHERE t.due_date < NOW() AND t.status IN ('pending', 'in_progress')";
    $params = [];
    
    // Apply role-based filtering
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'tasks' => $tasks, 'count' => count($tasks)]);
}

function getUpcomingTasks($user_id, $user_role, $pdo) {
    $days = (int)($_GET['days'] ?? 7);
    
    $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name,
                   u2.name as assigned_to_name
            FROM tasks t 
            LEFT JOIN cases c ON t.case_id = c.id 
            JOIN users u ON t.created_by = u.id 
            JOIN users u2 ON t.assigned_to = u2.id 
            WHERE t.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY) 
            AND t.status IN ('pending', 'in_progress')";
    $params = [$days];
    
    // Apply role-based filtering
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'tasks' => $tasks, 'count' => count($tasks)]);
}

function getTaskStats($user_id, $user_role, $pdo) {
    $stats = [];
    
    if (in_array($user_role, ['attorney', 'paralegal'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status IN ('pending', 'in_progress')");
        $stmt->execute([$user_id]);
        $stats['pending'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND due_date < NOW() AND status IN ('pending', 'in_progress')");
        $stmt->execute([$user_id]);
        $stats['overdue'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$user_id]);
        $stats['completed_this_month'] = $stmt->fetchColumn();
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('pending', 'in_progress')");
        $stats['pending'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < NOW() AND status IN ('pending', 'in_progress')");
        $stats['overdue'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['completed_this_month'] = $stmt->fetchColumn();
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function createTask($user_id, $user_role, $pdo) {
    // Check permissions
    if (!has_permission('task:create')) {
        throw new Exception('Insufficient permissions to create tasks');
    }
    
    $case_id = !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null;
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $due_date = $_POST['due_date'] ?? null;
    $priority = (string)($_POST['priority'] ?? 'medium');
    $task_type = (string)($_POST['task_type'] ?? 'custom');
    
    if (empty($title) || $assigned_to <= 0) {
        throw new Exception('Title and assigned user are required');
    }
    
    // Validate case access if case_id provided
    if ($case_id) {
        $case_ids = get_user_cases_access($user_id, $user_role);
        if (!in_array($case_id, $case_ids)) {
            throw new Exception('Access denied to specified case');
        }
    }
    
    $task_data = [
        'case_id' => $case_id,
        'assigned_to' => $assigned_to,
        'title' => $title,
        'description' => $description ?: null,
        'due_date' => $due_date ?: null,
        'priority' => $priority,
        'task_type' => $task_type
    ];
    
    $task_id = create_task($task_data);
    
    echo json_encode(['success' => true, 'task_id' => $task_id, 'message' => 'Task created successfully']);
}

function updateTask($user_id, $user_role, $pdo) {
    $task_id = (int)($_POST['task_id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    
    if ($task_id <= 0 || empty($status)) {
        throw new Exception('Task ID and status are required');
    }
    
    // Check if user can update this task
    $stmt = $pdo->prepare("SELECT assigned_to, case_id FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        throw new Exception('Task not found');
    }
    
    // Check permissions
    $can_update = false;
    if ($task['assigned_to'] == $user_id) {
        $can_update = true; // User can update their own tasks
    } elseif (has_permission('task:update')) {
        $can_update = true; // User has update permission
    } elseif ($task['case_id'] && in_array($user_role, ['case_manager', 'partner'])) {
        $can_update = true; // Case managers and partners can update tasks for their cases
    }
    
    if (!$can_update) {
        throw new Exception('Insufficient permissions to update this task');
    }
    
    $success = update_task_status($task_id, $status, $user_id);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
    } else {
        throw new Exception('Failed to update task');
    }
}

function completeTask($user_id, $user_role, $pdo) {
    $task_id = (int)($_POST['task_id'] ?? 0);
    
    if ($task_id <= 0) {
        throw new Exception('Task ID is required');
    }
    
    // Check if user can complete this task
    $stmt = $pdo->prepare("SELECT assigned_to FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        throw new Exception('Task not found');
    }
    
    if ($task['assigned_to'] != $user_id && !has_permission('task:complete')) {
        throw new Exception('Insufficient permissions to complete this task');
    }
    
    $success = update_task_status($task_id, 'completed', $user_id);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Task completed successfully']);
    } else {
        throw new Exception('Failed to complete task');
    }
}

function deleteTask($user_id, $user_role, $pdo) {
    if (!has_permission('task:delete')) {
        throw new Exception('Insufficient permissions to delete tasks');
    }
    
    $task_id = (int)($_POST['task_id'] ?? 0);
    
    if ($task_id <= 0) {
        throw new Exception('Task ID is required');
    }
    
    // Get task details for logging
    $stmt = $pdo->prepare("SELECT case_id, title FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        throw new Exception('Task not found');
    }
    
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $success = $stmt->execute([$task_id]);
    
    if ($success && $stmt->rowCount() > 0) {
        // Log activity if associated with a case
        if ($task['case_id']) {
            log_case_activity($task['case_id'], $user_id, 'admin_action', 'Task Deleted', "Task: {$task['title']}");
        }
        
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
    } else {
        throw new Exception('Failed to delete task');
    }
}
?>
