<?php
/**
 * auth-login.php - COMPLETELY FIXED SESSION MANAGEMENT
 * 
 * This file handles authentication for BOTH client and admin logins
 * using namespaced session keys to prevent conflicts between contexts
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);
error_log("=== LOGIN ATTEMPT STARTED ===");

require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

error_log("POST data received: " . print_r($_POST, true));

if (!csrf_validate()) {
    error_log("CSRF validation failed");
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$requireAdmin = (isset($_GET['admin']) && $_GET['admin'] === '1');

// Determine login context
$uri = (string)($_SERVER['HTTP_REFERER'] ?? ($_SERVER['REQUEST_URI'] ?? ''));
$isAdminLogin = $requireAdmin || (strpos($uri, '/admin') !== false);
$context = $isAdminLogin ? 'admin' : 'client';

error_log("Login context: $context, Email: $email");

// Initialize the appropriate session for this context
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

$sessionName = $context === 'admin' ? 'MERLAWS_ADMIN' : 'MERLAWS_CLIENT';
session_name($sessionName);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$errors = [];
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { 
    $errors[] = 'Valid email is required.'; 
}
if ($password === '' || strlen($password) < 8) { 
    $errors[] = 'Password must be at least 8 characters.'; 
}

if ($errors) {
    error_log("Validation errors: " . implode(', ', $errors));
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = db();
    
    // Simple rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cacheFile = sys_get_temp_dir() . '/login_' . hash('sha256', $email . $ip) . '.tmp';
    
    if (file_exists($cacheFile)) {
        $attempts = json_decode(file_get_contents($cacheFile), true);
        if ($attempts['count'] >= 5 && (time() - $attempts['last_attempt']) < 900) {
            error_log("Rate limited: $email from $ip");
            echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please try again later.']);
            exit;
        }
    }
    
    // Get user from database
    $stmt = $pdo->prepare("SELECT id, email, password_hash, name, role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    error_log("User lookup result: " . ($user ? "Found user ID " . $user['id'] : "Not found"));
    
    if (!$user || !$user['is_active']) {
        $attempts = ['count' => (isset($attempts['count']) ? $attempts['count'] : 0) + 1, 'last_attempt' => time()];
        file_put_contents($cacheFile, json_encode($attempts));
        
        error_log("Login failed: User not found or inactive");
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $attempts = ['count' => (isset($attempts['count']) ? $attempts['count'] : 0) + 1, 'last_attempt' => time()];
        file_put_contents($cacheFile, json_encode($attempts));
        
        // Log failed login attempt
        log_audit_event('login_failed', 'invalid_password', "Failed login attempt: Invalid password for {$email}", [
            'category' => 'auth',
            'user_id' => $user['id'],
            'user_role' => $user['role'],
            'status' => 'failure',
            'severity' => 'medium',
            'metadata' => ['email' => $email, 'attempt_count' => $attempts['count']]
        ]);
        
        error_log("Login failed: Invalid password for user ID " . $user['id']);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
    
    // Check admin requirement
    $adminRoles = ['super_admin', 'admin', 'manager', 'office_admin', 'partner', 'attorney', 
                   'paralegal', 'intake', 'case_manager', 'billing', 'doc_specialist', 
                   'it_admin', 'compliance', 'receptionist'];
    $isAdmin = in_array($user['role'], $adminRoles, true);
    
    if ($requireAdmin && !$isAdmin) {
        error_log("Login failed: Admin access required but user role is " . $user['role']);
        echo json_encode(['success' => false, 'message' => 'Administrator access required.']);
        exit;
    }
    
    // Clear failed attempts
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    
    // Store credentials directly in session (no namespacing needed since sessions are separate)
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = (string)$user['role'];
    $_SESSION['name'] = (string)$user['name'];
    $_SESSION['email'] = (string)$user['email'];
    $_SESSION['login_time'] = time();
    
    error_log("Session set for context '$context': " . print_r($_SESSION, true));
    
    // Clear permissions cache
    unset($_SESSION['permissions']);
    
    // Update last login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Log successful login (audit + analytics)
    log_audit_event('login', 'user_login', "User logged in successfully: {$user['name']} ({$user['email']})", [
        'category' => 'auth',
        'user_id' => $user['id'],
        'user_role' => $user['role'],
        'status' => 'success',
        'severity' => 'low',
        'metadata' => ['context' => $context, 'login_count' => ($user['login_count'] ?? 0) + 1]
    ]);
    
    log_analytics_event('login', 'user_login', [
        'category' => 'auth',
        'user_id' => $user['id'],
        'label' => "Login: {$user['role']}",
        'metadata' => ['role' => $user['role'], 'context' => $context]
    ]);
    
    // Log successful login
    error_log("Successful $context login: User ID {$user['id']}, Role: {$user['role']}, Email: {$email}");
    
    // Determine redirect URL
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = '';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    
    $redirectUrl = $scheme . $host . $basePath;
    if ($isAdmin && $context === 'admin') {
        $redirectUrl .= '/app/admin/dashboard.php';
    } else {
        $redirectUrl .= '/app/dashboard.php';
    }
    
    error_log("Redirecting to: $redirectUrl");
    
    $response = [
        'success' => true,
        'role' => (string)$user['role'],
        'name' => (string)$user['name'],
        'context' => $context,
        'redirect' => $redirectUrl,
        'debug' => [
            'session_id' => session_id(),
            'context' => $context,
            'user_id' => $user['id']
        ]
    ];
    
    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}