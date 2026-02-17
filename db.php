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
    // Unda PDO connection
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Show errors as exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES => false                  // Use native prepared statements
        ]
    );
} catch(PDOException $e) {
    // Andika error kwenye log na onyesha ujumbe wa kawaida kwa mtumiaji
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Tafadhali angalia credentials.");
}
