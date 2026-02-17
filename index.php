<?php
require_once 'functions.php';
startSession();

$products = [];
$categoryList = getProductCategories();
$selectedCategory = $_GET['category'] ?? '';

if (!empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $products = searchProducts($searchTerm, $selectedCategory);
} elseif (!empty($selectedCategory)) {
    $products = getAllApprovedProducts($selectedCategory);
} else {
    $products = getAllApprovedProducts();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroLink - Farm to Table Marketplace</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 24px;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .hero {
            text-align: center;
            margin-bottom: 40px;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .hero h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .hero p {
            color: #999;
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            max-width: 400px;
            margin: 20px auto 0;
        }
        
        .search-form input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-form button {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-form button:hover {
            background: #764ba2;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filters h3 {
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            border-color: #667eea;
            background: #f5f7ff;
            color: #667eea;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-image {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
        }
        
        .product-content {
            padding: 15px;
        }
        
        .product-category {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-farmer {
            font-size: 13px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
            color: #999;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .add-to-cart-btn {
            width: 100%;
            padding: 10px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .add-to-cart-btn:hover {
            background: #45a049;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>🌾 AgroLink</h1>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <?php if ($_SESSION['role'] === 'Customer'): ?>
                        <a href="dashboard/customer.php">Dashboard</a>
                    <?php elseif ($_SESSION['role'] === 'Farmer'): ?>
                        <a href="dashboard/farmer.php">Dashboard</a>
                    <?php elseif ($_SESSION['role'] === 'Admin'): ?>
                        <a href="dashboard/admin.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register_customer.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="hero">
            <h2>Farm to Table Marketplace</h2>
            <p>Fresh produce directly from local farmers to your doorstep</p>
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="filters">
            <h3>Browse by Category</h3>
            <div class="filter-buttons">
                <a href="index.php" class="filter-btn <?php echo empty($selectedCategory) ? 'active' : ''; ?>">All Products</a>
                <?php foreach ($categoryList as $category): ?>
                    <a href="index.php?category=<?php echo urlencode($category); ?>" class="filter-btn <?php echo ($selectedCategory === $category) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (count($products) === 0): ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Please try a different search or category</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">📦</div>
                        <div class="product-content">
                            <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                            <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                            <div class="product-farmer">🚜 <?php echo htmlspecialchars($product['farm_name']); ?></div>
                            <div class="product-meta">
                                <span>In Stock: <?php echo $product['quantity_available']; ?></span>
                            </div>
                            <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                            <?php if (isLoggedIn() && $_SESSION['role'] === 'Customer'): ?>
                                <form method="GET" action="orders/checkout.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="add-to-cart-btn">View Details</button>
                                </form>
                            <?php else: ?>
                                <button class="add-to-cart-btn" onclick="alert('Please login as a customer to purchase')">View Details</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
