-- Add alert column to poll table for tracking publish events
-- Migration: Add alert INT column to track which publish batch the data belongs to

ALTER TABLE `poll` ADD COLUMN `alert` INT NOT NULL DEFAULT 0 AFTER `application_type`;

-- Create index on alert column for faster queries
CREATE INDEX `idx_alert` ON `poll`(`alert`);
