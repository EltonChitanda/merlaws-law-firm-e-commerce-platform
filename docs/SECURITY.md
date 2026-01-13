# Security Documentation

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** System Administrators, Developers, Security Personnel

---

## Table of Contents

1. [Security Overview](#security-overview)
2. [Authentication](#authentication)
3. [Authorization and RBAC](#authorization-and-rbac)
4. [Data Protection](#data-protection)
5. [File Upload Security](#file-upload-security)
6. [Session Security](#session-security)
7. [CSRF Protection](#csrf-protection)
8. [SQL Injection Prevention](#sql-injection-prevention)
9. [XSS Protection](#xss-protection)
10. [Audit Logging](#audit-logging)
11. [Security Best Practices](#security-best-practices)
12. [Incident Response](#incident-response)

---

## Security Overview

### Security Principles

The MerLaws system implements multiple layers of security:

1. **Defense in Depth:** Multiple security layers
2. **Least Privilege:** Users have minimum necessary access
3. **Input Validation:** All user input is validated
4. **Output Escaping:** All output is escaped
5. **Secure by Default:** Secure configurations by default

### Security Features

- ✅ Password hashing (bcrypt)
- ✅ Session-based authentication
- ✅ Role-based access control (RBAC)
- ✅ CSRF protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ Secure file uploads
- ✅ Audit logging
- ✅ Rate limiting (login)
- ✅ Secure session management

---

## Authentication

### Password Security

**Hashing Algorithm:**
- Uses PHP's `password_hash()` with `PASSWORD_DEFAULT`
- Currently bcrypt with cost factor 10
- Automatically upgrades to stronger algorithms as PHP updates

**Password Requirements:**
- Minimum 8 characters (enforced client-side and server-side)
- No maximum length (handled by hashing)
- Strong passwords recommended

**Password Storage:**
```php
// Never store plain text passwords
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Verify passwords
if (password_verify($input_password, $stored_hash)) {
    // Password correct
}
```

### Login Security

**Rate Limiting:**
- 5 failed attempts per 15 minutes per IP/email
- Stored in temporary files
- Prevents brute force attacks

**Session Management:**
- Separate sessions for admin and client
- Secure session cookies
- Session timeout on browser close

**Login Audit:**
- All login attempts logged
- Failed logins logged with medium severity
- Successful logins logged with low severity
- IP address and user agent recorded

---

## Authorization and RBAC

### Role-Based Access Control

**System Roles:**
- `client` - Client users
- `super_admin` - Full system access
- `admin` - Administrative access
- `attorney` - Case handling
- `paralegal` - Case support
- `billing` - Finance access
- `case_manager` - Case coordination
- And more...

### Permission System

**Permission Checks:**
```php
// Check if user has permission
if (has_permission('invoice:create')) {
    // Allow action
}

// Require permission (redirects if not)
require_permission('invoice:create');
```

**Permission Categories:**
- `case:*` - Case permissions
- `user:*` - User management
- `invoice:*` - Invoice permissions
- `payment:*` - Payment permissions
- `system:*` - System permissions

### Access Control Functions

**Login Requirements:**
```php
require_login();  // Requires user to be logged in
require_admin();  // Requires admin role
require_permission('permission_name');  // Requires specific permission
```

**Role Checks:**
```php
is_logged_in();  // Check if logged in
is_admin();  // Check if admin role
get_user_role();  // Get current user role
has_permission('permission');  // Check permission
```

---

## Data Protection

### Input Validation

**Server-Side Validation:**
- All user input validated on server
- Type checking
- Length validation
- Format validation (email, etc.)

**Example:**
```php
$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address';
}
```

### Output Escaping

**HTML Escaping:**
```php
// Escape output
echo htmlspecialchars($user_input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Helper function
echo e($user_input);  // Shortcut for htmlspecialchars
```

**JSON Encoding:**
```php
// Safe JSON encoding
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
```

### Data Encryption

**Sensitive Data:**
- Passwords: Hashed (not encrypted, one-way)
- Session data: Stored in secure session files
- File uploads: Stored securely outside web root (recommended)

**Database:**
- Use SSL/TLS for database connections (recommended for production)
- Encrypt sensitive columns if needed

---

## File Upload Security

### Upload Validation

**File Type Validation:**
- Extension checking
- MIME type validation
- File content validation (if implemented)

**Allowed Types:**
```php
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);
```

**File Size Limits:**
```php
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);  // 100MB
```

### Secure Storage

**File Storage:**
- Files stored outside web root (recommended)
- Unique filenames generated
- SHA256 checksum calculated
- Case-specific directories

**File Access:**
- Access controlled by application
- Users can only access their own files
- Admin access controlled by permissions

### Security Measures

1. **Type Validation:** Only allowed file types
2. **Size Limits:** Maximum file size enforced
3. **Unique Filenames:** Prevent overwrites and directory traversal
4. **Checksum:** Verify file integrity
5. **Access Control:** Permission-based file access

---

## Session Security

### Session Configuration

**Secure Session Settings:**
```php
session_set_cookie_params([
    'lifetime' => 0,  // Until browser closes
    'path' => '/www.merlaws.com',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']),  // HTTPS only
    'httponly' => true,  // No JavaScript access
    'samesite' => 'Lax'  // CSRF protection
]);
```

### Separate Sessions

**Admin and Client Sessions:**
- Different session names prevent conflicts
- `MERLAWS_ADMIN` for admin sessions
- `MERLAWS_CLIENT` for client sessions

### Session Management

**Session Initialization:**
- Context-aware session creation
- Automatic session cleanup
- Secure session storage

**Session Data:**
- Only store necessary data
- No sensitive data in session (except user ID)
- Session data validated on each request

---

## CSRF Protection

### CSRF Token System

**Token Generation:**
- Unique token per session
- Stored in session
- Included in forms

**Token Validation:**
```php
// Validate CSRF token
if (!csrf_validate()) {
    // Invalid token, reject request
}
```

### Implementation

**In Forms:**
```html
<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
```

**In JavaScript:**
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
```

**Validation:**
- All POST requests validated
- Token must match session token
- Token expires with session

---

## SQL Injection Prevention

### Prepared Statements

**Always Use Prepared Statements:**
```php
// ✅ CORRECT - Prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ WRONG - String concatenation (vulnerable)
$stmt = $pdo->query("SELECT * FROM users WHERE email = '$email'");
```

### PDO Configuration

**Secure PDO Settings:**
```php
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
    // ...
];
```

### Best Practices

1. **Never concatenate user input into SQL**
2. **Always use prepared statements**
3. **Validate input before database queries**
4. **Use parameterized queries**
5. **Escape only when necessary (prepared statements preferred)**

---

## XSS Protection

### Output Escaping

**HTML Escaping:**
```php
// Escape all user-generated content
echo htmlspecialchars($user_input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Helper function
echo e($user_input);
```

### Content Security

**Context-Aware Escaping:**
- HTML context: `htmlspecialchars()`
- JavaScript context: `json_encode()`
- URL context: `urlencode()`
- Attribute context: `htmlspecialchars()` with quotes

### Security Headers

**XSS Protection Headers:**
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
```

---

## Audit Logging

### Audit Event Logging

**Log Function:**
```php
log_audit_event('create', 'case_created', 'Case created', [
    'category' => 'case',
    'entity_type' => 'case',
    'entity_id' => $case_id,
    'severity' => 'medium'
]);
```

### Logged Events

**Authentication Events:**
- Login attempts (success/failure)
- Logout events
- Password reset requests

**Data Events:**
- Case creation/updates
- Document uploads
- User management actions
- Invoice creation/payments

**Security Events:**
- Permission changes
- Role changes
- Access denied attempts
- Suspicious activity

### Audit Log Data

**Logged Information:**
- Event type and action
- User ID and role
- IP address
- User agent
- Timestamp
- Entity information
- Old/new values (for updates)
- Metadata (JSON)

---

## Security Best Practices

### For Administrators

1. **Use Strong Passwords**
2. **Enable HTTPS** in production
3. **Keep Software Updated**
4. **Regular Backups**
5. **Monitor Audit Logs**
6. **Restrict File Permissions**
7. **Use Environment Variables** for sensitive config
8. **Regular Security Audits**

### For Developers

1. **Validate All Input**
2. **Escape All Output**
3. **Use Prepared Statements**
4. **Implement CSRF Protection**
5. **Follow Principle of Least Privilege**
6. **Log Security Events**
7. **Keep Dependencies Updated**
8. **Code Review Security-Critical Changes**

### For Users

1. **Use Strong, Unique Passwords**
2. **Don't Share Credentials**
3. **Log Out on Shared Computers**
4. **Report Suspicious Activity**
5. **Keep Browsers Updated**

---

## Incident Response

### Security Incident Procedure

1. **Identify Incident**
   - Unusual activity
   - Failed login attempts
   - Data breaches
   - System compromises

2. **Contain Incident**
   - Disable affected accounts
   - Block suspicious IPs
   - Isolate affected systems

3. **Investigate**
   - Review audit logs
   - Check system logs
   - Analyze attack vectors

4. **Remediate**
   - Fix vulnerabilities
   - Reset compromised credentials
   - Restore from backups if needed

5. **Document**
   - Document incident
   - Update security procedures
   - Notify affected users (if required)

### Reporting Security Issues

**Report to:**
- System administrator
- IT security team
- Management (for serious incidents)

**Include:**
- Description of issue
- Steps to reproduce
- Affected systems/users
- Evidence (screenshots, logs)

---

## Security Checklist

### Installation Security

- [ ] HTTPS enabled
- [ ] Strong database passwords
- [ ] File permissions correct
- [ ] Environment variables used
- [ ] Default admin password changed
- [ ] Firewall configured

### Configuration Security

- [ ] CSRF protection enabled
- [ ] Session security configured
- [ ] File upload restrictions set
- [ ] Rate limiting enabled
- [ ] Audit logging enabled
- [ ] Error reporting disabled in production

### Ongoing Security

- [ ] Regular security updates
- [ ] Audit log monitoring
- [ ] Backup verification
- [ ] Access review
- [ ] Security scanning
- [ ] Penetration testing (periodic)

---

**Last Updated:** January 2025

