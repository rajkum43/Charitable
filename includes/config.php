<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bharat');

// Site configuration
define('SITE_NAME', 'BRCT Bharat Trust');
define('SITE_EMAIL', '');
define('SITE_PHONE', '+91 98765 43210');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Time zone
date_default_timezone_set('Asia/Kolkata');

// Database PDO Connection
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Auto-create core_team_members table if not exists
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `core_team_members` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `full_name` VARCHAR(100) NOT NULL,
      `mobile_number` VARCHAR(20) NOT NULL,
      `post_name` VARCHAR(100) NOT NULL,
      `photo` VARCHAR(255) NOT NULL,
      `photo_size` INT,
      `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `status` ENUM('active', 'inactive') DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    
    // Store PDO in globals for access in other files
    $GLOBALS['pdo'] = $pdo;
    
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

/**
 * Get PDO Connection
 * Helper function to access the PDO connection from config
 */
function getPDOConnection() {
    global $pdo;
    if (!isset($GLOBALS['pdo'])) {
        // Create new connection if not exists
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $GLOBALS['pdo'] = $pdo;
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            return null;
        }
    }
    return $GLOBALS['pdo'];
}

// ===== URL ENCRYPTION/DECRYPTION FUNCTIONS =====

// Encryption key (keep this secret and same across all instances)
// Change this to a random string for production
define('ENCRYPTION_KEY', 'BRCT_BHARAT_SECURE_KEY_2026');

/**
 * Encrypt URL parameters
 */
function encryptUrlParams($data) {
    $json = json_encode($data);
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($json, 'AES-256-CBC', hash('sha256', ENCRYPTION_KEY), 0, $iv);
    
    if ($encrypted === false) {
        return null;
    }
    
    // Combine IV and encrypted data, then base64 encode
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt URL parameters
 */
function decryptUrlParams($encryptedData) {
    try {
        $data = base64_decode($encryptedData, true);
        if ($data === false) {
            return null;
        }
        
        // Extract IV from the beginning
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        // Decrypt
        $json = openssl_decrypt($encrypted, 'AES-256-CBC', hash('sha256', ENCRYPTION_KEY), 0, $iv);
        
        if ($json === false) {
            return null;
        }
        
        return json_decode($json, true);
    } catch (Exception $e) {
        return null;
    }
}