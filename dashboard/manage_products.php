<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : 'Pending';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $productId = intval($_POST['product_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'approve') {
        $result = approveProduct($productId, 'Approved');
        if ($result['success']) {
            $success = 'Product approved successfully!';
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'reject') {
        $result = approveProduct($productId, 'Rejected');
        if ($result['success']) {
            $success = 'Product rejected successfully!';
        } else {
            $error = $result['message'];
        }
    }
}

// Get all products with the filter
$stmt = $GLOBALS['conn']->prepare("
    SELECT p.*, f.farm_name, u.full_name as farmer_name 
    FROM products p 
    JOIN farmers f ON p.farmer_id = f.farmer_id 
    JOIN users u ON f.user_id = u.user_id
    WHERE p.approval_status = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$filterStatus]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - AgroLink Admin</title>
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
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            cursor: pointer;
        }
        
        .filter-btn.active {
            border-color: #11998e;
            background: #11998e;
            color: white;
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
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            color: #11998e;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin: 5px 5px 5px 0;
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
        
        .btn-approve {
            background: #4caf50;
            color: white;
        }
        
        .btn-approve:hover {
            background: #45a049;
        }
        
        .btn-reject {
            background: #f44336;
            color: white;
        }
        
        .btn-reject:hover {
            background: #da190b;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <header>
        <h1>📦 Manage Products</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="filters">
            <h3 style="margin-bottom: 12px;">Filter by Status</h3>
            <div class="filter-buttons">
                <a href="manage_products.php?status=Pending" class="filter-btn <?php echo ($filterStatus === 'Pending') ? 'active' : ''; ?>">Pending Review</a>
                <a href="manage_products.php?status=Approved" class="filter-btn <?php echo ($filterStatus === 'Approved') ? 'active' : ''; ?>">Approved</a>
                <a href="manage_products.php?status=Rejected" class="filter-btn <?php echo ($filterStatus === 'Rejected') ? 'active' : ''; ?>">Rejected</a>
            </div>
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
                    <h3>No products with this status</h3>
                    <p>Try a different filter</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <div class="product-meta">
                                By: <strong><?php echo htmlspecialchars($product['farmer_name']); ?></strong> - 
                                Farm: <?php echo htmlspecialchars($product['farm_name']); ?>
                            </div>
                            <div class="product-meta">
                                Category: <?php echo htmlspecialchars($product['category']); ?> | 
                                Quantity: <?php echo $product['quantity_available']; ?> units
                            </div>
                            <div class="price"><?php echo formatCurrency($product['price']); ?></div>
                            <span class="status-badge status-<?php echo strtolower($product['approval_status']); ?>">
                                <?php echo htmlspecialchars($product['approval_status']); ?>
                            </span>
                            <span style="color: #999; font-size: 12px;">
                                Added: <?php echo formatDateTime($product['created_at']); ?>
                            </span>
                        </div>
                        <div class="actions">
                            <?php if ($product['approval_status'] === 'Pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this product?');">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="btn btn-reject">Reject</button>
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
