# Technical Staff Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** Developers, System Administrators, Technical Staff

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Technology Stack](#technology-stack)
3. [Code Structure](#code-structure)
4. [Database Design](#database-design)
5. [API Architecture](#api-architecture)
6. [Security Implementation](#security-implementation)
7. [Session Management](#session-management)
8. [File Upload System](#file-upload-system)
9. [Authentication Flow](#authentication-flow)
10. [Performance Optimization](#performance-optimization)
11. [Error Handling](#error-handling)
12. [Logging and Monitoring](#logging-and-monitoring)

---

## System Architecture

### Overview

MerLaws is a monolithic PHP application with a modular structure:

```
www.merlaws.com/
├── app/                    # Main application
│   ├── admin/              # Admin portal
│   ├── api/                # API endpoints
│   ├── cases/              # Case management
│   ├── documents/          # Document handling
│   ├── services/           # Service requests
│   ├── messages/           # Messaging system
│   ├── appointments/       # Appointment scheduling
│   ├── invoices/           # Invoice management
│   ├── config.php          # Core configuration
│   └── ...
├── database/               # Database schemas
├── uploads/                # File storage
├── include/                # Shared components
└── [public pages]          # Public website
```

### Request Flow

1. **User Request** → Web Server (Apache/Nginx)
2. **Web Server** → PHP-FPM/Mod_PHP
3. **PHP** → `app/config.php` (initialization)
4. **Config** → Session management, database connection
5. **Application** → Route to appropriate handler
6. **Handler** → Process request, query database
7. **Response** → Return HTML/JSON to user

### Session Architecture

The system uses **separate sessions** for admin and client contexts:

- **Client Sessions:** `MERLAWS_CLIENT` session name
- **Admin Sessions:** `MERLAWS_ADMIN` session name

This prevents session conflicts when users have both client and admin accounts.

---

## Technology Stack

### Backend

- **PHP:** 8.0+ (8.1+ recommended)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Web Server:** Apache 2.4+ or Nginx 1.18+

### Frontend

- **HTML5:** Semantic markup
- **CSS3:** Modern styling with CSS variables
- **JavaScript:** ES2023 (modern JavaScript)
- **Bootstrap:** 5.3 (UI framework)
- **Chart.js:** (for analytics charts)

### Third-Party Services

- **Resend API:** Email delivery
- **PayFast:** Payment processing
- **reCAPTCHA:** (if configured) Bot protection

### PHP Extensions Required

```ini
pdo_mysql
mbstring
xml
curl
gd
zip
json
openssl
```

---

## Code Structure

### Directory Organization

```
app/
├── admin/              # Admin portal pages
│   ├── dashboard.php
│   ├── cases.php
│   ├── users.php
│   └── ...
├── api/                # API endpoints (JSON)
│   ├── auth-login.php
│   ├── cases.php
│   ├── notifications.php
│   └── ...
├── cases/              # Case management
│   ├── create.php
│   ├── view.php
│   ├── edit.php
│   └── ...
├── config.php          # Core configuration and helpers
├── csrf.php            # CSRF protection
└── ...
```

### File Naming Conventions

- **PHP Files:** `kebab-case.php` (e.g., `create-case.php`)
- **Directories:** `kebab-case/` (e.g., `case-management/`)
- **Functions:** `snake_case()` (e.g., `get_user_id()`)
- **Variables:** `$camelCase` or `$snake_case`
- **Constants:** `UPPER_CASE` (e.g., `DB_HOST`)

### Code Organization

#### Configuration File (`app/config.php`)

Contains:
- Database configuration
- Helper functions
- Authentication helpers
- Case management functions
- Document management functions
- Notification functions
- Audit logging functions
- Analytics functions

#### CSRF Protection (`app/csrf.php`)

Handles:
- CSRF token generation
- Token validation
- Token storage in session

---

## Database Design

### Database Connection

Uses PDO with prepared statements:

```php
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    return $pdo;
}
```

### Core Tables

#### Users Table
- `id` (Primary Key)
- `email` (Unique)
- `password_hash`
- `name`
- `role`
- `is_active`
- `created_at`, `updated_at`

#### Cases Table
- `id` (Primary Key)
- `user_id` (Foreign Key → users)
- `title`
- `description`
- `case_type`
- `status`
- `priority`
- `assigned_to` (Foreign Key → users)
- `created_at`, `updated_at`

#### Case Documents Table
- `id` (Primary Key)
- `case_id` (Foreign Key → cases)
- `filename`
- `original_filename`
- `file_path`
- `file_size`
- `checksum` (SHA256)
- `uploaded_by` (Foreign Key → users)
- `uploaded_at`

#### Service Requests Table
- `id` (Primary Key)
- `case_id` (Foreign Key → cases)
- `service_id` (Foreign Key → services)
- `status` (cart, pending, approved, rejected)
- `requested_at`
- `processed_at`
- `processed_by` (Foreign Key → users)

#### Messages Table
- `id` (Primary Key)
- `thread_id` (Foreign Key → message_threads)
- `sender_id` (Foreign Key → users)
- `message` (text)
- `created_at`
- `read_at`

#### Invoices Table
- `id` (Primary Key)
- `case_id` (Foreign Key → cases)
- `client_id` (Foreign Key → users)
- `invoice_number` (Unique)
- `amount`
- `status`
- `due_date`
- `created_at`

#### Audit Logs Table
- `id` (Primary Key)
- `event_type`
- `event_category`
- `event_action`
- `user_id` (Foreign Key → users)
- `ip_address`
- `message`
- `metadata` (JSON)
- `created_at`

### Database Relationships

```
users
  ├── cases (one-to-many)
  ├── case_documents (one-to-many via cases)
  ├── service_requests (one-to-many via cases)
  ├── messages (one-to-many)
  └── invoices (one-to-many)

cases
  ├── case_documents (one-to-many)
  ├── service_requests (one-to-many)
  ├── appointments (one-to-many)
  ├── messages (one-to-many via threads)
  └── invoices (one-to-many)
```

---

## API Architecture

### API Endpoints

All API endpoints return JSON:

```json
{
    "success": true|false,
    "data": {...},
    "message": "Success message",
    "error": "Error message"
}
```

### Authentication

Most API endpoints require authentication:

```php
require_login();  // Checks if user is logged in
require_admin();  // Checks if user is admin
require_permission('permission_name');  // Checks specific permission
```

### API Endpoints List

#### Authentication
- `POST /app/api/auth-login.php` - User login
- `POST /app/api/logout.php` - User logout

#### Cases
- `GET /app/api/cases.php?action=get_attorneys&case_id=X` - Get case attorneys
- `GET /app/api/get_cases.php` - Get active cases

#### Notifications
- `GET /app/api/notifications.php` - Get user notifications
- `POST /app/api/notifications.php?action=mark_read` - Mark notification as read

#### Appointments
- `GET /app/api/appointments.php` - Get appointments
- `POST /app/api/appointments.php` - Create appointment

#### Analytics
- `GET /app/api/admin-analytics.php?action=dashboard_stats` - Dashboard statistics
- `GET /app/api/admin-analytics.php?action=user_activity` - User activity data

#### Reports
- `GET /app/api/export-report.php?type=overview&format=csv` - Export report

### API Response Format

**Success Response:**
```json
{
    "success": true,
    "data": {
        "key": "value"
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "error": "Error message",
    "errors": ["Error 1", "Error 2"]
}
```

---

## Security Implementation

### Authentication

- **Password Hashing:** PHP `password_hash()` with `PASSWORD_DEFAULT`
- **Session Management:** Secure session cookies
- **Login Rate Limiting:** File-based rate limiting
- **Context Separation:** Separate admin/client sessions

### Authorization

- **Role-Based Access Control (RBAC):** Role-based permissions
- **Permission System:** Granular permission checks
- **Route Protection:** `require_login()`, `require_admin()`, `require_permission()`

### Data Protection

- **SQL Injection Prevention:** PDO prepared statements
- **XSS Protection:** `htmlspecialchars()` output escaping
- **CSRF Protection:** Token-based CSRF protection
- **File Upload Security:** Type validation, size limits, secure storage

### Security Headers

Configured in web server:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000`

---

## Session Management

### Session Initialization

```php
function init_session_for_context(string $context): void {
    $sessionName = $context === 'admin' ? 'MERLAWS_ADMIN' : 'MERLAWS_CLIENT';
    session_name($sessionName);
    session_set_cookie_params([...]);
    session_start();
}
```

### Session Data Structure

**Client Session:**
```php
$_SESSION['user_id'] = 123;
$_SESSION['name'] = 'John Doe';
$_SESSION['email'] = 'john@example.com';
$_SESSION['role'] = 'client';
```

**Admin Session:**
```php
$_SESSION['user_id'] = 456;
$_SESSION['name'] = 'Admin User';
$_SESSION['email'] = 'admin@merlaws.com';
$_SESSION['role'] = 'super_admin';
$_SESSION['permissions'] = ['permission1', 'permission2'];
```

### Session Security

- **HttpOnly:** Prevents JavaScript access
- **Secure:** HTTPS only (when HTTPS detected)
- **SameSite:** Lax (CSRF protection)
- **Path:** Restricted to application path

---

## File Upload System

### Upload Process

1. **Validation:**
   - File type check (extension)
   - File size check
   - MIME type validation

2. **Storage:**
   - Generate unique filename
   - Create case-specific directory
   - Move uploaded file
   - Calculate SHA256 checksum

3. **Database:**
   - Insert document record
   - Link to case
   - Store metadata

### Upload Configuration

```php
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);  // 100MB
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/cases/documents/');
```

### File Storage Structure

```
uploads/
└── cases/
    └── documents/
        └── {case_id}/
            └── {unique_filename}_{timestamp}.{ext}
```

---

## Authentication Flow

### Login Process

1. **User submits credentials** → `app/api/auth-login.php`
2. **CSRF validation** → Check CSRF token
3. **Rate limiting check** → Prevent brute force
4. **Database lookup** → Find user by email
5. **Password verification** → `password_verify()`
6. **Context detection** → Admin or client
7. **Session initialization** → Create appropriate session
8. **Session data storage** → Store user info
9. **Audit logging** → Log login event
10. **Response** → JSON success/error

### Logout Process

1. **User clicks logout** → `app/logout-client.php` or `app/logout-admin.php`
2. **Audit logging** → Log logout event
3. **Session destruction** → `session_destroy()`
4. **Redirect** → Login page

---

## Performance Optimization

### Database Optimization

- **Indexes:** Key columns indexed
- **Prepared Statements:** Reusable queries
- **Connection Pooling:** Single PDO instance (static)
- **Query Optimization:** Efficient queries

### Caching

- **OPcache:** PHP opcode caching (recommended)
- **Session Storage:** File-based (can use Redis/Memcached)
- **Analytics Cache:** `analytics_metrics_cache` table

### Frontend Optimization

- **Bootstrap CDN:** External CDN for Bootstrap
- **Minified Assets:** (if implemented)
- **Lazy Loading:** (if implemented)

---

## Error Handling

### PHP Error Handling

```php
try {
    // Code that may throw exception
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Handle error
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    // Handle error
}
```

### Error Logging

- **PHP Errors:** Logged to system error log
- **Application Errors:** Custom error logging
- **Database Errors:** Caught and logged
- **Audit Logs:** Security and audit events

### User-Friendly Errors

- **Generic Messages:** Don't expose system details
- **Error Pages:** Custom error pages (if implemented)
- **JSON Errors:** API returns error JSON

---

## Logging and Monitoring

### Audit Logging

```php
log_audit_event('create', 'case_created', 'Case created', [
    'category' => 'case',
    'entity_type' => 'case',
    'entity_id' => $case_id,
    'severity' => 'medium'
]);
```

### Analytics Logging

```php
log_analytics_event('case_created', 'create_case', [
    'category' => 'case',
    'label' => 'Case Type: Medical Negligence'
]);
```

### Log Tables

- **audit_logs:** Security and audit events
- **analytics_events:** User activity and analytics
- **security_logs:** (Fallback if audit_logs doesn't exist)

### Monitoring

- **System Health:** Health check endpoints (if implemented)
- **Performance Metrics:** Query execution times
- **Error Rates:** Track error frequency
- **User Activity:** Track user actions

---

## Development Guidelines

### Code Standards

- **PSR Standards:** Follow PSR-12 coding standards
- **Comments:** Document complex logic
- **Function Names:** Descriptive, snake_case
- **Variable Names:** Clear, meaningful

### Database Guidelines

- **Always use prepared statements**
- **Never concatenate user input into SQL**
- **Use transactions for multi-step operations**
- **Index frequently queried columns**

### Security Guidelines

- **Validate all user input**
- **Escape all output**
- **Use CSRF tokens on forms**
- **Check permissions before actions**
- **Log security events**

---

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check credentials
   - Verify MySQL service
   - Check firewall

2. **Session Issues**
   - Check session directory permissions
   - Verify cookie settings
   - Check for session conflicts

3. **File Upload Fails**
   - Check PHP upload settings
   - Verify directory permissions
   - Check disk space

See [Troubleshooting Guide](TROUBLESHOOTING.md) for more details.

---

**Last Updated:** January 2025

