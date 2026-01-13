<?php
// app/config.php - FINAL FIX: Both Admin and Client Can Login

if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'medlaw');
define('DB_USER', 'root');
define('DB_PASS', '');

// Email settings
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'no-reply@merlaws.com');

// File upload settings
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/cases/documents/');

date_default_timezone_set('Africa/Johannesburg');

// CRITICAL: Helper to determine current context
if (!function_exists('get_context')) {
    function get_context(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Check for admin parameter in query string (for API calls)
        if (isset($_GET['admin']) && $_GET['admin'] === '1') {
            return 'admin';
        }
        
        // Check if in admin area by URL or script path
        if (strpos($uri, '/app/admin/') !== false || strpos($script, '/app/admin/') !== false) {
            return 'admin';
        }
        
        // Check if it's admin login page
        if (strpos($uri, 'admin-login.php') !== false || strpos($script, 'admin-login.php') !== false) {
            return 'admin';
        }
        
        return 'client';
    }
}

// Context-aware session initialization
if (!function_exists('init_session_for_context')) {
    function init_session_for_context(string $context): void {
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
        
        $sessionName = $context === 'admin' ? 'MERLAWS_ADMIN' : 'MERLAWS_CLIENT';
        session_name($sessionName);
        
        // Auto-detect domain from HTTP_HOST
        $domain = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            // Remove port if present
            $host = preg_replace('/:\d+$/', '', $host);
            // For production domains, use the domain (without www if present)
            // For localhost, leave empty
            if ($host !== 'localhost' && $host !== '127.0.0.1' && strpos($host, 'localhost') === false) {
                // Remove 'www.' prefix if present for cookie domain
                $domain = preg_replace('/^www\./', '', $host);
                // Only set domain if it's a valid domain (has at least one dot)
                if (strpos($domain, '.') === false) {
                    $domain = '';
                }
            }
        }
        
        // Determine if we're using HTTPS
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                 || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                 || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
                 || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',  // Changed from '' to '/' for production
            'domain' => $domain,
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

    session_start();
    }
}

// Auto-initialize session based on context
$context = get_context();
init_session_for_context($context);

// Database connection
if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $baseOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 3,
            ];

            $attempts = [];
            $attempts[] = ['dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 'user' => DB_USER, 'pass' => DB_PASS];
            if (DB_HOST !== 'localhost') {
                $attempts[] = ['dsn' => 'mysql:host=localhost;dbname=' . DB_NAME . ';charset=utf8mb4', 'user' => DB_USER, 'pass' => DB_PASS];
            }

            $lastException = null;
            foreach ($attempts as $attempt) {
                try {
                    $pdo = new PDO($attempt['dsn'], $attempt['user'], $attempt['pass'], $baseOptions);
                    break;
                } catch (Throwable $ex) {
                    $lastException = $ex;
                }
            }

            if ($pdo === null && $lastException) {
                throw $lastException;
            }
        }
        return $pdo;
    }
}

// Basic helpers
if (!function_exists('is_post')) {
    function is_post(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('e')) {
    function e(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// Authentication helpers for separate sessions
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('get_user_id')) {
    function get_user_id(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }
}

if (!function_exists('get_user_name')) {
    function get_user_name(): string {
        return (string)($_SESSION['name'] ?? '');
    }
}

if (!function_exists('get_user_role')) {
    function get_user_role(): string {
        $base = (string)($_SESSION['role'] ?? 'client');
        
        // Handle role override for super_admin testing
        if ($base === 'super_admin' && !empty($_SESSION['role_override'])) {
            return (string)$_SESSION['role_override'];
        }
        
        return $base;
    }
}

if (!function_exists('get_user_email')) {
    function get_user_email(): string {
        return (string)($_SESSION['email'] ?? '');
    }
}

if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            $context = get_context();
            $loginUrl = $context === 'admin' 
                ? '/app/admin-login.php' 
                : '/app/login.php';
            redirect($loginUrl);
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void {
        require_login();
        
        $adminRoles = [
            'super_admin', 'admin', 'manager', 'office_admin', 'partner',
            'attorney', 'paralegal', 'intake', 'case_manager', 'billing',
            'doc_specialist', 'it_admin', 'compliance', 'receptionist'
        ];
        
        $role = get_user_role();
        
        if (!in_array($role, $adminRoles, true)) {
            redirect('/app/dashboard.php?error=insufficient_permissions');
        }
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool {
        $role = get_user_role();
        return in_array($role, [
            'super_admin', 'admin', 'manager', 'office_admin', 'partner',
            'attorney', 'paralegal', 'intake', 'case_manager', 'billing',
            'doc_specialist', 'it_admin', 'compliance', 'receptionist'
        ], true);
    }
}

// RBAC helpers
if (!function_exists('get_user_permissions')) {
    function get_user_permissions(): array {
        if (!is_logged_in()) {
            return [];
        }

        if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
            return $_SESSION['permissions'];
        }

        $role = get_user_role();
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT p.name 
            FROM role_permissions rp 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE rp.role = ?
        ");
        $stmt->execute([$role]);
        $perms = array_column($stmt->fetchAll(), 'name');

        $_SESSION['permissions'] = $perms;
        return $perms;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool {
        if (get_user_role() === 'super_admin') {
            return true;
        }
        
        $perms = get_user_permissions();
        return in_array($permission, $perms, true);
    }
}

if (!function_exists('require_permission')) {
    function require_permission(string $permission): void {
        require_login();
        
        if (!has_permission($permission)) {
            redirect('/app/dashboard.php?error=insufficient_permissions');
        }
    }
}

// Case management functions
if (!function_exists('get_user_cases')) {
    function get_user_cases(int $user_id, string $status = null): array {
        $pdo = db();
        $sql = "SELECT c.*, 
                COUNT(DISTINCT d.id) as document_count, 
                COUNT(DISTINCT sr.id) as service_count
                FROM cases c 
                LEFT JOIN case_documents d ON c.id = d.case_id 
                LEFT JOIN service_requests sr ON c.id = sr.case_id AND sr.status != 'cart'
                WHERE c.user_id = ?";
        $params = [$user_id];
        
        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.updated_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('create_case')) {
    function create_case(array $data): int {
        $pdo = db();
        
        // Prepare data with defaults
        $user_id = (int)($data['user_id'] ?? 0);
        $title = trim((string)($data['title'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $case_type = (string)($data['case_type'] ?? 'other');
        $status = (string)($data['status'] ?? 'draft');
        $priority = (string)($data['priority'] ?? 'medium');
        
        // Insert the case
        $stmt = $pdo->prepare("
            INSERT INTO cases (user_id, title, description, case_type, status, priority) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $description, $case_type, $status, $priority]);
        
        $case_id = (int)$pdo->lastInsertId();
        
        // Log activity (case activity log)
        log_case_activity($case_id, $user_id, 'case_created', 'Case Created', "New case '{$title}' created");
        
        // Note: Audit logging is done in create.php after case creation
        // to ensure we have the case_id for the entity_id field
        
        return $case_id;
    }
}

if (!function_exists('get_case')) {
    function get_case(int $case_id, int $user_id): ?array {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT c.*, u.name as client_name 
                FROM cases c 
                JOIN users u ON c.user_id = u.id 
            WHERE c.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$case_id, $user_id]);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('get_case_documents')) {
    function get_case_documents(int $case_id): array {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT cd.*, u.name as uploaded_by_name 
            FROM case_documents cd 
            JOIN users u ON cd.uploaded_by = u.id 
            WHERE cd.case_id = ? 
            ORDER BY cd.uploaded_at DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
}

if (!function_exists('upload_case_document')) {
    function upload_case_document(int $case_id, array $file, array $options = []): array {
        $pdo = db();
        
        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed'];
        }
        
        // Check file size
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'error' => 'File too large. Maximum size: ' . format_file_size(UPLOAD_MAX_SIZE)];
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, UPLOAD_ALLOWED_TYPES)) {
            return ['success' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', UPLOAD_ALLOWED_TYPES)];
        }
        
        // Generate unique filename
        $original_filename = $file['name'];
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        
        // Create upload directory if it doesn't exist
        $upload_dir = UPLOAD_PATH . $case_id . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_path = $upload_dir . $unique_filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
        
        // Calculate file checksum
        $checksum = hash_file('sha256', $file_path);
        
        // Insert document record
        $stmt = $pdo->prepare("
            INSERT INTO case_documents (
                case_id, filename, original_filename, file_path, file_size, 
                mime_type, document_type, description, uploaded_by, checksum
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $case_id,
            $unique_filename,
            $original_filename,
            $file_path,
            $file['size'],
            $file['type'],
            $options['document_type'] ?? null,
            $options['description'] ?? null,
            get_user_id(),
            $checksum
        ]);
        
        if (!$result) {
            // Clean up file if database insert failed
            unlink($file_path);
            return ['success' => false, 'error' => 'Failed to save document record'];
        }
        
        $document_id = (int)$pdo->lastInsertId();
        
        // Log activity (case activity log)
        log_case_activity(
            $case_id, 
            get_user_id(), 
            'document_upload', 
            'Document Uploaded', 
            "Uploaded document: {$original_filename}"
        );
        
        // Note: Audit logging is done in upload-document.php after successful upload
        // to ensure we have all the file information
        
        return [
            'success' => true, 
            'document_id' => $document_id,
            'filename' => $unique_filename,
            'original_filename' => $original_filename
        ];
    }
}

if (!function_exists('get_case_activities')) {
    function get_case_activities(int $case_id): array {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT ca.*, u.name as user_name 
            FROM case_activities ca 
            JOIN users u ON ca.user_id = u.id 
            WHERE ca.case_id = ? 
            ORDER BY ca.created_at DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
}

if (!function_exists('get_cart_items')) {
    function get_cart_items(int $case_id): array {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT sr.*, s.name, s.description, s.category, s.estimated_duration
            FROM service_requests sr 
            JOIN services s ON sr.service_id = s.id 
            WHERE sr.case_id = ? AND sr.status = 'cart'
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
}

if (!function_exists('get_case_status_badge')) {
    function get_case_status_badge(string $status): string {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'active' => '<span class="badge bg-success">Active</span>',
            'under_review' => '<span class="badge bg-warning">Under Review</span>',
            'closed' => '<span class="badge bg-dark">Closed</span>'
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('get_priority_badge')) {
    function get_priority_badge(string $priority): string {
        $badges = [
            'low' => '<span class="badge bg-info">Low Priority</span>',
            'medium' => '<span class="badge bg-primary">Medium Priority</span>',
            'high' => '<span class="badge bg-warning">High Priority</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>'
        ];
        return $badges[$priority] ?? '<span class="badge bg-secondary">' . ucfirst($priority) . '</span>';
    }
}

if (!function_exists('get_services')) {
    function get_services(string $category = ''): array {
        $pdo = db();
        $sql = "SELECT * FROM services WHERE is_active = 1";
        $params = [];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY category, name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('get_service_categories')) {
    function get_service_categories(): array {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT DISTINCT category 
            FROM services 
            WHERE is_active = 1 
            ORDER BY category
        ");
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'category');
    }
}

if (!function_exists('add_to_cart')) {
    function add_to_cart(int $case_id, int $service_id, array $options = []): bool {
        $pdo = db();
        
        // Check if service already exists in cart
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM service_requests 
            WHERE case_id = ? AND service_id = ? AND status = 'cart'
        ");
        $stmt->execute([$case_id, $service_id]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // Already in cart
        }
        
        // Get service details to check if it's a consultation
        $service_stmt = $pdo->prepare("SELECT category FROM services WHERE id = ?");
        $service_stmt->execute([$service_id]);
        $service = $service_stmt->fetch();
        
        // Only include consult_date and consult_time for consultation services
        $is_consultation = $service && strtolower($service['category']) === 'consultation';
        
        if ($is_consultation) {
            // For consultation services, include consult_date and consult_time
            $stmt = $pdo->prepare("
                INSERT INTO service_requests (case_id, service_id, status, notes, urgency, consult_date, consult_time) 
                VALUES (?, ?, 'cart', ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $case_id,
                $service_id,
                $options['notes'] ?? null,
                $options['urgency'] ?? 'standard',
                $options['consult_date'] ?? null,
                $options['consult_time'] ?? null
            ]);
        } else {
            // For non-consultation services, don't include consult_date and consult_time
            $stmt = $pdo->prepare("
                INSERT INTO service_requests (case_id, service_id, status, notes, urgency) 
                VALUES (?, ?, 'cart', ?, ?)
            ");
            
            $result = $stmt->execute([
                $case_id,
                $service_id,
                $options['notes'] ?? null,
                $options['urgency'] ?? 'standard'
            ]);
        }
        
        return $result;
    }
}

if (!function_exists('remove_from_cart')) {
    function remove_from_cart(int $case_id, int $item_id): bool {
        $pdo = db();
        
        // Verify item belongs to case and is in cart
        $stmt = $pdo->prepare("
            DELETE FROM service_requests 
            WHERE id = ? AND case_id = ? AND status = 'cart'
        ");
        
        $result = $stmt->execute([$item_id, $case_id]);
        
        return $result && $stmt->rowCount() > 0;
    }
}

if (!function_exists('log_case_activity')) {
    function log_case_activity(int $case_id, int $user_id, string $type, string $title, string $description = null): void {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO case_activities (case_id, user_id, activity_type, title, description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$case_id, $user_id, $type, $title, $description]);
    }
}

if (!function_exists('create_user_notification')) {
    function create_user_notification(int $user_id, string $type, string $title, string $message, string $action_url = null): bool {
            $pdo = db();
            $stmt = $pdo->prepare("
            INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read) 
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        return $stmt->execute([$user_id, $type, $title, $message, $action_url]);
    }
}

if (!function_exists('create_role_notification')) {
    function create_role_notification(string $role, string $type, string $title, string $message, string $action_url = null): int {
        $pdo = db();
        $created = 0;
        
        // Get all users with the specified role
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ? AND is_active = 1");
        $stmt->execute([$role]);
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            if (create_user_notification($user['id'], $type, $title, $message, $action_url)) {
                $created++;
            }
        }
        
        return $created;
    }
}

if (!function_exists('extract_name_parts')) {
    /**
     * Extract first name and surname from a full name string
     * @param string $fullName The full name (e.g., "John Doe" or "Mary Jane Smith")
     * @return array ['first_name' => string, 'surname' => string]
     */
    function extract_name_parts(string $fullName): array {
        $fullName = trim($fullName);
        $parts = explode(' ', $fullName);
        
        if (count($parts) === 1) {
            // Only one name provided, use as first name
            return [
                'first_name' => $parts[0],
                'surname' => ''
            ];
        } else {
            // First part is first name, rest is surname
            $first_name = $parts[0];
            $surname = implode(' ', array_slice($parts, 1));
            
            return [
                'first_name' => $first_name,
                'surname' => $surname
            ];
        }
    }
}

if (!function_exists('create_case_notification')) {
    function create_case_notification(int $case_id, string $type, string $title, string $message, string $action_url = null): int {
        $pdo = db();
        $created = 0;
        
        // Get case details
        $stmt = $pdo->prepare("SELECT assigned_to, user_id FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $case = $stmt->fetch();
        
        if (!$case) return 0;
        
        $notify_users = [];
        
        // Notify assigned attorney/paralegal
        if ($case['assigned_to']) {
            $notify_users[] = $case['assigned_to'];
        }
        
        // Notify case manager
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'case_manager' AND is_active = 1 LIMIT 1");
        $stmt->execute();
        $case_manager = $stmt->fetch();
        if ($case_manager) {
            $notify_users[] = $case_manager['id'];
        }
        
        // Notify client
        if ($case['user_id']) {
            $notify_users[] = $case['user_id'];
        }
        
        // Remove duplicates
        $notify_users = array_unique($notify_users);
        
        foreach ($notify_users as $user_id) {
            if (create_user_notification($user_id, $type, $title, $message, $action_url)) {
                $created++;
            }
        }
        
        return $created;
    }
}

if (!function_exists('format_file_size')) {
    function format_file_size(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Case status validation helper
if (!function_exists('can_add_service_requests')) {
    function can_add_service_requests(array $case): array {
        $status = $case['status'] ?? '';
        
        if (in_array($status, ['draft', 'closed'], true)) {
            $message = $status === 'draft' 
                ? 'Service requests cannot be added to draft cases. Please wait for your case to be activated.'
                : 'Service requests cannot be added to closed cases. Please contact support if you need to reopen your case.';
            
            return [
                'can_add' => false,
                'message' => $message,
                'status' => $status
            ];
        }
        
        return [
            'can_add' => true,
            'message' => '',
            'status' => $status
        ];
    }
}

// Update case function
if (!function_exists('update_case')) {
    function update_case(int $case_id, array $data): bool {
        $pdo = db();
        $stmt = $pdo->prepare("
            UPDATE cases 
            SET title = ?, description = ?, case_type = ?, status = ?, priority = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['case_type'] ?? 'other',
            $data['status'] ?? 'draft',
            $data['priority'] ?? 'medium',
            $case_id
        ]);
    }
}

// Service request management functions
if (!function_exists('get_all_service_requests')) {
    function get_all_service_requests(string $status = null): array {
        $pdo = db();
        $sql = "SELECT sr.*, s.name as service_name, s.category, c.title as case_title, u.name as client_name
                FROM service_requests sr
                JOIN services s ON sr.service_id = s.id
                JOIN cases c ON sr.case_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE sr.status != 'cart'";
        $params = [];
        
        if ($status) {
            $sql .= " AND sr.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sr.urgency DESC, sr.requested_at ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('get_service_request')) {
    function get_service_request(int $request_id): array|false {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT sr.*, s.name as service_name, s.category, c.title as case_title, c.user_id as case_owner_id, u.name as client_name
            FROM service_requests sr
            JOIN services s ON sr.service_id = s.id
            JOIN cases c ON sr.case_id = c.id
            JOIN users u ON c.user_id = u.id
            WHERE sr.id = ?
        ");
        $stmt->execute([$request_id]);
        return $stmt->fetch();
    }
}

if (!function_exists('approve_service_request')) {
    function approve_service_request(int $request_id, int $admin_id, string $notes = null): bool {
        $pdo = db();
        
        // Get request details first
        $request = get_service_request($request_id);
        if (!$request) {
            return false;
        }
        
        $stmt = $pdo->prepare("
            UPDATE service_requests 
            SET status = 'approved', processed_at = NOW(), processed_by = ?, admin_notes = ?
            WHERE id = ? AND status = 'pending'
        ");
        
        $success = $stmt->execute([$admin_id, $notes, $request_id]);
        
        if ($success && $stmt->rowCount() > 0) {
            // Log activity
            log_case_activity($request['case_id'], $admin_id, 'admin_action', 'Service Request Approved', "Service: {$request['service_name']}");
            
            // Create user notification
            create_user_notification($request['case_owner_id'], 'service_approved', 'Service Request Approved', 
                "Your request for '{$request['service_name']}' has been approved.", 
                "/app/cases/view.php?id={$request['case_id']}");
        }
        
        return $success && $stmt->rowCount() > 0;
    }
}

if (!function_exists('reject_service_request')) {
    function reject_service_request(int $request_id, int $admin_id, string $notes = null): bool {
        $pdo = db();
        
        // Get request details first
        $request = get_service_request($request_id);
        if (!$request) {
            return false;
        }
        
        $stmt = $pdo->prepare("
            UPDATE service_requests 
            SET status = 'rejected', processed_at = NOW(), processed_by = ?, admin_notes = ?
            WHERE id = ? AND status = 'pending'
        ");
        
        $success = $stmt->execute([$admin_id, $notes, $request_id]);
        
        if ($success && $stmt->rowCount() > 0) {
            // Log activity
            log_case_activity($request['case_id'], $admin_id, 'admin_action', 'Service Request Rejected', "Service: {$request['service_name']}");
            
            // Create user notification
            create_user_notification($request['case_owner_id'], 'service_rejected', 'Service Request Rejected', 
                "Your request for '{$request['service_name']}' was rejected. " . ($notes ? "Reason: $notes" : ""), 
                "/app/cases/view.php?id={$request['case_id']}");
        }
        
        return $success && $stmt->rowCount() > 0;
    }
}

// User notification functions
if (!function_exists('get_user_notifications')) {
    function get_user_notifications(int $user_id, bool $unread_only = false): array {
        $pdo = db();
        $sql = "SELECT * FROM user_notifications WHERE user_id = ?";
        $params = [$user_id];
        
        if ($unread_only) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

// Dashboard statistics functions
if (!function_exists('get_dashboard_stats')) {
    function get_dashboard_stats(int $user_id = null): array {
        $pdo = db();
        
        if ($user_id && !is_admin()) {
            // Client statistics
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE user_id = ? AND status IN ('active', 'under_review')");
            $stmt->execute([$user_id]);
            $active_cases = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE user_id = ? AND status = 'closed'");
            $stmt->execute([$user_id]);
            $completed_cases = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT cd.id) 
                FROM case_documents cd 
                JOIN cases c ON cd.case_id = c.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $total_documents = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM service_requests sr
                JOIN cases c ON sr.case_id = c.id
                WHERE c.user_id = ? AND sr.status = 'pending'
            ");
            $stmt->execute([$user_id]);
            $pending_requests = $stmt->fetchColumn();
            
            return [
                'active_cases' => $active_cases,
                'completed_cases' => $completed_cases,
                'total_documents' => $total_documents,
                'pending_requests' => $pending_requests,
                'upcoming_appointments' => 0, // TODO: Implement appointments
                'unread_messages' => 0 // TODO: Implement messages
            ];
        } else {
            // Admin statistics
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'client' AND is_active = TRUE");
            $stmt->execute();
            $total_clients = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
            $stmt->execute();
            $active_cases = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
            $stmt->execute();
            $pending_requests = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE status != 'cart'");
            $stmt->execute();
            $total_requests = $stmt->fetchColumn();
            
            return [
                'total_clients' => $total_clients,
                'active_cases' => $active_cases,
                'pending_requests' => $pending_requests,
                'total_requests' => $total_requests
            ];
        }
    }
}

// Security and validation helpers
if (!function_exists('sanitize_filename')) {
    function sanitize_filename(string $filename): string {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}

if (!function_exists('is_safe_path')) {
    function is_safe_path(string $path): bool {
        $realPath = realpath($path);
        $uploadPath = realpath(UPLOAD_PATH);
        return $realPath && $uploadPath && strpos($realPath, $uploadPath) === 0;
    }
}

// Error logging
if (!function_exists('log_error')) {
    function log_error(string $message, array $context = []): void {
        $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $log_message .= ' - Context: ' . json_encode($context);
        }
        error_log($log_message);
    }
}

// Upload error message helper
if (!function_exists('get_upload_error_message')) {
    function get_upload_error_message(int $error_code): string {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}

// Document download helper
if (!function_exists('download_case_document')) {
    function download_case_document(int $document_id, int $user_id = null): array|false {
        $pdo = db();
        $sql = "SELECT d.*, c.user_id as case_owner_id 
                FROM case_documents d 
                JOIN cases c ON d.case_id = c.id 
                WHERE d.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$document_id]);
        $document = $stmt->fetch();
        
        if (!$document) {
            return false;
        }
        
        // Check access permissions
        if ($user_id && !is_admin() && (int)$document['case_owner_id'] !== $user_id) {
            return false;
        }
        
        return $document;
    }
}

// Submit service requests from cart
if (!function_exists('submit_service_requests')) {
    function submit_service_requests(int $case_id): bool {
        $pdo = db();
        $stmt = $pdo->prepare("
            UPDATE service_requests 
            SET status = 'pending', requested_at = NOW() 
            WHERE case_id = ? AND status = 'cart'
        ");
        
        $success = $stmt->execute([$case_id]);
        
        if ($success) {
            log_case_activity($case_id, get_user_id(), 'service_request', 'Service Requests Submitted', 'Client submitted service requests for approval');
        }
        
        return $success;
    }
}

// ========================================
///* ====  INVOICE MANAGEMENT FUNCTIONS/==========================================================

if (!function_exists('get_invoice_status_badge')) {
    function get_invoice_status_badge(string $status): string {
        $badges = [
            'draft'     => '<span class="badge bg-secondary">Draft</span>',
            'sent'      => '<span class="badge bg-info">Sent</span>',
            'paid'      => '<span class="badge bg-success">Paid</span>',
            'overdue'   => '<span class="badge bg-danger">Overdue</span>',
            'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
            'void'      => '<span class="badge bg-dark">Void</span>'
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount): string {
        return 'R' . number_format($amount, 2);
    }
}

if (!function_exists('get_invoice_permissions')) {
    function get_invoice_permissions(): array {
        return [
            'invoice:create'   => 'Create invoices',
            'invoice:view'     => 'View invoices',
            'invoice:edit'     => 'Edit invoices',
            'invoice:delete'   => 'Delete/void invoices',
            'invoice:send'     => 'Send invoices to clients',
            'invoice:payment'  => 'Record payments',
            'invoice:pdf'      => 'Generate invoice PDFs',
            'payment:process'  => 'Process payments',
            'payment:view'     => 'View payment history'
        ];
    }
}

/* --------------------------------------------------------------
   CAN ACCESS INVOICE
   -------------------------------------------------------------- */
if (!function_exists('can_access_invoice')) {
    function can_access_invoice(int $invoice_id, int $user_id = null): bool {
        $user_id = $user_id ?: get_user_id();
        $role    = get_user_role();

        if (in_array($role, ['super_admin', 'billing'])) {
            return true;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT i.client_id, i.created_by 
            FROM invoices i 
            WHERE i.id = ?
            LIMIT 1
        ");
        $stmt->execute([$invoice_id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            return false;
        }

        return (int)$invoice['created_by'] === $user_id
            || (int)$invoice['client_id'] === $user_id;
    }
}

if (!function_exists('get_payment_methods')) {
    function get_payment_methods(): array {
        return [
            'payfast'       => 'PayFast Online Payment',
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque'        => 'Cheque',
            'eft'           => 'EFT'
        ];
    }
}

/* --------------------------------------------------------------
   INVOICE STATISTICS
   -------------------------------------------------------------- */
if (!function_exists('get_invoice_statistics')) {
    function get_invoice_statistics(): array {
        $pdo = db();

        // Total outstanding
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(total_amount),0) AS total_outstanding
            FROM invoices 
            WHERE status IN ('sent','overdue')
        ");
        $stats['total_outstanding'] = $stmt->fetchColumn();

        // Overdue amount
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(total_amount),0) AS overdue_amount
            FROM invoices 
            WHERE status = 'overdue' 
               OR (status = 'sent' AND due_date < CURDATE())
        ");
        $stats['overdue_amount'] = $stmt->fetchColumn();

        // Paid this month (from invoice_payments)
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(ip.amount),0) AS paid_this_month
            FROM invoice_payments ip
            JOIN invoices i ON ip.invoice_id = i.id
            WHERE i.status = 'paid'
              AND MONTH(ip.payment_date) = MONTH(CURDATE())
              AND YEAR(ip.payment_date)  = YEAR(CURDATE())
        ");
        $stats['paid_this_month'] = $stmt->fetchColumn();

        // Total invoices
        $stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
        $stats['total_invoices'] = $stmt->fetchColumn();

        return $stats;
    }
}

// ========================================
// ROLE-BASED DATA FILTERING FUNCTIONS
// ========================================

if (!function_exists('get_user_cases_access')) {
    function get_user_cases_access(int $user_id, string $role): array {
        $pdo = db();
        
        if (in_array($role, ['super_admin', 'partner', 'case_manager', 'office_admin'])) {
            // Full access - return all case IDs
            $stmt = $pdo->query("SELECT id FROM cases ORDER BY updated_at DESC");
            return array_column($stmt->fetchAll(), 'id');
        } elseif (in_array($role, ['attorney', 'paralegal'])) {
            // Only assigned cases
            $stmt = $pdo->prepare("SELECT id FROM cases WHERE assigned_to = ? ORDER BY updated_at DESC");
            $stmt->execute([$user_id]);
            return array_column($stmt->fetchAll(), 'id');
        } elseif ($role === 'billing') {
            // Cases with financial activity
            $stmt = $pdo->query("
                SELECT DISTINCT c.id 
                FROM cases c 
                WHERE EXISTS (
                    SELECT 1 FROM service_requests sr 
                    WHERE sr.case_id = c.id AND sr.status != 'cart'
                ) 
                ORDER BY c.updated_at DESC
            ");
            return array_column($stmt->fetchAll(), 'id');
        } elseif ($role === 'doc_specialist') {
            // Cases with document activity
            $stmt = $pdo->query("
                SELECT DISTINCT c.id 
                FROM cases c 
                WHERE EXISTS (
                    SELECT 1 FROM case_documents cd 
                    WHERE cd.case_id = c.id
                ) 
                ORDER BY c.updated_at DESC
            ");
            return array_column($stmt->fetchAll(), 'id');
        } elseif ($role === 'compliance') {
            // Cases with compliance requests
            $stmt = $pdo->query("
                SELECT DISTINCT c.id 
                FROM cases c 
                WHERE EXISTS (
                    SELECT 1 FROM compliance_requests cr 
                    WHERE cr.case_id = c.id
                ) 
                ORDER BY c.updated_at DESC
            ");
            return array_column($stmt->fetchAll(), 'id');
        } elseif ($role === 'receptionist') {
            // Cases with appointments
            $stmt = $pdo->query("
                SELECT DISTINCT c.id 
                FROM cases c 
                WHERE EXISTS (
                    SELECT 1 FROM appointments a 
                    WHERE a.case_id = c.id
                ) 
                ORDER BY c.updated_at DESC
            ");
            return array_column($stmt->fetchAll(), 'id');
        }
        
        return [];
    }
}

if (!function_exists('get_user_tasks')) {
    function get_user_tasks(int $user_id, int $limit = 20, string $status = null): array {
        $pdo = db();
        
        $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name 
                FROM tasks t 
                LEFT JOIN cases c ON t.case_id = c.id 
                JOIN users u ON t.created_by = u.id 
                WHERE t.assigned_to = ?";
        $params = [$user_id];
        
        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY t.due_date ASC, t.priority DESC, t.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('get_overdue_tasks')) {
    function get_overdue_tasks(int $user_id = null): array {
        $pdo = db();
        
        $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name 
                FROM tasks t 
                LEFT JOIN cases c ON t.case_id = c.id 
                JOIN users u ON t.created_by = u.id 
                WHERE t.due_date < NOW() AND t.status IN ('pending', 'in_progress')";
        $params = [];
        
        if ($user_id) {
            $sql .= " AND t.assigned_to = ?";
            $params[] = $user_id;
        }
        
        $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('create_task')) {
    function create_task(array $data): int {
        $pdo = db();
        
        $case_id = isset($data['case_id']) ? (int)$data['case_id'] : null;
        $assigned_to = (int)$data['assigned_to'];
        $title = trim((string)$data['title']);
        $description = isset($data['description']) ? trim((string)$data['description']) : null;
        $due_date = isset($data['due_date']) ? $data['due_date'] : null;
        $priority = (string)($data['priority'] ?? 'medium');
        $task_type = (string)($data['task_type'] ?? 'custom');
        $created_by = get_user_id();
        
        $stmt = $pdo->prepare("
            INSERT INTO tasks (case_id, assigned_to, title, description, due_date, priority, task_type, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$case_id, $assigned_to, $title, $description, $due_date, $priority, $task_type, $created_by]);
        
        $task_id = (int)$pdo->lastInsertId();
        
        // Log activity if associated with a case
        if ($case_id) {
            log_case_activity($case_id, $created_by, 'admin_action', 'Task Created', "Task: {$title}");
        }
        
        return $task_id;
    }
}

if (!function_exists('update_task_status')) {
    function update_task_status(int $task_id, string $status, int $user_id = null): bool {
        $pdo = db();
        
        $user_id = $user_id ?: get_user_id();
        $completed_at = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
        $completed_by = ($status === 'completed') ? $user_id : null;
        
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = ?, completed_at = ?, completed_by = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $success = $stmt->execute([$status, $completed_at, $completed_by, $task_id]);
        
        if ($success && $stmt->rowCount() > 0) {
            // Get task details for logging
            $task_stmt = $pdo->prepare("SELECT case_id, title FROM tasks WHERE id = ?");
            $task_stmt->execute([$task_id]);
            $task = $task_stmt->fetch();
            
            if ($task && $task['case_id']) {
                log_case_activity($task['case_id'], $user_id, 'admin_action', 'Task Updated', "Task '{$task['title']}' status changed to {$status}");
            }
        }
        
        return $success && $stmt->rowCount() > 0;
    }
}

if (!function_exists('get_role_based_stats')) {
    function get_role_based_stats(int $user_id, string $role): array {
        $pdo = db();
        $stats = [];
        
        switch($role) {
            case 'super_admin':
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
                $stats['total_users'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
                $stats['active_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
                $stats['pending_requests'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('pending', 'in_progress')");
                $stats['pending_tasks'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < NOW() AND status IN ('pending', 'in_progress')");
                $stats['overdue_tasks'] = $stmt->fetchColumn();
                break;
                
            case 'partner':
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
                $stats['active_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'closed' AND YEAR(updated_at) = YEAR(NOW())");
                $stats['cases_closed_ytd'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
                $stats['pending_requests'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('pending', 'in_progress')");
                $stats['team_tasks'] = $stmt->fetchColumn();
                break;
                
            case 'case_manager':
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
                $stats['active_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE assigned_to IS NULL AND status = 'active'");
                $stats['unassigned_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status IN ('pending', 'in_progress')");
                $stats['team_tasks'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date < NOW() AND status IN ('pending', 'in_progress')");
                $stats['overdue_tasks'] = $stmt->fetchColumn();
                break;
                
            case 'attorney':
            case 'paralegal':
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE assigned_to = ? AND status IN ('active', 'under_review')");
                $stmt->execute([$user_id]);
                $stats['my_active_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE assigned_to = ? AND start_time > NOW() AND start_time <= DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'scheduled'");
                $stmt->execute([$user_id]);
                $stats['upcoming_appointments'] = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status IN ('pending', 'in_progress')");
                $stmt->execute([$user_id]);
                $stats['my_tasks'] = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND due_date < NOW() AND status IN ('pending', 'in_progress')");
                $stmt->execute([$user_id]);
                $stats['overdue_tasks'] = $stmt->fetchColumn();
                break;
                
            case 'billing':
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
                $stats['pending_requests'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'approved' AND processed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stats['approved_this_month'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE task_type = 'service_followup' AND status IN ('pending', 'in_progress')");
                $stats['billing_tasks'] = $stmt->fetchColumn();
                break;
                
            case 'compliance':
                $stmt = $pdo->query("SELECT COUNT(*) FROM compliance_requests WHERE status = 'submitted'");
                $stats['pending_requests'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM compliance_requests WHERE status = 'under_review'");
                $stats['under_review'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE task_type = 'admin_task' AND status IN ('pending', 'in_progress')");
                $stats['compliance_tasks'] = $stmt->fetchColumn();
                break;
                
            default:
                $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')");
                $stats['active_cases'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
                $stats['pending_requests'] = $stmt->fetchColumn();
        }
        
        return $stats;
    }
}

if (!function_exists('get_upcoming_deadlines')) {
    function get_upcoming_deadlines(int $user_id, string $role, int $days = 7): array {
        $pdo = db();
        
        $sql = "SELECT t.*, c.title as case_title, u.name as created_by_name 
                FROM tasks t 
                LEFT JOIN cases c ON t.case_id = c.id 
                JOIN users u ON t.created_by = u.id 
                WHERE t.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY) 
                AND t.status IN ('pending', 'in_progress')";
        $params = [$days];
        
        if (in_array($role, ['attorney', 'paralegal'])) {
            $sql .= " AND t.assigned_to = ?";
            $params[] = $user_id;
        }
        
        $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('auto_create_tasks_from_appointments')) {
    function auto_create_tasks_from_appointments(): int {
        $pdo = db();
        $created = 0;
        
        // Create reminder tasks for appointments 24 hours in advance
        $stmt = $pdo->query("
            SELECT a.id, a.case_id, a.assigned_to, a.title, a.start_time, a.description
            FROM appointments a
            WHERE a.start_time BETWEEN DATE_ADD(NOW(), INTERVAL 23 HOUR) AND DATE_ADD(NOW(), INTERVAL 25 HOUR)
            AND a.status = 'scheduled'
            AND NOT EXISTS (
                SELECT 1 FROM tasks t 
                WHERE t.case_id = a.case_id 
                AND t.task_type = 'appointment_reminder' 
                AND t.due_date BETWEEN DATE_SUB(a.start_time, INTERVAL 1 HOUR) AND a.start_time
            )
        ");
        
        $appointments = $stmt->fetchAll();
        
        foreach ($appointments as $apt) {
            if ($apt['assigned_to']) {
                $task_data = [
                    'case_id' => $apt['case_id'],
                    'assigned_to' => $apt['assigned_to'],
                    'title' => 'Appointment Reminder: ' . $apt['title'],
                    'description' => 'Prepare for appointment: ' . $apt['description'],
                    'due_date' => date('Y-m-d H:i:s', strtotime($apt['start_time']) - 3600), // 1 hour before
                    'priority' => 'medium',
                    'task_type' => 'appointment_reminder'
                ];
                
                create_task($task_data);
                $created++;
            }
        }
        
        return $created;
    }
}

// ============================================================================
// AUDIT LOGGING FUNCTIONS
// ============================================================================

/**
 * Log an audit event to the audit_logs table
 * 
 * @param string $event_type Type of event (login, logout, create, update, delete, etc.)
 * @param string $event_action Specific action taken
 * @param string $message Human-readable description
 * @param array $options Additional options:
 *   - category: Event category (default: 'general')
 *   - user_id: User ID (default: current user)
 *   - entity_type: Type of entity affected (case, user, invoice, etc.)
 *   - entity_id: ID of entity affected
 *   - old_values: Previous values (for updates)
 *   - new_values: New values (for updates)
 *   - metadata: Additional context data
 *   - severity: low, medium, high, critical (default: medium)
 *   - status: success, failure, warning (default: success)
 * @return bool|int Returns log ID on success, false on failure
 */
if (!function_exists('log_audit_event')) {
    function log_audit_event(string $event_type, string $event_action, string $message, array $options = []): bool|int {
        try {
            $pdo = db();
            
            // Get current user info
            $user_id = $options['user_id'] ?? (is_logged_in() ? get_user_id() : null);
            $user_role = $options['user_role'] ?? (is_logged_in() ? get_user_role() : null);
            
            // Get request info
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $request_method = $_SERVER['REQUEST_METHOD'] ?? null;
            $request_uri = $_SERVER['REQUEST_URI'] ?? null;
            
            // Prepare data
            $data = [
                'event_type' => $event_type,
                'event_category' => $options['category'] ?? 'general',
                'event_action' => $event_action,
                'message' => $message,
                'user_id' => $user_id,
                'user_role' => $user_role,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent ? substr($user_agent, 0, 500) : null,
                'request_method' => $request_method,
                'request_uri' => $request_uri ? substr($request_uri, 0, 500) : null,
                'entity_type' => $options['entity_type'] ?? null,
                'entity_id' => $options['entity_id'] ?? null,
                'old_values' => !empty($options['old_values']) ? json_encode($options['old_values']) : null,
                'new_values' => !empty($options['new_values']) ? json_encode($options['new_values']) : null,
                'metadata' => !empty($options['metadata']) ? json_encode($options['metadata']) : null,
                'severity' => $options['severity'] ?? 'medium',
                'status' => $options['status'] ?? 'success'
            ];
            
            // Insert into audit_logs (try audit_logs first, fallback to security_logs)
            $sql = "INSERT INTO audit_logs (
                event_type, event_category, event_action, message, user_id, user_role,
                ip_address, user_agent, request_method, request_uri,
                entity_type, entity_id, old_values, new_values, metadata,
                severity, status, created_at
            ) VALUES (
                :event_type, :event_category, :event_action, :message, :user_id, :user_role,
                :ip_address, :user_agent, :request_method, :request_uri,
                :entity_type, :entity_id, :old_values, :new_values, :metadata,
                :severity, :status, NOW()
            )";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                return (int)$pdo->lastInsertId();
            } catch (PDOException $e) {
                // Fallback to security_logs if audit_logs doesn't exist
                if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Unknown table") !== false) {
                    $sql_fallback = "INSERT INTO security_logs (event_type, message, user_id, ip_address, created_at) 
                                     VALUES (:event_type, :message, :user_id, :ip_address, NOW())";
                    $stmt = $pdo->prepare($sql_fallback);
                    $stmt->execute([
                        'event_type' => $event_type,
                        'message' => $message,
                        'user_id' => $user_id,
                        'ip_address' => $ip_address
                    ]);
                    return (int)$pdo->lastInsertId();
                }
                throw $e;
            }
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("Audit logging failed: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Log an analytics event
 * 
 * @param string $event_type Type of event
 * @param string $event_action Specific action
 * @param array $options Additional options
 * @return bool|int Returns event ID on success, false on failure
 */
if (!function_exists('log_analytics_event')) {
    function log_analytics_event(string $event_type, string $event_action, array $options = []): bool|int {
        try {
            $pdo = db();
            
            $user_id = $options['user_id'] ?? (is_logged_in() ? get_user_id() : null);
            $session_id = session_id();
            
            // Get browser/device info
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $device_type = 'desktop';
            $browser = 'unknown';
            $os = 'unknown';
            
            if (preg_match('/mobile|android|iphone|ipad/i', $user_agent)) {
                $device_type = 'mobile';
            } elseif (preg_match('/tablet/i', $user_agent)) {
                $device_type = 'tablet';
            }
            
            if (preg_match('/(chrome|firefox|safari|edge|opera)/i', $user_agent, $matches)) {
                $browser = strtolower($matches[1]);
            }
            
            if (preg_match('/(windows|mac|linux|android|ios)/i', $user_agent, $matches)) {
                $os = strtolower($matches[1]);
            }
            
            $sql = "INSERT INTO analytics_events (
                user_id, event_type, event_category, event_action, event_label, event_value,
                session_id, page_url, referrer_url, device_type, browser, os, metadata, created_at
            ) VALUES (
                :user_id, :event_type, :event_category, :event_action, :event_label, :event_value,
                :session_id, :page_url, :referrer_url, :device_type, :browser, :os, :metadata, NOW()
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'event_type' => $event_type,
                'event_category' => $options['category'] ?? 'general',
                'event_action' => $event_action,
                'event_label' => $options['label'] ?? null,
                'event_value' => $options['value'] ?? null,
                'session_id' => $session_id,
                'page_url' => $_SERVER['REQUEST_URI'] ?? null,
                'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                'device_type' => $device_type,
                'browser' => $browser,
                'os' => $os,
                'metadata' => !empty($options['metadata']) ? json_encode($options['metadata']) : null
            ]);
            
            return (int)$pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Analytics logging failed: " . $e->getMessage());
            return false;
        }
    }
}

// ============================================================================
// OPTIONAL: AUTOMATIC HTTP REQUEST LOGGING (Middleware)
// ============================================================================
// Uncomment the code below to automatically log all POST/PUT/DELETE requests
// This provides comprehensive logging without modifying every file

/*
if (is_logged_in() && in_array($_SERVER['REQUEST_METHOD'] ?? '', ['POST', 'PUT', 'DELETE'])) {
    // Skip logging for certain paths to avoid log spam
    $skip_paths = ['/api/auth-login.php', '/api/analytics', '/api/ping'];
    $current_path = $_SERVER['REQUEST_URI'] ?? '';
    
    $should_skip = false;
    foreach ($skip_paths as $skip_path) {
        if (strpos($current_path, $skip_path) !== false) {
            $should_skip = true;
            break;
        }
    }
    
    if (!$should_skip) {
        $action = match($_SERVER['REQUEST_METHOD']) {
            'POST' => 'create',
            'PUT' => 'update',
            'DELETE' => 'delete',
            default => 'unknown'
        };
        
        log_audit_event($action, 'http_request', "HTTP {$_SERVER['REQUEST_METHOD']} request to {$_SERVER['REQUEST_URI']}", [
            'category' => 'system',
            'severity' => 'low',
            'metadata' => [
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'referrer' => $_SERVER['HTTP_REFERER'] ?? null
            ]
        ]);
    }
}
*/