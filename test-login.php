<?php
/**
 * Quick Test - Verify Login Works
 */
require_once 'db.php';
require_once 'functions.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<style>body{font-family:Arial;margin:40px;}";
echo ".success{color:#28a745;padding:15px;background:#d4edda;border-radius:5px;}";
echo ".error{color:#dc3545;padding:15px;background:#f8d7da;border-radius:5px;}";
echo "code{display:block;padding:10px;background:#f5f5f5;margin:10px 0;border-radius:5px;}";
echo "</style></head><body>";

echo "<h1>🔐 Login Test</h1>";

// Test 1: Database connection
echo "<h2>1. Database Connection</h2>";
try {
    $stmt = $GLOBALS['conn']->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<div class='success'>✓ Database connected! Total users: " . $result['count'] . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 2: Get all admins
echo "<h2>2. Admin Accounts in Database</h2>";
try {
    $stmt = $GLOBALS['conn']->prepare("SELECT user_id, email, role FROM users WHERE role='Admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    foreach ($admins as $admin) {
        echo "<code>ID: " . $admin['user_id'] . " | Email: " . htmlspecialchars($admin['email']) . " | Role: " . $admin['role'] . "</code>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 3: Test login function
echo "<h2>3. Test Login Function</h2>";
$test_cases = [
    ['admin@agrolink.local', 'admin123'],
    ['mbarouk@gmail.com', 'said2004']
];

foreach ($test_cases as $case) {
    $result = loginUser($case[0], $case[1]);
    if ($result['success']) {
        echo "<div class='success'>✓ " . htmlspecialchars($case[0]) . " - Login SUCCESSFUL</div>";
    } else {
        echo "<div class='error'>✗ " . htmlspecialchars($case[0]) . " - " . htmlspecialchars($result['message']) . "</div>";
    }
}

echo "<h2>✅ If all tests passed, try logging in!</h2>";
echo "<a href='login.php' style='padding:10px 20px;background:#667eea;color:white;border-radius:5px;text-decoration:none;'>Go to Login</a>";

echo "</body></html>";
?>
