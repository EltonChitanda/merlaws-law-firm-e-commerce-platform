<?php
// Save as /app/test-admin-api.php
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';

// Simulate a POST request to the auth API
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Generate a test form
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Test Admin API</title></head>
    <body>
        <h2>Test Admin Login API</h2>
        <form method="post" action="">
            <?php echo csrf_field(); ?>
            <label>Email: <input type="email" name="email" value="owner.admin@gmail.com" required></label><br><br>
            <label>Password: <input type="password" name="password" value="2025@Password" required></label><br><br>
            <label><input type="checkbox" name="admin" value="1" checked> Admin Login</label><br><br>
            <button type="submit">Test API</button>
        </form>
    </body>
    </html>
    <?php
} else {
    // Simulate the API call
    echo "<h3>Testing Admin API...</h3>";
    
    // Set up the POST data like the JavaScript would
    $_GET['admin'] = '1'; // Simulate admin parameter
    $_POST['email'] = $_POST['email'] ?? '';
    $_POST['password'] = $_POST['password'] ?? '';
    
    echo "<strong>Request Data:</strong><br>";
    echo "Email: " . htmlspecialchars($_POST['email']) . "<br>";
    echo "Password: " . (empty($_POST['password']) ? 'Empty' : '[PROVIDED]') . "<br>";
    echo "Admin Parameter: " . ($_GET['admin'] ?? 'Not set') . "<br>";
    echo "CSRF Token: " . (isset($_POST['_csrf']) ? 'Present' : 'Missing') . "<br><br>";
    
    // Capture the output from the auth API
    ob_start();
    
    try {
        // Include the auth API logic directly
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!csrf_validate()) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
            exit;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $requireAdmin = (isset($_GET['admin']) && $_GET['admin'] === '1');

        $errors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { 
            $errors[] = 'Valid email is required.'; 
        }
        if ($password === '' || strlen($password) < 8) { 
            $errors[] = 'Password must be at least 8 characters.'; 
        }

        if ($errors) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $pdo = db();
        
        // Simple rate limiting check
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $cacheFile = sys_get_temp_dir() . '/login_' . hash('sha256', $email . $ip) . '.tmp';
        
        if (file_exists($cacheFile)) {
            $attempts = json_decode(file_get_contents($cacheFile), true);
            if ($attempts['count'] >= 5 && (time() - $attempts['last_attempt']) < 900) {
                echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please try again later.']);
                exit;
            }
        }
        
        // Get user from database
        $stmt = $pdo->prepare("SELECT id, email, password_hash, name, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_active']) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            exit;
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            exit;
        }
        
        // Check admin requirement
        if ($requireAdmin && !in_array($user['role'], ['admin', 'super_admin', 'manager'], true)) {
            echo json_encode(['success' => false, 'message' => 'Administrator access required.', 'user_role' => $user['role']]);
            exit;
        }
        
        // Success
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = (string)$user['role'];
        $_SESSION['name'] = (string)$user['name'];
        
        echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']]]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
    }
    
    $output = ob_get_clean();
    
    echo "<strong>API Response:</strong><br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to decode JSON to see if it's valid
    $json = json_decode($output, true);
    if ($json === null) {
        echo "<br><strong>JSON Parse Error:</strong> " . json_last_error_msg();
    } else {
        echo "<br><strong>Parsed JSON:</strong><br>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    }
}
?>