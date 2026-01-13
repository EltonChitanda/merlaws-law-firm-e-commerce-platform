<?php
// app/csrf.php

function csrf_token(): string {
	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function csrf_field(): string {
	$token = csrf_token();
	return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate(): bool {
	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	
	// Check if both token and POST data exist
	if (!isset($_POST['_csrf'])) {
		error_log('CSRF validation failed: POST token not set');
		return false;
	}
	
	if (!isset($_SESSION['csrf_token'])) {
		error_log('CSRF validation failed: Session token not set');
		return false;
	}
	
	$postToken = (string)$_POST['_csrf'];
	$sessionToken = (string)$_SESSION['csrf_token'];
	
	$isValid = hash_equals($sessionToken, $postToken);
	
	if (!$isValid) {
		error_log('CSRF validation failed: Tokens do not match. POST: ' . substr($postToken, 0, 10) . '..., SESSION: ' . substr($sessionToken, 0, 10) . '...');
	}
	
	return $isValid;
} 