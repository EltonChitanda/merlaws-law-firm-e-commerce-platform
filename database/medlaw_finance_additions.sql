-- MedLaw Finance Additions / Compatibility Migration
-- Purpose: Align DB schema with admin finance pages and services (InvoiceService/PayFastService)
-- Usage: Import this file into the existing `medlaw` database via phpMyAdmin

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Ensure database selected
CREATE DATABASE IF NOT EXISTS `medlaw` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `medlaw`;

-- =============================
-- 1) invoices table adjustments
-- =============================
-- Add columns needed by InvoiceService (keeps legacy `amount` used by admin finance page)
ALTER TABLE `invoices`
    ADD COLUMN IF NOT EXISTS `client_id` INT NULL AFTER `case_id`,
    ADD COLUMN IF NOT EXISTS `invoice_date` DATE NULL AFTER `client_id`,
    ADD COLUMN IF NOT EXISTS `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `amount`,
    ADD COLUMN IF NOT EXISTS `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 15.00 AFTER `subtotal`,
    ADD COLUMN IF NOT EXISTS `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tax_rate`,
    ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`,
    ADD COLUMN IF NOT EXISTS `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`,
    ADD COLUMN IF NOT EXISTS `terms_conditions` TEXT NULL AFTER `notes`,
    ADD COLUMN IF NOT EXISTS `payment_instructions` TEXT NULL AFTER `terms_conditions`,
    ADD COLUMN IF NOT EXISTS `sent_at` DATETIME NULL AFTER `paid_at`,
    ADD COLUMN IF NOT EXISTS `updated_by` INT NULL AFTER `created_by`;

-- Make sure status supports values used across code: draft, pending, sent, paid, overdue, void, cancelled
SET @enum_stmt = (SELECT IF(
  (SELECT COLUMN_TYPE FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'status') LIKE '%\'sent\'%'
  , 'SELECT 1',
  'ALTER TABLE `invoices` MODIFY `status` ENUM("draft","pending","sent","paid","overdue","void","cancelled") NOT NULL DEFAULT "draft"'
));
PREPARE alter_enum FROM @enum_stmt; EXECUTE alter_enum; DEALLOCATE PREPARE alter_enum;

-- Foreign keys and helpful indexes
ALTER TABLE `invoices`
    ADD INDEX IF NOT EXISTS `idx_invoices_client` (`client_id`),
    ADD INDEX IF NOT EXISTS `idx_invoices_case` (`case_id`),
    ADD INDEX IF NOT EXISTS `idx_invoices_status` (`status`),
    ADD INDEX IF NOT EXISTS `idx_invoices_paid_at` (`paid_at`);

-- Add FKs only if not present
-- (MariaDB/MySQL ignore duplicate constraint names automatically only if identical; using IF NOT EXISTS pattern)
SET @fk1 := (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS 
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_invoices_client');
SET @sql1 := IF(@fk1=0, 'ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE SET NULL', 'SELECT 1');
PREPARE s1 FROM @sql1; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @fk2 := (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS 
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_invoices_case');
SET @sql2 := IF(@fk2=0, 'ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoices_case` FOREIGN KEY (`case_id`) REFERENCES `cases`(`id`) ON DELETE SET NULL', 'SELECT 1');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;

-- Keep legacy `amount` in sync with `total_amount` for backward compatibility
DROP TRIGGER IF EXISTS trg_invoices_bi_sync_amount;
DELIMITER $$
CREATE TRIGGER trg_invoices_bi_sync_amount
BEFORE INSERT ON `invoices`
FOR EACH ROW
BEGIN
    IF NEW.total_amount IS NULL OR NEW.total_amount = 0 THEN
        SET NEW.total_amount = IFNULL(NEW.amount, 0.00);
    END IF;
    IF NEW.amount IS NULL OR NEW.amount = 0 THEN
        SET NEW.amount = IFNULL(NEW.total_amount, 0.00);
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_invoices_bu_sync_amount;
DELIMITER $$
CREATE TRIGGER trg_invoices_bu_sync_amount
BEFORE UPDATE ON `invoices`
FOR EACH ROW
BEGIN
    IF NEW.total_amount <> OLD.total_amount THEN
        SET NEW.amount = NEW.total_amount;
    ELSEIF NEW.amount <> OLD.amount THEN
        SET NEW.total_amount = NEW.amount;
    END IF;
END$$
DELIMITER ;

-- ===================================
-- 2) invoice_items table adjustments
-- ===================================
-- Add columns expected by InvoiceService
ALTER TABLE `invoice_items`
    ADD COLUMN IF NOT EXISTS `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 15.00 AFTER `unit_price`,
    ADD COLUMN IF NOT EXISTS `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tax_rate`,
    ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0 AFTER `amount`;

-- Helpful indexes
ALTER TABLE `invoice_items`
    ADD INDEX IF NOT EXISTS `idx_invoice_items_invoice` (`invoice_id`),
    ADD INDEX IF NOT EXISTS `idx_invoice_items_sort` (`invoice_id`,`sort_order`);

-- ===================================
-- 3) invoice_payments table (NEW)
-- ===================================
CREATE TABLE IF NOT EXISTS `invoice_payments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `invoice_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `transaction_id` VARCHAR(100) NULL,
  `reference_number` VARCHAR(100) NULL,
  `payfast_payment_id` VARCHAR(100) NULL,
  `payfast_status` VARCHAR(50) NULL,
  `payfast_raw_response` MEDIUMTEXT NULL,
  `notes` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_payments_invoice` (`invoice_id`),
  KEY `idx_invoice_payments_payfast` (`payfast_payment_id`),
  CONSTRAINT `fk_invoice_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_payments_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 4) payfast_transactions table (NEW)
-- ===================================
CREATE TABLE IF NOT EXISTS `payfast_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `invoice_id` INT NOT NULL,
  `pf_payment_id` VARCHAR(100) NOT NULL,
  `payment_status` VARCHAR(50) NULL,
  `signature_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `raw_post` MEDIUMTEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pf_payment_id` (`pf_payment_id`),
  KEY `idx_pf_invoice` (`invoice_id`),
  CONSTRAINT `fk_pf_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 5) trust_accounts table (optional, used in finance page summary)
-- ===================================
CREATE TABLE IF NOT EXISTS `trust_accounts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL DEFAULT 'Primary Trust Account',
  `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active','closed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trust_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure required columns exist if table pre-existed without them
ALTER TABLE `trust_accounts`
    ADD COLUMN IF NOT EXISTS `name` VARCHAR(190) NOT NULL DEFAULT 'Primary Trust Account',
    ADD COLUMN IF NOT EXISTS `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active','closed') NOT NULL DEFAULT 'active',
    ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Seed a default active trust account if none exists (handle legacy schemas with case_id FK)
SET @has_case_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trust_accounts' AND COLUMN_NAME = 'case_id'
);
SET @case_nullable := (
    SELECT CASE WHEN IS_NULLABLE = 'YES' THEN 1 ELSE 0 END 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trust_accounts' AND COLUMN_NAME = 'case_id' 
    LIMIT 1
);
SET @existing_trust_rows := (SELECT COUNT(*) FROM `trust_accounts`);

SET @seed_sql := (
    SELECT CASE
        WHEN @existing_trust_rows = 0 AND @has_case_col = 0 THEN 
            'INSERT INTO `trust_accounts` (`name`,`balance`,`status`) VALUES (''Primary Trust Account'', 0.00, ''active'')'
        WHEN @existing_trust_rows = 0 AND @has_case_col = 1 AND @case_nullable = 1 THEN 
            'INSERT INTO `trust_accounts` (`name`,`balance`,`status`,`case_id`) VALUES (''Primary Trust Account'', 0.00, ''active'', NULL)'
        ELSE 'SELECT 1'
    END
);
PREPARE seed_stmt FROM @seed_sql; EXECUTE seed_stmt; DEALLOCATE PREPARE seed_stmt;

-- ===================================
-- 6) financial_requests table (optional, used in finance page sidebar)
-- ===================================
CREATE TABLE IF NOT EXISTS `financial_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `case_id` INT NOT NULL,
  `type` ENUM('discount','write_off','trust_disbursement','refund','other') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `reason` TEXT NULL,
  `requested_by` INT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_finreq_case` (`case_id`),
  KEY `idx_finreq_status` (`status`),
  CONSTRAINT `fk_finreq_case` FOREIGN KEY (`case_id`) REFERENCES `cases`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_finreq_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 7) data harmonization (optional backfill)
-- ===================================
-- Copy legacy amount into total_amount where needed
UPDATE `invoices`
SET `total_amount` = `amount`
WHERE (`total_amount` IS NULL OR `total_amount` = 0.00) AND `amount` IS NOT NULL;

-- Backfill invoice_date with created_at date if NULL
UPDATE `invoices`
SET `invoice_date` = DATE(`created_at`)
WHERE `invoice_date` IS NULL;

-- Recompute invoice_items.amount if zero and total_price exists
UPDATE `invoice_items` SET `amount` = `total_price` WHERE (`amount` = 0 OR `amount` IS NULL) AND `total_price` IS NOT NULL;

-- Done
SELECT 'medlaw_finance_additions.sql migration completed' AS status;


