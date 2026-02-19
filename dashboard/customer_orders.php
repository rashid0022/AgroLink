<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$orders = getCustomerOrders($user['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - AgroLink</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-info h3 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .order-meta {
            font-size: 13px;
            color: #999;
            margin-bottom: 10px;
        }
        
        .price {
            font-size: 18px;
            font-weight: 600;
            color: #11998e;
            margin-bottom: 10px;
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
        
        .view-link {
            color: #11998e;
            text-decoration: none;
            font-weight: 600;
        }
        
        .view-link:hover {
            text-decoration: underline;
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
        
        .shop-btn {
            display: inline-block;
            margin-top: 15px;
            background: #11998e;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .shop-btn:hover {
            background: #38ef7d;
        }
    </style>
</head>
<body>
    <header>
        <h1>📋 My Orders</h1>
        <div class="header-right">
            <a href="../dashboard/customer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="orders-list">
            <?php if (count($orders) === 0): ?>
                <div class="empty-state">
                    <h3>No orders yet</h3>
                    <p>Start shopping for fresh produce from local farmers</p>
                    <a href="../index.php" class="shop-btn">Browse Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <div class="order-meta">
                                <?php echo formatDateTime($order['order_date']); ?>
                            </div>
                            <div class="price"><?php echo formatCurrency($order['total_amount']); ?></div>
                            <div>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['order_status'])); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['delivery_status'])); ?>">
                                    <?php echo 'Delivery: ' . htmlspecialchars($order['delivery_status']); ?>
                                </span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <a href="../orders/order_details.php?order_id=<?php echo $order['order_id']; ?>" class="view-link">View Details →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
