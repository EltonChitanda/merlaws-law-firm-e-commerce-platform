<?php
/**
 * Client Logout - Destroys only the MERLAWS_CLIENT session
 * This allows admin sessions to remain active
 */

// Start the client session to destroy it
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

session_name('MERLAWS_CLIENT');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Log logout before destroying session
require __DIR__ . '/config.php';
if (is_logged_in()) {
    $user_id = get_user_id();
    $user_role = get_user_role();
    $user_name = get_user_name();
    
    log_audit_event('logout', 'user_logout', "User logged out: {$user_name}", [
        'category' => 'auth',
        'user_id' => $user_id,
        'user_role' => $user_role,
        'status' => 'success',
        'severity' => 'low'
    ]);
}

// Clear all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Redirect to home page or client login
header('Location: /index.php');
exit;
