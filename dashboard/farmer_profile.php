<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Farm Profile - AgroLink</title>
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
        
        .profile-container {
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
        
        .info-row {
            margin-bottom: 20px;
        }
        
        .info-label {
            color: #999;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            padding: 12px;
            background: #f5f6fa;
            border-radius: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #1565c0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <h1>🚜 My Farm Profile</h1>
        <div class="header-right">
            <a href="../dashboard/farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="profile-container">
            <?php if ($farmer['verification_status'] === 'Pending'): ?>
                <div class="info-box">
                    ⏱️ Your farm is pending verification. Admin will review your documents shortly.
                </div>
            <?php elseif ($farmer['verification_status'] === 'Suspended'): ?>
                <div class="info-box" style="background: #ffebee; border-color: #f44336; color: #c62828;">
                    ⚠️ Your farm account has been suspended. Please contact admin support.
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">Personal Information</h2>
                <div class="info-row">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['phone_number']); ?></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Farm Information</h2>
                <div class="info-row">
                    <div class="info-label">Farm Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['farm_name']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Location</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['location']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Verification Status</div>
                    <div>
                        <span class="status-badge status-<?php echo strtolower($farmer['verification_status']); ?>">
                            <?php echo htmlspecialchars($farmer['verification_status']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Farm Registered</div>
                    <div class="info-value"><?php echo formatDate($farmer['registered_date']); ?></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Verification Documents</h2>
                <div class="info-row">
                    <div class="info-label">National ID Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['national_id_number']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">House/Plot Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['house_number']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Business License Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($farmer['business_license_number']); ?></div>
                </div>
            </div>
            
            <a href="../dashboard/farmer.php" class="back-link">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
