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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f3, #dfe9f3);
            color: #333;
        }

        /* HEADER */
        header {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            padding: 18px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 26px;
            font-weight: 700;
            color: white;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.7;
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 40px 20px;
        }

        /* HERO */
        .hero {
            background: white;
            padding: 50px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .hero h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #11998e;
        }

        .hero p {
            color: #777;
            margin-bottom: 25px;
        }

        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            max-width: 500px;
            margin: auto;
        }

        .search-form input {
            flex: 1;
            padding: 14px;
            border-radius: 30px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .search-form input:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.2);
        }

        .search-form button {
            padding: 14px 25px;
            border-radius: 30px;
            border: none;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .search-form button:hover {
            transform: scale(1.05);
        }

        /* FILTERS */
        .filters {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 35px;
        }

        .filters h3 {
            margin-bottom: 15px;
            color: #11998e;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .filter-btn {
            padding: 8px 18px;
            border-radius: 30px;
            border: 1px solid #ddd;
            background: #f8f8f8;
            text-decoration: none;
            color: #444;
            font-size: 14px;
            transition: 0.3s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            border: none;
        }

        /* PRODUCTS GRID */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        /* PRODUCT CARD */
        .product-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            transition: 0.3s;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            height: 190px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 55px;
            color: white;
        }

        .product-content {
            padding: 20px;
        }

        .product-category {
            font-size: 11px;
            color: #999;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .product-farmer {
            font-size: 13px;
            color: #11998e;
            margin-bottom: 10px;
        }

        .product-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #11998e;
            margin-bottom: 15px;
        }

        /* BUTTON */
        .add-to-cart-btn {
            width: 100%;
            padding: 12px;
            border-radius: 30px;
            border: none;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .add-to-cart-btn:hover {
            transform: scale(1.05);
        }

        /* EMPTY STATE */
        .empty-state {
            background: white;
            padding: 60px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            color: #777;
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
                        <div class="product-image">
                            <?php if (!empty($product['product_image']) && file_exists('assets/images/products/' . $product['product_image'])): ?>
                                <img src="assets/images/products/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
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