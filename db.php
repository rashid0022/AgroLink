<?php
// ========================================
// DATABASE CONFIGURATION
// ========================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'agrolink_db');
define('DB_USER', 'root');
define('DB_PASS', 's@id2004$$');  // Password yako ya MySQL
define('DB_PORT', 3306);

define('APP_NAME', 'AgroLink');
define('APP_URL', 'http://localhost/agrolink/');

try {
    $GLOBALS['conn'] = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    // Make available as global variable too for functions that use 'global $conn'
    global $conn;
    $conn = $GLOBALS['conn'];
} catch(PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Tafadhali angalia credentials.");
}
?>
