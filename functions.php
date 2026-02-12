<?php
// ========================================
// AGROLINK APPLICATION FUNCTIONS
// ========================================

require_once 'db.php';

// ========================================
// SESSION MANAGEMENT
// ========================================

/**
 * Start Session if not already started
 */
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user information
 */
function getCurrentUser() {
    global $conn;
    startSession();
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Redirect to login if not authenticated
 */
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Check user role
 */
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

/**
 * Redirect if user doesn't have required role
 */
function requireRole($role) {
    redirectIfNotLoggedIn();
    if (!hasRole($role)) {
        header("Location: index.php");
        exit();
    }
}

// ========================================
// AUTHENTICATION FUNCTIONS
// ========================================

/**
 * Register new user (Generic)
 */
function registerUser($fullName, $email, $password, $role) {
    global $conn;
    
    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$fullName, $email, $hashedPassword, $role]);
        
        return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 */
function loginUser($email, $password) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if ($user['account_status'] === 'Blocked') {
            return ['success' => false, 'message' => 'Your account has been blocked'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        startSession();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Login failed'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    startSession();
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}

// ========================================
// FARMER FUNCTIONS
// ========================================

/**
 * Register farmer
 */
function registerFarmer($userId, $farmName, $phoneNumber, $location, $nationalId, $houseNumber, $businessLicense) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO farmers (user_id, farm_name, phone_number, location, national_id_number, house_number, business_license_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $farmName, $phoneNumber, $location, $nationalId, $houseNumber, $businessLicense]);
        
        return ['success' => true, 'message' => 'Farmer registration submitted for verification', 'farmer_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Farmer registration failed'];
    }
}

/**
 * Get farmer details
 */
function getFarmerByUserId($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM farmers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Get all farmers (for admin)
 */
function getAllFarmers($status = null) {
    global $conn;
    
    try {
        if ($status) {
            $stmt = $conn->prepare("
                SELECT f.*, u.full_name, u.email 
                FROM farmers f 
                JOIN users u ON f.user_id = u.user_id 
                WHERE f.verification_status = ? 
                ORDER BY f.registered_date DESC
            ");
            $stmt->execute([$status]);
        } else {
            $stmt = $conn->prepare("
                SELECT f.*, u.full_name, u.email 
                FROM farmers f 
                JOIN users u ON f.user_id = u.user_id 
                ORDER BY f.registered_date DESC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Verify/Approve farmer (Admin)
 */
function verifyFarmer($farmerId, $status) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE farmers SET verification_status = ? WHERE farmer_id = ?");
        $stmt->execute([$status, $farmerId]);
        return ['success' => true, 'message' => 'Farmer verification status updated'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Update failed'];
    }
}

// ========================================
// CUSTOMER FUNCTIONS
// ========================================

/**
 * Register customer
 */
function registerCustomer($userId, $phoneNumber, $address, $city, $postalCode) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO customers (user_id, phone_number, address, city, postal_code) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $phoneNumber, $address, $city, $postalCode]);
        
        return ['success' => true, 'message' => 'Customer registration successful', 'customer_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Get customer details
 */
function getCustomerByUserId($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Update customer profile
 */
function updateCustomerProfile($userId, $phoneNumber, $address, $city, $postalCode) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE customers 
            SET phone_number = ?, address = ?, city = ?, postal_code = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$phoneNumber, $address, $city, $postalCode, $userId]);
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Profile update failed'];
    }
}

// ========================================
// PRODUCT FUNCTIONS
// ========================================

/**
 * Add new product
 */
function addProduct($farmerId, $productName, $category, $price, $quantityAvailable) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO products (farmer_id, product_name, category, price, quantity_available) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$farmerId, $productName, $category, $price, $quantityAvailable]);
        
        return ['success' => true, 'message' => 'Product added successfully', 'product_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to add product'];
    }
}

/**
 * Get product by ID
 */
function getProduct($productId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT p.*, f.farm_name, u.full_name as farmer_name 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.farmer_id 
            JOIN users u ON f.user_id = u.user_id 
            WHERE p.product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Get all approved products
 */
function getAllApprovedProducts($category = null) {
    global $conn;
    
    try {
        if ($category) {
            $stmt = $conn->prepare("
                SELECT p.*, f.farm_name, u.full_name as farmer_name 
                FROM products p 
                JOIN farmers f ON p.farmer_id = f.farmer_id 
                JOIN users u ON f.user_id = u.user_id 
                WHERE p.approval_status = 'Approved' AND p.category = ? 
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$category]);
        } else {
            $stmt = $conn->prepare("
                SELECT p.*, f.farm_name, u.full_name as farmer_name 
                FROM products p 
                JOIN farmers f ON p.farmer_id = f.farmer_id 
                JOIN users u ON f.user_id = u.user_id 
                WHERE p.approval_status = 'Approved' 
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Get farmer's products
 */
function getFarmerProducts($farmerId, $status = null) {
    global $conn;
    
    try {
        if ($status) {
            $stmt = $conn->prepare("
                SELECT * FROM products 
                WHERE farmer_id = ? AND approval_status = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$farmerId, $status]);
        } else {
            $stmt = $conn->prepare("
                SELECT * FROM products 
                WHERE farmer_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$farmerId]);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Update product
 */
function updateProduct($productId, $productName, $category, $price, $quantityAvailable) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE products 
            SET product_name = ?, category = ?, price = ?, quantity_available = ? 
            WHERE product_id = ?
        ");
        $stmt->execute([$productName, $category, $price, $quantityAvailable, $productId]);
        
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to update product'];
    }
}

/**
 * Delete product
 */
function deleteProduct($productId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete product'];
    }
}

/**
 * Approve/Reject product (Admin)
 */
function approveProduct($productId, $status) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE products SET approval_status = ? WHERE product_id = ?");
        $stmt->execute([$status, $productId]);
        
        return ['success' => true, 'message' => 'Product approval status updated'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Update failed'];
    }
}

/**
 * Get featured products
 */
function getFeaturedProducts() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT p.*, f.farm_name 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.farmer_id 
            WHERE p.is_featured = 'Yes' AND p.approval_status = 'Approved' 
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

// ========================================
// ORDER FUNCTIONS
// ========================================

/**
 * Create order
 */
function createOrder($userId, $totalAmount) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount) 
            VALUES (?, ?)
        ");
        $stmt->execute([$userId, $totalAmount]);
        
        return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to create order'];
    }
}

/**
 * Add order item
 */
function addOrderItem($orderId, $productId, $quantity, $price) {
    global $conn;
    
    try {
        // Update product quantity
        $stmt = $conn->prepare("
            UPDATE products 
            SET quantity_available = quantity_available - ? 
            WHERE product_id = ?
        ");
        $stmt->execute([$quantity, $productId]);
        
        // Insert order item
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $productId, $quantity, $price]);
        
        return ['success' => true, 'message' => 'Item added to order'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to add item'];
    }
}

/**
 * Get order details
 */
function getOrder($orderId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Get order items
 */
function getOrderItems($orderId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT oi.*, p.product_name, f.farm_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            JOIN farmers f ON p.farmer_id = f.farmer_id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Get customer orders
 */
function getCustomerOrders($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM orders 
            WHERE user_id = ? 
            ORDER BY order_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Update order status
 */
function updateOrderStatus($orderId, $orderStatus, $deliveryStatus = null) {
    global $conn;
    
    try {
        if ($deliveryStatus) {
            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = ?, delivery_status = ? 
                WHERE order_id = ?
            ");
            $stmt->execute([$orderStatus, $deliveryStatus, $orderId]);
        } else {
            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = ? 
                WHERE order_id = ?
            ");
            $stmt->execute([$orderStatus, $orderId]);
        }
        
        return ['success' => true, 'message' => 'Order status updated'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to update order'];
    }
}

// ========================================
// PAYMENT FUNCTIONS
// ========================================

/**
 * Create payment record (Escrow)
 */
function createPayment($orderId, $paymentMethod) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO payments (order_id, payment_method) 
            VALUES (?, ?)
        ");
        $stmt->execute([$orderId, $paymentMethod]);
        
        // Update order status to Paid
        updateOrderStatus($orderId, 'Paid');
        
        return ['success' => true, 'message' => 'Payment recorded', 'payment_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Payment creation failed'];
    }
}

/**
 * Get payment details
 */
function getPayment($paymentId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Release payment to farmer
 */
function releasePayment($paymentId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE payments SET payment_status = 'Released' WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        
        return ['success' => true, 'message' => 'Payment released successfully'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to release payment'];
    }
}

/**
 * Refund payment
 */
function refundPayment($paymentId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE payments SET payment_status = 'Refunded' WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        
        return ['success' => true, 'message' => 'Payment refunded successfully'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to refund payment'];
    }
}

// ========================================
// RECEIPT FUNCTIONS
// ========================================

/**
 * Create receipt
 */
function createReceipt($paymentId, $totalPaid) {
    global $conn;
    
    try {
        // Generate unique receipt number
        $receiptNumber = 'RCP-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        $stmt = $conn->prepare("
            INSERT INTO receipts (payment_id, receipt_number, total_paid) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$paymentId, $receiptNumber, $totalPaid]);
        
        return ['success' => true, 'message' => 'Receipt generated', 'receipt_id' => $conn->lastInsertId(), 'receipt_number' => $receiptNumber];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Receipt generation failed'];
    }
}

/**
 * Get receipt
 */
function getReceipt($receiptId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT r.*, p.order_id, o.total_amount, o.order_date, u.full_name, u.email 
            FROM receipts r 
            JOIN payments p ON r.payment_id = p.payment_id 
            JOIN orders o ON p.order_id = o.order_id 
            JOIN users u ON o.user_id = u.user_id 
            WHERE r.receipt_id = ?
        ");
        $stmt->execute([$receiptId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

// ========================================
// REPORT FUNCTIONS
// ========================================

/**
 * Create report (Fraud/Issue)
 */
function createReport($orderId, $userId, $reason) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO reports (order_id, user_id, reason) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$orderId, $userId, $reason]);
        
        return ['success' => true, 'message' => 'Report submitted successfully', 'report_id' => $conn->lastInsertId()];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to submit report'];
    }
}

/**
 * Get all reports (Admin)
 */
function getAllReports($status = null) {
    global $conn;
    
    try {
        if ($status) {
            $stmt = $conn->prepare("
                SELECT r.*, o.order_id, u.full_name, u.email 
                FROM reports r 
                JOIN orders o ON r.order_id = o.order_id 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.report_status = ? 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute([$status]);
        } else {
            $stmt = $conn->prepare("
                SELECT r.*, o.order_id, u.full_name, u.email 
                FROM reports r 
                JOIN orders o ON r.order_id = o.order_id 
                JOIN users u ON r.user_id = u.user_id 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Update report status
 */
function updateReportStatus($reportId, $status) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE reports SET report_status = ? WHERE report_id = ?");
        $stmt->execute([$status, $reportId]);
        
        return ['success' => true, 'message' => 'Report status updated'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Failed to update report'];
    }
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Format currency value
 */
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

/**
 * Get unique categories
 */
function getProductCategories() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE approval_status = 'Approved' ORDER BY category");
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array_column($results, 'category');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * Search products
 */
function searchProducts($keyword, $category = null) {
    global $conn;
    
    try {
        if ($category) {
            $stmt = $conn->prepare("
                SELECT p.*, f.farm_name 
                FROM products p 
                JOIN farmers f ON p.farmer_id = f.farmer_id 
                WHERE (p.product_name LIKE ? OR f.farm_name LIKE ?) 
                AND p.category = ? 
                AND p.approval_status = 'Approved' 
                ORDER BY p.created_at DESC
            ");
            $keyword = '%' . $keyword . '%';
            $stmt->execute([$keyword, $keyword, $category]);
        } else {
            $stmt = $conn->prepare("
                SELECT p.*, f.farm_name 
                FROM products p 
                JOIN farmers f ON p.farmer_id = f.farmer_id 
                WHERE (p.product_name LIKE ? OR f.farm_name LIKE ?) 
                AND p.approval_status = 'Approved' 
                ORDER BY p.created_at DESC
            ");
            $keyword = '%' . $keyword . '%';
            $stmt->execute([$keyword, $keyword]);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

?>
