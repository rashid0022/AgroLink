<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';
$success = '';

// Check if user already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'Admin') {
        header("Location: ../dashboard/admin.php");
    } elseif ($user['role'] === 'Farmer') {
        header("Location: ../dashboard/farmer.php");
    } else {
        header("Location: ../dashboard/customer.php");
    }
    exit();
}

// Server-side login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $result = loginUser($email, $password); // your function from functions.php

        if ($result['success']) {
            $role = $_SESSION['role'];
            if ($role === 'Admin') {
                header("Location: dashboard/admin.php");
            } elseif ($role === 'Farmer') {
                header("Location: dashboard/farmer.php");
            } else {
                header("Location: dashboard/customer.php");
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
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
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error,
        .success {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-links p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 15px 0;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <h1>🌾 AgroLink</h1>
            <p>Farm to Table Marketplace</p>
        </div>

        <!-- Server-side Error -->
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="footer-links">
            <p>Don't have an account?</p>
            <a href="register_farmer.php">Register as Farmer</a>
            <a href="register_customer.php">Register as Customer</a>
            <div class="divider">OR</div>
            <a href="index.php">Back to Home</a>
        </div>
    </div>

    <!-- Client-side validation -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value.trim();
            let errors = [];

            if (!email) errors.push("Email is required.");
            if (!password) errors.push("Password is required.");

            if (errors.length > 0) {
                e.preventDefault(); // stop form submission
                alert(errors.join("\n"));
            }
        });
    </script>
</body>

</html>