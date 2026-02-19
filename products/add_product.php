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

// Create uploads directory if it doesn't exist
$uploadsDir = '../assets/images/products/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $productImage = null;

    if (empty($productName) || empty($category) || $price <= 0 || $quantity <= 0) {
        $error = 'Please fill all fields with valid values';
    } elseif (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = 'Please upload a product image';
    } else {
        // Handle file upload
        $file = $_FILES['product_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error uploading file';
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $error = 'Only image files are allowed (JPG, PNG, GIF, WebP)';
        } elseif ($file['size'] > $maxFileSize) {
            $error = 'Image size must be less than 5MB';
        } else {
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $productImage = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $uploadPath = $uploadsDir . $productImage;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $result = addProduct($farmer['farmer_id'], $productName, $category, $price, $quantity, $productImage);
                if ($result['success']) {
                    $success = 'Product added successfully with image! It is now available in the marketplace.';
                    // Reset variables after successful submission
                    $productName = $category = $price = $quantity = '';
                } else {
                    $error = $result['message'];
                    // Delete uploaded image if database insert failed
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f3, #dfe9f3);
            color: #333;
        }

        header {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        header h1 {
            display: inline-block;
            font-size: 26px;
            font-weight: 700;
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

        .header-right a:hover { text-decoration: underline; }

        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .info {
            background: #e0f7fa;
            border: 1px solid #26a69a;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #00796b;
            font-size: 14px;
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

        .form-group label .required { color: #e74c3c; }

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
            box-shadow: 0 0 0 3px rgba(17,152,142,0.1);
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
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover { transform: translateY(-2px); }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #11998e;
            text-decoration: none;
            font-weight: 600;
        }

        .cancel-link:hover { text-decoration: underline; }
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
                Add your product details and upload a high-quality product image. Products are automatically available in the marketplace.
            </div>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="add_product.php" enctype="multipart/form-data">
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

                <div class="form-group">
                    <label>Product Image <span class="required">*</span></label>
                    <input type="file" name="product_image" accept="image/*" required>
                    <small style="color: #666; margin-top: 5px; display: block;">Accepted formats: JPG, PNG, GIF, WebP. Maximum size: 5MB</small>
                </div>

                <button type="submit" class="submit-btn">Add Product</button>
                <a href="list_products.php" class="cancel-link">View My Products</a>
            </form>
        </div>
    </div>
</body>
</html>
