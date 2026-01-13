# Maintenance & Operations Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** System Administrators, Operations Staff

---

## Table of Contents

1. [Maintenance Overview](#maintenance-overview)
2. [Regular Maintenance Tasks](#regular-maintenance-tasks)
3. [Database Maintenance](#database-maintenance)
4. [Backup Procedures](#backup-procedures)
5. [Log Management](#log-management)
6. [Performance Monitoring](#performance-monitoring)
7. [Security Updates](#security-updates)
8. [System Health Checks](#system-health-checks)
9. [Scheduled Tasks](#scheduled-tasks)
10. [Disaster Recovery](#disaster-recovery)
11. [Capacity Planning](#capacity-planning)
12. [Monitoring and Alerting](#monitoring-and-alerting)

---

## Maintenance Overview

### Maintenance Goals

- Ensure system reliability
- Maintain performance
- Preserve data integrity
- Ensure security
- Plan for growth

### Maintenance Schedule

**Daily:**
- Monitor system health
- Check error logs
- Verify backups

**Weekly:**
- Review audit logs
- Check disk space
- Review performance metrics

**Monthly:**
- Database optimization
- Log archiving
- Security updates
- Performance review

**Quarterly:**
- Full system audit
- Capacity planning
- Disaster recovery testing
- Documentation updates

---

## Regular Maintenance Tasks

### Daily Tasks

1. **Check System Health**
   - Verify system is operational
   - Check error logs
   - Monitor resource usage

2. **Verify Backups**
   - Check backup completion
   - Verify backup integrity
   - Test backup restoration (periodic)

3. **Monitor Logs**
   - Review error logs
   - Check for security events
   - Monitor application logs

### Weekly Tasks

1. **Review Audit Logs**
   - Check for suspicious activity
   - Review failed login attempts
   - Monitor permission changes

2. **Check Disk Space**
   ```bash
   df -h
   ```
   - Monitor upload directory
   - Check log directory
   - Plan cleanup if needed

3. **Review Performance**
   - Check page load times
   - Review database query times
   - Monitor server resources

### Monthly Tasks

1. **Database Optimization**
   - Analyze tables
   - Optimize indexes
   - Clean up old data

2. **Log Archiving**
   - Archive old audit logs
   - Archive old analytics events
   - Compress archived logs

3. **Security Updates**
   - Update PHP
   - Update system packages
   - Review security advisories

---

## Database Maintenance

### Regular Database Tasks

**Optimize Tables:**
```sql
OPTIMIZE TABLE cases;
OPTIMIZE TABLE case_documents;
OPTIMIZE TABLE messages;
-- etc.
```

**Analyze Tables:**
```sql
ANALYZE TABLE cases;
ANALYZE TABLE users;
-- etc.
```

**Check Table Status:**
```sql
CHECK TABLE cases;
CHECK TABLE users;
-- etc.
```

### Database Cleanup

**Archive Old Data:**
```sql
-- Archive audit logs older than 90 days
CALL sp_archive_audit_logs(90);
```

**Remove Old Sessions:**
- PHP handles session cleanup automatically
- Configure `session.gc_maxlifetime` in php.ini

**Clean Up Temporary Data:**
- Remove old temporary files
- Clean up failed uploads
- Remove expired tokens

### Database Backup

See [Backup Procedures](#backup-procedures) section.

---

## Backup Procedures

### Database Backups

**Manual Backup:**
```bash
# Full database backup
mysqldump -u username -p medlaw > backup_$(date +%Y%m%d).sql

# Backup with compression
mysqldump -u username -p medlaw | gzip > backup_$(date +%Y%m%d).sql.gz
```

**Automated Backups:**
```bash
# Create backup script
#!/bin/bash
BACKUP_DIR="/backups/merlaws"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p medlaw | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Add to crontab (daily at 2 AM)
0 2 * * * /path/to/backup_script.sh
```

**Backup Retention:**
- Daily backups: Keep 7 days
- Weekly backups: Keep 4 weeks
- Monthly backups: Keep 12 months

### File Backups

**Upload Directory Backup:**
```bash
# Backup uploads directory
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/

# Automated backup script
#!/bin/bash
BACKUP_DIR="/backups/merlaws"
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" /path/to/uploads/
```

### Backup Verification

**Test Restorations:**
- Test database restoration monthly
- Test file restoration quarterly
- Document restoration procedures
- Keep restoration logs

**Backup Integrity:**
- Verify backup file sizes
- Check backup completion
- Test backup files periodically
- Monitor backup failures

---

## Log Management

### Log Rotation

**PHP Error Logs:**
```bash
# Configure logrotate
/path/to/php_errors.log {
    daily
    rotate 7
    compress
    missingok
    notifempty
}
```

**Application Logs:**
- Rotate application logs weekly
- Compress old logs
- Archive logs older than 90 days
- Remove logs older than 1 year

### Log Archiving

**Audit Logs:**
```sql
-- Archive audit logs older than 90 days
CALL sp_archive_audit_logs(90);

-- Or manually
INSERT INTO audit_logs_archive 
SELECT * FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

**Analytics Events:**
- Archive analytics events older than 1 year
- Keep aggregated data
- Remove detailed events

### Log Monitoring

**Monitor Log Sizes:**
```bash
# Check log file sizes
du -sh /var/log/apache2/
du -sh /var/log/nginx/
du -sh /path/to/application/logs/
```

**Review Logs Regularly:**
- Daily: Error logs
- Weekly: Audit logs
- Monthly: Analytics logs

---

## Performance Monitoring

### Server Resources

**CPU Usage:**
```bash
top
htop
```

**Memory Usage:**
```bash
free -h
```

**Disk Usage:**
```bash
df -h
du -sh /path/to/directories/
```

### Database Performance

**Slow Query Log:**
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

**Query Analysis:**
```sql
-- Explain query
EXPLAIN SELECT * FROM cases WHERE status = 'active';

-- Show process list
SHOW PROCESSLIST;
```

### Application Performance

**Page Load Times:**
- Monitor average page load times
- Identify slow pages
- Optimize database queries
- Review caching strategies

**Database Connections:**
- Monitor connection count
- Check for connection leaks
- Optimize connection pooling

---

## Security Updates

### PHP Updates

**Check PHP Version:**
```bash
php -v
```

**Update PHP:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt upgrade php8.0

# CentOS/RHEL
sudo yum update php
```

**Test After Updates:**
- Test application functionality
- Verify database connectivity
- Check file uploads
- Test authentication

### System Updates

**Operating System:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt upgrade

# CentOS/RHEL
sudo yum update
```

**Web Server:**
```bash
# Apache
sudo apt upgrade apache2

# Nginx
sudo apt upgrade nginx
```

**Database:**
```bash
# MySQL/MariaDB
sudo apt upgrade mysql-server
```

### Security Patches

**Monitor Security Advisories:**
- PHP security advisories
- MySQL/MariaDB security updates
- Operating system security patches
- Application dependencies

**Apply Patches:**
- Test patches in staging
- Apply during maintenance window
- Verify functionality after patching
- Document patch applications

---

## System Health Checks

### Daily Health Checks

1. **System Availability**
   - Website accessible
   - Login working
   - Database connected

2. **Error Rates**
   - Check error log counts
   - Monitor 500 errors
   - Review failed logins

3. **Resource Usage**
   - CPU usage
   - Memory usage
   - Disk space

### Weekly Health Checks

1. **Performance Metrics**
   - Average page load time
   - Database query times
   - File upload success rate

2. **Security Metrics**
   - Failed login attempts
   - Suspicious activity
   - Permission changes

3. **Data Integrity**
   - Database consistency
   - File system integrity
   - Backup verification

### Monthly Health Checks

1. **Full System Audit**
   - Review all logs
   - Check system configuration
   - Verify security settings

2. **Capacity Review**
   - Database growth
   - File storage growth
   - User growth

3. **Disaster Recovery Test**
   - Test backup restoration
   - Verify recovery procedures
   - Document test results

---

## Scheduled Tasks

### Cron Jobs

**Database Backup:**
```bash
# Daily backup at 2 AM
0 2 * * * /path/to/backup_database.sh
```

**Log Archiving:**
```bash
# Weekly log archiving on Sunday at 3 AM
0 3 * * 0 /path/to/archive_logs.sh
```

**Database Optimization:**
```bash
# Monthly optimization on 1st at 4 AM
0 4 1 * * /path/to/optimize_database.sh
```

**Cleanup Tasks:**
```bash
# Daily cleanup of temporary files
0 1 * * * /path/to/cleanup_temp.sh
```

### Scheduled Tasks in Application

**Appointment Reminders:**
- Check for upcoming appointments
- Send reminder notifications
- Create reminder tasks

**Invoice Status Updates:**
- Check for overdue invoices
- Update invoice status
- Send overdue notifications

**Log Archiving:**
- Archive old audit logs
- Archive old analytics events
- Clean up old sessions

---

## Disaster Recovery

### Recovery Procedures

**Database Recovery:**
```bash
# Stop application
sudo systemctl stop apache2

# Restore database
mysql -u username -p medlaw < backup_file.sql

# Verify restoration
mysql -u username -p medlaw -e "SELECT COUNT(*) FROM users;"

# Start application
sudo systemctl start apache2
```

**File Recovery:**
```bash
# Restore uploads
tar -xzf uploads_backup.tar.gz -C /path/to/restore/

# Verify file permissions
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```

### Recovery Testing

**Test Procedures:**
1. Create test backup
2. Simulate disaster
3. Restore from backup
4. Verify functionality
5. Document results

**Test Frequency:**
- Monthly: Database restoration
- Quarterly: Full system restoration
- Annually: Disaster recovery drill

---

## Capacity Planning

### Growth Monitoring

**Database Growth:**
```sql
-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'medlaw'
ORDER BY size_mb DESC;
```

**File Storage Growth:**
```bash
# Check upload directory size
du -sh uploads/

# Check growth over time
# Monitor weekly
```

**User Growth:**
```sql
-- Count users by month
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') AS month,
    COUNT(*) AS new_users
FROM users
GROUP BY month
ORDER BY month DESC;
```

### Capacity Planning

**Project Growth:**
- Estimate user growth
- Estimate data growth
- Plan storage capacity
- Plan database capacity

**Scaling Strategies:**
- Vertical scaling (more resources)
- Horizontal scaling (more servers)
- Database optimization
- Caching strategies

---

## Monitoring and Alerting

### Monitoring Tools

**Server Monitoring:**
- CPU usage
- Memory usage
- Disk space
- Network traffic

**Application Monitoring:**
- Error rates
- Response times
- User activity
- Database performance

**Security Monitoring:**
- Failed login attempts
- Suspicious activity
- Permission changes
- Audit log events

### Alerting

**Set Up Alerts For:**
- High CPU usage (>80%)
- Low disk space (<20%)
- High error rates
- Database connection failures
- Backup failures
- Security events

**Alert Channels:**
- Email alerts
- SMS alerts (if configured)
- Monitoring dashboard
- Log aggregation tools

---

## Maintenance Checklist

### Daily
- [ ] Check system health
- [ ] Verify backups completed
- [ ] Review error logs
- [ ] Monitor resource usage

### Weekly
- [ ] Review audit logs
- [ ] Check disk space
- [ ] Review performance metrics
- [ ] Verify backup integrity

### Monthly
- [ ] Database optimization
- [ ] Log archiving
- [ ] Security updates
- [ ] Performance review
- [ ] Backup restoration test

### Quarterly
- [ ] Full system audit
- [ ] Capacity planning review
- [ ] Disaster recovery test
- [ ] Documentation updates
- [ ] Security audit

---

**Last Updated:** January 2025

