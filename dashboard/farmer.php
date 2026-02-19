<?php
require_once '../functions.php';
requireRole('Farmer');

$user = getCurrentUser();
$farmer = getFarmerByUserId($user['user_id']);
$products = getFarmerProducts($farmer['farmer_id']);
$approvedCount = count(getFarmerProducts($farmer['farmer_id'], 'Approved'));
$pendingCount = count(getFarmerProducts($farmer['farmer_id'], 'Pending'));
$rejectedCount = count(getFarmerProducts($farmer['farmer_id'], 'Rejected'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AgroLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f3, #dfe9f3);
            /* like your homepage */
            color: #333;
        }

        /* HEADER */
        header {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            display: inline-block;
            font-size: 26px;
            font-weight: 700;
            color: white;
        }

        .header-right {
            float: right;
            text-align: right;
        }

        .header-right p {
            font-size: 14px;
            margin-bottom: 5px;
            color: white;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* STATUS BOX */
        .status-box {
            background: #ffffff;
            border: 1px solid #38ef7d;
            /* green border */
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .status-box p {
            font-weight: 600;
            color: #11998e;
            /* teal text */
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #11998e;
            text-align: center;
        }

        .stat-card h3 {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #11998e;
        }

        /* MENU GRID */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #11998e;
        }

        .menu-card h3 {
            color: #11998e;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .menu-card p {
            color: #777;
            font-size: 13px;
        }

        /* INFO CARD */
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }

        .info-card h3 {
            color: #11998e;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-item label {
            color: #777;
            font-size: 13px;
            text-transform: uppercase;
        }

        .info-item p {
            font-size: 16px;
            font-weight: 600;
            margin-top: 5px;
            color: #11998e;
        }
    </style>

</head>

<body>
    <header>
        <h1>🚜 Farmer Dashboard</h1>
        <div class="header-right">
            <p>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></p>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
        <div style="clear:both;"></div>
    </header>

    <div class="container">
        <?php if ($farmer['verification_status'] === 'Pending'): ?>
            <div class="status-box">
                <p>⏱️ Your account is pending verification. Admin will review your documents shortly.</p>
            </div>
        <?php elseif ($farmer['verification_status'] === 'Suspended'): ?>
            <div class="status-box" style="background: #ffebee; border-color: #f44336;">
                <p style="color: #c62828;">⚠️ Your account has been suspended. Please contact admin support.</p>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="stat-value"><?php echo count($products); ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved</h3>
                <div class="stat-value"><?php echo $approvedCount; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <div class="stat-value"><?php echo $pendingCount; ?></div>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <div class="stat-value"><?php echo $rejectedCount; ?></div>
            </div>
        </div>

        <h2 style="margin: 40px 0 20px 0;">Quick Actions</h2>

        <div class="menu-grid">
            <a href="../products/add_product.php" class="menu-card">
                <div class="menu-icon">➕</div>
                <h3>Add Product</h3>
                <p>List a new product for sale</p>
            </a>

            <a href="../products/list_products.php" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>My Products</h3>
                <p>View and manage your products</p>
            </a>

            <a href="farmer_orders.php" class="menu-card">
                <div class="menu-icon">📋</div>
                <h3>My Orders</h3>
                <p>View customer orders</p>
            </a>

            <a href="farmer_deliveries.php" class="menu-card">
                <div class="menu-icon">🚚</div>
                <h3>Manage Deliveries</h3>
                <p>Track and dispatch orders</p>
            </a>

            <a href="farmer_profile.php" class="menu-card">
                <div class="menu-icon">👤</div>
                <h3>My Profile</h3>
                <p>View and edit farm details</p>
            </a>
        </div>

        <div class="info-card">
            <h3>🌾 Farm Information</h3>
            <div class="info-row">
                <div class="info-item">
                    <label>Farm Name</label>
                    <p><?php echo htmlspecialchars($farmer['farm_name']); ?></p>
                </div>
                <div class="info-item">
                    <label>Verification Status</label>
                    <p><?php echo htmlspecialchars($farmer['verification_status']); ?></p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <label>Location</label>
                    <p><?php echo htmlspecialchars($farmer['location']); ?></p>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <p><?php echo htmlspecialchars($farmer['phone_number']); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>