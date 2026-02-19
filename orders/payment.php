<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: ../index.php');
    exit;
}

$order = getOrder($order_id);
if (!$order || $order['user_id'] != $user['user_id']) {
    header('Location: ../index.php');
    exit;
}

$order_items = getOrderItems($order_id);

// Simulate payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'Card';
    
    // Validate payment method
    $valid_methods = ['Card', 'M-Pesa', 'Bank Transfer', 'Wallet'];
    if (!in_array($payment_method, $valid_methods)) {
        $error = "Invalid payment method";
    } else {
        // Create payment record - mark as Held (escrow)
        $payment_result = createPayment($order_id, $payment_method);

        
        if ($payment_result['success']) {
            // Create receipt
            $receipt_result = createReceipt($payment_result['payment_id']);
            
            if ($receipt_result['success']) {
                header("Location: receipt_view.php?receipt_id=" . $receipt_result['receipt_id']);
                exit;
            } else {
                $error = "Payment recorded but receipt generation failed";
            }
        } else {
            $error = "Payment processing failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            color: #333;
        }
        
        header {
            background: rgba(0,0,0,0.1);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        header h1 {
            font-size: 28px;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .payment-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        @media (max-width: 768px) {
            .payment-content {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .order-summary h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .order-reference {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .order-reference p {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .order-reference strong {
            color: #667eea;
        }
        
        .item-list {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-bottom: 20px;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-size: 14px;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-price {
            color: #667eea;
            font-weight: 600;
        }
        
        .price-summary {
            border-top: 2px solid #eee;
            padding-top: 15px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
            font-weight: 600;
            font-size: 18px;
            color: #667eea;
        }
        
        .payment-form h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .payment-methods {
            margin-bottom: 25px;
        }
        
        .method-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .method-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .method-option input[type="radio"] {
            margin-right: 15px;
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }
        
        .method-option label {
            flex: 1;
            cursor: pointer;
            margin: 0;
        }
        
        .method-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .method-text h4 {
            color: #333;
            margin: 0 0 3px 0;
            font-size: 15px;
        }
        
        .method-text p {
            color: #999;
            font-size: 12px;
            margin: 0;
        }
        
        .security-info {
            background: #e7f5e7;
            color: #2d5d2d;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
            font-size: 13px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        button {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-back {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-back:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <header>
        <h1>💳 Payment</h1>
    </header>
    
    <div class="container">
        <div class="payment-content">
            <!-- Order Summary -->
            <div class="card order-summary">
                <h2>Order Details</h2>
                
                <div class="order-reference">
                    <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                    <p><strong>Date:</strong> <?php echo formatDateTime($order['order_date']); ?></p>
                    <p><strong>Status:</strong> <span style="color: #ff9800;"><?php echo htmlspecialchars($order['order_status']); ?></span></p>
                </div>
                
                <div class="item-list">
                    <h4 style="margin-bottom: 10px; color: #667eea; font-size: 14px;">Items:</h4>
                    <?php foreach ($order_items as $item): ?>
                        <div class="item">
                            <div class="item-name">
                                <?php echo htmlspecialchars($item['product_name']); ?><br>
                                <span style="color: #999; font-size: 12px;">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="item-price"><?php echo number_format($item['subtotal'], 2); ?> TZS</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="price-summary">
                    <div class="price-row">
                        <span>Subtotal:</span>
                        <span><?php echo number_format($order['total_amount'], 2); ?> TZS</span>
                    </div>
                    <div class="price-row">
                        <span>Delivery:</span>
                        <span>TBD</span>
                    </div>
                    <div class="total-row">
                        <span>Amount Due:</span>
                        <span><?php echo number_format($order['total_amount'], 2); ?> TZS</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="card payment-form">
                <h2>Select Payment Method</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="security-info">
                    🔒 Your payment is secured and encrypted. Funds are held in escrow until delivery confirmation.
                </div>
                
                <form method="POST">
                    <div class="payment-methods">
                        <!-- Card Payment -->
                        <div class="method-option">
                            <input type="radio" id="card" name="payment_method" value="Card" checked>
                            <label for="card" style="display: flex; align-items: center;">
                                <div class="method-icon">💳</div>
                                <div class="method-text">
                                    <h4>Credit/Debit Card</h4>
                                    <p>Visa, Mastercard, American Express</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- M-Pesa -->
                        <div class="method-option">
                            <input type="radio" id="mpesa" name="payment_method" value="M-Pesa">
                            <label for="mpesa" style="display: flex; align-items: center;">
                                <div class="method-icon">📱</div>
                                <div class="method-text">
                                    <h4>M-Pesa</h4>
                                    <p>Fast mobile money transfer</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Bank Transfer -->
                        <div class="method-option">
                            <input type="radio" id="bank" name="payment_method" value="Bank Transfer">
                            <label for="bank" style="display: flex; align-items: center;">
                                <div class="method-icon">🏦</div>
                                <div class="method-text">
                                    <h4>Bank Transfer</h4>
                                    <p>Direct account to account</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Wallet -->
                        <div class="method-option">
                            <input type="radio" id="wallet" name="payment_method" value="Wallet">
                            <label for="wallet" style="display: flex; align-items: center;">
                                <div class="method-icon">👛</div>
                                <div class="method-text">
                                    <h4>AgroLink Wallet</h4>
                                    <p>Available balance</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn-back" onclick="window.history.back();">Back</button>
                        <button type="submit" class="btn-pay">Pay <?php echo number_format($order['total_amount'], 2); ?> TZS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
