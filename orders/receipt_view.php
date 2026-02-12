<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$receipt_id = $_GET['receipt_id'] ?? null;

if (!$receipt_id) {
    header('Location: ../index.php');
    exit;
}

$receipt = getReceipt($receipt_id);
if (!$receipt) {
    header('Location: ../dashboard/customer.php');
    exit;
}

// Verify receipt belongs to current user
$order = getOrder($receipt['order_id']);
if (!$order || $order['user_id'] != $user['user_id']) {
    header('Location: ../dashboard/customer.php');
    exit;
}

$order_items = getOrderItems($receipt['order_id']);

// Get payment info
$stmt = $GLOBALS['conn']->prepare("SELECT * FROM payments WHERE payment_id = ?");
$stmt->execute([$receipt['payment_id']]);
$payment = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - AgroLink</title>
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
            font-size: 24px;
        }
        
        header p {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .receipt-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .receipt-header h1 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .receipt-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .receipt-body {
            padding: 40px;
        }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }
        
        .receipt-row:last-child {
            border-bottom: none;
        }
        
        .receipt-label {
            font-weight: 600;
            color: #667eea;
            flex: 1;
        }
        
        .receipt-value {
            text-align: right;
            color: #333;
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            font-size: 16px;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f4ff;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .items-table thead {
            background: #f8f9fa;
        }
        
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #667eea;
            border-bottom: 2px solid #eee;
            font-size: 13px;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .price {
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        
        .total-section {
            margin-top: 30px;
            text-align: right;
            font-size: 18px;
        }
        
        .total-row {
            margin: 10px 0;
            display: flex;
            justify-content: flex-end;
            gap: 50px;
        }
        
        .total-label {
            font-weight: 600;
            color: #667eea;
        }
        
        .total-amount {
            font-weight: 700;
            color: #667eea;
        }
        
        .grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
            font-size: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c3e6cb;
            font-size: 14px;
        }
        
        .receipt-footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            color: #999;
            font-size: 13px;
        }
        
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        button, .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
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
        
        @media print {
            header, .button-group {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>📄 Order Receipt</h1>
        <p>Your order has been confirmed and payment is being processed</p>
    </header>
    
    <div class="container">
        <div class="success-message">
            ✓ Payment confirmed! Your order is being prepared. You'll receive updates via email.
        </div>
        
        <div class="receipt-container">
            <div class="receipt-header">
                <h1>AgroLink</h1>
                <p>Farm Fresh Marketplace</p>
            </div>
            
            <div class="receipt-body">
                <div class="receipt-row">
                    <span class="receipt-label">Receipt #</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($receipt['receipt_number']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Order #</span>
                    <span class="receipt-value"><?php echo $receipt['order_id']; ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Issue Date</span>
                    <span class="receipt-value"><?php echo formatDateTime($receipt['receipt_date']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Payment Status</span>
                    <span class="receipt-value">
                        <?php echo htmlspecialchars($payment['payment_status']); ?>
                        <span class="status-badge status-pending">Held in Escrow</span>
                    </span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Payment Method</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['payment_method']); ?></span>
                </div>
                
                <div class="section-title">Customer Information</div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Name</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Email</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="section-title">Order Items</div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Product</th>
                            <th style="text-align: center;">Quantity</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                                <td class="price"><?php echo formatCurrency($item['unit_price']); ?></td>
                                <td class="price"><?php echo formatCurrency($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="receipt-row">
                    <span class="receipt-label">Subtotal</span>
                    <span class="receipt-value"><?php echo formatCurrency($receipt['total_amount']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Delivery Charges</span>
                    <span class="receipt-value">TBD</span>
                </div>
                
                <div class="total-section">
                    <div class="receipt-row" style="border: none; margin: 20px 0;">
                        <span class="total-label">TOTAL AMOUNT</span>
                        <span class="total-amount" style="font-size: 24px;">
                            <?php echo formatCurrency($receipt['total_amount']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="receipt-footer">
                <p>Thank you for your order! Your items will be delivered within 2-3 business days.</p>
                <p>For support, contact us at support@agrolink.local or call +254 123 456 789</p>
                <p style="margin-top: 20px; color: #ccc;">Receipt generated on <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
        
        <div class="button-group">
            <button class="btn btn-secondary" onclick="window.print();">🖨️ Print Receipt</button>
            <a href="../dashboard/customer.php" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
