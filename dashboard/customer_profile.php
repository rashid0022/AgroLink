<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$customer = getCustomerByUserId($user['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $postalCode = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
    
    $result = updateCustomerProfile($user['user_id'], $phoneNumber, $address, $city, $postalCode);
    if ($result['success']) {
        $success = 'Profile updated successfully!';
        $customer = getCustomerByUserId($user['user_id']);
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        header h1 {
            display: inline-block;
            font-size: 24px;
            margin-right: 20px;
        }
        
        .header-right {
            float: right;
            text-align: right;
        }
        
        .header-right a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #667eea;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .readonly-field {
            background: #f5f6fa;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .submit-btn {
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
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>👤 My Profile</h1>
        <div class="header-right">
            <a href="../dashboard/customer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">Account Information</h2>
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($user['full_name']); ?></div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
                <div class="form-group">
                    <label>Account Status</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($user['account_status']); ?></div>
                </div>
            </div>
            
            <form method="POST" action="customer_profile.php">
                <div class="section">
                    <h2 class="section-title">Delivery Information</h2>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($customer['phone_number']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($customer['postal_code']); ?>">
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Save Changes</button>
            </form>
            
            <a href="../dashboard/customer.php" class="back-link">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
