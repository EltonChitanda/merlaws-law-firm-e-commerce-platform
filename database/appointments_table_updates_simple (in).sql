-- Simple Migration: Add missing columns to appointments table
-- Run this in phpMyAdmin SQL tab
-- If you get errors about columns/indexes already existing, that's fine - just ignore them

-- Step 1: Add 'type' column to appointments table
ALTER TABLE `appointments` 
ADD COLUMN `type` VARCHAR(50) DEFAULT 'consultation' AFTER `title`;

-- Step 2: Update status enum to include 'approved' and 'pending'
ALTER TABLE `appointments` 
MODIFY COLUMN `status` ENUM('pending','scheduled','approved','confirmed','completed','cancelled','rejected') NOT NULL DEFAULT 'pending';

-- Step 3: Make created_by nullable (for system-created appointments from requests)
ALTER TABLE `appointments` 
MODIFY COLUMN `created_by` INT(11) NULL;

-- Step 4: Add index on type for better query performance
ALTER TABLE `appointments` 
ADD INDEX `idx_appt_type` (`type`);

-- Step 5: Add index on status for better query performance
ALTER TABLE `appointments` 
ADD INDEX `idx_appt_status` (`status`);

-- Note: If any of these commands fail because the column/index already exists,
-- that's okay - just continue with the rest. The important ones are:
-- 1. Adding the 'type' column
-- 2. Updating the status enum to include 'approved'

