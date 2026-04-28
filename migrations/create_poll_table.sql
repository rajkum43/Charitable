-- Create Poll Table for Admin Poll System
-- This table stores the final poll results for members

CREATE TABLE `poll` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `claim_number` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(20) NOT NULL,
  `poll` CHAR(1) NOT NULL,
  `application_type` ENUM('Death Claim', 'Beti Vivah') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_poll` (`poll`),
  INDEX `idx_application_type` (`application_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add poll_status column to death_claims if not exists
-- ALTER TABLE `death_claims` ADD COLUMN `poll_status` TINYINT(1) NOT NULL DEFAULT 0 AFTER `claim_id`;

-- Add poll_status column to beti_vivah_aavedan if not exists  
-- ALTER TABLE `beti_vivah_aavedan` ADD COLUMN `poll_status` TINYINT(1) NOT NULL DEFAULT 0 AFTER `updated_at`;

-- Add poll column to members table to store final poll assignment
-- ALTER TABLE `members` ADD COLUMN `poll` CHAR(1) DEFAULT NULL AFTER `referrer_member_id`;
