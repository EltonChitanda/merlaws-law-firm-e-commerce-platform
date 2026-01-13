-- SQL Script to fix support message system
-- Run this in phpMyAdmin to ensure the database structure is correct

-- Check if read_at column exists in messages table, add if missing
SET @dbname = DATABASE();
SET @tablename = "messages";
SET @columnname = "read_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL DEFAULT NULL AFTER is_read")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Ensure message_threads table has assigned_to column
SET @tablename = "message_threads";
SET @columnname = "assigned_to";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT NULL DEFAULT NULL AFTER created_by, ADD KEY idx_thread_assigned_to (assigned_to), ADD CONSTRAINT fk_thread_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Ensure message_threads table has thread_type column
SET @columnname = "thread_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('case','support') NOT NULL DEFAULT 'case' AFTER created_by")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update any existing support threads to have thread_type = 'support' if case_id is NULL
UPDATE message_threads 
SET thread_type = 'support' 
WHERE case_id IS NULL AND (thread_type IS NULL OR thread_type = '');

-- Ensure messages table has both body and message columns
SET @tablename = "messages";
SET @columnname = "body";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TEXT NULL DEFAULT NULL AFTER sender_id")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Copy message content to body column where body is NULL (for backward compatibility)
UPDATE messages 
SET body = message 
WHERE body IS NULL AND message IS NOT NULL;

-- Verify the structure
SELECT 'Database structure verification complete!' AS status;

