<?php
require_once '../functions.php';
requireRole('Customer');

$user = getCurrentUser();
$customer = getCustomerByUserId($user['user_id']);
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    header('Location: ../index.php');
    exit;
}

$product = getProduct($product_id);
if (!$product || $product['approval_status'] !== 'Approved') {
    header('Location: ../index.php');
    exit;
}

$quantity = $_GET['quantity'] ?? 1;
$quantity = max(1, min($quantity, $product['quantity_available']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'] ?? 1;
    $quantity = intval($quantity);
    
    if ($quantity < 1 || $quantity > $product['quantity_available']) {
        $error = "Invalid quantity";
    } else {
        // Create order
        $total_amount = $product['price'] * $quantity;
        $order_result = createOrder($user['user_id'], $total_amount, $customer['phone_number'], $customer['address'], $customer['city'], $customer['postal_code']);
        
        if ($order_result['success']) {
            $order_id = $order_result['order_id'];
            
            // Add order item
            $item_result = addOrderItem($order_id, $product_id, $quantity, $product['price']);
            
            if ($item_result['success']) {
                header("Location: ../orders/payment.php?order_id=" . $order_id);
                exit;
            } else {
                $error = "Failed to add item to order";
            }
        } else {
            $error = "Failed to create order";
        }
    }
}

// Get farmer info
$stmt = $GLOBALS['conn']->prepare("
    SELECT u.full_name, f.farm_name 
    FROM farmers f
    JOIN users u ON f.user_id = u.user_id
    WHERE f.farmer_id = ?
");
$stmt->execute([$product['farmer_id']]);
$farmer = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .product-summary h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .product-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .product-details p {
            margin: 10px 0;
            font-size: 14px;
        }
        
        .product-details strong {
            color: #667eea;
        }
        
        .farmer-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }
        
        .farmer-info h4 {
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .farmer-info p {
            font-size: 13px;
            color: #666;
        }
        
        .price-breakdown {
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
        
        .checkout-form h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .readonly-field {
            background: #f8f9fa;
            color: #666;
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f4ff;
            font-size: 13px;
            text-transform: uppercase;
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
        
        .btn-checkout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c3e6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            color: #0066cc;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border-left: 4px solid #0066cc;
        }
    </style>
</head>
<body>
    <header>
        <h1>🛒 Checkout</h1>
    </header>
    
    <div class="container">
        <div class="checkout-content">
            <!-- Product Summary -->
            <div class="card product-summary">
                <h2>Order Summary</h2>
                
                <div class="product-details">
                    <p><strong>Product:</strong><br><?php echo htmlspecialchars($product['product_name']); ?></p>
                    <p><strong>Category:</strong><br><?php echo htmlspecialchars($product['product_category']); ?></p>
                    <p><strong>Unit Price:</strong><br><?php echo formatCurrency($product['price']); ?></p>
                    <p><strong>Available:</strong><br><?php echo $product['quantity_available']; ?> units</p>
                </div>
                
                <div class="farmer-info">
                    <h4>From Farm:</h4>
                    <p><strong><?php echo htmlspecialchars($farmer['farm_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($farmer['full_name']); ?></p>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Unit Price:</span>
                        <span><?php echo formatCurrency($product['price']); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Quantity:</span>
                        <span id="qty-display"><?php echo $quantity; ?></span>
                    </div>
                    <div class="total-row">
                        <span>Total Amount:</span>
                        <span id="total-display"><?php echo formatCurrency($product['price'] * $quantity); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div class="card checkout-form">
                <h2>Complete Order</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="info-box">
                    ✓ Secure checkout with order confirmation email
                </div>
                
                <form method="POST">
                    <div class="section-title">Delivery Address</div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="readonly-field" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="readonly-field" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone_number']); ?>" required readonly class="readonly-field">
                    </div>
                    
                    <div class="form-group">
                        <label>Delivery Address *</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required readonly class="readonly-field">
                    </div>
                    
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city']); ?>" required readonly class="readonly-field">
                    </div>
                    
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($customer['postal_code']); ?>" readonly class="readonly-field">
                    </div>
                    
                    <div class="section-title">Order Details</div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['quantity_available']; ?>" value="<?php echo $quantity; ?>" required onchange="updateTotal()">
                    </div>
                    
                    <div class="form-group">
                        <label>Special Instructions (Optional)</label>
                        <textarea name="instructions" placeholder="Any special delivery instructions or notes..."></textarea>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn-cancel" onclick="window.history.back();">Cancel</button>
                        <button type="submit" class="btn-checkout">Proceed to Payment →</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function updateTotal() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const maxQty = <?php echo $product['quantity_available']; ?>;
            
            if (quantity > maxQty) {
                document.getElementById('quantity').value = maxQty;
                return;
            }
            
            const unitPrice = <?php echo $product['price']; ?>;
            const total = unitPrice * quantity;
            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            });
            
            document.getElementById('qty-display').textContent = quantity;
            document.getElementById('total-display').textContent = formatter.format(total);
        }
    </script>
</body>
</html>
