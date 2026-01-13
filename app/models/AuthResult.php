<?php
// app/models/AuthResult.php
class AuthResult
{
    const SUCCESS = 'success';
    const ERROR_INVALID_INPUT = 'invalid_input';
    const ERROR_INVALID_CREDENTIALS = 'invalid_credentials';
    const ERROR_RATE_LIMITED = 'rate_limited';
    const ERROR_ACCOUNT_DEACTIVATED = 'account_deactivated';
    const ERROR_USER_EXISTS = 'user_exists';
    const ERROR_VALIDATION = 'validation';
    const ERROR_SYSTEM = 'system';
    
    private bool $success;
    private string $message;
    private string $code;
    private array $data;
    
    public function __construct(bool $success, string $message, string $code, array $data = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->code = $code;
        $this->data = $data;
    }
    
    public function isSuccess(): bool { return $this->success; }
    public function getMessage(): string { return $this->message; }
    public function getCode(): string { return $this->code; }
    public function getData(): array { return $this->data; }
    public function get(string $key) { return $this->data[$key] ?? null; }
}

// app/models/ValidationResult.php
class ValidationResult
{
    private bool $valid;
    private array $errors;
    
    public function __construct(bool $valid, array $errors = [])
    {
        $this->valid = $valid;
        $this->errors = $errors;
    }
    
    public function isValid(): bool { return $this->valid; }
    public function getErrors(): array { return $this->errors; }
    public function hasError(string $field): bool { return isset($this->errors[$field]); }
    public function getError(string $field): ?string { return $this->errors[$field] ?? null; }
}

// app/models/UserSession.php
class UserSession
{
    private string $id;
    private int $userId;
    private string $role;
    private array $data;
    private int $createdAt;
    private int $lastActivity;
    
    public function __construct(array $sessionData)
    {
        $this->id = session_id() ?: $this->generateSessionId();
        $this->userId = $sessionData['user_id'];
        $this->role = $sessionData['role'];
        $this->data = $sessionData;
        $this->createdAt = $sessionData['login_time'] ?? time();
        $this->lastActivity = time();
    }
    
    public function getId(): string { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getRole(): string { return $this->role; }
    public function getData(): array { return $this->data; }
    public function get(string $key) { return $this->data[$key] ?? null; }
    
    public function isExpired(int $timeout = 3600): bool
    {
        return (time() - $this->lastActivity) > $timeout;
    }
    
    public function updateActivity(): void
    {
        $this->lastActivity = time();
    }
    
    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }
}

// app/services/SecurityService.php
class SecurityService
{
    private PDO $pdo;
    private array $rateLimits = [];
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent(string $eventType, array $data = []): void
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO security_logs (event_type, message, user_id, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $eventType,
                json_encode($data),
                $data['user_id'] ?? null,
                $this->getClientIp()
            ]);
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Rate limiting implementation
     */
    public function getRateLimitData(string $key): array
    {
        $cacheKey = 'rate_limit_' . $key;
        
        // Simple file-based cache for rate limiting
        $cacheFile = sys_get_temp_dir() . '/' . hash('sha256', $cacheKey) . '.cache';
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && $data['expires'] > time()) {
                return $data;
            }
        }
        
        return ['count' => 0, 'first_attempt' => time(), 'expires' => time() + 900];
    }
    
    /**
     * Update rate limit data
     */
    public function updateRateLimitData(string $key, array $data): void
    {
        $cacheKey = 'rate_limit_' . $key;
        $cacheFile = sys_get_temp_dir() . '/' . hash('sha256', $cacheKey) . '.cache';
        
        file_put_contents($cacheFile, json_encode($data));
    }
    
    /**
     * Sanitize input data
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

// app/services/SessionService.php
class SessionService
{
    private PDO $pdo;
    private int $timeout;
    
    public function __construct(PDO $pdo, int $timeout = 3600)
    {
        $this->pdo = $pdo;
        $this->timeout = $timeout;
        
        // Configure secure session settings
        $this->configureSession();
    }
    
    /**
     * Configure secure session parameters
     */
    private function configureSession(): void
    {
        // If a session is already active, do not change INI settings or handlers
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Security settings (only before session_start)
        ini_set('session.cookie_httponly', '1');
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', (string)$this->timeout);

        // Custom session handler for database storage (must be set before session_start)
        session_set_save_handler(
            [$this, 'sessionOpen'],
            [$this, 'sessionClose'],
            [$this, 'sessionRead'],
            [$this, 'sessionWrite'],
            [$this, 'sessionDestroy'],
            [$this, 'sessionGc']
        );
    }
    
    /**
     * Store user session
     */
    public function store(UserSession $session): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        foreach ($session->getData() as $key => $value) {
            $_SESSION[$key] = $value;
        }
        
        // Store in database
        $this->storeInDatabase($session);
    }
    
    /**
     * Retrieve current session
     */
    public function getCurrentSession(): ?UserSession
    {
        if (session_status() === PHP_SESSION_NONE || empty($_SESSION['user_id'])) {
            return null;
        }
        
        return new UserSession($_SESSION);
    }
    
    /**
     * Destroy session
     */
    public function destroy(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            $sessionId = session_id();
            
            // Remove from database
            $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            
            // Destroy PHP session
            $_SESSION = [];
            session_destroy();
        }
    }
    
    /**
     * Store session in database
     */
    private function storeInDatabase(UserSession $session): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (id, user_id, ip_address, user_agent, data, created_at, last_activity)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                data = VALUES(data),
                last_activity = NOW()
        ");
        
        $stmt->execute([
            $session->getId(),
            $session->getUserId(),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            json_encode($session->getData())
        ]);
    }
    
    // Session handler methods for database storage
    public function sessionOpen($savePath, $sessionName) { return true; }
    public function sessionClose() { return true; }
    
    public function sessionRead($sessionId)
    {
        $stmt = $this->pdo->prepare("SELECT data FROM user_sessions WHERE id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$sessionId, $this->timeout]);
        $result = $stmt->fetch();
        
        return $result ? json_decode($result['data'], true) : '';
    }
    
    public function sessionWrite($sessionId, $sessionData)
    {
        $data = json_decode($sessionData, true);
        if (empty($data['user_id'])) return true;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (id, user_id, ip_address, user_agent, data, last_activity)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = NOW()
        ");
        
        return $stmt->execute([
            $sessionId,
            $data['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $sessionData
        ]);
    }
    
    public function sessionDestroy($sessionId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE id = ?");
        return $stmt->execute([$sessionId]);
    }
    
    public function sessionGc($maxLifetime)
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        return $stmt->execute([$maxLifetime]);
    }
}

// app/middleware/AuthMiddleware.php
class AuthMiddleware
{
    private SessionService $session;
    private SecurityService $security;
    
    public function __construct(SessionService $session, SecurityService $security)
    {
        $this->session = $session;
        $this->security = $security;
    }
    
    /**
     * Require authenticated user
     */
    public function requireAuth(): UserSession
    {
        $session = $this->session->getCurrentSession();
        
        if (!$session) {
            $this->redirectToLogin();
        }
        
        if ($session->isExpired()) {
            $this->session->destroy();
            $this->redirectToLogin();
        }
        
        // Update activity
        $session->updateActivity();
        
        return $session;
    }
    
    /**
     * Require specific role
     */
    public function requireRole(string $role): UserSession
    {
        $session = $this->requireAuth();
        
        if ($session->getRole() !== $role) {
            http_response_code(403);
            header('Location: /app/dashboard.php?error=insufficient_permissions');
            exit;
        }
        
        return $session;
    }
    
    /**
     * Require admin access
     */
    public function requireAdmin(): UserSession
    {
        $session = $this->requireAuth();
        
        $adminRoles = ['super_admin','admin','manager','office_admin','partner','attorney','paralegal','intake','case_manager','billing','doc_specialist','it_admin','compliance','receptionist'];
        if (!in_array($session->getRole(), $adminRoles, true)) {
            http_response_code(403);
            header('Location: /app/dashboard.php?error=admin_required');
            exit;
        }
        
        return $session;
    }
    
    /**
     * Redirect to appropriate login page
     */
    private function redirectToLogin(): void
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $isAdminPath = strpos($currentPath, '/admin/') !== false;
        
        if ($isAdminPath) {
            header('Location: /app/admin-login.php?redirect=' . urlencode($currentPath));
        } else {
            header('Location: /app/login.php?redirect=' . urlencode($currentPath));
        }
        exit;
    }
}