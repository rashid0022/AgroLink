<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);

if ($farmer['verification_status'] !== 'Verified') {
    echo '<div style="padding: 20px; background: #fff3cd; border-radius: 5px; margin: 20px;">
        <strong>Your account must be verified before you can add products.</strong>
    </div>';
    exit;
}

// Define variables with defaults to prevent "undefined variable" warnings
$error = '';
$success = '';
$productName = '';
$category = '';
$price = '';
$quantity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if (empty($productName) || empty($category) || $price <= 0 || $quantity <= 0) {
        $error = 'Please fill all fields with valid values';
    } else {
        $result = addProduct($farmer['farmer_id'], $productName, $category, $price, $quantity);
        if ($result['success']) {
            $success = 'Product added successfully! It will appear in the marketplace after admin approval.';
            // Reset variables after successful submission
            $productName = $category = $price = $quantity = '';
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
    <title>Add Product - AgroLink</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .cancel-link:hover {
            text-decoration: underline;
        }

        .info {
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
        <h1>➕ Add Product</h1>
        <div class="header-right">
            <a href="../dashboard/farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="info">
                Products require admin approval before appearing in the marketplace.
            </div>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="add_product.php">
                <div class="form-group">
                    <label>Product Name <span class="required">*</span></label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($productName ?? ''); ?>" placeholder="e.g., Fresh Tomatoes" required>
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Vegetables" <?php echo ($category === 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
                        <option value="Fruits" <?php echo ($category === 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
                        <option value="Grains" <?php echo ($category === 'Grains') ? 'selected' : ''; ?>>Grains</option>
                        <option value="Dairy" <?php echo ($category === 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
                        <option value="Herbs" <?php echo ($category === 'Herbs') ? 'selected' : ''; ?>>Herbs</option>
                        <option value="Other" <?php echo ($category === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>


                </div>

                <div class="form-group">
                    <label>Price (KES) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label>Quantity Available <span class="required">*</span></label>
                    <input type="number" name="quantity" min="1" value="<?php echo htmlspecialchars($quantity ?? ''); ?>" placeholder="In kg or units" required>
                </div>

                <button type="submit" class="submit-btn">Add Product</button>
                <a href="list_products.php" class="cancel-link">View My Products</a>
            </form>
        </div>
    </div>
</body>

</html>