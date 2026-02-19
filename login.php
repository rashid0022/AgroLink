<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'Admin') {
        header("Location: dashboard/admin.php");
    } elseif ($user['role'] === 'Farmer') {
        header("Location: dashboard/farmer.php");
    } else {
        header("Location: dashboard/customer.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            $role = $_SESSION['role'];
            if ($role === 'Admin') header("Location: dashboard/admin.php");
            elseif ($role === 'Farmer') header("Location: dashboard/farmer.php");
            else header("Location: dashboard/customer.php");
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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;

            margin: 50px;

            /* BACKGROUND IMAGE */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('assets/images/photo.jpg') no-repeat center center/cover;
        }

        /* LOGIN CARD */
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 45px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.6s ease-in-out;
            border: 3px solid #11998e;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #11998e;
            font-size: 30px;
            font-weight: 700;
            border-bottom: 3px solid #11998e;
            padding-bottom: 10px;
        }

        .logo p {
            color: #666;
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
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.2);
        }

        /* ALERT BOXES */
        .error,
        .success {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error {
            background: #ffe5e5;
            color: #b30000;
            border-left: 4px solid #ff4d4d;
        }

        .success {
            background: #e6fff2;
            color: #006644;
            border-left: 4px solid #00cc66;
        }

        /* BUTTON */
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* FOOTER */
        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-links p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #11998e;
            text-decoration: none;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            transition: 0.3s;
        }

        .footer-links a:hover {
            opacity: 0.7;
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