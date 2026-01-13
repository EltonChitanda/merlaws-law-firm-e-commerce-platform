# Configuration Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** System Administrators, Developers

---

## Table of Contents

1. [Configuration File Overview](#configuration-file-overview)
2. [Database Configuration](#database-configuration)
3. [Email Configuration](#email-configuration)
4. [File Upload Configuration](#file-upload-configuration)
5. [Security Settings](#security-settings)
6. [Session Management](#session-management)
7. [Payment Gateway Configuration](#payment-gateway-configuration)
8. [Timezone and Localization](#timezone-and-localization)
9. [Environment Variables](#environment-variables)
10. [Advanced Configuration](#advanced-configuration)

---

## Configuration File Overview

The main configuration file is located at `app/config.php`. This file contains all system-wide settings and helper functions.

### File Structure

```php
app/config.php
├── Database Configuration
├── Email Settings
├── File Upload Settings
├── Session Configuration
├── Helper Functions
│   ├── Authentication Helpers
│   ├── Case Management Functions
│   ├── Document Management Functions
│   ├── Service Request Functions
│   ├── Notification Functions
│   ├── Audit Logging Functions
│   └── Analytics Functions
└── Optional Middleware
```

---

## Database Configuration

### Basic Database Settings

Located at the top of `app/config.php`:

```php
// Database credentials
define('DB_HOST', '127.0.0.1');      // Database host (use 127.0.0.1 instead of localhost for better performance)
define('DB_NAME', 'medlaw');          // Database name
define('DB_USER', 'root');            // Database username
define('DB_PASS', '');                // Database password
```

### Connection Options

The database connection uses PDO with the following options:

```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION        // Throw exceptions on errors
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC   // Return associative arrays
PDO::ATTR_EMULATE_PREPARES => false                // Use native prepared statements
PDO::ATTR_TIMEOUT => 3                             // Connection timeout in seconds
```

### Connection Fallback

The system automatically tries multiple connection methods:

1. Primary connection (DB_HOST)
2. Fallback to `localhost` if primary fails

### Security Best Practices

- **Never commit credentials to version control**
- Use environment variables for production:
  ```php
  define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
  define('DB_NAME', getenv('DB_NAME') ?: 'medlaw');
  define('DB_USER', getenv('DB_USER') ?: 'root');
  define('DB_PASS', getenv('DB_PASS') ?: '');
  ```

---

## Email Configuration

### Resend API Configuration

```php
// Email settings
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'no-reply@merlaws.com');
```

### Setting Up Resend API

1. **Sign up for Resend account:**
   - Visit https://resend.com
   - Create account and verify email
   - Navigate to API Keys section

2. **Generate API Key:**
   - Create new API key
   - Copy the key (only shown once)

3. **Configure in System:**
   ```bash
   # Set environment variable
   export RESEND_API_KEY="re_xxxxxxxxxxxxx"
   export RESEND_FROM_EMAIL="no-reply@merlaws.com"
   ```

   Or set in `app/config.php`:
   ```php
   define('RESEND_API_KEY', 're_xxxxxxxxxxxxx');
   define('RESEND_FROM_EMAIL', 'no-reply@merlaws.com');
   ```

4. **Verify Domain (Production):**
   - Add DNS records in Resend dashboard
   - Verify domain ownership
   - Update FROM_EMAIL to use verified domain

### Email Functionality

The system uses Resend API for:
- Password reset emails
- Notification emails
- Invoice/payment confirmations
- Case update notifications
- Appointment reminders

**Note:** Email functionality requires proper API key configuration. See [Communication System Analysis](../COMMUNICATION_SYSTEM_ANALYSIS.md) for implementation status.

---

## File Upload Configuration

### Upload Settings

```php
// File upload settings
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);  // 100MB maximum file size
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);  // Allowed file extensions
define('UPLOAD_PATH', __DIR__ . '/../uploads/cases/documents/');  // Upload directory
```

### Configuration Options

#### Maximum File Size

```php
// Examples:
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);   // 10MB
define('UPLOAD_MAX_SIZE', 50 * 1024 * 1024);   // 50MB
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);  // 100MB (current)
```

**Note:** Also configure PHP settings:
```ini
upload_max_filesize = 100M
post_max_size = 100M
```

#### Allowed File Types

```php
// Current: PDF and JPEG images only
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);

// Example: Add more types
define('UPLOAD_ALLOWED_TYPES', [
    'pdf', 'doc', 'docx',           // Documents
    'jpeg', 'jpg', 'png', 'gif',    // Images
    'txt', 'rtf'                     // Text files
]);
```

#### Upload Path

```php
// Default path (relative to config.php)
define('UPLOAD_PATH', __DIR__ . '/../uploads/cases/documents/');

// Absolute path example
define('UPLOAD_PATH', '/var/www/uploads/merlaws/documents/');
```

### File Upload Security

The system implements:
- File type validation (extension check)
- File size validation
- Unique filename generation
- SHA256 checksum calculation
- Secure file storage (outside web root recommended)

---

## Security Settings

### CSRF Protection

CSRF protection is handled by `app/csrf.php`. Configuration:

```php
// CSRF token lifetime (default: 3600 seconds = 1 hour)
// Configured in csrf.php
```

### Session Security

Session configuration in `app/config.php`:

```php
session_set_cookie_params([
    'lifetime' => 0,                    // Session cookie lifetime (0 = until browser closes)
    'path' => '/www.merlaws.com',       // Cookie path
    'domain' => '',                     // Cookie domain (empty = current domain)
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',  // HTTPS only
    'httponly' => true,                 // HTTP-only cookies (prevent JavaScript access)
    'samesite' => 'Lax'                  // CSRF protection
]);
```

### Separate Admin/Client Sessions

The system uses separate session names:
- Client sessions: `MERLAWS_CLIENT`
- Admin sessions: `MERLAWS_ADMIN`

This prevents session conflicts between admin and client logins.

### Password Security

Password hashing uses PHP's `password_hash()`:
- Algorithm: `PASSWORD_DEFAULT` (currently bcrypt)
- Cost factor: Default (10 rounds)

---

## Session Management

### Context Detection

The system automatically detects context (admin vs client):

```php
function get_context(): string {
    // Checks URL path, query parameters, and script name
    // Returns 'admin' or 'client'
}
```

### Session Initialization

```php
function init_session_for_context(string $context): void {
    // Sets appropriate session name
    // Configures session cookie parameters
    // Starts session
}
```

### Session Configuration

- **Session Name:** Context-specific (`MERLAWS_ADMIN` or `MERLAWS_CLIENT`)
- **Cookie Path:** `/www.merlaws.com`
- **Secure Flag:** Enabled when HTTPS is detected
- **HttpOnly:** Enabled (prevents JavaScript access)
- **SameSite:** `Lax` (CSRF protection)

---

## Payment Gateway Configuration

### PayFast Integration

PayFast is integrated for invoice payments. Configuration is typically in payment processing files.

#### PayFast Settings

1. **Merchant Credentials:**
   - Merchant ID
   - Merchant Key
   - Passphrase (if configured)

2. **Environment:**
   - Sandbox URL: `https://sandbox.payfast.co.za`
   - Production URL: `https://www.payfast.co.za`

3. **Configuration Location:**
   - Check `app/services/PayFastService.php`
   - Check `app/payment.php`
   - Check `app/payment-notify.php`

#### PayFast Configuration Example

```php
// In payment processing files
$merchant_id = 'your_merchant_id';
$merchant_key = 'your_merchant_key';
$passphrase = 'your_passphrase';  // Optional
$environment = 'production';  // or 'sandbox'
```

---

## Timezone and Localization

### Timezone Configuration

```php
date_default_timezone_set('Africa/Johannesburg');
```

### Supported Timezones

Change timezone as needed:

```php
// South Africa
date_default_timezone_set('Africa/Johannesburg');

// Other options
date_default_timezone_set('UTC');
date_default_timezone_set('America/New_York');
date_default_timezone_set('Europe/London');
```

### Currency Formatting

Currency formatting is handled by helper functions:

```php
function format_currency(float $amount): string {
    return 'R' . number_format($amount, 2);  // South African Rand format
}
```

---

## Environment Variables

### Recommended Environment Variables

For production, use environment variables instead of hardcoded values:

```bash
# Database
export DB_HOST="127.0.0.1"
export DB_NAME="medlaw"
export DB_USER="merlaws_user"
export DB_PASS="secure_password"

# Email
export RESEND_API_KEY="re_xxxxxxxxxxxxx"
export RESEND_FROM_EMAIL="no-reply@merlaws.com"

# Application
export APP_ENV="production"
export APP_DEBUG="false"
```

### Setting Environment Variables

#### Linux/Unix

**Option 1: System-wide (`/etc/environment`)**
```bash
sudo nano /etc/environment
# Add variables
DB_HOST=127.0.0.1
DB_NAME=medlaw
# etc.
```

**Option 2: User-specific (`~/.bashrc` or `~/.profile`)**
```bash
nano ~/.bashrc
# Add export statements
export DB_HOST="127.0.0.1"
export DB_NAME="medlaw"
```

**Option 3: Application-specific (`.env` file)**
```bash
# Create .env file in application root
cat > .env << EOF
DB_HOST=127.0.0.1
DB_NAME=medlaw
DB_USER=merlaws_user
DB_PASS=secure_password
RESEND_API_KEY=re_xxxxxxxxxxxxx
RESEND_FROM_EMAIL=no-reply@merlaws.com
EOF
```

Then load in PHP:
```php
// Load .env file (requires parsing library or manual implementation)
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    putenv("$key=$value");
}
```

#### Windows

**Option 1: System Environment Variables**
1. Right-click "This PC" → Properties
2. Advanced System Settings → Environment Variables
3. Add variables

**Option 2: Command Prompt (Temporary)**
```cmd
set DB_HOST=127.0.0.1
set DB_NAME=medlaw
```

**Option 3: PowerShell (Temporary)**
```powershell
$env:DB_HOST = "127.0.0.1"
$env:DB_NAME = "medlaw"
```

### Using Environment Variables in config.php

```php
// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'medlaw');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Email settings
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'no-reply@merlaws.com');
```

---

## Advanced Configuration

### PHP Settings

Recommended PHP settings (in `php.ini` or `.user.ini`):

```ini
; File Uploads
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 20

; Execution
max_execution_time = 300
memory_limit = 256M

; Timezone
date.timezone = Africa/Johannesburg

; Error Reporting (Production)
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Session
session.gc_maxlifetime = 1440
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### Database Connection Pooling

For high-traffic sites, consider connection pooling:

```php
// Example with persistent connections (use with caution)
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_PERSISTENT => true,  // Persistent connections
    // ... other options
]);
```

### Caching Configuration

For performance optimization, consider implementing:

1. **OPcache** (PHP opcode cache)
   ```ini
   ; In php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Application-level caching**
   - Cache frequently accessed data
   - Use Redis or Memcached for distributed caching

### Logging Configuration

Audit and analytics logging is configured in `app/config.php`:

```php
// Audit logging function
log_audit_event($event_type, $event_action, $message, $options);

// Analytics logging function
log_analytics_event($event_type, $event_action, $options);
```

Log tables:
- `audit_logs` - Security and audit events
- `analytics_events` - User activity and analytics

---

## Configuration Verification

### Verify Configuration

1. **Database Connection:**
   ```php
   <?php
   require 'app/config.php';
   try {
       $pdo = db();
       echo "Database connection successful!";
   } catch (Exception $e) {
       echo "Database connection failed: " . $e->getMessage();
   }
   ?>
   ```

2. **File Upload:**
   - Test file upload functionality
   - Verify file size limits
   - Check file type restrictions

3. **Email Configuration:**
   - Test password reset email
   - Check Resend API key validity
   - Verify FROM_EMAIL domain

4. **Session Management:**
   - Test client login
   - Test admin login
   - Verify sessions don't conflict

---

## Troubleshooting Configuration

### Common Issues

1. **Database Connection Failed**
   - Verify credentials
   - Check MySQL service status
   - Verify user permissions

2. **File Upload Fails**
   - Check PHP upload settings
   - Verify directory permissions
   - Check disk space

3. **Email Not Sending**
   - Verify API key
   - Check API quota
   - Verify domain configuration

4. **Session Issues**
   - Check session directory permissions
   - Verify cookie settings
   - Check for session conflicts

See [Troubleshooting Guide](TROUBLESHOOTING.md) for more details.

---

## Security Best Practices

1. **Never commit credentials to version control**
2. **Use environment variables for sensitive data**
3. **Enable HTTPS in production**
4. **Use strong database passwords**
5. **Regularly update API keys**
6. **Monitor audit logs**
7. **Restrict file upload types**
8. **Use prepared statements (already implemented)**
9. **Enable CSRF protection (already implemented)**
10. **Keep PHP and dependencies updated**

---

## Next Steps

After configuration:

1. Review [Security Documentation](SECURITY.md)
2. Test all functionality
3. Set up monitoring
4. Configure backups
5. Review [Maintenance Guide](MAINTENANCE.md)

---

**Last Updated:** January 2025

