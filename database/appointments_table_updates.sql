-- Migration: Add missing columns to appointments table
-- This file adds the 'type' column and updates the status enum to support appointment requests
-- Run this in phpMyAdmin SQL tab

-- Step 1: Check and add 'type' column to appointments table
-- If column already exists, you'll get an error - just ignore it
SET @dbname = DATABASE();
SET @tablename = "appointments";
SET @columnname = "type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column type already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(50) DEFAULT 'consultation' AFTER `title`")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Update status enum to include 'approved' and 'pending'
-- This will modify the enum to support appointment request workflow
ALTER TABLE `appointments` 
MODIFY COLUMN `status` ENUM('pending','scheduled','approved','confirmed','completed','cancelled','rejected') NOT NULL DEFAULT 'pending';

-- Step 3: Make created_by nullable (for system-created appointments from requests)
-- Check if created_by is currently NOT NULL, if so make it nullable
SET @preparedStatement = (SELECT IF(
  (
    SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = 'created_by')
  ) = 'NO',
  CONCAT("ALTER TABLE ", @tablename, " MODIFY COLUMN `created_by` INT(11) NULL"),
  "SELECT 'Column created_by already allows NULL.'"
));
PREPARE alterIfNotNull FROM @preparedStatement;
EXECUTE alterIfNotNull;
DEALLOCATE PREPARE alterIfNotNull;

-- Step 4: Add index on type for better query performance (if not exists)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'idx_appt_type')
  ) > 0,
  "SELECT 'Index idx_appt_type already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD INDEX `idx_appt_type` (`type`)")
));
PREPARE alterIfNotExistsIndex FROM @preparedStatement;
EXECUTE alterIfNotExistsIndex;
DEALLOCATE PREPARE alterIfNotExistsIndex;

-- Step 5: Verify status index exists (add if needed)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'idx_appt_status')
  ) > 0,
  "SELECT 'Index idx_appt_status already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD INDEX `idx_appt_status` (`status`)")
));
PREPARE alterIfNotExistsStatusIndex FROM @preparedStatement;
EXECUTE alterIfNotExistsStatusIndex;
DEALLOCATE PREPARE alterIfNotExistsStatusIndex;

-- Alternative simpler version (if the above doesn't work, use this):
-- Just run these commands and ignore errors if columns/indexes already exist:

-- ALTER TABLE `appointments` ADD COLUMN `type` VARCHAR(50) DEFAULT 'consultation' AFTER `title`;
-- ALTER TABLE `appointments` MODIFY COLUMN `status` ENUM('pending','scheduled','approved','confirmed','completed','cancelled','rejected') NOT NULL DEFAULT 'pending';
-- ALTER TABLE `appointments` MODIFY COLUMN `created_by` INT(11) NULL;
-- ALTER TABLE `appointments` ADD INDEX `idx_appt_type` (`type`);
-- ALTER TABLE `appointments` ADD INDEX `idx_appt_status` (`status`);

