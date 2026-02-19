<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);
$deliveryOrders = getFarmerDeliveryOrders($farmer['farmer_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = intval($_POST['order_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'dispatch') {
        $result = updateDeliveryStatus($orderId, 'Out for Delivery');
        if ($result['success']) {
            $success = 'Order marked as out for delivery!';
            $deliveryOrders = getFarmerDeliveryOrders($farmer['farmer_id']);
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'delivered') {
        $result = updateDeliveryStatus($orderId, 'Delivered');
        if ($result['success']) {
            $success = 'Order marked as delivered! Awaiting customer confirmation.';
            $deliveryOrders = getFarmerDeliveryOrders($farmer['farmer_id']);
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
    <title>Manage Deliveries - AgroLink Farmer</title>
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
        
        .delivery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
        }
        
        .delivery-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #11998e;
        }
        
        .delivery-card h3 {
            color: #11998e;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .delivery-info {
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .delivery-info strong {
            color: #555;
            display: inline-block;
            width: 120px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-dispatched {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 13px;
        }
        
        .btn-dispatch {
            background: #3498db;
            color: white;
        }
        
        .btn-dispatch:hover {
            background: #2980b9;
        }
        
        .btn-delivered {
            background: #27ae60;
            color: white;
        }
        
        .btn-delivered:hover {
            background: #229954;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <header>
        <h1>🚚 Manage Deliveries</h1>
        <div class="header-right">
            <a href="farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (count($deliveryOrders) === 0): ?>
            <div class="empty-state">
                <h3>No deliveries pending</h3>
                <p>All orders have been delivered or customers have no pending orders.</p>
            </div>
        <?php else: ?>
            <div class="delivery-grid">
                <?php foreach ($deliveryOrders as $order): ?>
                    <div class="delivery-card">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        
                        <div class="delivery-info">
                            <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? ''); ?>
                        </div>
                        
                        <div class="delivery-info">
                            <strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? ''); ?>
                        </div>
                        
                        <div class="delivery-info">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone_number'] ?? ''); ?>
                        </div>
                        
                        <div class="delivery-info">
                            <strong>Amount:</strong> <?php echo formatCurrency($order['total_amount']); ?>
                        </div>
                        
                        <div class="delivery-info">
                            <strong>Order Date:</strong> <?php echo formatDate($order['order_date']); ?>
                        </div>
                        
                        <div class="delivery-info">
                            <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($order['delivery_status'] ?? 'Pending')); ?>">
                                <?php echo htmlspecialchars($order['delivery_status'] ?? 'Pending'); ?>
                            </span>
                        </div>
                        
                        <div class="action-buttons">
                            <?php if ($order['delivery_status'] === 'Pending' || empty($order['delivery_status'])): ?>
                                <form method="POST" action="" style="flex: 1;">
                                    <input type="hidden" name="action" value="dispatch">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="btn btn-dispatch">🚗 Dispatch</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($order['delivery_status'] === 'Out for Delivery'): ?>
                                <form method="POST" action="" style="flex: 1;">
                                    <input type="hidden" name="action" value="delivered">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="btn btn-delivered">✓ Delivered</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
