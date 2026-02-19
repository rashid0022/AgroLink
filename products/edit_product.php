<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$productId) {
    header("Location: list_products.php");
    exit;
}

// Get product
$product = getProduct($productId);
if (!$product || $product['farmer_id'] !== $farmer['farmer_id']) {
    header("Location: list_products.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if (empty($productName) || empty($category) || $price <= 0 || $quantity <= 0) {
        $error = 'Please fill all fields with valid values';
    } else {
        $result = updateProduct($productId, $productName, $category, $price, $quantity);
        if ($result['success']) {
            $success = 'Product updated successfully!';
            $product = getProduct($productId);
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
    <title>Edit Product - AgroLink</title>
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
        
        .header-right a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label .required {
            color: #e74c3c;
        }
        
        .form-group input,
        .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
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
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #11998e;
            text-decoration: none;
            font-weight: 600;
        }
        
        .cancel-link:hover {
            text-decoration: underline;
        }
        
        .status-info {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #1565c0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <h1>✏️ Edit Product</h1>
        <div class="header-right">
            <a href="list_products.php">Back to Products</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="form-container">
            <div class="status-info">
                <strong>Current Status:</strong> <?php echo htmlspecialchars($product['approval_status']); ?><br>
                <?php if ($product['approval_status'] === 'Pending'): ?>
                    Awaiting admin approval before appearing in marketplace.
                <?php elseif ($product['approval_status'] === 'Approved'): ?>
                    Your product is live in the marketplace!
                <?php else: ?>
                    Your product was rejected. Please review and make changes.
                <?php endif; ?>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="edit_product.php?id=<?php echo $productId; ?>">
                <div class="form-group">
                    <label>Product Name <span class="required">*</span></label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="Vegetables" <?php echo ($product['category'] === 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
                        <option value="Fruits" <?php echo ($product['category'] === 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
                        <option value="Grains" <?php echo ($product['category'] === 'Grains') ? 'selected' : ''; ?>>Grains</option>
                        <option value="Dairy" <?php echo ($product['category'] === 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
                        <option value="Herbs" <?php echo ($product['category'] === 'Herbs') ? 'selected' : ''; ?>>Herbs</option>
                        <option value="Other" <?php echo ($product['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Price (KES) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Quantity Available <span class="required">*</span></label>
                    <input type="number" name="quantity" min="1" value="<?php echo htmlspecialchars($product['quantity_available']); ?>" required>
                </div>
                
                <button type="submit" class="submit-btn">Update Product</button>
                <a href="list_products.php" class="cancel-link">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
