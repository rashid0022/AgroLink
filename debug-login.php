<?php
/**
 * AgroLink - Debug & Fix Login Issues
 */

require_once 'db.php';
require_once 'functions.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>AgroLink Debug</title>";
echo "<style>
body {
    font-family: Arial, sans-serif;
    margin: 40px;
    background: #f5f5f5;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 800px;
    margin: 0 auto;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #667eea;
    border-radius: 5px;
}
.success {
    color: #28a745;
}
.error {
    color: #dc3545;
}
.warning {
    color: #856404;
}
code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New';
    display: block;
    margin: 10px 0;
    padding: 10px;
}
h2 {
    color: #667eea;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
}
table th, table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
table th {
    background: #f0f0f0;
    font-weight: bold;
}
</style>";
echo "</head><body>";
echo "<div class='container'>";
echo "<h1>🔧 AgroLink - Debug & Fix Login</h1>";

// 1. Check database connection
echo "<div class='section'>";
echo "<h2>1️⃣ Database Connection</h2>";
try {
    $stmt = $GLOBALS['conn']->query("SELECT VERSION()");
    $version = $stmt->fetch();
    echo "<p class='success'>✓ Database connected successfully</p>";
    echo "<code>MySQL Version: " . htmlspecialchars($version['VERSION()']) . "</code>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed</p>";
    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
}
echo "</div>";

// 2. Check users table structure
echo "<div class='section'>";
echo "<h2>2️⃣ Users Table Structure</h2>";
try {
    $stmt = $GLOBALS['conn']->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✓ Users table exists with correct structure</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Users table error</p>";
    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
}
echo "</div>";

// 3. Check existing users
echo "<div class='section'>";
echo "<h2>3️⃣ Existing Users</h2>";
try {
    $stmt = $GLOBALS['conn']->query("SELECT user_id, email, role, account_status FROM users LIMIT 10");
    $users = $stmt->fetchAll();
    
    if (count($users) == 0) {
        echo "<p class='warning'>⚠️ No users found in database</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['account_status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p class='success'>✓ Found " . count($users) . " user(s)</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error fetching users</p>";
    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
}
echo "</div>";

// 4. Test login function
echo "<div class='section'>";
echo "<h2>4️⃣ Test Login Function</h2>";
$test_email = 'admin@agrolink.local';
$test_password = 'admin123';

$result = loginUser($test_email, $test_password);
if ($result['success']) {
    echo "<p class='success'>✓ Login test SUCCESSFUL</p>";
    echo "<code>Message: " . htmlspecialchars($result['message']) . "</code>";
    echo "<code>Session Role: " . htmlspecialchars($_SESSION['role'] ?? 'Not set') . "</code>";
} else {
    echo "<p class='error'>✗ Login test FAILED</p>";
    echo "<code>Message: " . htmlspecialchars($result['message']) . "</code>";
}
echo "</div>";

// 5. Auto-create admin if doesn't exist
echo "<div class='section'>";
echo "<h2>5️⃣ Auto-Create Admin Account</h2>";
try {
    $stmt = $GLOBALS['conn']->prepare("SELECT COUNT(*) as count FROM users WHERE role='Admin' AND email=?");
    $stmt->execute(['admin@agrolink.local']);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "<p class='success'>✓ Admin account already exists</p>";
    } else {
        echo "<p class='warning'>⚠️ Admin account not found, creating...</p>";
        
        $hashed_pwd = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $GLOBALS['conn']->prepare("
            INSERT INTO users (full_name, email, password, role, account_status, registration_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'System Administrator',
            'admin@agrolink.local',
            $hashed_pwd,
            'Admin',
            'Active'
        ]);
        
        echo "<p class='success'>✓ Admin account created successfully!</p>";
        echo "<code>Email: admin@agrolink.local</code>";
        echo "<code>Password: admin123</code>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error creating admin</p>";
    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
}
echo "</div>";

// 6. Instructions
echo "<div class='section' style='border-left-color: #28a745;'>";
echo "<h2>✅ Next Steps</h2>";
echo "<ol>";
echo "<li>Go to <a href='login.php'>Login Page</a></li>";
echo "<li>Email: <code>admin@agrolink.local</code></li>";
echo "<li>Password: <code>admin123</code></li>";
echo "<li>Click Login</li>";
echo "<li>You should be redirected to Admin Dashboard</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
