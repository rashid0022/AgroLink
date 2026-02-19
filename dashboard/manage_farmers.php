<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$farmers = getAllFarmers($filterStatus ?: null);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $farmerId = intval($_POST['farmer_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'verify') {
        $result = verifyFarmer($farmerId, 'Verified');
        if ($result['success']) {
            $success = 'Farmer verified successfully!';
            $farmers = getAllFarmers($filterStatus ?: null);
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'suspend') {
        $result = verifyFarmer($farmerId, 'Suspended');
        if ($result['success']) {
            $success = 'Farmer suspended successfully!';
            $farmers = getAllFarmers($filterStatus ?: null);
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
    <title>Manage Farmers - AgroLink Admin</title>
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
            background: linear-gradient(135deg, #11998e, #38ef7d);
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
        
        .farmers-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-verify {
            background: #4caf50;
            color: white;
        }
        
        .btn-suspend {
            background: #f44336;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .farmer-photo {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .photo-placeholder {
            width: 50px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 24px;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
        <h1>👨‍🌾 Manage Farmers</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="filters">
            <h3 style="margin-bottom: 12px;">Filter by Status</h3>
            <div class="filter-buttons">
                <a href="manage_farmers.php" class="filter-btn <?php echo empty($filterStatus) ? 'active' : ''; ?>">All Farmers</a>
                <a href="manage_farmers.php?status=Pending" class="filter-btn <?php echo ($filterStatus === 'Pending') ? 'active' : ''; ?>">Pending Verification</a>
                <a href="manage_farmers.php?status=Verified" class="filter-btn <?php echo ($filterStatus === 'Verified') ? 'active' : ''; ?>">Verified</a>
                <a href="manage_farmers.php?status=Suspended" class="filter-btn <?php echo ($filterStatus === 'Suspended') ? 'active' : ''; ?>">Suspended</a>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="farmers-table">
            <?php if (count($farmers) === 0): ?>
                <div class="empty-state">
                    <h3>No farmers found</h3>
                    <p>Try a different filter status</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Farmer Name</th>
                            <th>Email</th>
                            <th>Farm Name</th>
                            <th>Location</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($farmers as $farmer): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($farmer['passport_photo']) && file_exists('../assets/images/farmers/' . $farmer['passport_photo'])): ?>
                                        <img src="../assets/images/farmers/<?php echo htmlspecialchars($farmer['passport_photo']); ?>" alt="Farmer Photo" class="farmer-photo">
                                    <?php else: ?>
                                        <div class="photo-placeholder">📷</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($farmer['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['email']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['farm_name']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['location']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['phone_number']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($farmer['verification_status']); ?>">
                                        <?php echo htmlspecialchars($farmer['verification_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($farmer['registered_date']); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($farmer['verification_status'] !== 'Verified'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="verify">
                                                <input type="hidden" name="farmer_id" value="<?php echo $farmer['farmer_id']; ?>">
                                                <button type="submit" class="btn btn-verify">Verify</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($farmer['verification_status'] !== 'Suspended'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Suspend this farmer?');">
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="farmer_id" value="<?php echo $farmer['farmer_id']; ?>">
                                                <button type="submit" class="btn btn-suspend">Suspend</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
