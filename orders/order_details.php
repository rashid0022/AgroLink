<?php
require_once '../functions.php';

$user = getCurrentUser();
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: ../index.php');
    exit;
}

$order = getOrder($order_id);
if (!$order) {
    header('Location: ../index.php');
    exit;
}

// Verify access (customer, farmer with products in order, or admin)
$can_access = false;
$is_customer = $user['role'] === 'Customer' && $order['user_id'] == $user['user_id'];
$is_admin = $user['role'] === 'Admin';

$is_farmer = false;
if ($user['role'] === 'Farmer') {
    $farmer = getFarmerByUserId($user['user_id']);
    $order_items = getOrderItems($order_id);

    foreach ($order_items as $item) {
        $stmt = $GLOBALS['conn']->prepare("
            SELECT farmer_id FROM products WHERE product_id = ?
        ");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();
        if ($product && $product['farmer_id'] == $farmer['farmer_id']) {
            $is_farmer = true;
            break;
        }
    }
}

if (!($is_customer || $is_farmer || $is_admin)) {
    header('Location: ../index.php');
    exit;
}

$order_items = getOrderItems($order_id);

// Get customer info
$stmt = $GLOBALS['conn']->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$order['user_id']]);
$customer = $stmt->fetch();

// Get payment info
$stmt = $GLOBALS['conn']->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

// Handle order status updates (for farmer/admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($is_farmer || $is_admin)) {
    $action = $_POST['action'] ?? null;

    if ($action === 'mark_delivered') {
        $stmt = $GLOBALS['conn']->prepare("
            UPDATE orders SET delivery_status = 'Delivered' WHERE order_id = ?
        ");
        if ($stmt->execute([$order_id])) {
            $success = "Order marked as delivered";
            // Refresh order data
            $order = getOrder($order_id);
        }
    }
}

// Handle report order (for customer on delivered/failed orders)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_customer && isset($_POST['report_action'])) {
    $reason = $_POST['reason'] ?? '';
    if (!empty($reason)) {
        $report_result = createReport($order_id, $user['user_id'], $reason, 'Open');
        if ($report_result['success']) {
            $success = "Report submitted successfully. Our team will review and contact you within 24 hours.";
        }
    }
}

// Get farmers for items
$farmers = [];
foreach ($order_items as $item) {
    $stmt = $GLOBALS['conn']->prepare("
        SELECT f.*, u.full_name, u.email
        FROM farmers f
        JOIN users u ON f.user_id = u.user_id
        WHERE f.farmer_id = (SELECT farmer_id FROM products WHERE product_id = ?)
    ");
    $stmt->execute([$item['product_id']]);
    $farmer = $stmt->fetch();
    if ($farmer) {
        $farmers[$item['product_id']] = $farmer;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - AgroLink</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f4ff;
        }

        h3 {
            color: #333;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .order-header {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item label {
            display: block;
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-item value {
            display: block;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
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

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #667eea;
            border-bottom: 2px solid #eee;
            font-size: 13px;
        }

        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
        }

        .items-table tr:hover {
            background: #f8f9fa;
        }

        .item-name {
            font-weight: 600;
            color: #333;
        }

        .item-price {
            text-align: right;
            color: #667eea;
            font-weight: 600;
        }

        .price-summary {
            text-align: right;
            padding: 15px 0;
            border-top: 2px solid #eee;
        }

        .price-row {
            display: flex;
            justify-content: flex-end;
            gap: 50px;
            margin: 8px 0;
            font-size: 14px;
        }

        .total-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
            font-weight: 600;
            font-size: 18px;
            color: #667eea;
        }

        .section {
            margin-bottom: 30px;
        }

        .address-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .address-box {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .address-box label {
            display: block;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
        }

        .address-box p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        .farmer-card {
            padding: 15px;
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .farmer-card h4 {
            color: #667eea;
            margin-bottom: 5px;
        }

        .farmer-card p {
            font-size: 13px;
            color: #666;
            margin: 3px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c3e6cb;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-modal {
            color: #999;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #333;
        }
    </style>
</head>

<body>
    <header>
        <h1>📦 Order #<?php echo $order['order_id']; ?></h1>
        <div class="header-right">
            <a href="../dashboard/<?php echo strtolower($user['role']); ?>.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="order-header">
                <div class="info-item">
                    <label>Order Date</label>
                    <value><?php echo formatDateTime($order['order_date']); ?></value>
                </div>
                <div class="info-item">
                    <label>Total Amount</label>
                    <value><?php echo formatCurrency($order['total_amount']); ?></value>
                </div>
                <div class="info-item">
                    <label>Order Status</label>
                    <div>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['order_status'])); ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <label>Delivery Status</label>
                    <div>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['delivery_status'])); ?>">
                            <?php echo htmlspecialchars($order['delivery_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information (for farmer/admin view) -->
        <?php if ($is_farmer || $is_admin): ?>
            <div class="card section">
                <h2>Customer Information</h2>
                <div class="address-info">
                    <div class="address-box">
                        <label>Name</label>
                        <p><?php echo htmlspecialchars($customer['full_name']); ?></p>
                    </div>
                    <div class="address-box">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($customer['email']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Delivery Address (for all) -->
        <div class="card section">
            <h2>Delivery Information</h2>
            <div class="address-info">
                <div class="address-box">
                    <label>Address</label>
                    <p><?php echo htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?></p>
                </div>
                <div class="address-box">
                    <label>City</label>
                    <p><?php echo htmlspecialchars($order['delivery_city'] ?? 'N/A'); ?></p>
                </div>
                <div class="address-box">
                    <label>Postal Code</label>
                    <p><?php echo htmlspecialchars($order['delivery_postal_code'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>


        <!-- Order Items -->
        <div class="card section">
            <h2>Order Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <?php if ($is_farmer || $is_admin): ?><th>Farmer</th><?php endif; ?>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <?php if ($is_farmer || $is_admin): ?>
                                <td><?php echo htmlspecialchars($farmers[$item['product_id']]['farm_name'] ?? 'N/A'); ?></td>
                            <?php endif; ?>
                            <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td class="item-price"><?php echo formatCurrency($item['unit_price']); ?></td>
                            <td class="item-price"><?php echo formatCurrency($item['subtotal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="price-summary">
                <div class="price-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
                <div class="price-row">
                    <span>Delivery:</span>
                    <span>TBD</span>
                </div>
                <div class="price-row total-row">
                    <span>Total Amount:</span>
                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <?php if ($payment): ?>
            <div class="card section">
                <h2>Payment Information</h2>
                <div class="address-info">
                    <div class="address-box">
                        <label>Payment Method</label>
                        <p><?php echo htmlspecialchars($payment['payment_method']); ?></p>
                    </div>
                    <div class="address-box">
                        <label>Payment Status</label>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $payment['payment_status'])); ?>">
                            <?php echo htmlspecialchars($payment['payment_status']); ?>
                        </span>
                    </div>
                    <div class="address-box">
                        <label>Amount Paid</label>
                        <p><?php echo formatCurrency($payment['amount']); ?></p>
                    </div>
                    <div class="address-box">
                        <label>Payment Date</label>
                        <p><?php echo formatDateTime($payment['payment_date']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Farmer Details (for customer view) -->
        <?php if ($is_customer): ?>
            <div class="card section">
                <h2>Farms & Sellers</h2>
                <?php foreach ($order_items as $item): ?>
                    <?php if (isset($farmers[$item['product_id']])): ?>
                        <div class="farmer-card">
                            <h4>🌾 <?php echo htmlspecialchars($farmers[$item['product_id']]['farm_name']); ?></h4>
                            <p><strong><?php echo htmlspecialchars($farmers[$item['product_id']]['full_name']); ?></strong></p>
                            <p>📧 <?php echo htmlspecialchars($farmers[$item['product_id']]['email']); ?></p>
                            <p>📍 <?php echo htmlspecialchars($farmers[$item['product_id']]['location'] ?? 'Location not specified'); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="card">
            <?php if ($is_farmer || $is_admin): ?>
                <?php if ($order['delivery_status'] !== 'Delivered'): ?>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="action" value="mark_delivered" class="btn-primary">
                            ✓ Mark as Delivered
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($is_customer && ($order['delivery_status'] === 'Delivered' || $order['delivery_status'] === 'Failed')): ?>
                <button class="btn-primary" onclick="openReportModal()">⚠️ Report Issue</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Modal -->
    <?php if ($is_customer): ?>
        <div id="reportModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close-modal" onclick="closeReportModal()">&times;</span>
                    <h2>Report Order Issue</h2>
                </div>
                <form method="POST">
                    <input type="hidden" name="report_action" value="report">
                    <div class="form-group">
                        <label for="reason">What happened? *</label>
                        <textarea id="reason" name="reason" required placeholder="Please describe the issue with your order..."></textarea>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-secondary" onclick="closeReportModal()" style="flex: 1;">Cancel</button>
                        <button type="submit" class="btn-primary" style="flex: 1;">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openReportModal() {
                document.getElementById('reportModal').style.display = 'block';
            }

            function closeReportModal() {
                document.getElementById('reportModal').style.display = 'none';
            }

            window.onclick = function(event) {
                const modal = document.getElementById('reportModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>