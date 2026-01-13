# Troubleshooting Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** All Users, System Administrators

---

## Table of Contents

1. [General Troubleshooting](#general-troubleshooting)
2. [Client Portal Issues](#client-portal-issues)
3. [Admin Portal Issues](#admin-portal-issues)
4. [Installation Issues](#installation-issues)
5. [Configuration Issues](#configuration-issues)
6. [Database Issues](#database-issues)
7. [File Upload Issues](#file-upload-issues)
8. [Authentication Issues](#authentication-issues)
9. [Session Issues](#session-issues)
10. [Payment Issues](#payment-issues)
11. [Communication Issues](#communication-issues)
12. [Performance Issues](#performance-issues)

---

## General Troubleshooting

### Basic Steps

1. **Clear Browser Cache**
   - Clear browser cache and cookies
   - Try incognito/private mode
   - Try different browser

2. **Check Internet Connection**
   - Verify internet connectivity
   - Check if other websites work
   - Try different network

3. **Check System Status**
   - Verify system is operational
   - Check for maintenance notices
   - Contact administrator

4. **Review Error Messages**
   - Read error messages carefully
   - Note error codes
   - Take screenshots if possible

---

## Client Portal Issues

### Cannot Log In

**Symptoms:**
- Login fails with correct credentials
- "Invalid email or password" error
- Redirected back to login page

**Solutions:**
1. Verify email and password are correct
2. Check if Caps Lock is on
3. Try password reset
4. Clear browser cache and cookies
5. Try different browser
6. Check if account is active (contact support)
7. Wait 15 minutes if rate limited

### Cannot Create Case

**Symptoms:**
- Case creation form not working
- Error when submitting case
- "Profile incomplete" message

**Solutions:**
1. Complete your profile:
   - Full name
   - Valid email
   - Phone number
   - Address
   - City
2. Refresh the page
3. Try again after completing profile
4. Contact support if issue persists

### Cannot Upload Documents

**Symptoms:**
- File upload fails
- "File too large" error
- "File type not allowed" error

**Solutions:**
1. Check file format (PDF, JPEG, JPG only)
2. Check file size (maximum 100MB)
3. Try smaller file
4. Check internet connection
5. Try different file
6. Contact support if issue persists

### Cannot View Cases

**Symptoms:**
- Cases not showing
- "Access denied" error
- Blank page

**Solutions:**
1. Verify you're logged in
2. Refresh the page
3. Clear browser cache
4. Check if cases exist
5. Try different browser
6. Contact support

---

## Admin Portal Issues

### Cannot Access Admin Features

**Symptoms:**
- Feature not visible
- "Access denied" error
- Redirected to dashboard

**Solutions:**
1. Verify your role has required permissions
2. Check with system administrator
3. Verify account is active
4. Log out and log back in
5. Contact system administrator

### Reports Not Generating

**Symptoms:**
- Report generation fails
- Empty reports
- Export not working

**Solutions:**
1. Check date range is valid
2. Verify you have permission for report type
3. Try different date range
4. Try different export format
5. Check system logs
6. Contact system administrator

### Cannot Create Invoices

**Symptoms:**
- Invoice creation fails
- "Permission denied" error
- Form not submitting

**Solutions:**
1. Verify billing permissions
2. Check case status
3. Verify all required fields are filled
4. Refresh the page
5. Try again
6. Contact billing department

---

## Installation Issues

### Database Connection Failed

**Symptoms:**
- "Database connection failed" error
- Cannot connect to database

**Solutions:**
1. Verify database credentials in `app/config.php`
2. Check MySQL service is running:
   ```bash
   sudo systemctl status mysql
   ```
3. Verify database exists:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```
4. Check user permissions
5. Check firewall rules
6. Verify database host (127.0.0.1 vs localhost)

### File Permissions Issues

**Symptoms:**
- Cannot upload files
- Permission denied errors
- Files not saving

**Solutions:**
1. Check directory permissions:
   ```bash
   ls -la uploads/
   ```
2. Set correct permissions:
   ```bash
   chmod -R 755 uploads/
   chown -R www-data:www-data uploads/
   ```
3. Verify web server user
4. Check disk space:
   ```bash
   df -h
   ```

### 500 Internal Server Error

**Symptoms:**
- Blank page or 500 error
- Server error message

**Solutions:**
1. Check PHP error logs:
   ```bash
   tail -f /var/log/apache2/error.log
   # or
   tail -f /var/log/nginx/error.log
   ```
2. Check PHP syntax:
   ```bash
   php -l app/config.php
   ```
3. Verify file permissions
4. Check .htaccess file (Apache)
5. Verify PHP version:
   ```bash
   php -v
   ```

---

## Configuration Issues

### Email Not Sending

**Symptoms:**
- Password reset emails not received
- Notification emails not sent
- Email errors in logs

**Solutions:**
1. Verify Resend API key is set:
   ```php
   // Check app/config.php
   define('RESEND_API_KEY', 'your_key');
   ```
2. Check API key validity
3. Verify FROM_EMAIL domain is verified
4. Check email service quota
5. Review email logs
6. Test with simple email

### Payment Gateway Not Working

**Symptoms:**
- Payment processing fails
- PayFast errors
- Payment not completing

**Solutions:**
1. Verify PayFast credentials
2. Check PayFast account status
3. Verify environment (sandbox vs production)
4. Check payment gateway logs
5. Test with test transaction
6. Contact PayFast support

---

## Database Issues

### Slow Queries

**Symptoms:**
- Pages load slowly
- Timeout errors
- Database errors

**Solutions:**
1. Check database indexes
2. Optimize slow queries
3. Check database server resources
4. Review query logs
5. Consider database optimization
6. Contact database administrator

### Database Errors

**Symptoms:**
- SQL errors
- Table not found errors
- Connection errors

**Solutions:**
1. Verify database schema is imported
2. Check table existence:
   ```sql
   SHOW TABLES;
   ```
3. Verify database user permissions
4. Check database logs
5. Restore from backup if needed

---

## File Upload Issues

### Upload Fails

**Symptoms:**
- File upload fails
- No error message
- File not appearing

**Solutions:**
1. Check PHP upload settings:
   ```ini
   upload_max_filesize = 100M
   post_max_size = 100M
   ```
2. Check directory permissions
3. Verify disk space
4. Check file type and size
5. Review PHP error logs
6. Test with smaller file

### Files Not Accessible

**Symptoms:**
- Cannot download files
- 404 errors on files
- Permission denied

**Solutions:**
1. Verify file paths are correct
2. Check file permissions
3. Verify file exists
4. Check web server configuration
5. Review access control logic

---

## Authentication Issues

### Cannot Log In

**Symptoms:**
- Login fails
- Session errors
- Redirect loops

**Solutions:**
1. Clear browser cookies
2. Try different browser
3. Check session directory permissions
4. Verify session configuration
5. Check for session conflicts
6. Review authentication logs

### Session Expires Too Quickly

**Symptoms:**
- Logged out frequently
- Session timeout errors

**Solutions:**
1. Check session configuration
2. Verify session lifetime settings
3. Check browser cookie settings
4. Review session cleanup processes

---

## Session Issues

### Session Not Persisting

**Symptoms:**
- Logged out on page refresh
- Session data lost
- Cannot maintain login

**Solutions:**
1. Check session directory permissions:
   ```bash
   ls -la /var/lib/php/sessions/
   ```
2. Verify session configuration
3. Check cookie settings
4. Clear old session files
5. Check for session conflicts

### Session Conflicts

**Symptoms:**
- Admin and client sessions conflict
- Cannot switch between contexts

**Solutions:**
1. Verify separate session names are used
2. Clear all cookies
3. Log out completely
4. Log in to correct context
5. Check session initialization code

---

## Payment Issues

### Payment Not Processing

**Symptoms:**
- Payment fails
- PayFast errors
- Payment not recorded

**Solutions:**
1. Check payment details
2. Verify internet connection
3. Try different payment method
4. Check PayFast status
5. Review payment logs
6. Contact billing department

### Invoice Not Showing

**Symptoms:**
- Invoice not visible
- Cannot access invoice
- Invoice link broken

**Solutions:**
1. Verify invoice exists
2. Check permissions
3. Verify case association
4. Refresh the page
5. Check invoice status
6. Contact billing department

---

## Communication Issues

### Messages Not Sending

**Symptoms:**
- Message send fails
- Message not delivered
- Error when sending

**Solutions:**
1. Check internet connection
2. Verify recipient exists
3. Try refreshing page
4. Check message logs
5. Try again later
6. Contact support

### Notifications Not Appearing

**Symptoms:**
- No notifications
- Notifications delayed
- Notification count wrong

**Solutions:**
1. Refresh the page
2. Check notification settings
3. Clear browser cache
4. Check notification logs
5. Verify user permissions

---

## Performance Issues

### Slow Page Loads

**Symptoms:**
- Pages load slowly
- Timeout errors
- Unresponsive interface

**Solutions:**
1. Check server resources
2. Review database queries
3. Check network connection
4. Clear browser cache
5. Optimize database
6. Contact system administrator

### High Server Load

**Symptoms:**
- Server slow
- Timeout errors
- Resource exhaustion

**Solutions:**
1. Check server resources (CPU, memory, disk)
2. Review active processes
3. Check database connections
4. Review error logs
5. Consider server upgrade
6. Contact hosting provider

---

## Getting Help

### Support Channels

1. **Documentation:** Review relevant guides
2. **System Administrator:** Contact IT support
3. **Email Support:** Send detailed issue description
4. **Phone Support:** Call during business hours

### Information to Provide

When reporting issues, include:
- Description of problem
- Steps to reproduce
- Error messages
- Screenshots (if possible)
- Browser and version
- Operating system
- User role
- Time of occurrence

---

**Last Updated:** January 2025

