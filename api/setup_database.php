<?php
/**
 * Database Setup Script - BRCT Bharat Trust
 * यह script database और tables को create करता है
 * 
 * Usage: http://localhost/Charitable/api/setup_database.php
 */

header('Content-Type: application/json; charset=UTF-8');

require_once '../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'details' => []
];

try {
    // Check if password provided
    $password = isset($_GET['key']) ? $_GET['key'] : '';
    
    // Simple security check
    if ($password !== 'brct_setup_2025') {
        throw new Exception('अनुमति नहीं है। सही access key प्रदान करें।');
    }

    // Create connection to MySQL server (without specifying database first)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception('MySQL Connection Failed: ' . $conn->connect_error);
    }

    // Create database if not exists
    $db_name = DB_NAME;
    $create_db = $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    if (!$create_db) {
        throw new Exception('Database Creation Failed: ' . $conn->error);
    }
    
    $response['details'][] = '✅ Database ' . $db_name . ' तैयार है';

    // Select the database
    $conn->select_db($db_name);
    $conn->set_charset("utf8mb4");

    // SQL to create members table
    $members_table_sql = "
    CREATE TABLE IF NOT EXISTS `members` (
        `member_id` VARCHAR(20) NOT NULL PRIMARY KEY COMMENT 'आधार के अंतिम 8 अंक',
        `login_id` VARCHAR(20) NOT NULL UNIQUE COMMENT 'लॉगिन ID',
        `password` VARCHAR(255) NOT NULL COMMENT 'पासवर्ड (bcrypt)',
        `full_name` VARCHAR(100) NOT NULL COMMENT 'पूरा नाम',
        `aadhar_number` VARCHAR(12) NOT NULL UNIQUE COMMENT 'आधार संख्या',
        `father_husband_name` VARCHAR(100) COMMENT 'पिता/पति का नाम',
        `date_of_birth` DATE COMMENT 'जन्मतिथि',
        `mobile_number` VARCHAR(10) COMMENT 'मोबाइल नंबर',
        `gender` VARCHAR(20) COMMENT 'लिंग',
        `occupation` VARCHAR(50) COMMENT 'व्यवसाय',
        `office_name` VARCHAR(100) COMMENT 'कार्यालय का नाम',
        `office_address` TEXT COMMENT 'कार्यालय पता',
        `state` VARCHAR(50) COMMENT 'राज्य',
        `district` VARCHAR(50) COMMENT 'जिला',
        `block` VARCHAR(50) COMMENT 'ब्लॉक',
        `permanent_address` TEXT COMMENT 'स्थायी पता',
        `email` VARCHAR(100) COMMENT 'ईमेल पता',
        `utr_number` VARCHAR(30) COMMENT 'भुगतान UTR संख्या',
        `payment_verified` TINYINT(1) DEFAULT 0 COMMENT 'भुगतान सत्यापित (0=No, 1=Yes)',
        `payment_verified_at` TIMESTAMP NULL COMMENT 'भुगतान सत्यापन का समय',
        `status` TINYINT(1) DEFAULT 0 COMMENT 'स्थिति (0=Inactive, 1=Active)',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'बनाया गया',
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'अपडेट किया गया',
        
        KEY `idx_aadhar` (`aadhar_number`),
        KEY `idx_mobile` (`mobile_number`),
        KEY `idx_email` (`email`),
        KEY `idx_utr` (`utr_number`),
        KEY `idx_status` (`status`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='सदस्य रिकॉर्ड'
    ";

    if (!$conn->query($members_table_sql)) {
        throw new Exception('Members Table Creation Failed: ' . $conn->error);
    }
    
    $response['details'][] = '✅ members table तैयार है';

    // SQL to create payment_receipts table
    $receipts_table_sql = "
    CREATE TABLE IF NOT EXISTS `payment_receipts` (
        `receipt_id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'रसीद ID',
        `member_id` VARCHAR(20) NOT NULL COMMENT 'सदस्य ID',
        `aadhar_number` VARCHAR(12) NOT NULL COMMENT 'आधार संख्या',
        `receipt_file_name` VARCHAR(255) NOT NULL COMMENT 'फाइल का नाम',
        `receipt_file_path` VARCHAR(500) NOT NULL COMMENT 'फाइल का पथ',
        `file_size` INT COMMENT 'फाइल का आकार (bytes)',
        `file_type` VARCHAR(100) COMMENT 'फाइल का प्रकार (MIME)',
        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'अपलोड का समय',
        
        KEY `idx_member` (`member_id`),
        KEY `idx_aadhar` (`aadhar_number`),
        FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='भुगतान रसीद';
    ";

    if (!$conn->query($receipts_table_sql)) {
        throw new Exception('Payment Receipts Table Creation Failed: ' . $conn->error);
    }
    
    $response['details'][] = '✅ payment_receipts table तैयार है';

    // Verify tables exist
    $tables_check = $conn->query("SHOW TABLES LIKE 'members'");
    if ($tables_check->num_rows === 0) {
        throw new Exception('Members table verification failed');
    }

    $response['success'] = true;
    $response['message'] = '✅ Database Setup सफल! सभी tables तैयार हैं।';

} catch (Exception $e) {
    $response['message'] = '❌ Error: ' . $e->getMessage();
    error_log('Database Setup Error: ' . $e->getMessage());
}

// Ensure connection is closed
if (isset($conn)) {
    $conn->close();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
