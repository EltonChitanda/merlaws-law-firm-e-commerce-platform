<?php
// app/admin/_widgets.php - Reusable Widget Components

if (!function_exists('render_tasks_widget')) {
    function render_tasks_widget(int $user_id, string $role, int $limit = 10): string {
        $tasks = get_user_tasks($user_id, $limit);
        $overdue_tasks = get_overdue_tasks($user_id);
        
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-tasks"></i>';
        $html .= 'My Tasks';
        $html .= '<span class="badge bg-warning ms-2">' . count($tasks) . '</span>';
        $html .= '</h3>';
        
        if (empty($tasks)) {
            $html .= '<div class="empty-state">';
            $html .= '<i class="fas fa-check-circle"></i>';
            $html .= '<p class="mb-0">No pending tasks</p>';
            $html .= '</div>';
        } else {
            foreach ($tasks as $task) {
                $html .= '<div class="task-item" data-task-id="' . $task['id'] . '">';
                $html .= '<div class="task-checkbox" onclick="toggleTask(' . $task['id'] . ')"></div>';
                $html .= '<div class="task-content">';
                $html .= '<div class="task-title">' . e($task['title']) . '</div>';
                $html .= '<div class="task-meta">';
                if ($task['due_date']) {
                    $html .= '<span><i class="far fa-calendar"></i> Due: ' . date('M d, Y', strtotime($task['due_date'])) . '</span>';
                }
                $html .= '<span><i class="fas fa-flag"></i> ' . ucfirst($task['priority']) . '</span>';
                if ($task['case_title']) {
                    $html .= '<span><i class="fas fa-briefcase"></i> ' . e($task['case_title']) . '</span>';
                }
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<span class="priority-badge ' . $task['priority'] . '">' . $task['priority'] . '</span>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        
        // Add overdue tasks widget if there are any
        if (!empty($overdue_tasks)) {
            $html .= '<div class="content-card">';
            $html .= '<h3 class="section-title">';
            $html .= '<i class="fas fa-exclamation-triangle"></i>';
            $html .= 'Overdue Tasks';
            $html .= '<span class="badge bg-danger ms-2">' . count($overdue_tasks) . '</span>';
            $html .= '</h3>';
            
            foreach ($overdue_tasks as $task) {
                $html .= '<div class="task-item overdue" data-task-id="' . $task['id'] . '">';
                $html .= '<div class="task-checkbox" onclick="toggleTask(' . $task['id'] . ')"></div>';
                $html .= '<div class="task-content">';
                $html .= '<div class="task-title">' . e($task['title']) . '</div>';
                $html .= '<div class="task-meta">';
                $html .= '<span class="text-danger"><i class="far fa-calendar"></i> Overdue: ' . date('M d, Y', strtotime($task['due_date'])) . '</span>';
                $html .= '<span><i class="fas fa-flag"></i> ' . ucfirst($task['priority']) . '</span>';
                if ($task['case_title']) {
                    $html .= '<span><i class="fas fa-briefcase"></i> ' . e($task['case_title']) . '</span>';
                }
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<span class="priority-badge urgent">Overdue</span>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
}

if (!function_exists('render_notifications_widget')) {
    function render_notifications_widget(int $user_id, int $limit = 5): string {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT * FROM user_notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        $notifications = $stmt->fetchAll();
        
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-bell"></i>';
        $html .= 'Notifications';
        $html .= '<span class="badge bg-primary ms-2">' . count($notifications) . '</span>';
        $html .= '</h3>';
        
        if (empty($notifications)) {
            $html .= '<div class="empty-state">';
            $html .= '<i class="fas fa-bell-slash"></i>';
            $html .= '<p class="mb-0">No new notifications</p>';
            $html .= '</div>';
        } else {
            foreach ($notifications as $notification) {
                $html .= '<div class="notification-item">';
                $html .= '<div class="notification-content">';
                $html .= '<div class="notification-title">' . e($notification['title']) . '</div>';
                $html .= '<div class="notification-message">' . e($notification['message']) . '</div>';
                $html .= '<div class="notification-time">' . time_ago($notification['created_at']) . '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('render_case_trends_widget')) {
    function render_case_trends_widget(int $user_id, string $role): string {
        $case_ids = get_user_cases_access($user_id, $role);
        $pdo = db();
        
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-chart-line"></i>';
        $html .= 'Case Trends';
        $html .= '</h3>';
        
        if (empty($case_ids)) {
            $html .= '<div class="empty-state">';
            $html .= '<i class="fas fa-chart-line"></i>';
            $html .= '<p class="mb-0">No case data available</p>';
            $html .= '</div>';
        } else {
            $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
            
            // Last 6 months case trends
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM cases
                WHERE id IN ($placeholders)
                AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            $stmt->execute($case_ids);
            $trends = $stmt->fetchAll();
            
            $html .= '<canvas id="caseTrendsChart" width="400" height="200"></canvas>';
            $html .= '<script>';
            $html .= 'const caseTrendsData = ' . json_encode($trends) . ';';
            $html .= '</script>';
        }
        
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('render_upcoming_widget')) {
    function render_upcoming_widget(int $user_id, string $role, int $days = 7): string {
        $upcoming = get_upcoming_deadlines($user_id, $role, $days);
        
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-calendar-alt"></i>';
        $html .= 'Upcoming This Week';
        $html .= '<span class="badge bg-info ms-2">' . count($upcoming) . '</span>';
        $html .= '</h3>';
        
        if (empty($upcoming)) {
            $html .= '<div class="empty-state">';
            $html .= '<i class="fas fa-calendar-check"></i>';
            $html .= '<p class="mb-0">No upcoming deadlines</p>';
            $html .= '</div>';
        } else {
            foreach ($upcoming as $item) {
                $html .= '<div class="upcoming-item">';
                $html .= '<div class="upcoming-content">';
                $html .= '<div class="upcoming-title">' . e($item['title']) . '</div>';
                $html .= '<div class="upcoming-meta">';
                $html .= '<span><i class="far fa-calendar"></i> ' . date('M d, Y', strtotime($item['due_date'])) . '</span>';
                $html .= '<span><i class="fas fa-flag"></i> ' . ucfirst($item['priority']) . '</span>';
                if ($item['case_title']) {
                    $html .= '<span><i class="fas fa-briefcase"></i> ' . e($item['case_title']) . '</span>';
                }
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<span class="priority-badge ' . $item['priority'] . '">' . $item['priority'] . '</span>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('render_quick_actions_widget')) {
    function render_quick_actions_widget(string $role): string {
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-bolt"></i>';
        $html .= 'Quick Actions';
        $html .= '</h3>';
        
        $html .= '<div class="quick-actions-grid">';
        
        // Common actions for all roles
        if (has_permission('case:view')) {
            $html .= '<a href="/app/admin/cases.php" class="quick-action">';
            $html .= '<i class="fas fa-briefcase"></i>';
            $html .= '<span>View Cases</span>';
            $html .= '</a>';
        }
        
        if (has_permission('appointment:view')) {
            $html .= '<a href="/app/admin/calendar.php" class="quick-action">';
            $html .= '<i class="fas fa-calendar"></i>';
            $html .= '<span>Calendar</span>';
            $html .= '</a>';
        }
        
        if (has_permission('message:view')) {
            $html .= '<a href="/app/admin/messages.php" class="quick-action">';
            $html .= '<i class="fas fa-envelope"></i>';
            $html .= '<span>Messages</span>';
            $html .= '</a>';
        }
        
        // Role-specific actions
        if (in_array($role, ['attorney', 'paralegal'])) {
            if (has_permission('case:create')) {
                $html .= '<a href="/app/cases/create.php" class="quick-action">';
                $html .= '<i class="fas fa-plus"></i>';
                $html .= '<span>New Case</span>';
                $html .= '</a>';
            }
        }
        
        if ($role === 'billing') {
            if (has_permission('billing:invoice_approve')) {
                $html .= '<a href="/app/admin/service-requests.php" class="quick-action">';
                $html .= '<i class="fas fa-credit-card"></i>';
                $html .= '<span>Service Requests</span>';
                $html .= '</a>';
            }
        }
        
        if (in_array($role, ['super_admin', 'partner', 'case_manager'])) {
            if (has_permission('user:view')) {
                $html .= '<a href="/app/admin/user-management.php" class="quick-action">';
                $html .= '<i class="fas fa-users"></i>';
                $html .= '<span>User Management</span>';
                $html .= '</a>';
            }
            
            if (has_permission('report:view')) {
                $html .= '<a href="/app/admin/reports.php" class="quick-action">';
                $html .= '<i class="fas fa-chart-bar"></i>';
                $html .= '<span>Reports</span>';
                $html .= '</a>';
            }
        }
        
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('render_recent_activity_widget')) {
    function render_recent_activity_widget(int $user_id, string $role, int $limit = 8): string {
        $case_ids = get_user_cases_access($user_id, $role);
        $pdo = db();
        
        $html = '<div class="content-card">';
        $html .= '<h3 class="section-title">';
        $html .= '<i class="fas fa-history"></i>';
        $html .= 'Recent Activity';
        $html .= '</h3>';
        
        if (empty($case_ids)) {
            $html .= '<div class="empty-state">';
            $html .= '<i class="fas fa-history"></i>';
            $html .= '<p class="mb-0">No recent activity</p>';
            $html .= '</div>';
        } else {
            $placeholders = implode(',', array_fill(0, count($case_ids), '?'));
            $stmt = $pdo->prepare("
                SELECT ca.*, c.title as case_title, u.name as user_name
                FROM case_activities ca
                JOIN cases c ON ca.case_id = c.id
                JOIN users u ON ca.user_id = u.id
                WHERE ca.case_id IN ($placeholders)
                ORDER BY ca.created_at DESC
                LIMIT ?
            ");
            $stmt->execute(array_merge($case_ids, [$limit]));
            $activities = $stmt->fetchAll();
            
            if (empty($activities)) {
                $html .= '<div class="empty-state">';
                $html .= '<i class="fas fa-history"></i>';
                $html .= '<p class="mb-0">No recent activity</p>';
                $html .= '</div>';
            } else {
                foreach ($activities as $activity) {
                    $html .= '<div class="activity-item">';
                    $html .= '<div class="activity-icon">';
                    $html .= '<i class="fas fa-' . get_activity_icon($activity['type']) . '"></i>';
                    $html .= '</div>';
                    $html .= '<div class="activity-content">';
                    $html .= '<div class="activity-title">' . e($activity['title']) . '</div>';
                    $html .= '<div class="activity-meta">';
                    $html .= '<span>' . e($activity['case_title']) . '</span>';
                    $html .= '<span>by ' . e($activity['user_name']) . '</span>';
                    $html .= '<span>' . time_ago($activity['created_at']) . '</span>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
            }
        }
        
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('get_activity_icon')) {
    function get_activity_icon(string $type): string {
        $icons = [
            'document' => 'file',
            'message' => 'envelope',
            'appointment' => 'calendar',
            'status_change' => 'exchange-alt',
            'assignment' => 'user-plus',
            'service_request' => 'credit-card',
            'admin_action' => 'cog',
            'client_action' => 'user'
        ];
        
        return $icons[$type] ?? 'circle';
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        if ($time < 31536000) return floor($time/2592000) . ' months ago';
        
        return floor($time/31536000) . ' years ago';
    }
}
?>
