-- Fix age and relation_type columns in death_claims
-- This migration populates age and relation_type fields from existing data

-- Step 1: Copy age_at_death to age if age is NULL but age_at_death exists
UPDATE `death_claims` 
SET `age` = `age_at_death` 
WHERE `age` IS NULL AND `age_at_death` IS NOT NULL;

-- Step 2: Copy nominee_relation to relation_type if relation_type is NULL
UPDATE `death_claims` 
SET `relation_type` = `nominee_relation` 
WHERE `relation_type` IS NULL AND `nominee_relation` IS NOT NULL;

-- Step 3: Log completion
SELECT 'Migration completed: age and relation_type fields updated' AS status;

ALTER TABLE death_claims
ADD COLUMN poll_status TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE beti_vivah_aavedan
ADD COLUMN poll_status TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE members
ADD COLUMN poll CHAR(1);
