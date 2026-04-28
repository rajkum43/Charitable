<?php
// Database Setup for Poll System - Poll Applications and Payments
header('Content-Type: application/json');

require_once '../includes/config.php';

// Security key for setup
$setup_key = 'brct_setup_2025';
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $setup_key) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'brct_bharat');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$conn->set_charset("utf8mb4");
$tables_created = [];
$errors = [];

try {
    // 1. Create poll_applications table
    $sql1 = "CREATE TABLE IF NOT EXISTS `poll_applications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_id` VARCHAR(50) NOT NULL,
        `type` ENUM('vivah', 'death') NOT NULL,
        `status` ENUM('Pending', 'Under Review', 'Approved', 'Rejected') DEFAULT 'Pending',
        `approved_date` DATETIME NULL,
        `rejection_reason` TEXT,
        `bride_name` VARCHAR(100),
        `groom_name` VARCHAR(100),
        `death_person_name` VARCHAR(100),
        `death_date` DATE,
        `family_income` BIGINT,
        `family_members` INT,
        `bank_name` VARCHAR(100),
        `branch_name` VARCHAR(100),
        `account_number` VARCHAR(50),
        `ifsc_code` VARCHAR(20),
        `remarks` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE RESTRICT,
        INDEX idx_member_id (member_id),
        INDEX idx_status (status),
        INDEX idx_type (type),
        INDEX idx_approved_date (approved_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql1)) {
        $tables_created[] = 'poll_applications';
    } else {
        $errors[] = 'poll_applications: ' . $conn->error;
    }

    // 2. Create poll_payments table
    $sql2 = "CREATE TABLE IF NOT EXISTS `poll_payments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `poll_id` INT DEFAULT 0,
        `member_id` VARCHAR(50) NOT NULL,
        `beneficiary_id` VARCHAR(50) NOT NULL,
        `amount` BIGINT NOT NULL,
        `payment_method` ENUM('UPI', 'Bank Transfer', 'Cash', 'Check') DEFAULT 'UPI',
        `payment_proof` VARCHAR(255),
        `payment_status` ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
        `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `verified_by` VARCHAR(50),
        `verified_at` DATETIME,
        `remarks` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE RESTRICT,
        FOREIGN KEY (beneficiary_id) REFERENCES members(member_id) ON DELETE RESTRICT,
        INDEX idx_member_id (member_id),
        INDEX idx_beneficiary_id (beneficiary_id),
        INDEX idx_payment_date (payment_date),
        INDEX idx_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql2)) {
        $tables_created[] = 'poll_payments';
    } else {
        $errors[] = 'poll_payments: ' . $conn->error;
    }

    // 3. Add missing columns to members table
    $check_upi = $conn->query("SHOW COLUMNS FROM members LIKE 'upi_id'");
    if ($check_upi->num_rows == 0) {
        $sql3 = "ALTER TABLE members ADD COLUMN upi_id VARCHAR(100)";
        if ($conn->query($sql3)) {
            $tables_created[] = 'members.upi_id (column added)';
        }
    }

    $check_bank = $conn->query("SHOW COLUMNS FROM members LIKE 'bank_name'");
    if ($check_bank->num_rows == 0) {
        $sql4 = "ALTER TABLE members ADD COLUMN bank_name VARCHAR(100), ADD COLUMN account_number VARCHAR(50), ADD COLUMN ifsc_code VARCHAR(20), ADD COLUMN account_holder_name VARCHAR(100)";
        if ($conn->query($sql4)) {
            $tables_created[] = 'members.bank_details (columns added)';
        }
    }

    // 4. Create poll_logs table for audit
    $sql5 = "CREATE TABLE IF NOT EXISTS `poll_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_id` VARCHAR(50),
        `action` VARCHAR(100),
        `description` TEXT,
        `ip_address` VARCHAR(45),
        `user_agent` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE SET NULL,
        INDEX idx_member_id (member_id),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql5)) {
        $tables_created[] = 'poll_logs';
    } else {
        $errors[] = 'poll_logs: ' . $conn->error;
    }

    // Count approved applications
    $approved_count = $conn->query("SELECT COUNT(*) as cnt FROM poll_applications WHERE status = 'Approved'");
    $approved_data = $approved_count->fetch_assoc();

    $response = [
        'success' => true,
        'message' => 'Database setup completed successfully',
        'tables_created' => $tables_created,
        'errors' => $errors,
        'approved_applications' => $approved_data['cnt'] ?? 0,
        'status' => count($errors) === 0 ? 'Ready for Production' : 'Setup Complete with Warnings'
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
