<?php
require_once 'db.php';
require_once 'functions.php';

$admin_email = 'admin@agrolink.local';
$admin_password = 'admin123';
$admin_name = 'System Administrator';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Admin Setup</title>";
echo "<style>body{font-family:Arial;margin:40px;background:#f5f5f5;}";
echo ".container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 0 10px rgba(0,0,0,0.1);}";
echo ".success{color:#28a745;padding:10px;background:#d4edda;border-radius:5px;margin:10px 0;}";
echo ".error{color:#dc3545;padding:10px;background:#f8d7da;border-radius:5px;margin:10px 0;}";
echo ".info{color:#004085;padding:10px;background:#d1ecf1;border-radius:5px;margin:10px 0;}";
echo "a{color:#11998e;text-decoration:none;}a:hover{text-decoration:underline;}</style>";
echo "</head><body><div class='container'>";
echo "<h1>🌾 AgroLink - Admin Setup</h1>";

try {
    // Check if admin already exists
    $stmt = $GLOBALS['conn']->prepare("SELECT COUNT(*) as count FROM users WHERE role='Admin'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "<div class='warning' style='color:#856404;background:#fff3cd;border-color:#ffc107;'>";
        echo "⚠️ Admin account tayari yupo katika database!<br>";
        echo "<strong>Email:</strong> admin@agrolink.local<br>";
        echo "<strong>Password:</strong> admin123<br>";
        echo "</div>";
        echo "<div class='info'>";
        echo "Tafadhali <a href='login.php'><strong>login hapa</strong></a>";
        echo "</div>";
    } else {
        // Create admin account
        $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
        
        $stmt = $GLOBALS['conn']->prepare("
            INSERT INTO users (full_name, email, password, role, account_status, registration_date)
            VALUES (?, ?, ?, 'Admin', 'Active', NOW())
        ");
        
        $stmt->execute([$admin_name, $admin_email, $hashed_password]);
        
        echo "<div class='success'>";
        echo "✓ Admin account imeundwa kwa mafanikio!<br>";
        echo "<strong>Email:</strong> " . htmlspecialchars($admin_email) . "<br>";
        echo "<strong>Password:</strong> " . htmlspecialchars($admin_password) . "<br>";
        echo "<strong>Jina:</strong> " . htmlspecialchars($admin_name);
        echo "</div>";
        
        echo "<div class='info'>";
        echo "⚠️ Badilisha password baada ya kuingia!<br>";
        echo "<a href='login.php'><strong>Ingia hapa</strong></a>";
        echo "</div>";
    }
} catch(Exception $e) {
    echo "<div class='error'>";
    echo "✗ Kosa: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div></body></html>";
?>
