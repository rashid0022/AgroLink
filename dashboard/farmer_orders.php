<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);

// Get orders for products sold by this farmer
$stmt = $GLOBALS['conn']->prepare("
    SELECT DISTINCT o.*, u.full_name as customer_name, u.email
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    JOIN users u ON o.user_id = u.user_id
    WHERE p.farmer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$farmer['farmer_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - AgroLink Farmer</title>
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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .orders-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-info h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-meta {
            font-size: 13px;
            color: #999;
            margin-bottom: 10px;
        }
        
        .customer-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 13px;
        }
        
        .price {
            font-size: 18px;
            font-weight: 600;
            color: #11998e;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
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
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <header>
        <h1>📋 Customer Orders</h1>
        <div class="header-right">
            <a href="../dashboard/farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="orders-list">
            <?php if (count($orders) === 0): ?>
                <div class="empty-state">
                    <h3>No customer orders yet</h3>
                    <p>When customers buy your products, their orders will appear here</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <div>
                                <h3>Order #<?php echo $order['order_id']; ?></h3>
                                <div class="order-meta">
                                    <?php echo formatDateTime($order['order_date']); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="price"><?php echo formatCurrency($order['total_amount']); ?></div>
                            </div>
                        </div>
                        
                        <div class="customer-info">
                            <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                        </div>
                        
                        <div>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['order_status'])); ?>">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </span>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['delivery_status'])); ?>">
                                Delivery: <?php echo htmlspecialchars($order['delivery_status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
