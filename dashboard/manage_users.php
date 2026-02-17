<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = trim($_POST['action']);

    // Block/enable user
    $newStatus = ($action === 'block') ? 'Blocked' : 'Active';

    try {
        $stmt = $GLOBALS['conn']->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        $stmt->execute([$newStatus, $userId]);

        $success = 'User status updated successfully!';
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Failed to update user status';
    }
}

// Get all users
$stmt = $GLOBALS['conn']->prepare("
    SELECT * FROM users 
    WHERE user_id != ?
    ORDER BY user_id DESC
");

$stmt->execute([$user['user_id']]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - AgroLink Admin</title>
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

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
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

        .users-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-admin {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .role-farmer {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .role-customer {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-blocked {
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

        .btn-block {
            background: #f44336;
            color: white;
        }

        .btn-enable {
            background: #4caf50;
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
    </style>
</head>

<body>
    <header>
        <h1>👥 Manage Users</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>

    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="users-table">
            <?php if (count($users) === 0): ?>
                <div class="empty-state">
                    <h3>No users found</h3>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo strtolower($u['role']); ?>">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($u['account_status']); ?>">
                                        <?php echo htmlspecialchars($u['account_status']); ?>
                                    </span>
                                </td>
                                <td><?= isset($u['registered_date']) ? formatDateTime($u['registered_date']) : 'N/A' ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($u['account_status'] === 'Active'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Block this user?');">
                                                <input type="hidden" name="action" value="block">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <button type="submit" class="btn btn-block">Block</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="enable">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <button type="submit" class="btn btn-enable">Enable</button>
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