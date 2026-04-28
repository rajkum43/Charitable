-- Poll System Tables for Trust Sahyog

-- 1. Applications Table (if not exists, add columns)
CREATE TABLE IF NOT EXISTS `poll_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` VARCHAR(50) NOT NULL,
  `type` ENUM('vivah', 'death') NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `application_details` LONGTEXT,
  `approved_date` DATE NULL,
  `poll_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`),
  UNIQUE KEY `unique_application` (`member_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Polls Table
CREATE TABLE IF NOT EXISTS `polls` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_name` VARCHAR(100) NOT NULL,
  `poll_code` VARCHAR(20) UNIQUE NOT NULL,
  `application_id` INT NOT NULL,
  `beneficiary_id` VARCHAR(50) NOT NULL,
  `beneficiary_name` VARCHAR(100) NOT NULL,
  `poll_type` ENUM('vivah', 'death') NOT NULL,
  `total_members` INT NOT NULL,
  `donation_amount` INT DEFAULT 50,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `status` ENUM('Active', 'Closed', 'Completed') DEFAULT 'Active',
  `total_collected` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `poll_applications`(`id`),
  FOREIGN KEY (`beneficiary_id`) REFERENCES `members`(`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Poll Members Table
CREATE TABLE IF NOT EXISTS `poll_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_id` INT NOT NULL,
  `member_id` VARCHAR(50) NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
  `payment_date` DATETIME NULL,
  `utr_number` VARCHAR(50) NULL,
  `transaction_id` VARCHAR(100) NULL,
  `screenshot_path` VARCHAR(255) NULL,
  `paid_amount` INT DEFAULT 0,
  `payment_method` ENUM('UPI', 'Bank Transfer', 'Cheque') NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`),
  UNIQUE KEY `unique_poll_member` (`poll_id`, `member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payment Tracking Table
CREATE TABLE IF NOT EXISTS `poll_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_member_id` INT NOT NULL,
  `poll_id` INT NOT NULL,
  `member_id` VARCHAR(50) NOT NULL,
  `amount` INT NOT NULL,
  `payment_date` DATETIME,
  `payment_method` ENUM('UPI', 'Bank Transfer', 'Cheque', 'Cash') NULL,
  `utr_number` VARCHAR(50),
  `transaction_id` VARCHAR(100),
  `screenshot_path` VARCHAR(255),
  `status` ENUM('Pending', 'Verified', 'Failed') DEFAULT 'Pending',
  `verified_by_admin` INT NULL,
  `verified_date` DATETIME NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`poll_member_id`) REFERENCES `poll_members`(`id`),
  FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_poll_status ON polls(status);
CREATE INDEX idx_poll_member_status ON poll_members(payment_status);
CREATE INDEX idx_poll_payment_status ON poll_payments(status);
CREATE INDEX idx_member_poll ON poll_members(member_id, poll_id);
