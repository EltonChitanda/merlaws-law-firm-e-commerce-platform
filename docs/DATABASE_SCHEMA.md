# Database Schema Documentation

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** Developers, Database Administrators

---

## Table of Contents

1. [Database Overview](#database-overview)
2. [Core Tables](#core-tables)
3. [User Management Tables](#user-management-tables)
4. [Case Management Tables](#case-management-tables)
5. [Document Management Tables](#document-management-tables)
6. [Service Request Tables](#service-request-tables)
7. [Appointment Tables](#appointment-tables)
8. [Messaging Tables](#messaging-tables)
9. [Invoice and Payment Tables](#invoice-and-payment-tables)
10. [Analytics and Audit Tables](#analytics-and-audit-tables)
11. [System Tables](#system-tables)
12. [Table Relationships](#table-relationships)
13. [Indexes and Performance](#indexes-and-performance)
14. [Stored Procedures](#stored-procedures)
15. [Data Migration](#data-migration)

---

## Database Overview

### Database Name

`medlaw`

### Character Set

`utf8mb4` with `utf8mb4_unicode_ci` collation

### Engine

InnoDB (default)

---

## Core Tables

### users

User accounts and profiles.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `email` (VARCHAR(255), Unique, Not Null)
- `password_hash` (VARCHAR(255), Not Null)
- `name` (VARCHAR(255), Not Null)
- `phone` (VARCHAR(50), Nullable)
- `address` (TEXT, Nullable)
- `city` (VARCHAR(100), Nullable)
- `role` (ENUM, Not Null) - client, super_admin, admin, attorney, etc.
- `is_active` (TINYINT(1), Default: 1)
- `last_login` (TIMESTAMP, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`email`)
- KEY `idx_role` (`role`)
- KEY `idx_is_active` (`is_active`)

---

## Case Management Tables

### cases

Legal case records.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key → users.id, Not Null)
- `title` (VARCHAR(255), Not Null)
- `description` (TEXT, Nullable)
- `case_type` (VARCHAR(100), Not Null)
- `status` (ENUM, Not Null) - draft, active, under_review, closed
- `priority` (ENUM, Not Null) - low, medium, high, urgent
- `assigned_to` (INT, Foreign Key → users.id, Nullable)
- `total_won_amount` (DECIMAL(10,2), Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_status` (`status`)
- KEY `idx_assigned_to` (`assigned_to`)
- KEY `idx_case_type` (`case_type`)

### case_activities

Activity log for cases.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Not Null)
- `user_id` (INT, Foreign Key → users.id, Not Null)
- `activity_type` (VARCHAR(100), Not Null)
- `title` (VARCHAR(255), Not Null)
- `description` (TEXT, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_created_at` (`created_at`)

---

## Document Management Tables

### case_documents

Documents uploaded for cases.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Not Null)
- `filename` (VARCHAR(255), Not Null)
- `original_filename` (VARCHAR(255), Not Null)
- `file_path` (VARCHAR(500), Not Null)
- `file_size` (INT, Not Null) - Size in bytes
- `mime_type` (VARCHAR(100), Nullable)
- `document_type` (VARCHAR(100), Nullable)
- `description` (TEXT, Nullable)
- `uploaded_by` (INT, Foreign Key → users.id, Not Null)
- `checksum` (VARCHAR(64), Nullable) - SHA256 hash
- `uploaded_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_uploaded_by` (`uploaded_by`)
- KEY `idx_uploaded_at` (`uploaded_at`)

---

## Service Request Tables

### services

Available services catalog.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR(255), Not Null)
- `description` (TEXT, Nullable)
- `category` (VARCHAR(100), Not Null)
- `estimated_duration` (INT, Nullable) - Duration in hours
- `is_active` (TINYINT(1), Default: 1)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_category` (`category`)
- KEY `idx_is_active` (`is_active`)

### service_requests

Service requests from clients.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Not Null)
- `service_id` (INT, Foreign Key → services.id, Not Null)
- `status` (ENUM, Not Null) - cart, pending, approved, rejected
- `notes` (TEXT, Nullable)
- `urgency` (ENUM, Default: 'standard') - low, standard, high, urgent
- `consult_date` (DATE, Nullable) - For consultation services
- `consult_time` (TIME, Nullable) - For consultation services
- `requested_at` (TIMESTAMP, Nullable)
- `processed_at` (TIMESTAMP, Nullable)
- `processed_by` (INT, Foreign Key → users.id, Nullable)
- `admin_notes` (TEXT, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_service_id` (`service_id`)
- KEY `idx_status` (`status`)
- KEY `idx_requested_at` (`requested_at`)

---

## Appointment Tables

### appointments

Scheduled appointments.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Not Null)
- `created_by` (INT, Foreign Key → users.id, Not Null)
- `assigned_to` (INT, Foreign Key → users.id, Nullable)
- `title` (VARCHAR(255), Not Null)
- `description` (TEXT, Nullable)
- `location` (VARCHAR(255), Nullable)
- `start_time` (DATETIME, Not Null)
- `end_time` (DATETIME, Nullable)
- `status` (ENUM, Default: 'scheduled') - scheduled, completed, cancelled
- `reminder_minutes_before` (INT, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_assigned_to` (`assigned_to`)
- KEY `idx_start_time` (`start_time`)
- KEY `idx_status` (`status`)

---

## Messaging Tables

### message_threads

Message conversation threads.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Nullable)
- `subject` (VARCHAR(255), Not Null)
- `created_by` (INT, Foreign Key → users.id, Not Null)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_created_by` (`created_by`)

### messages

Individual messages in threads.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `thread_id` (INT, Foreign Key → message_threads.id, Not Null)
- `sender_id` (INT, Foreign Key → users.id, Not Null)
- `message` (TEXT, Not Null)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `read_at` (TIMESTAMP, Nullable)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_thread_id` (`thread_id`)
- KEY `idx_sender_id` (`sender_id`)
- KEY `idx_created_at` (`created_at`)

### message_participants

Participants in message threads.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `thread_id` (INT, Foreign Key → message_threads.id, Not Null)
- `user_id` (INT, Foreign Key → users.id, Not Null)
- `joined_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`thread_id`, `user_id`)

---

## Invoice and Payment Tables

### invoices

Invoice records.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `case_id` (INT, Foreign Key → cases.id, Nullable)
- `client_id` (INT, Foreign Key → users.id, Not Null)
- `invoice_number` (VARCHAR(50), Unique, Not Null)
- `amount` (DECIMAL(10,2), Not Null)
- `total_amount` (DECIMAL(10,2), Not Null)
- `status` (ENUM, Not Null) - draft, sent, paid, overdue, cancelled, void
- `due_date` (DATE, Not Null)
- `created_by` (INT, Foreign Key → users.id, Not Null)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`invoice_number`)
- KEY `idx_case_id` (`case_id`)
- KEY `idx_client_id` (`client_id`)
- KEY `idx_status` (`status`)
- KEY `idx_due_date` (`due_date`)

### invoice_items

Line items for invoices.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `invoice_id` (INT, Foreign Key → invoices.id, Not Null)
- `description` (VARCHAR(255), Not Null)
- `quantity` (DECIMAL(10,2), Default: 1)
- `unit_price` (DECIMAL(10,2), Not Null)
- `total_price` (DECIMAL(10,2), Not Null)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_invoice_id` (`invoice_id`)

### invoice_payments

Payment records for invoices.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `invoice_id` (INT, Foreign Key → invoices.id, Not Null)
- `amount` (DECIMAL(10,2), Not Null)
- `payment_method` (VARCHAR(50), Not Null)
- `payment_date` (DATE, Not Null)
- `transaction_id` (VARCHAR(255), Nullable)
- `notes` (TEXT, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_invoice_id` (`invoice_id`)
- KEY `idx_payment_date` (`payment_date`)

---

## Analytics and Audit Tables

### audit_logs

Security and audit event logs.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `event_type` (VARCHAR(100), Not Null) - login, logout, create, update, delete
- `event_category` (VARCHAR(100), Not Null) - auth, case, user, system
- `event_action` (VARCHAR(100), Not Null)
- `message` (TEXT, Not Null)
- `user_id` (INT, Foreign Key → users.id, Nullable)
- `user_role` (VARCHAR(50), Nullable)
- `ip_address` (VARCHAR(45), Nullable)
- `user_agent` (VARCHAR(500), Nullable)
- `request_method` (VARCHAR(10), Nullable)
- `request_uri` (VARCHAR(500), Nullable)
- `entity_type` (VARCHAR(100), Nullable)
- `entity_id` (INT, Nullable)
- `old_values` (TEXT, Nullable) - JSON
- `new_values` (TEXT, Nullable) - JSON
- `metadata` (TEXT, Nullable) - JSON
- `severity` (ENUM, Default: 'medium') - low, medium, high, critical
- `status` (ENUM, Default: 'success') - success, failure, warning
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_event_type` (`event_type`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_created_at` (`created_at`)
- KEY `idx_entity` (`entity_type`, `entity_id`)

### analytics_events

User activity and analytics events.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key → users.id, Nullable)
- `event_type` (VARCHAR(100), Not Null)
- `event_category` (VARCHAR(100), Not Null)
- `event_action` (VARCHAR(100), Not Null)
- `event_label` (VARCHAR(255), Nullable)
- `event_value` (DECIMAL(10,2), Nullable)
- `session_id` (VARCHAR(255), Nullable)
- `page_url` (VARCHAR(500), Nullable)
- `referrer_url` (VARCHAR(500), Nullable)
- `device_type` (VARCHAR(50), Nullable) - desktop, mobile, tablet
- `browser` (VARCHAR(50), Nullable)
- `os` (VARCHAR(50), Nullable)
- `metadata` (TEXT, Nullable) - JSON
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_event_type` (`event_type`)
- KEY `idx_created_at` (`created_at`)

### analytics_metrics_cache

Cached analytics metrics for performance.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `metric_name` (VARCHAR(100), Not Null)
- `metric_value` (TEXT, Not Null) - JSON
- `calculated_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)
- `expires_at` (TIMESTAMP, Nullable)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`metric_name`)

---

## System Tables

### permissions

System permissions.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR(100), Unique, Not Null)
- `description` (TEXT, Nullable)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`name`)

### role_permissions

Role-to-permission mappings.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `role` (VARCHAR(50), Not Null)
- `permission_id` (INT, Foreign Key → permissions.id, Not Null)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`role`, `permission_id`)

### user_notifications

User notifications.

**Columns:**
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key → users.id, Not Null)
- `type` (VARCHAR(100), Not Null)
- `title` (VARCHAR(255), Not Null)
- `message` (TEXT, Not Null)
- `action_url` (VARCHAR(500), Nullable)
- `is_read` (TINYINT(1), Default: 0)
- `created_at` (TIMESTAMP, Default: CURRENT_TIMESTAMP)

**Indexes:**
- PRIMARY KEY (`id`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_is_read` (`is_read`)
- KEY `idx_created_at` (`created_at`)

---

## Table Relationships

### Entity Relationship Diagram (Text)

```
users
  ├── cases (one-to-many)
  │   ├── case_documents (one-to-many)
  │   ├── case_activities (one-to-many)
  │   ├── service_requests (one-to-many)
  │   ├── appointments (one-to-many)
  │   ├── invoices (one-to-many)
  │   └── message_threads (one-to-many)
  ├── messages (one-to-many)
  ├── user_notifications (one-to-many)
  └── audit_logs (one-to-many)

cases
  ├── assigned_to → users (many-to-one)
  └── user_id → users (many-to-one)

service_requests
  ├── case_id → cases (many-to-one)
  ├── service_id → services (many-to-one)
  └── processed_by → users (many-to-one)

invoices
  ├── case_id → cases (many-to-one)
  ├── client_id → users (many-to-one)
  └── created_by → users (many-to-one)
```

---

## Indexes and Performance

### Key Indexes

All foreign keys are indexed for join performance. Additional indexes on:
- Status columns (for filtering)
- Date columns (for date range queries)
- Search columns (for text search)

### Performance Considerations

1. **Large Tables:** `audit_logs` and `analytics_events` can grow large
   - Consider archiving old records
   - Use `analytics_metrics_cache` for aggregated data

2. **Query Optimization:**
   - Use indexes for WHERE clauses
   - Limit result sets with LIMIT
   - Use appropriate JOIN types

3. **Archiving:**
   - `audit_logs_archive` table for old audit logs
   - Stored procedure `sp_archive_audit_logs()` for archiving

---

## Stored Procedures

### sp_get_admin_dashboard_stats()

Returns dashboard statistics for admin panel.

**Returns:**
- Total users
- Active cases
- Pending requests
- Average processing hours

**Note:** Falls back to direct queries if stored procedure doesn't exist.

### sp_archive_audit_logs(days INT)

Archives audit logs older than specified days.

**Parameters:**
- `days`: Number of days to keep (logs older than this are archived)

---

## Data Migration

### Importing Database

```bash
# Import main schema
mysql -u username -p medlaw < database/medlaw\ v15.sql

# Import additional schemas
mysql -u username -p medlaw < database/medlaw_audit_analytics_reports\(in\).sql
mysql -u username -p medlaw < database/medlaw_finance_additions.sql
```

### Backup and Restore

```bash
# Backup
mysqldump -u username -p medlaw > backup_$(date +%Y%m%d).sql

# Restore
mysql -u username -p medlaw < backup_20250115.sql
```

### Schema Updates

When updating schema:
1. Backup existing database
2. Test migration on development environment
3. Apply migration to production during maintenance window
4. Verify data integrity

---

## Additional Tables

The database may include additional tables for:
- Compliance management
- Backup scheduling
- System health monitoring
- Integration status
- Contact submissions
- And more...

Refer to the actual SQL schema files for complete table definitions.

---

**Last Updated:** January 2025

