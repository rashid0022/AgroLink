<?php
/**
 * AgroLink - Auto Admin Creator
 * Simple script to automatically create first admin account
 * Safe: Only creates one admin, all subsequent access denied
 */

require_once 'db.php';

header('Content-Type: application/json');

// Check if admin already exists
try {
    $stmt = $GLOBALS['conn']->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'Admin'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        // Admin already exists - deny creation
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin account already exists',
            'action' => 'login',
            'redirect' => 'login.php'
        ]);
        exit;
    }
    
    // Create default admin account
    $admin_data = [
        'email' => 'admin@agrolink.local',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'full_name' => 'System Administrator',
        'role' => 'Admin'
    ];
    
    $stmt = $GLOBALS['conn']->prepare("
        INSERT INTO users (email, password, full_name, role, account_status, registration_date)
        VALUES (?, ?, ?, ?, 'Active', NOW())
    ");
    
    if ($stmt->execute([$admin_data['email'], $admin_data['password'], $admin_data['full_name'], $admin_data['role']])) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Admin account created successfully',
            'admin' => [
                'email' => $admin_data['email'],
                'password' => 'admin123 (default - change after login)',
                'full_name' => $admin_data['full_name']
            ],
            'action' => 'login',
            'redirect' => 'login.php'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create admin account',
            'error' => 'Database error'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => 'Database connection failed'
    ]);
}
?>
