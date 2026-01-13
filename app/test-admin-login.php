<?php
// Quick debug script - save as /app/test-admin-login.php
require __DIR__ . '/config.php';

try {
    $pdo = db();
    
    // Check what admin users exist
    $stmt = $pdo->prepare("SELECT id, email, name, role, is_active FROM users WHERE role IN ('admin', 'super_admin', 'manager', 'office_admin') ORDER BY role");
    $stmt->execute();
    $adminUsers = $stmt->fetchAll();
    
    echo "<h3>Available Admin Users:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th><th>Active</th></tr>";
    
    foreach ($adminUsers as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test password verification for the first admin user
    if (!empty($adminUsers)) {
        $testUser = $adminUsers[0];
        $testPassword = '2025@Password'; // Based on your SQL seed data
        
        echo "<h3>Password Test for {$testUser['email']}:</h3>";
        
        // Get the actual password hash from database
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$testUser['id']]);
        $hashData = $stmt->fetch();
        
        if ($hashData) {
            $isValidPassword = password_verify($testPassword, $hashData['password_hash']);
            echo "Password verification: " . ($isValidPassword ? 'SUCCESS' : 'FAILED') . "<br>";
            echo "Hash: " . substr($hashData['password_hash'], 0, 50) . "...<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>