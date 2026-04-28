-- Poll System Database Migration Script
-- Creates poll table and adds poll_status tracking columns to death_claims and beti_vivah_aavedan

-- Create poll table if not exists
CREATE TABLE IF NOT EXISTS `poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_number` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `poll` char(1) NOT NULL COMMENT 'Poll option: A, B, C, etc.',
  `application_type` enum('Death_Claims','Beti_Vivah') NOT NULL,
  `start_poll_date` date NOT NULL COMMENT 'Poll start date (10th of month)',
  `expire_poll_date` date NOT NULL COMMENT 'Poll expiry date (20th of month)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_poll` (`poll`),
  KEY `idx_application_type` (`application_type`),
  KEY `idx_claim_number` (`claim_number`),
  KEY `idx_poll_dates` (`start_poll_date`, `expire_poll_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add poll date columns to poll table if they don't exist (backward compatibility)
ALTER TABLE `poll` 
ADD COLUMN IF NOT EXISTS `start_poll_date` date NOT NULL COMMENT 'Poll start date (10th of month)' AFTER `application_type`,
ADD COLUMN IF NOT EXISTS `expire_poll_date` date NOT NULL COMMENT 'Poll expiry date (20th of month)' AFTER `start_poll_date`,
ADD KEY IF NOT EXISTS `idx_poll_dates` (`start_poll_date`, `expire_poll_date`);

-- Add poll_status column to death_aavedan table if not exists
ALTER TABLE `death_aavedan` 
ADD COLUMN IF NOT EXISTS `poll_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Not in poll, 1=In poll' AFTER `status`,
ADD KEY IF NOT EXISTS `idx_poll_status` (`poll_status`);

-- Add poll_status column to beti_vivah_aavedan table if not exists
ALTER TABLE `beti_vivah_aavedan` 
ADD COLUMN IF NOT EXISTS `poll_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Not in poll, 1=In poll' AFTER `status`,
ADD KEY IF NOT EXISTS `idx_poll_status` (`poll_status`);

-- Add poll tracking columns to members table if they don't exist
ALTER TABLE `members` 
ADD COLUMN IF NOT EXISTS `poll_option` char(1) NULL COMMENT 'Assigned poll option (A, B, C, etc.)' AFTER `status`,
ADD COLUMN IF NOT EXISTS `poll_assigned_at` timestamp NULL COMMENT 'When the poll option was assigned' AFTER `poll_option`,
ADD KEY IF NOT EXISTS `idx_poll_option` (`poll_option`);

-- Set default values for existing null records
UPDATE `members` SET `poll_option` = NULL WHERE `poll_option` = '';
UPDATE `members` SET `poll_assigned_at` = NULL WHERE `poll_assigned_at` = '0000-00-00 00:00:00';

-- Create index on poll table for better query performance
CREATE INDEX IF NOT EXISTS `idx_poll_compound` ON `poll` (`application_type`, `poll`, `user_id`);

-- Add foreign key constraint if members table exists (optional but recommended)
-- ALTER TABLE `poll` 
-- ADD CONSTRAINT `fk_poll_user` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
