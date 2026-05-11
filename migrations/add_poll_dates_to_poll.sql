-- Add poll date columns to poll table if they don't exist
-- This allows filtering alerts by date range

ALTER TABLE `poll`
ADD COLUMN `start_poll_date` DATE DEFAULT '0000-00-00' AFTER `alert`,
ADD COLUMN `expire_poll_date` DATE DEFAULT '0000-00-00' AFTER `start_poll_date`;
