<?php
require_once 'functions.php';
session_start();

// Check if user already logged in
if (isLoggedIn()) {
    header("Location: dashboard/customer.php");
    exit();
}

// =======================
// Initialize variables
// =======================
$fullName = '';
$email = '';
$password = '';
$confirmPassword = '';
$phoneNumber = '';
$address = '';
$city = '';
$postalCode = '';

$error = '';
$success = '';

// =======================
// Handle form submission
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');

    // Validation
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Register user
        $result = registerUser($fullName, $email, $password, 'Customer');
        if ($result['success']) {
            $userId = $result['user_id'];
            
            // Register customer details
            $customerResult = registerCustomer($userId, $phoneNumber, $address, $city, $postalCode);
            if ($customerResult['success']) {
                $success = 'Registration successful! Please log in.';
                // Clear form values
                $fullName = $email = $password = $confirmPassword = $phoneNumber = $address = $city = $postalCode = '';
            } else {
                $error = $customerResult['message'];
            }
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
    <title>Register as Customer - AgroLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #667eea; margin-bottom: 5px; }
        .header p { color: #999; font-size: 14px; }
        .section-title { color: #667eea; font-weight: 600; margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-row.full { grid-template-columns: 1fr; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 8px; font-weight: 500; }
        .form-group input { padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; transition: border-color 0.3s; }
        .form-group input:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); outline: none; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .register-btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .register-btn:hover { transform: translateY(-2px); transition: transform 0.2s; }
        .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .footer a { color: #667eea; text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌾 Customer Registration</h1>
            <p>Join AgroLink marketplace</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?><br><br>
                <a href="login.php" style="color: #155724;">Click here to login</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_customer.php">
            <div class="section-title">Personal Information</div>
            <div class="form-row full">
                <div class="form-group">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($fullName) ?>" required>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <div class="section-title">Contact Information</div>
            <div class="form-row full">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" value="<?= htmlspecialchars($phoneNumber) ?>">
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($city) ?>">
                </div>
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" name="postal_code" value="<?= htmlspecialchars($postalCode) ?>">
                </div>
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p>Want to be a farmer? <a href="register_farmer.php">Register as Farmer</a></p>
        </div>
    </div>
</body>
</html>
