# Installation & Deployment Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** System Administrators

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Web Server Configuration](#web-server-configuration)
5. [SSL/HTTPS Setup](#sslhttps-setup)
6. [Post-Installation Configuration](#post-installation-configuration)
7. [Verification Checklist](#verification-checklist)
8. [Troubleshooting Installation](#troubleshooting-installation)

---

## Prerequisites

### System Requirements

#### Server Requirements
- **Operating System:** Linux (Ubuntu 20.04+, CentOS 7+, Debian 10+) or Windows Server 2016+
- **PHP:** 8.0 or higher (8.1+ recommended)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Memory:** Minimum 2GB RAM (4GB+ recommended for production)
- **Disk Space:** Minimum 10GB free space (20GB+ recommended)
- **SSL Certificate:** Required for production (Let's Encrypt recommended)

#### PHP Extensions Required
```bash
php8.0-mysql (or php8.1-mysql)
php8.0-pdo
php8.0-mbstring
php8.0-xml
php8.0-curl
php8.0-gd
php8.0-zip
php8.0-json
php8.0-openssl
```

#### Software Dependencies
- **Composer:** Optional (for future dependency management)
- **Git:** Recommended for version control

### Pre-Installation Checklist

- [ ] Server meets minimum requirements
- [ ] PHP 8.0+ installed with required extensions
- [ ] MySQL/MariaDB installed and running
- [ ] Web server (Apache/Nginx) installed and running
- [ ] SSL certificate obtained (for production)
- [ ] Domain name configured (for production)
- [ ] Firewall rules configured
- [ ] Backup strategy planned

---

## Installation Steps

### Step 1: Download/Clone the Application

#### Option A: From Git Repository
```bash
cd /var/www
git clone <repository-url> www.merlaws.com
cd www.merlaws.com
```

#### Option B: Upload Files via FTP/SFTP
1. Upload all files to your web server directory
2. Ensure file permissions are correct (see Step 3)

### Step 2: Set File Permissions

```bash
# Navigate to application directory
cd /path/to/www.merlaws.com

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Set special permissions for uploads directory
chmod -R 755 uploads/
chmod -R 755 uploads/cases/
chmod -R 755 uploads/cases/documents/

# Set permissions for storage directory (if exists)
chmod -R 755 storage/

# Ensure web server can write to uploads
chown -R www-data:www-data uploads/  # For Apache (adjust user/group as needed)
# OR
chown -R nginx:nginx uploads/  # For Nginx
```

### Step 3: Create Required Directories

```bash
# Create upload directories if they don't exist
mkdir -p uploads/cases/documents
mkdir -p storage/availability
mkdir -p storage/logs

# Set permissions
chmod -R 755 uploads/
chmod -R 755 storage/
```

---

## Database Setup

### Step 1: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE medlaw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create database user (recommended)
CREATE USER 'merlaws_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON medlaw.* TO 'merlaws_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Import Database Schema

```bash
# Import the main database schema
mysql -u merlaws_user -p medlaw < database/medlaw\ v15.sql

# Import additional schema files if needed
mysql -u merlaws_user -p medlaw < database/medlaw_audit_analytics_reports\(in\).sql
mysql -u merlaws_user -p medlaw < database/medlaw_finance_additions.sql
```

**Note:** Adjust the SQL file names based on your actual database files.

### Step 3: Verify Database Import

```bash
mysql -u merlaws_user -p medlaw -e "SHOW TABLES;"
```

You should see tables like:
- `users`
- `cases`
- `case_documents`
- `service_requests`
- `appointments`
- `messages`
- `invoices`
- `audit_logs`
- `analytics_events`
- And many more...

---

## Web Server Configuration

### Apache Configuration

#### 1. Enable Required Modules

```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

#### 2. Create Virtual Host Configuration

Create `/etc/apache2/sites-available/merlaws.conf`:

```apache
<VirtualHost *:80>
    ServerName www.merlaws.com
    ServerAlias merlaws.com
    DocumentRoot /var/www/www.merlaws.com
    
    <Directory /var/www/www.merlaws.com>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Redirect to HTTPS (for production)
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName www.merlaws.com
    ServerAlias merlaws.com
    DocumentRoot /var/www/www.merlaws.com
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/merlaws.crt
    SSLCertificateKeyFile /etc/ssl/private/merlaws.key
    SSLCertificateChainFile /etc/ssl/certs/merlaws-chain.crt
    
    <Directory /var/www/www.merlaws.com>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</VirtualHost>
```

#### 3. Enable Site and Restart

```bash
sudo a2ensite merlaws.conf
sudo systemctl restart apache2
```

### Nginx Configuration

Create `/etc/nginx/sites-available/merlaws`:

```nginx
# HTTP to HTTPS redirect
server {
    listen 80;
    server_name www.merlaws.com merlaws.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name www.merlaws.com merlaws.com;
    root /var/www/www.merlaws.com;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/merlaws.crt;
    ssl_certificate_key /etc/ssl/private/merlaws.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Upload size limit
    client_max_body_size 100M;
}
```

Enable and restart:

```bash
sudo ln -s /etc/nginx/sites-available/merlaws /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## SSL/HTTPS Setup

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache  # For Apache
# OR
sudo apt-get install certbot python3-certbot-nginx  # For Nginx

# Obtain certificate
sudo certbot --apache -d www.merlaws.com -d merlaws.com  # For Apache
# OR
sudo certbot --nginx -d www.merlaws.com -d merlaws.com  # For Nginx

# Auto-renewal (should be set up automatically)
sudo certbot renew --dry-run
```

### Using Commercial SSL Certificate

1. Purchase SSL certificate from provider
2. Generate Certificate Signing Request (CSR)
3. Submit CSR to certificate authority
4. Install certificate files on server
5. Configure web server to use certificate
6. Test SSL configuration

### Verify SSL Configuration

```bash
# Test SSL
openssl s_client -connect www.merlaws.com:443

# Online SSL checker
# Visit: https://www.ssllabs.com/ssltest/
```

---

## Post-Installation Configuration

### Step 1: Configure Database Connection

Edit `app/config.php`:

```php
// Database credentials
define('DB_HOST', '127.0.0.1');  // or 'localhost'
define('DB_NAME', 'medlaw');
define('DB_USER', 'merlaws_user');
define('DB_PASS', 'your_strong_password');
```

### Step 2: Configure Email Settings

Edit `app/config.php`:

```php
// Email settings
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: 'your_resend_api_key');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'no-reply@merlaws.com');
```

**Or set environment variables:**

```bash
# Add to /etc/environment or ~/.bashrc
export RESEND_API_KEY="your_api_key_here"
export RESEND_FROM_EMAIL="no-reply@merlaws.com"
```

### Step 3: Configure File Upload Settings

Edit `app/config.php` if needed:

```php
// File upload settings
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpeg', 'jpg']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/cases/documents/');
```

### Step 4: Configure PHP Settings

Edit `php.ini` or create `.user.ini` in application root:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
date.timezone = Africa/Johannesburg
```

### Step 5: Create Initial Admin User

```bash
mysql -u merlaws_user -p medlaw
```

```sql
-- Create super admin user (adjust email and password hash)
INSERT INTO users (email, password_hash, name, role, is_active, created_at) 
VALUES (
    'admin@merlaws.com',
    '$2y$10$YourHashedPasswordHere',  -- Use password_hash() in PHP to generate
    'System Administrator',
    'super_admin',
    1,
    NOW()
);
```

**To generate password hash:**

```php
<?php
echo password_hash('your_secure_password', PASSWORD_DEFAULT);
?>
```

### Step 6: Configure PayFast (Payment Gateway)

1. Sign up for PayFast account
2. Get Merchant ID and Merchant Key
3. Configure in payment processing files (see Configuration Guide)

---

## Verification Checklist

### Database Verification

- [ ] Database connection successful
- [ ] All tables created
- [ ] Initial admin user created
- [ ] Can login with admin credentials

### File System Verification

- [ ] Upload directories exist and are writable
- [ ] File permissions correct
- [ ] Storage directories exist

### Web Server Verification

- [ ] Site accessible via HTTP
- [ ] Site accessible via HTTPS
- [ ] SSL certificate valid
- [ ] PHP processing working
- [ ] URL rewriting working

### Application Verification

- [ ] Can access login page
- [ ] Can login as admin
- [ ] Can access dashboard
- [ ] File uploads working
- [ ] Database queries working
- [ ] No PHP errors in logs

### Security Verification

- [ ] HTTPS enforced
- [ ] Security headers present
- [ ] CSRF protection working
- [ ] SQL injection protection (prepared statements)
- [ ] File upload restrictions working

### Performance Verification

- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] No slow query warnings
- [ ] Memory usage acceptable

---

## Troubleshooting Installation

### Common Issues

#### Database Connection Failed

**Symptoms:** "Database connection failed" error

**Solutions:**
1. Verify database credentials in `app/config.php`
2. Check MySQL service is running: `sudo systemctl status mysql`
3. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
4. Check user permissions: `mysql -u root -p -e "SHOW GRANTS FOR 'merlaws_user'@'localhost';"`
5. Check firewall rules

#### File Upload Not Working

**Symptoms:** Cannot upload files, permission errors

**Solutions:**
1. Check directory permissions: `ls -la uploads/`
2. Check web server user: `ps aux | grep apache` or `ps aux | grep nginx`
3. Set correct ownership: `chown -R www-data:www-data uploads/`
4. Check PHP upload settings: `php -i | grep upload_max_filesize`
5. Check disk space: `df -h`

#### 500 Internal Server Error

**Symptoms:** Blank page or 500 error

**Solutions:**
1. Check PHP error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
2. Check PHP syntax: `php -l app/config.php`
3. Verify file permissions
4. Check .htaccess file (Apache)
5. Verify PHP version: `php -v`

#### SSL Certificate Issues

**Symptoms:** Browser shows SSL warning

**Solutions:**
1. Verify certificate files exist and are readable
2. Check certificate expiration: `openssl x509 -in cert.crt -noout -dates`
3. Verify certificate matches domain
4. Check certificate chain is complete
5. Restart web server after certificate changes

#### Session Issues

**Symptoms:** Cannot maintain login, session errors

**Solutions:**
1. Check session directory permissions: `ls -la /var/lib/php/sessions/`
2. Verify session configuration in `php.ini`
3. Check session path in `app/config.php`
4. Clear old session files
5. Verify cookie settings

---

## Next Steps

After successful installation:

1. Review [Configuration Guide](CONFIGURATION.md) for detailed configuration options
2. Review [Security Documentation](SECURITY.md) for security best practices
3. Set up automated backups (see [Maintenance Guide](MAINTENANCE.md))
4. Configure monitoring and logging
5. Train users (see [User Guides](USER_GUIDE_CLIENT.md) and [USER_GUIDE_ADMIN.md](USER_GUIDE_ADMIN.md))

---

## Support

For installation issues:
1. Review [Troubleshooting Guide](TROUBLESHOOTING.md)
2. Check error logs
3. Contact system administrator

---

**Last Updated:** January 2025

