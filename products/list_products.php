<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);
$products = getFarmerProducts($farmer['farmer_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $productId = intval($_POST['product_id']);
    $result = deleteProduct($productId);
    if ($result['success']) {
        $success = $result['message'];
        // Refresh products list
        $products = getFarmerProducts($farmer['farmer_id']);
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - AgroLink</title>
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
        
        .header-right a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .action-bar {
            margin-bottom: 20px;
        }
        
        .add-btn {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        
        .add-btn:hover {
            background: #45a049;
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
        
        .products-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .product-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-info h3 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-meta {
            font-size: 13px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .price {
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
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
        
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0b7dda;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background: #da190b;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>📦 My Products</h1>
        <div class="header-right">
            <a href="../dashboard/farmer.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="action-bar">
            <a href="add_product.php" class="add-btn">+ Add New Product</a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="products-list">
            <?php if (count($products) === 0): ?>
                <div class="empty-state">
                    <h3>No products yet</h3>
                    <p>Start by adding your first product to the marketplace</p>
                    <a href="add_product.php" class="add-btn" style="display: inline-block; margin-top: 15px;">Add Product</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <div class="product-meta">
                                Category: <?php echo htmlspecialchars($product['category']); ?> | 
                                Quantity: <?php echo $product['quantity_available']; ?> units
                            </div>
                            <div class="price"><?php echo formatCurrency($product['price']); ?></div>
                            <div>
                                <span class="status-badge status-<?php echo strtolower($product['approval_status']); ?>">
                                    <?php echo htmlspecialchars($product['approval_status']); ?>
                                </span>
                                <span style="color: #999; font-size: 13px;">
                                    Added: <?php echo formatDate($product['created_at']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="actions">
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-edit">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
