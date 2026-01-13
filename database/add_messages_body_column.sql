-- Add missing 'body' column to messages table if it doesn't exist
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `body` TEXT NULL AFTER `sender_id`;

-- Optional: ensure has_attachments exists for consistency
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `has_attachments` TINYINT(1) NOT NULL DEFAULT 0 AFTER `body`;

-- Optional: ensure created_at exists
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `has_attachments`;

-- Add compatibility 'message' column for admin views if it doesn't exist
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `message` TEXT NULL AFTER `body`;

-- Triggers to keep body and message in sync
DROP TRIGGER IF EXISTS `messages_bi_sync`;
DELIMITER $$
CREATE TRIGGER `messages_bi_sync` BEFORE INSERT ON `messages`
FOR EACH ROW
BEGIN
  SET NEW.body = COALESCE(NEW.body, NEW.message);
  SET NEW.message = COALESCE(NEW.message, NEW.body);
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `messages_bu_sync`;
DELIMITER $$
CREATE TRIGGER `messages_bu_sync` BEFORE UPDATE ON `messages`
FOR EACH ROW
BEGIN
  SET NEW.body = COALESCE(NEW.body, NEW.message);
  SET NEW.message = COALESCE(NEW.message, NEW.body);
END$$
DELIMITER ;

