<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$customer = getCustomerByUserId($user['user_id']);
$orders = getCustomerOrders($user['user_id']);
$categoryList = getProductCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - AgroLink</title>
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 4px solid #11998e;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #11998e;
            text-align: center;
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
            color: #11998e;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
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
            color: #11998e;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .menu-card p {
            color: #999;
            font-size: 13px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        
        .category-btn {
            background: white;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-decoration: none;
            color: #11998e;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
        }
        
        .category-btn:hover {
            border-color: #11998e;
            background: #f5f7ff;
            transform: translateY(-2px);
        }
        
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .info-card h3 {
            color: #11998e;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .info-item label {
            color: #999;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .info-item p {
            font-size: 16px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .order-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-info {
            flex: 1;
        }
        
        .order-id {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-date {
            color: #999;
            font-size: 13px;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <header>
        <h1>🛒 Customer Dashboard</h1>
        <div class="header-right">
            <p>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></p>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-value"><?php echo count($orders); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <div class="stat-value"><?php echo count(array_filter($orders, function($o) { return $o['order_status'] === 'Pending'; })); ?></div>
            </div>
            <div class="stat-card">
                <h3>Delivered</h3>
                <div class="stat-value"><?php echo count(array_filter($orders, function($o) { return $o['order_status'] === 'Delivered'; })); ?></div>
            </div>
        </div>
        
        <h2 style="margin: 40px 0 20px 0;">Shop by Category</h2>
        
        <div class="category-grid">
            <a href="../index.php" class="category-btn">🌾 All Products</a>
            <?php foreach ($categoryList as $category): ?>
                <a href="../index.php?category=<?php echo urlencode($category); ?>" class="category-btn">
                    <?php echo htmlspecialchars($category); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <h2 style="margin: 40px 0 20px 0;">Quick Actions</h2>
        
        <div class="menu-grid">
            <a href="../index.php" class="menu-card">
                <div class="menu-icon">🛍️</div>
                <h3>Browse Products</h3>
                <p>Discover fresh produce from local farmers</p>
            </a>
            
            <a href="customer_orders.php" class="menu-card">
                <div class="menu-icon">📋</div>
                <h3>My Orders</h3>
                <p>View and track your orders</p>
            </a>
            
            <a href="customer_deliveries.php" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>My Deliveries</h3>
                <p>Track deliveries and notifications</p>
            </a>
            
            <a href="customer_profile.php" class="menu-card">
                <div class="menu-icon">👤</div>
                <h3>My Profile</h3>
                <p>Update contact information</p>
            </a>
        </div>
        
        <?php if (count($orders) > 0): ?>
            <div class="order-list">
                <div style="background: #f8f9fa; padding: 15px; font-weight: 600; color: #11998e; border-bottom: 2px solid #eee;">
                    📋 Recent Orders
                </div>
                <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date"><?php echo formatDateTime($order['order_date']); ?> - <?php echo formatCurrency($order['total_amount']); ?></div>
                        </div>
                        <span class="order-status status-<?php echo strtolower(str_replace(' ', '_', $order['order_status'])); ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-card">
            <h3>📍 Delivery Address</h3>
            <div class="info-row">
                <div class="info-item">
                    <label>Phone Number</label>
                    <p><?php echo htmlspecialchars($customer['phone_number']); ?></p>
                </div>
                <div class="info-item">
                    <label>City</label>
                    <p><?php echo htmlspecialchars($customer['city']); ?></p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <label>Address</label>
                    <p><?php echo htmlspecialchars($customer['address']); ?></p>
                </div>
                <div class="info-item">
                    <label>Postal Code</label>
                    <p><?php echo htmlspecialchars($customer['postal_code']); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
