<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgroLink</title>
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

        /* ================= HEADER (Same as Index Style) ================= */

        header {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            padding: 18px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        header h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .header-right {
            text-align: right;
            margin-left: 50rem;
        }

        .header-right p {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .logout-link {
            text-decoration: none;
            color: white;
            font-weight: 600;
            padding: 6px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
        }

        .logout-link:hover {
            background: white;
            color: #11998e;
        }

        /* ================= CONTAINER ================= */

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* SECTION TITLES */

        h2 {
            color: #11998e;
            font-weight: 600;
        }

        /* ================= STATS CARDS ================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-radius: 50%;
            top: -50px;
            right: -50px;
            opacity: 0.15;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
        }

        .stat-card h3 {
            font-size: 13px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #11998e;
        }

        /* ================= MENU GRID ================= */

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }

        .menu-card {
            background: white;
            padding: 40px 25px;
            border-radius: 22px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .menu-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            transform: scaleX(0);
            transform-origin: left;
            transition: 0.3s;
        }

        .menu-card:hover::after {
            transform: scaleX(1);
        }

        .menu-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.15);
        }

        .menu-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .menu-card h3 {
            font-size: 18px;
            color: #11998e;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .menu-card p {
            font-size: 13px;
            color: #777;
        }

        /* ================= RECENT ACTIVITY ================= */

        .container>div:last-child {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 22px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        .container>div:last-child h3 {
            color: #11998e;
            margin-bottom: 15px;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width:768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .header-right {
                text-align: left;
            }
        }
    </style>

</head>

<body>
    <header>
        <h1>🌾 AgroLink Admin</h1>
        <div class="header-right">
            <p>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></p>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
        <div style="clear:both;"></div>
    </header>

    <div class="container">
        <h2 style="margin-bottom: 20px;">Admin Dashboard</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Farmers</h3>
                <div class="stat-value">
                    <?php
                    $farmers = getAllFarmers();
                    echo count($farmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Pending Farmers</h3>
                <div class="stat-value">
                    <?php
                    $pendingFarmers = getAllFarmers('Pending');
                    echo count($pendingFarmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Verified Farmers</h3>
                <div class="stat-value">
                    <?php
                    $verifiedFarmers = getAllFarmers('Verified');
                    echo count($verifiedFarmers);
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Open Reports</h3>
                <div class="stat-value">
                    <?php
                    $openReports = getAllReports('Open');
                    echo count($openReports);
                    ?>
                </div>
            </div>
        </div>

        <h2 style="margin-top: 40px; margin-bottom: 20px;">Management Options</h2>

        <div class="menu-grid">
            <a href="manage_farmers.php" class="menu-card">
                <div class="menu-icon">👨‍🌾</div>
                <h3>Manage Farmers</h3>
                <p>Verify and manage farmer accounts</p>
            </a>

            <a href="manage_products.php" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>Manage Products</h3>
                <p>Approve or reject products</p>
            </a>

            <a href="manage_orders.php" class="menu-card">
                <div class="menu-icon">📋</div>
                <h3>Manage Orders</h3>
                <p>View and manage all orders</p>
            </a>

            <a href="manage_payments.php" class="menu-card">
                <div class="menu-icon">💳</div>
                <h3>Manage Payments</h3>
                <p>Release or refund escrow payments</p>
            </a>

            <a href="manage_reports.php" class="menu-card">
                <div class="menu-icon">🚨</div>
                <h3>Manage Reports</h3>
                <p>Handle fraud and dispute reports</p>
            </a>

            <a href="manage_users.php" class="menu-card">
                <div class="menu-icon">👥</div>
                <h3>Manage Users</h3>
                <p>Block or enable user accounts</p>
            </a>
        </div>

        <div style="margin-top: 40px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #667eea; margin-bottom: 15px;">📊 Recent Activity</h3>
            <p>Check the individual management pages for the latest activities and updates.</p>
        </div>
    </div>
</body>

</html>