<?php
/**
 * Database Configuration
 * Auto-detects LOCAL (XAMPP) vs ASTON UNIVERSITY SERVER
 */

// =====================================================
// ENVIRONMENT DETECTION
// =====================================================
$SERVER_HOST = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

$IS_LOCAL = (
    strpos($SERVER_HOST, 'localhost') !== false ||
    strpos($SERVER_HOST, '127.0.0.1') !== false ||
    strpos($SERVER_HOST, '::1') !== false
);

$IS_ASTON = (
    strpos($SERVER_HOST, 'cs2410-web01pvm.aston.ac.uk') !== false ||
    strpos($SERVER_HOST, '240364436') !== false ||
    strpos($SERVER_HOST, 'cs2team57') !== false
);

// =====================================================
// DATABASE CONFIGURATION
// =====================================================
if ($IS_LOCAL) {
    // LOCAL XAMPP
    $DB_HOST = "localhost";
    $DB_USER = "root";
    $DB_PASS = "";
    $DB_NAME = "serenique_db";
    
    define('SITE_URL', '/public_html');
    define('SITE_ROOT', 'C:/xampp/htdocs/public_html/');
    define('BASE_URL', 'http://localhost/public_html');
    
} else {
    // ASTON UNIVERSITY SERVER
    $DB_HOST = "localhost";
    $DB_USER = "cs2team57";
    $DB_PASS = "EruuMu42kZHszDadyUWhXXNkc";
    $DB_NAME = "cs2team57_db";
    
    define('SITE_URL', '');
    define('SITE_ROOT', '/home/cs2team57/public_html/');
    define('BASE_URL', 'http://cs2team57.cs2410-web01pvm.aston.ac.uk');
}

define('SITE_NAME', 'Serenique');
define('IS_LOCAL', $IS_LOCAL);

// =====================================================
// DATABASE CONNECTION
// =====================================================
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Get MySQLi connection
 */
function getDB() {
    global $conn;
    return $conn;
}

/**
 * Get PDO connection (used by admin pages)
 */
function getPDOConnection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

/**
 * Generate URL with proper prefix
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return SITE_URL . '/' . $path;
}

/**
 * Generate asset URL
 */
function asset($path = '') {
    $path = ltrim($path, '/');
    return SITE_URL . '/' . $path;
}

/**
 * Get absolute file path
 */
function filePath($path = '') {
    $path = ltrim($path, '/');
    return SITE_ROOT . $path;
}

/**
 * Sanitize input for database
 */
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for HTML output
 */
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}
?>
