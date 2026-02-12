<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgroLink</title>
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
        
        .header-right p {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .logout-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .logout-link:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .menu-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .menu-card p {
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <header>
        <h1>🌾 AgroLink Admin</h1>
        <div class="header-right">
            <p>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></p>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <h2 style="margin-bottom: 20px;">Admin Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Farmers</h3>
                <div class="stat-value">
                    <?php
                    $farmers = getAllFarmers();
                    echo count($farmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Pending Farmers</h3>
                <div class="stat-value">
                    <?php
                    $pendingFarmers = getAllFarmers('Pending');
                    echo count($pendingFarmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Verified Farmers</h3>
                <div class="stat-value">
                    <?php
                    $verifiedFarmers = getAllFarmers('Verified');
                    echo count($verifiedFarmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Open Reports</h3>
                <div class="stat-value">
                    <?php
                    $openReports = getAllReports('Open');
                    echo count($openReports);
                    ?>
                </div>
            </div>
        </div>
        
        <h2 style="margin-top: 40px; margin-bottom: 20px;">Management Options</h2>
        
        <div class="menu-grid">
            <a href="manage_farmers.php" class="menu-card">
                <div class="menu-icon">👨‍🌾</div>
                <h3>Manage Farmers</h3>
                <p>Verify and manage farmer accounts</p>
            </a>
            
            <a href="manage_products.php" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>Manage Products</h3>
                <p>Approve or reject products</p>
            </a>
            
            <a href="manage_orders.php" class="menu-card">
                <div class="menu-icon">📋</div>
                <h3>Manage Orders</h3>
                <p>View and manage all orders</p>
            </a>
            
            <a href="manage_payments.php" class="menu-card">
                <div class="menu-icon">💳</div>
                <h3>Manage Payments</h3>
                <p>Release or refund escrow payments</p>
            </a>
            
            <a href="manage_reports.php" class="menu-card">
                <div class="menu-icon">🚨</div>
                <h3>Manage Reports</h3>
                <p>Handle fraud and dispute reports</p>
            </a>
            
            <a href="manage_users.php" class="menu-card">
                <div class="menu-icon">👥</div>
                <h3>Manage Users</h3>
                <p>Block or enable user accounts</p>
            </a>
        </div>
        
        <div style="margin-top: 40px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 15px;">📊 Recent Activity</h3>
            <p>Check the individual management pages for the latest activities and updates.</p>
        </div>
    </div>
</body>
</html>
