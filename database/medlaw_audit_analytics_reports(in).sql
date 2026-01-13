-- ============================================================================
-- MedLaw v15 - Audit Logs, Analytics & Reports Database Schema
-- ============================================================================
-- This SQL file adds comprehensive audit logging, analytics, and reporting
-- functionality to the MedLaw system.
-- 
-- IMPORTANT: Run this file in phpMyAdmin after backing up your database.
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================================
-- 1. AUDIT LOGS TABLES
-- ============================================================================

-- Main audit logs table (replaces/extends security_logs)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL COMMENT 'Type of event (login, logout, create, update, delete, etc.)',
  `event_category` varchar(50) NOT NULL DEFAULT 'general' COMMENT 'Category: auth, case, user, finance, system, etc.',
  `event_action` varchar(100) NOT NULL COMMENT 'Specific action taken',
  `message` text NOT NULL COMMENT 'Human-readable description',
  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'User who performed the action',
  `user_role` varchar(50) DEFAULT NULL COMMENT 'Role of user at time of action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of user',
  `user_agent` text DEFAULT NULL COMMENT 'Browser/user agent string',
  `request_method` varchar(10) DEFAULT NULL COMMENT 'HTTP method (GET, POST, etc.)',
  `request_uri` varchar(500) DEFAULT NULL COMMENT 'Request URI',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'Type of entity affected (case, user, invoice, etc.)',
  `entity_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID of entity affected',
  `old_values` json DEFAULT NULL COMMENT 'Previous values (for updates)',
  `new_values` json DEFAULT NULL COMMENT 'New values (for updates)',
  `metadata` json DEFAULT NULL COMMENT 'Additional context data',
  `severity` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Severity level',
  `status` enum('success','failure','warning') DEFAULT 'success' COMMENT 'Action status',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_event_category` (`event_category`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comprehensive audit log for all system events';

-- Audit log archive table (for old logs)
CREATE TABLE IF NOT EXISTS `audit_logs_archive` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_category` varchar(50) NOT NULL DEFAULT 'general',
  `event_action` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_uri` varchar(500) DEFAULT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('success','failure','warning') DEFAULT 'success',
  `created_at` timestamp NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archived audit logs';

-- ============================================================================
-- 2. ANALYTICS TABLES
-- ============================================================================

-- Enhanced analytics events table (extends existing if present)
CREATE TABLE IF NOT EXISTS `analytics_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'User who triggered the event',
  `event_type` varchar(100) NOT NULL COMMENT 'Type of event',
  `event_category` varchar(100) NOT NULL DEFAULT 'general' COMMENT 'Category of event',
  `event_action` varchar(100) NOT NULL COMMENT 'Specific action',
  `event_label` varchar(255) DEFAULT NULL COMMENT 'Event label/description',
  `event_value` decimal(10,2) DEFAULT NULL COMMENT 'Numeric value if applicable',
  `session_id` varchar(255) DEFAULT NULL COMMENT 'Session identifier',
  `page_url` varchar(500) DEFAULT NULL COMMENT 'Page URL where event occurred',
  `referrer_url` varchar(500) DEFAULT NULL COMMENT 'Referrer URL',
  `device_type` varchar(50) DEFAULT NULL COMMENT 'Device type (desktop, mobile, tablet)',
  `browser` varchar(100) DEFAULT NULL COMMENT 'Browser name',
  `os` varchar(100) DEFAULT NULL COMMENT 'Operating system',
  `metadata` json DEFAULT NULL COMMENT 'Additional event data',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_event_category` (`event_category`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Analytics events tracking';

-- Analytics metrics cache table (for performance)
CREATE TABLE IF NOT EXISTS `analytics_metrics_cache` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `metric_key` varchar(100) NOT NULL COMMENT 'Unique metric identifier',
  `metric_type` varchar(50) NOT NULL COMMENT 'Type: daily, weekly, monthly, custom',
  `period_start` date NOT NULL COMMENT 'Start of period',
  `period_end` date NOT NULL COMMENT 'End of period',
  `metric_value` decimal(15,2) DEFAULT NULL COMMENT 'Numeric metric value',
  `metric_data` json DEFAULT NULL COMMENT 'Complex metric data',
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_metric_period` (`metric_key`, `metric_type`, `period_start`, `period_end`),
  KEY `idx_calculated_at` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cached analytics metrics for performance';

-- ============================================================================
-- 3. REPORTS TABLES
-- ============================================================================

-- Report definitions table
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Report name',
  `description` text DEFAULT NULL COMMENT 'Report description',
  `report_type` varchar(50) NOT NULL COMMENT 'Type: user_activity, case_outcomes, financial, system_health, custom',
  `template` text DEFAULT NULL COMMENT 'Report template/configuration (JSON)',
  `created_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User who created the report',
  `is_scheduled` tinyint(1) DEFAULT 0 COMMENT 'Is this a scheduled report?',
  `schedule_cron` varchar(100) DEFAULT NULL COMMENT 'Cron expression for scheduling',
  `schedule_recipients` text DEFAULT NULL COMMENT 'Comma-separated email recipients',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Is report active?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_report_type` (`report_type`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Report definitions and templates';

-- Report execution history
CREATE TABLE IF NOT EXISTS `report_executions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Report definition ID',
  `report_name` varchar(255) NOT NULL COMMENT 'Report name at time of execution',
  `report_type` varchar(50) NOT NULL COMMENT 'Report type',
  `executed_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User who executed the report',
  `execution_type` enum('manual','scheduled','api') DEFAULT 'manual' COMMENT 'How report was executed',
  `parameters` json DEFAULT NULL COMMENT 'Report parameters used',
  `status` enum('pending','running','completed','failed') DEFAULT 'pending' COMMENT 'Execution status',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Path to generated report file',
  `file_format` varchar(20) DEFAULT NULL COMMENT 'Format: csv, pdf, excel, json',
  `file_size` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'File size in bytes',
  `record_count` int(11) UNSIGNED DEFAULT NULL COMMENT 'Number of records in report',
  `error_message` text DEFAULT NULL COMMENT 'Error message if failed',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'When execution started',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'When execution completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_report_id` (`report_id`),
  KEY `idx_executed_by` (`executed_by`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_report_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Report execution history';

-- ============================================================================
-- 4. INDEXES AND PERFORMANCE OPTIMIZATIONS
-- ============================================================================

-- Additional indexes for better query performance
ALTER TABLE `audit_logs` ADD INDEX `idx_category_type` (`event_category`, `event_type`);
ALTER TABLE `audit_logs` ADD INDEX `idx_user_created` (`user_id`, `created_at`);

-- ============================================================================
-- 5. STORED PROCEDURES (Optional - for advanced features)
-- ============================================================================

DELIMITER $$

-- Procedure to archive old audit logs
CREATE PROCEDURE IF NOT EXISTS `sp_archive_audit_logs`(IN days_to_keep INT)
BEGIN
    DECLARE archive_date DATE;
    SET archive_date = DATE_SUB(CURDATE(), INTERVAL days_to_keep DAY);
    
    INSERT INTO audit_logs_archive 
    SELECT *, NOW() as archived_at 
    FROM audit_logs 
    WHERE created_at < archive_date;
    
    DELETE FROM audit_logs WHERE created_at < archive_date;
END$$

-- Procedure to get dashboard statistics
CREATE PROCEDURE IF NOT EXISTS `sp_get_admin_dashboard_stats`()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_users,
        (SELECT COUNT(*) FROM cases WHERE status IN ('active', 'under_review')) as active_cases,
        (SELECT COUNT(*) FROM service_requests WHERE status = 'pending') as pending_requests,
        (SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) 
         FROM cases 
         WHERE status IN ('resolved', 'closed') 
         AND updated_at IS NOT NULL) as avg_processing_hours;
END$$

DELIMITER ;

-- ============================================================================
-- 6. INITIAL DATA / DEFAULT REPORTS
-- ============================================================================

-- Insert default report templates
INSERT INTO `reports` (`name`, `description`, `report_type`, `template`, `is_active`) VALUES
('User Activity Summary', 'Summary of user login and activity patterns', 'user_activity', 
 '{"metrics": ["total_logins", "active_users", "new_users"], "period": "monthly"}', 1),
('Case Outcomes Report', 'Analysis of case resolutions and outcomes', 'case_outcomes',
 '{"metrics": ["resolved_cases", "avg_resolution_time", "outcome_distribution"], "period": "monthly"}', 1),
('Financial Summary', 'Revenue, invoices, and payment statistics', 'financial',
 '{"metrics": ["total_revenue", "outstanding_invoices", "payment_rate"], "period": "monthly"}', 1),
('System Health Report', 'System performance and health metrics', 'system_health',
 '{"metrics": ["error_rate", "response_time", "uptime"], "period": "weekly"}', 1)
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================================
-- 7. VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View for recent audit events summary
CREATE OR REPLACE VIEW `v_recent_audit_summary` AS
SELECT 
    DATE(created_at) as event_date,
    event_category,
    event_type,
    COUNT(*) as event_count,
    COUNT(DISTINCT user_id) as unique_users
FROM audit_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), event_category, event_type
ORDER BY event_date DESC, event_count DESC;

-- View for user activity analytics
CREATE OR REPLACE VIEW `v_user_activity_analytics` AS
SELECT 
    DATE(ae.created_at) as activity_date,
    ae.event_type,
    COUNT(*) as event_count,
    COUNT(DISTINCT ae.user_id) as active_users
FROM analytics_events ae
WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(ae.created_at), ae.event_type
ORDER BY activity_date DESC;

-- ============================================================================
-- END OF SQL FILE
-- ============================================================================
-- 
-- After running this file:
-- 1. Verify tables were created: SHOW TABLES LIKE '%audit%' OR LIKE '%analytics%' OR LIKE '%report%';
-- 2. Check indexes: SHOW INDEXES FROM audit_logs;
-- 3. Test procedures: CALL sp_get_admin_dashboard_stats();
-- 
-- ============================================================================

