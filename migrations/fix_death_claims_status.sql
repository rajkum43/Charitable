-- Fix death claims status field and add claim_id
-- This migration fixes the mismatch between TINYINT and String status values
-- Also adds claim_id field for tracking claims

-- Step 1: Add claim_id column if it doesn't exist
ALTER TABLE `death_claims` 
ADD COLUMN `claim_id` VARCHAR(50) UNIQUE DEFAULT NULL;

-- Step 2: Modify the status column to VARCHAR if needed
ALTER TABLE `death_claims` 
MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'Pending';

-- Step 3: Update any existing records with NULL or numeric status to 'Pending'
UPDATE `death_claims` 
SET `status` = 'Pending' 
WHERE `status` IS NULL OR `status` = '' OR `status` = '0';

-- Step 4: Ensure all records have valid status values
UPDATE `death_claims` 
SET `status` = 'Pending' 
WHERE `status` NOT IN ('Pending', 'Under Review', 'Approved', 'Rejected');

-- Step 5: Generate claim_id for existing records that don't have one
-- Format: BRCT-D + YYYYMMDD + 4-digit ID
UPDATE `death_claims` 
SET `claim_id` = CONCAT('BRCT-D', DATE_FORMAT(created_at, '%Y%m%d'), LPAD(id, 4, '0'))
WHERE `claim_id` IS NULL OR `claim_id` = '';

