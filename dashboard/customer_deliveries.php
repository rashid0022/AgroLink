<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$notifications = getNotifications($user['user_id']);
$unreadCount = getUnreadNotificationsCount($user['user_id']);

$error = '';
$success = '';

// Handle delivery confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = intval($_POST['order_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'confirm_receipt') {
        $result = confirmDeliveryReceipt($orderId, $user['user_id']);
        if ($result['success']) {
            $success = 'Thank you! Order marked as received.';
            $notifications = getNotifications($user['user_id']);
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'mark_read') {
        $notificationId = intval($_POST['notification_id']);
        markNotificationAsRead($notificationId);
        $notifications = getNotifications($user['user_id']);
    }
}

// Get customer's orders with delivery status
$stmt = $GLOBALS['conn']->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? AND order_status IN ('Paid', 'Completed')
    ORDER BY order_date DESC
");
$stmt->execute([$user['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Deliveries - AgroLink</title>
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
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        
        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
        }
        
        .tab-btn.active {
            color: #11998e;
            border-bottom-color: #11998e;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
        
        .notification-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #11998e;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .notification-card.unread {
            background: #f0f4ff;
            border-left-color: #11998e;
            font-weight: 500;
        }
        
        .notification-card.delivery {
            border-left-color: #27ae60;
        }
        
        .notification-card.success {
            border-left-color: #27ae60;
        }
        
        .notification-message {
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .notification-time {
            font-size: 12px;
            color: #999;
        }
        
        .delivery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .delivery-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .delivery-card h3 {
            color: #11998e;
            margin-bottom: 15px;
        }
        
        .delivery-info {
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .delivery-status {
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: 600;
        }
        
        .status-paid {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .confirm-btn {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .confirm-btn:hover {
            background: #229954;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #999;
        }
        
        .badge {
            display: inline-block;
            background: #11998e;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>📦 My Deliveries</h1>
        <div class="header-right">
            <a href="customer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('deliveries')">
                Deliveries
            </button>
            <button class="tab-btn" onclick="switchTab('notifications')">
                Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </button>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- DELIVERIES TAB -->
        <div id="deliveries" class="tab-content active">
            <?php if (count($orders) === 0): ?>
                <div class="empty-state">
                    <h3>No orders</h3>
                    <p>You don't have any active orders yet.</p>
                </div>
            <?php else: ?>
                <div class="delivery-grid">
                    <?php foreach ($orders as $order): ?>
                        <div class="delivery-card">
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            
                            <div class="delivery-info">
                                <strong>Amount:</strong> <?php echo formatCurrency($order['total_amount']); ?>
                            </div>
                            
                            <div class="delivery-info">
                                <strong>Order Date:</strong> <?php echo formatDate($order['order_date']); ?>
                            </div>
                            
                            <div class="delivery-status status-<?php echo strtolower($order['order_status']); ?>">
                                <?php if ($order['delivery_status'] === 'Out for Delivery'): ?>
                                    🚗 Out for Delivery
                                <?php elseif ($order['delivery_status'] === 'Delivered'): ?>
                                    ✓ Delivered
                                <?php elseif ($order['order_status'] === 'Completed'): ?>
                                    ✓ Completed
                                <?php else: ?>
                                    ⏳ Processing
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($order['delivery_status'] === 'Delivered' && $order['order_status'] !== 'Completed'): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="confirm_receipt">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="confirm-btn">✓ I've Received My Order</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- NOTIFICATIONS TAB -->
        <div id="notifications" class="tab-content">
            <?php if (count($notifications) === 0): ?>
                <div class="empty-state">
                    <h3>No notifications</h3>
                    <p>You don't have any notifications yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-card <?php echo ($notif['is_read'] ? '' : 'unread'); ?> <?php echo htmlspecialchars($notif['type']); ?>">
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </div>
                        <div class="notification-time">
                            <?php echo formatDate($notif['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab and mark button as active
            document.getElementById(tabName).classList.add('active');
            event.target.closest('.tab-btn').classList.add('active');
        }
    </script>
</body>
</html>
