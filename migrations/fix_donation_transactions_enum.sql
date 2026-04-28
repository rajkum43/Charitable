-- Fix: Update donation_transactions application_type ENUM to match poll table
-- Date: April 20, 2026

ALTER TABLE `donation_transactions` 
MODIFY COLUMN `application_type` ENUM('Death_Claims', 'Beti_Vivah') NOT NULL;
