<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();

// Get all orders with details
$stmt = $GLOBALS['conn']->prepare("
    SELECT o.*, u.full_name as customer_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - AgroLink Admin</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
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
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .view-link:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <header>
        <h1>📋 Manage Orders</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="table-container">
            <?php if (count($orders) === 0): ?>
                <div class="empty-state">
                    <h3>No orders yet</h3>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Order Status</th>
                            <th>Delivery Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo formatDateTime($order['order_date']); ?></td>
                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['order_status'])); ?>">
                                        <?php echo htmlspecialchars($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['delivery_status'])); ?>">
                                        <?php echo htmlspecialchars($order['delivery_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
