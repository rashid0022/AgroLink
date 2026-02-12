<?php
require_once '../functions.php';
requireRole('Admin');

$user = getCurrentUser();
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reportId = intval($_POST['report_id']);
    $action = trim($_POST['action']);
    
    if ($action === 'reviewed') {
        $result = updateReportStatus($reportId, 'Reviewed');
        if ($result['success']) {
            $success = 'Report marked as reviewed!';
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'resolved') {
        $result = updateReportStatus($reportId, 'Resolved');
        if ($result['success']) {
            $success = 'Report marked as resolved!';
        } else {
            $error = $result['message'];
        }
    }
}

$reports = getAllReports($filterStatus ?: null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - AgroLink Admin</title>
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
            border-color: #667eea;
            background: #667eea;
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
        
        .reports-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .report-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .report-item:last-child {
            border-bottom: none;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-open {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-reviewed {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        
        .report-meta {
            font-size: 13px;
            color: #999;
            margin: 10px 0;
        }
        
        .report-reason {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #667eea;
        }
        
        .report-reason p {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
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
        
        .btn-reviewed {
            background: #2196F3;
            color: white;
        }
        
        .btn-resolved {
            background: #4caf50;
            color: white;
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
        <h1>🚨 Manage Reports</h1>
        <div class="header-right">
            <a href="admin.php">Back to Dashboard</a>
        </div>
        <div style="clear:both;"></div>
    </header>
    
    <div class="container">
        <div class="filters">
            <h3 style="margin-bottom: 12px;">Filter by Status</h3>
            <div class="filter-buttons">
                <a href="manage_reports.php" class="filter-btn <?php echo empty($filterStatus) ? 'active' : ''; ?>">All Reports</a>
                <a href="manage_reports.php?status=Open" class="filter-btn <?php echo ($filterStatus === 'Open') ? 'active' : ''; ?>">Open</a>
                <a href="manage_reports.php?status=Reviewed" class="filter-btn <?php echo ($filterStatus === 'Reviewed') ? 'active' : ''; ?>">Reviewed</a>
                <a href="manage_reports.php?status=Resolved" class="filter-btn <?php echo ($filterStatus === 'Resolved') ? 'active' : ''; ?>">Resolved</a>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="reports-list">
            <?php if (count($reports) === 0): ?>
                <div class="empty-state">
                    <h3>No reports with this status</h3>
                    <p>Try a different filter</p>
                </div>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <div class="report-item">
                        <div class="report-header">
                            <div class="report-title">Report #<?php echo $report['report_id']; ?> - Order #<?php echo $report['order_id']; ?></div>
                            <span class="status-badge status-<?php echo strtolower($report['report_status']); ?>">
                                <?php echo htmlspecialchars($report['report_status']); ?>
                            </span>
                        </div>
                        <div class="report-meta">
                            Reported by: <strong><?php echo htmlspecialchars($report['full_name']); ?></strong> (<?php echo htmlspecialchars($report['email']); ?>) |
                            Date: <?php echo formatDateTime($report['report_date']); ?>
                        </div>
                        <div class="report-reason">
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($report['reason']); ?></p>
                        </div>
                        <div class="actions">
                            <?php if ($report['report_status'] === 'Open'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="reviewed">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn btn-reviewed">Mark Reviewed</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($report['report_status'] !== 'Resolved'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="resolved">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn btn-resolved">Mark Resolved</button>
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
