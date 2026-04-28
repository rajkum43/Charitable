-- Migration: Create donation_transactions table
-- Purpose: Track member donations with transaction numbers and receipt uploads
-- Date: April 20, 2026

CREATE TABLE IF NOT EXISTS `donation_transactions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `member_id` VARCHAR(20) NOT NULL,
    `donation_to_member_id` VARCHAR(20) COMMENT 'Member ID of the person receiving donation (death/vivah applicant)',
    `claim_number` VARCHAR(100) NOT NULL COMMENT 'Application number (from death_aavedan or beti_vivah_aavedan)',
    `application_type` ENUM('Death', 'Beti_Vivah') NOT NULL,
    `transaction_number` VARCHAR(100) NOT NULL COMMENT 'Transaction ID or UTR number',
    `receipt_file_path` VARCHAR(255) COMMENT 'Path to uploaded receipt file',
    `file_size` INT COMMENT 'File size in bytes',
    `file_mime_type` VARCHAR(50) COMMENT 'MIME type of uploaded file',
    `amount` DECIMAL(10, 2) COMMENT 'Donation amount (if known)',
    `remarks` TEXT,
    `status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' COMMENT 'Verification status by admin',
    `admin_remarks` TEXT,
    `verified_by` VARCHAR(100) COMMENT 'Admin who verified',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    KEY `idx_member_id` (`member_id`),
    KEY `idx_donation_to_member_id` (`donation_to_member_id`),
    KEY `idx_claim_number` (`claim_number`),
    KEY `idx_transaction_number` (`transaction_number`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    
    -- Foreign key constraint (optional, depends on your setup)
    CONSTRAINT `fk_donation_member_id` FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
