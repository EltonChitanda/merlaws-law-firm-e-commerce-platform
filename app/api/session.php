<?php
/**
 * session.php - DUAL SESSION STATUS API
 * 
 * Returns session status for the requested context (admin or client)
 * Works with separate session cookies (MERLAWS_ADMIN and MERLAWS_CLIENT)
 */

require __DIR__ . '/../config.php';

// Determine context from URL parameter or current URI
$requestedScope = isset($_GET['scope']) ? strtolower((string)$_GET['scope']) : '';
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

if ($requestedScope === '') {
    // Auto-detect context from URI
    $requestedScope = (strpos($currentUri, '/app/admin/') !== false) ? 'admin' : 'client';
}

// Validate scope
if (!in_array($requestedScope, ['admin', 'client'], true)) {
    $requestedScope = 'client';
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Build response from current session data (no namespacing needed since sessions are separate)
$response = [
    'logged_in' => !empty($_SESSION['user_id']),
    'user_id' => (int)($_SESSION['user_id'] ?? 0),
    'name' => (string)($_SESSION['name'] ?? ''),
    'role' => (string)($_SESSION['role'] ?? ''),
    'context' => $requestedScope
];

// Optional: Include email for debugging (remove in production if concerned about privacy)
if (!empty($_SESSION['email'])) {
    $response['email'] = (string)$_SESSION['email'];
}

// Optional: Include login time
if (!empty($_SESSION['login_time'])) {
    $response['login_time'] = (int)$_SESSION['login_time'];
    $response['session_age_minutes'] = floor((time() - $_SESSION['login_time']) / 60);
}

echo json_encode($response);
exit;