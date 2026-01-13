<?php
/**
 * Admin Logout - Destroys only the MERLAWS_ADMIN session
 * This allows client sessions to remain active
 */

// Start the admin session to destroy it
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

session_name('MERLAWS_ADMIN');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Redirect to admin login
header('Location: /app/admin-login.php');
exit;
