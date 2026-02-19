<?php
require_once 'functions.php';

// Check if user already logged in
if (isLoggedIn()) {
    header("Location: dashboard/admin.php");
    exit();
}

// Check if there are any admins in the system
$stmt = $GLOBALS['conn']->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'Admin'");
$stmt->execute();
$result = $stmt->fetch();
$admin_exists = $result['admin_count'] > 0;

// If admin exists and this is not the setup, redirect
if ($admin_exists && !isset($_GET['setup'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists
        $stmt = $GLOBALS['conn']->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $error = 'Email is already registered';
        } else {
            // Register admin user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $stmt = $GLOBALS['conn']->prepare("
                    INSERT INTO users (email, password, full_name, role, account_status, registration_date)
                    VALUES (?, ?, ?, 'Admin', 'Active', NOW())
                ");
                $stmt->execute([$email, $hashed_password, $full_name]);
                
                $success = 'Admin account created successfully! Redirecting to login...';
                
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
            } catch (Exception $e) {
                error_log("Admin Registration Error: " . $e->getMessage());
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $admin_exists ? 'Admin Already Exists' : 'Create Admin Account'; ?> - AgroLink</title>
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
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            border: 3px solid #11998e;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #11998e;
            font-size: 28px;
            margin-bottom: 5px;
            border-bottom: 3px solid #11998e;
            padding-bottom: 10px;
        }
        
        .logo p {
            color: #999;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #11998e;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #11998e;
            box-shadow: 0 0 5px rgba(17, 153, 142, 0.3);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #11998e;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #11998e;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: 2px solid #11998e;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .info-box {
            background: #e7f3ff;
            border: 2px solid #11998e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #11998e;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .already-registered {
            text-align: center;
            color: #999;
            font-size: 13px;
            margin-top: 15px;
        }
        
        .already-registered a {
            color: #11998e;
            text-decoration: none;
            font-weight: 600;
        }
        
        .admin-exists-message {
            background: #fff3cd;
            border: 2px solid #11998e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            color: #11998e;
        }
        
        .admin-exists-message h2 {
            margin-bottom: 10px;
            color: #11998e;
        }
        
        .admin-exists-message a {
            display: inline-block;
            margin-top: 15px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🌾 AgroLink</h1>
            <p>Admin Registration</p>
        </div>
        
        <?php if ($admin_exists && !isset($_GET['setup'])): ?>
            <div class="admin-exists-message">
                <h2>⚠️ Admin Already Exists</h2>
                <p>An admin account has already been created for this system.</p>
                <p>Please log in with your admin credentials.</p>
                <a href="login.php">Go to Login</a>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="error-message">✗ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">✓ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>👮 Master Administrator Account</strong>
                Create an admin account to manage farmers, products, orders, and users. Admin has full system access.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                           placeholder="Enter full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="admin@agrolink.local" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="At least 6 characters" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Repeat password" required minlength="6">
                </div>
                
                <button type="submit" class="submit-btn">Create Admin Account</button>
            </form>
            
            <div class="already-registered">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
