<?php
/**
 * Dynamic Sahyog System - Database Migration
 * 
 * This script prepares the database for the dynamic sahyog system where:
 * 1. Members don't need pre-assigned polls
 * 2. System dynamically shows beneficiary with least donations
 * 3. Auto-rotates as donations come in
 * 
 * Usage: http://localhost/Charitable/api/migration_dynamic_sahyog.php?key=brct_setup_2025
 */

header('Content-Type: application/json');

require_once '../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'details' => []
];

try {
    // Check password
    $password = isset($_GET['key']) ? $_GET['key'] : '';
    if ($password !== 'brct_setup_2025') {
        throw new Exception('अनुमति नहीं है। सही access key प्रदान करें।');
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('कनेक्शन विफल: ' . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // 1. Update poll_applications table to ensure it has approved_date
    $check_column = $conn->query("SHOW COLUMNS FROM `poll_applications` LIKE 'approved_date'");
    if ($check_column->num_rows === 0) {
        $conn->query("ALTER TABLE `poll_applications` ADD COLUMN `approved_date` DATE NULL");
        $response['details'][] = '✓ poll_applications में approved_date कॉलम जोड़ा गया';
    } else {
        $response['details'][] = '✓ poll_applications में approved_date पहले से है';
    }

    // 2. Ensure poll_payments table has proper structure
    $check_table = $conn->query("SHOW TABLES LIKE 'poll_payments'");
    if ($check_table->num_rows === 0) {
        $create_payments = "CREATE TABLE IF NOT EXISTS `poll_payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `poll_id` INT DEFAULT 0,
            `member_id` VARCHAR(50) NOT NULL,
            `amount` INT NOT NULL DEFAULT 50,
            `payment_method` ENUM('UPI', 'Bank Transfer', 'Cheque') DEFAULT 'UPI',
            `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `transaction_id` VARCHAR(100),
            `screenshot_path` VARCHAR(255),
            `remarks` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_member (member_id),
            INDEX idx_date (payment_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($create_payments);
        $response['details'][] = '✓ poll_payments टेबल बनाया गया';
    } else {
        $response['details'][] = '✓ poll_payments टेबल पहले से है';
    }

    // 3. Add index on poll_applications for approval status and date
    $check_index = $conn->query("SHOW INDEX FROM `poll_applications` WHERE Key_name = 'idx_status_date'");
    if ($check_index->num_rows === 0) {
        $conn->query("ALTER TABLE `poll_applications` ADD INDEX idx_status_date (status, approved_date)");
        $response['details'][] = '✓ poll_applications में index बनाया गया';
    }

    // 4. Add index on poll_payments for quick lookups
    $check_index = $conn->query("SHOW INDEX FROM `poll_payments` WHERE Key_name = 'idx_member_date'");
    if ($check_index->num_rows === 0) {
        $conn->query("ALTER TABLE `poll_payments` ADD INDEX idx_member_date (member_id, payment_date)");
        $response['details'][] = '✓ poll_payments में index बनाया गया';
    }

    // 5. Backup sample approved applications (if any)
    $approved_count = $conn->query("SELECT COUNT(*) as count FROM `poll_applications` WHERE status = 'Approved'");
    $count_row = $approved_count->fetch_assoc();
    $response['details'][] = '📊 अनुमोदित आवेदन: ' . $count_row['count'];

    // 6. Verify UPI field in members table
    $check_upi = $conn->query("SHOW COLUMNS FROM `members` LIKE 'upi_id'");
    if ($check_upi->num_rows === 0) {
        $conn->query("ALTER TABLE `members` ADD COLUMN `upi_id` VARCHAR(50) AFTER `mobile_number`");
        $response['details'][] = '✓ members में upi_id कॉलम जोड़ा गया';
    } else {
        $response['details'][] = '✓ members में upi_id पहले से है';
    }

    // 7. Success
    $response['success'] = true;
    $response['message'] = 'डायनामिक सहयोग सिस्टम डेटाबेस तैयार है';

} catch (Exception $e) {
    $response['message'] = 'त्रुटि: ' . $e->getMessage();
}

echo json_encode($response);
?>
