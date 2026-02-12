<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $paymentId = intval($_POST['payment_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'release') {
        $result = releasePayment($paymentId);
        if ($result['success']) {
            $success = 'Payment released to farmer!';
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'refund') {
        $result = refundPayment($paymentId);
        if ($result['success']) {
            $success = 'Payment refunded to customer!';
        } else {
            $error = $result['message'];
        }
    }
}

// Get all payments
$stmt = $GLOBALS['conn']->prepare("
    SELECT p.*, o.order_id, o.total_amount, u.full_name as customer_name 
    FROM payments p 
    JOIN orders o ON p.order_id = o.order_id 
    JOIN users u ON o.user_id = u.user_id
    ORDER BY p.payment_date DESC
");
$stmt->execute();
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - AgroLink Admin</title>
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
        
        .payments-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .payment-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-info h3 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .payment-meta {
            font-size: 13px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .amount {
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
            margin: 10px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-held {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-released {
            background: #d4edda;
            color: #155724;
        }
        
        .status-refunded {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-release {
            background: #4caf50;
            color: white;
        }
        
        .btn-release:hover {
            background: #45a049;
        }
        
        .btn-refund {
            background: #ff9800;
            color: white;
        }
        
        .btn-refund:hover {
            background: #e68900;
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
        <h1>💳 Manage Payments</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
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
        
        <div class="payments-list">
            <?php if (count($payments) === 0): ?>
                <div class="empty-state">
                    <h3>No payments yet</h3>
                </div>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <div class="payment-item">
                        <div class="payment-info">
                            <h3>Order #<?php echo $payment['order_id']; ?></h3>
                            <div class="payment-meta">
                                Customer: <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong>
                            </div>
                            <div class="payment-meta">
                                Payment Method: <?php echo htmlspecialchars($payment['payment_method']); ?> |
                                Date: <?php echo formatDateTime($payment['payment_date']); ?>
                            </div>
                            <div class="amount"><?php echo formatCurrency($payment['total_amount']); ?></div>
                            <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                <?php echo htmlspecialchars($payment['payment_status']); ?>
                            </span>
                        </div>
                        <div class="actions">
                            <?php if ($payment['payment_status'] === 'Held'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="release">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                    <button type="submit" class="btn btn-release">Release to Farmer</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Refund this payment?');">
                                    <input type="hidden" name="action" value="refund">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                    <button type="submit" class="btn btn-refund">Refund</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
