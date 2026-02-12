<?php
require_once 'functions.php';
session_start();

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard/farmer.php");
    exit();
}

// =======================
// Initialize all variables
// =======================
$fullName = $email = $password = $confirmPassword = '';
$phoneNumber = $location = $farmName = '';
$nationalId = $houseNumber = $businessLicense = '';
$error = $success = '';

// =======================
// Handle POST form
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $farmName = trim($_POST['farm_name'] ?? '');
    $nationalId = trim($_POST['national_id'] ?? '');
    $houseNumber = trim($_POST['house_number'] ?? '');
    $businessLicense = trim($_POST['business_license'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($farmName) || empty($nationalId) || empty($businessLicense)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Register user
        $result = registerUser($fullName, $email, $password, 'Farmer');
        if ($result['success']) {
            $userId = $result['user_id'];
            
            // Register farmer details
            $farmerResult = registerFarmer($userId, $farmName, $phoneNumber, $location, $nationalId, $houseNumber, $businessLicense);
            if ($farmerResult['success']) {
                $success = 'Registration successful! Your account is pending verification. Please log in.';
                // Clear form
                $fullName = $email = $password = $confirmPassword = $phoneNumber = $location = $farmName = $nationalId = $houseNumber = $businessLicense = '';
            } else {
                $error = $farmerResult['message'];
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
<title>Register as Farmer - AgroLink</title>
<style>
/* Your existing CSS */
* {margin:0;padding:0;box-sizing:border-box;}
body {font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height:100vh;padding:20px;}
.container {max-width:700px;margin:30px auto;background:white;padding:40px;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.2);}
.header{text-align:center;margin-bottom:30px;}
.header h1{color:#667eea;font-size:24px;margin-bottom:5px;}
.header p{color:#999;font-size:14px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
.form-row.full{grid-template-columns:1fr;}
.form-group{display:flex;flex-direction:column;}
.form-group label{margin-bottom:8px;color:#333;font-weight:500;font-size:14px;}
.form-group label .required{color:#e74c3c;}
.form-group input,.form-group select{padding:12px;border:1px solid #ddd;border-radius:5px;font-size:14px;transition:border-color 0.3s;}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,0.1);}
.error{background:#f8d7da;color:#721c24;padding:12px;border-radius:5px;margin-bottom:20px;border:1px solid #f5c6cb;}
.success{background:#d4edda;color:#155724;padding:12px;border-radius:5px;margin-bottom:20px;border:1px solid #c3e6cb;}
.register-btn{width:100%;padding:12px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:5px;font-size:16px;font-weight:600;cursor:pointer;transition:transform 0.2s;margin-top:10px;}
.register-btn:hover{transform:translateY(-2px);}
.footer{text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #eee;}
.footer p{color:#666;font-size:14px;margin-bottom:10px;}
.footer a{color:#667eea;text-decoration:none;font-weight:600;}
.footer a:hover{text-decoration:underline;}
.section-title{color:#667eea;font-size:16px;font-weight:600;margin-top:20px;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;}
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>🚜 Farmer Registration</h1>
<p>Join AgroLink marketplace and reach more customers</p>
</div>

<?php if(!empty($error)): ?>
<div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if(!empty($success)): ?>
<div class="success">
<?php echo htmlspecialchars($success); ?>
<br><br>
<a href="login.php" style="color:#155724;text-decoration:underline;">Click here to login</a>
</div>
<?php endif; ?>

<form method="POST" action="register_farmer.php">
<div class="section-title">Personal Information</div>
<div class="form-row full">
<div class="form-group">
<label>Full Name <span class="required">*</span></label>
<input type="text" name="full_name" value="<?php echo htmlspecialchars($fullName ?? ''); ?>" required>
</div>
</div>

<div class="form-row full">
<div class="form-group">
<label>Email Address <span class="required">*</span></label>
<input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
</div>
</div>

<div class="form-row">
<div class="form-group">
<label>Password <span class="required">*</span></label>
<input type="password" name="password" required>
</div>
<div class="form-group">
<label>Confirm Password <span class="required">*</span></label>
<input type="password" name="confirm_password" required>
</div>
</div>

<div class="section-title">Farm Information</div>
<div class="form-row full">
<div class="form-group">
<label>Farm Name <span class="required">*</span></label>
<input type="text" name="farm_name" value="<?php echo htmlspecialchars($farmName ?? ''); ?>" required>
</div>
</div>

<div class="form-row">
<div class="form-group">
<label>Phone Number</label>
<input type="tel" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber ?? ''); ?>">
</div>
<div class="form-group">
<label>Location</label>
<input type="text" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>">
</div>
</div>

<div class="section-title">Verification Documents</div>
<div class="form-row full">
<div class="form-group">
<label>National ID Number <span class="required">*</span></label>
<input type="text" name="national_id" value="<?php echo htmlspecialchars($nationalId ?? ''); ?>" required>
</div>
</div>

<div class="form-row full">
<div class="form-group">
<label>House/Plot Number <span class="required">*</span></label>
<input type="text" name="house_number" value="<?php echo htmlspecialchars($houseNumber ?? ''); ?>" required>
</div>
</div>

<div class="form-row full">
<div class="form-group">
<label>Business License Number <span class="required">*</span></label>
<input type="text" name="business_license" value="<?php echo htmlspecialchars($businessLicense ?? ''); ?>" required>
</div>
</div>

<button type="submit" class="register-btn">Create Account</button>
</form>

<div class="footer">
<p>Already have an account? <a href="login.php">Login here</a></p>
<p>Want to be a customer? <a href="register_customer.php">Register as Customer</a></p>
</div>
</div>
</body>
</html>
