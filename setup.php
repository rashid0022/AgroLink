<?php
require_once 'db.php';

$setup_complete = false;
$errors = [];
$successes = [];

// Check if 'users' table exists
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $errors[] = 'Database tables not found. Please run database.sql first.';
    } else {
        // Check if admin exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            $successes[] = 'Admin account already exists.';
            $setup_complete = true;
        }
    }
} catch (Exception $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}

// Create default admin if none exists
if (empty($errors) && !$setup_complete) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, email, password, role, account_status, registration_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            'System Administrator',
            'admin@agrolink.local',
            password_hash('admin123', PASSWORD_BCRYPT),
            'Admin',
            'Active'
        ]);
        $successes[] = 'Default admin account created successfully!';
        $successes[] = 'Email: admin@agrolink.local';
        $successes[] = 'Password: admin123';
        $setup_complete = true;
    } catch (Exception $e) {
        $errors[] = 'Failed to create admin: ' . $e->getMessage();
    }
}



// Check if other tables exist
$required_tables = ['farmers', 'customers', 'products', 'orders', 'order_items', 'payments', 'receipts', 'reports'];
$missing_tables = [];

foreach ($required_tables as $table) {
    try {
        $stmt = $GLOBALS['conn']->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    } catch (Exception $e) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    $errors[] = 'Missing database tables: ' . implode(', ', $missing_tables) . '. Please run database.sql.';
} else {
    if (!empty($successes)) {
        $successes[] = 'All database tables are present.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroLink Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #11998e;
            font-size: 32px;
            margin-bottom: 5px;
            border-bottom: 3px solid #11998e;
            padding-bottom: 10px;
        }
        
        .logo p {
            color: #999;
            font-size: 14px;
        }
        
        .status-section {
            margin-bottom: 30px;
        }
        
        .status-section h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .message {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .success-message .icon {
            font-size: 16px;
            min-width: 20px;
        }
        
        .error-message .icon {
            font-size: 16px;
            min-width: 20px;
        }
        
        .next-steps {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .next-steps h3 {
            color: #004085;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .next-steps ol {
            margin-left: 20px;
            color: #004085;
            line-height: 1.8;
            font-size: 14px;
        }
        
        .next-steps li {
            margin-bottom: 10px;
        }
        
        .next-steps a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
        }
        
        .next-steps a:hover {
            text-decoration: underline;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-top: 10px;
            overflow-x: auto;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🌾 AgroLink</h1>
            <p>Farm to Table Marketplace Setup</p>
        </div>
        
        <div class="status-section">
            <h2>📋 Setup Status</h2>
            
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="message error-message">
                        <span class="icon">✗</span>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($successes)): ?>
                <?php foreach ($successes as $success): ?>
                    <div class="message success-message">
                        <span class="icon">✓</span>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($setup_complete && empty($errors)): ?>
            <div class="next-steps">
                <h3>🚀 Setup Complete!</h3>
                <ol>
                    <li>Log in with your admin account:
                        <div class="code-block">
                            Email: admin@agrolink.local<br>
                            Password: admin123
                        </div>
                    </li>
                    <li>Change the default password after first login</li>
                    <li>Register farmers and customers through their respective registration pages</li>
                    <li>Review and approve products before they appear in the marketplace</li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
                <a href="index.php" class="btn btn-secondary">Go to Marketplace</a>
            </div>
        <?php else: ?>
            <div class="next-steps">
                <h3>⚠️ Setup Incomplete</h3>
                <p>Please ensure:</p>
                <ol>
                    <li>Database is created with name: <code>agrolink_db</code></li>
                    <li>Run <code>database.sql</code> to create all tables</li>
                    <li>Check your database credentials in <code>db.php</code></li>
                    <li>Refresh this page after making changes</li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="location.reload()">Refresh Setup</button>
                <a href="login.php" class="btn btn-secondary">Try Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
