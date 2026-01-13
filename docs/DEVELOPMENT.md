# Development Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** Developers

---

## Table of Contents

1. [Development Environment Setup](#development-environment-setup)
2. [Code Structure](#code-structure)
3. [Coding Standards](#coding-standards)
4. [Adding New Features](#adding-new-features)
5. [Database Migrations](#database-migrations)
6. [Testing Procedures](#testing-procedures)
7. [Version Control](#version-control)
8. [Deployment Workflow](#deployment-workflow)
9. [Debugging](#debugging)
10. [Best Practices](#best-practices)

---

## Development Environment Setup

### Prerequisites

- PHP 8.0+ (8.1+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- Git (for version control)
- Code editor (VS Code, PHPStorm, etc.)

### Local Setup

1. **Clone Repository:**
   ```bash
   git clone <repository-url>
   cd www.merlaws.com
   ```

2. **Set Up Database:**
   ```bash
   mysql -u root -p
   CREATE DATABASE medlaw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   
   mysql -u root -p medlaw < database/medlaw\ v15.sql
   ```

3. **Configure Application:**
   ```bash
   # Copy and edit config
   cp app/config.php.example app/config.php
   nano app/config.php
   ```

4. **Set File Permissions:**
   ```bash
   chmod -R 755 uploads/
   chmod -R 755 storage/
   ```

5. **Start Development Server:**
   ```bash
   # PHP built-in server (for testing)
   php -S localhost:8000 -t .
   ```

### Development Tools

**Recommended:**
- **PHP Debugger:** Xdebug
- **Code Quality:** PHP_CodeSniffer, PHPStan
- **Version Control:** Git
- **Database Tools:** phpMyAdmin, MySQL Workbench
- **API Testing:** Postman, cURL

---

## Code Structure

### Directory Organization

```
app/
├── admin/              # Admin portal pages
├── api/                # API endpoints
├── cases/              # Case management
├── documents/          # Document handling
├── services/           # Service requests
├── messages/           # Messaging
├── appointments/       # Appointments
├── invoices/           # Invoice management
├── config.php          # Core configuration
└── csrf.php            # CSRF protection
```

### File Naming

- **PHP Files:** `kebab-case.php`
- **Directories:** `kebab-case/`
- **Functions:** `snake_case()`
- **Variables:** `$camelCase` or `$snake_case`
- **Constants:** `UPPER_CASE`

### Code Organization

**Configuration:**
- `app/config.php` - Core configuration and helpers
- `app/csrf.php` - CSRF protection

**Page Structure:**
```php
<?php
require __DIR__ . '/../config.php';
require_login();  // or require_admin()

// Page logic here

// Include header
require __DIR__ . '/../include/header.php';

// Page content
?>

<!-- HTML content -->

<?php
require __DIR__ . '/../include/footer.php';
?>
```

---

## Coding Standards

### PHP Standards

**Follow PSR-12 Coding Standards:**
- Use 4 spaces for indentation
- Use camelCase for variables
- Use snake_case for functions
- Use UPPER_CASE for constants
- Add type hints where possible
- Document complex functions

### Code Style Examples

**Function Definition:**
```php
function get_user_cases(int $user_id, string $status = null): array {
    // Function body
}
```

**Variable Naming:**
```php
$user_id = 123;
$case_title = "Case Title";
$is_active = true;
```

**Database Queries:**
```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM cases WHERE user_id = ?");
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();
```

### Comments

**Function Comments:**
```php
/**
 * Get cases for a user
 * 
 * @param int $user_id User ID
 * @param string|null $status Case status filter
 * @return array Array of case records
 */
function get_user_cases(int $user_id, string $status = null): array {
    // Implementation
}
```

**Inline Comments:**
```php
// Check if user has permission
if (has_permission('case:view')) {
    // Allow access
}
```

---

## Adding New Features

### Feature Development Process

1. **Plan Feature**
   - Define requirements
   - Design database schema (if needed)
   - Plan user interface
   - Consider security implications

2. **Create Database Schema** (if needed)
   ```sql
   CREATE TABLE new_feature (
       id INT PRIMARY KEY AUTO_INCREMENT,
       -- columns
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. **Create Backend Logic**
   - Add functions to `config.php` (if shared)
   - Create feature-specific files
   - Implement business logic
   - Add validation

4. **Create Frontend**
   - Create page files
   - Add forms and UI
   - Implement JavaScript (if needed)
   - Add CSRF protection

5. **Add Security**
   - Implement authentication checks
   - Add permission checks
   - Validate input
   - Escape output
   - Add audit logging

6. **Test Feature**
   - Test functionality
   - Test security
   - Test edge cases
   - Test with different roles

7. **Document Feature**
   - Update user guides
   - Update API documentation (if API)
   - Update this guide (if needed)

### Example: Adding a New Feature

**Feature:** Task Management

1. **Database Schema:**
   ```sql
   CREATE TABLE tasks (
       id INT PRIMARY KEY AUTO_INCREMENT,
       case_id INT,
       assigned_to INT,
       title VARCHAR(255),
       description TEXT,
       due_date DATE,
       status ENUM('pending', 'in_progress', 'completed'),
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

2. **Backend Functions:**
   ```php
   // In config.php
   function create_task(array $data): int {
       // Implementation
   }
   
   function get_user_tasks(int $user_id): array {
       // Implementation
   }
   ```

3. **Frontend Page:**
   ```php
   // app/tasks/index.php
   require __DIR__ . '/../config.php';
   require_login();
   
   $tasks = get_user_tasks(get_user_id());
   // Display tasks
   ```

---

## Database Migrations

### Migration Process

1. **Create Migration File:**
   ```sql
   -- database/migrations/001_add_tasks_table.sql
   CREATE TABLE tasks (
       -- schema
   );
   ```

2. **Test Migration:**
   ```bash
   mysql -u username -p medlaw < database/migrations/001_add_tasks_table.sql
   ```

3. **Verify Migration:**
   ```sql
   SHOW TABLES LIKE 'tasks';
   DESCRIBE tasks;
   ```

4. **Rollback Plan:**
   ```sql
   -- database/migrations/001_add_tasks_table_rollback.sql
   DROP TABLE IF EXISTS tasks;
   ```

### Migration Best Practices

- Always backup before migration
- Test on development first
- Create rollback scripts
- Document migrations
- Version migration files

---

## Testing Procedures

### Manual Testing

**Test Checklist:**
- [ ] Feature works as expected
- [ ] Error handling works
- [ ] Security checks work
- [ ] Different user roles tested
- [ ] Edge cases tested
- [ ] Performance acceptable

### Testing Scenarios

**Authentication:**
- Login with valid credentials
- Login with invalid credentials
- Password reset
- Session management

**Authorization:**
- Access with correct permissions
- Access without permissions
- Role-based access
- Permission checks

**Data Validation:**
- Valid input accepted
- Invalid input rejected
- SQL injection attempts
- XSS attempts

### Automated Testing

**Consider Implementing:**
- Unit tests (PHPUnit)
- Integration tests
- API tests
- Security tests

---

## Version Control

### Git Workflow

**Branch Strategy:**
- `main` - Production code
- `develop` - Development branch
- `feature/feature-name` - Feature branches
- `bugfix/bug-name` - Bug fix branches

**Commit Messages:**
```
feat: Add task management feature
fix: Fix login session issue
docs: Update API documentation
refactor: Improve database queries
```

### Git Best Practices

1. **Commit Often**
   - Small, logical commits
   - Clear commit messages
   - Related changes together

2. **Branch Management**
   - Create feature branches
   - Keep branches up to date
   - Delete merged branches

3. **Code Review**
   - Review before merging
   - Test before merging
   - Document changes

---

## Deployment Workflow

### Pre-Deployment

1. **Code Review**
   - Review all changes
   - Test functionality
   - Verify security

2. **Database Migration**
   - Backup database
   - Run migrations
   - Verify migration

3. **Configuration**
   - Update configuration
   - Set environment variables
   - Verify settings

### Deployment Steps

1. **Backup Current System**
   ```bash
   # Backup database
   mysqldump -u username -p medlaw > backup.sql
   
   # Backup files
   tar -czf files_backup.tar.gz uploads/
   ```

2. **Deploy Code**
   ```bash
   # Pull latest code
   git pull origin main
   
   # Or copy files
   rsync -av source/ destination/
   ```

3. **Run Migrations**
   ```bash
   mysql -u username -p medlaw < migrations/new_migration.sql
   ```

4. **Verify Deployment**
   - Test critical functionality
   - Check error logs
   - Monitor performance

### Post-Deployment

1. **Monitor System**
   - Check error logs
   - Monitor performance
   - Verify functionality

2. **Rollback Plan**
   - Keep backup ready
   - Document rollback steps
   - Test rollback procedure

---

## Debugging

### Error Debugging

**Enable Error Display (Development Only):**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Check Error Logs:**
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

**PHP Error Logging:**
```php
error_log("Debug message: " . print_r($data, true));
```

### Database Debugging

**Enable Query Logging:**
```php
// Log queries
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

**Check Slow Queries:**
```sql
SHOW FULL PROCESSLIST;
```

### Debugging Tools

- **Xdebug:** PHP debugger
- **var_dump():** Quick variable inspection
- **error_log():** Log debugging info
- **Browser DevTools:** Frontend debugging

---

## Best Practices

### Security

1. **Always validate input**
2. **Always escape output**
3. **Use prepared statements**
4. **Implement CSRF protection**
5. **Check permissions**
6. **Log security events**

### Performance

1. **Optimize database queries**
2. **Use indexes appropriately**
3. **Limit result sets**
4. **Cache when appropriate**
5. **Minimize database connections**

### Code Quality

1. **Follow coding standards**
2. **Write clear, readable code**
3. **Document complex logic**
4. **Keep functions focused**
5. **Avoid code duplication**

### Maintenance

1. **Keep dependencies updated**
2. **Remove unused code**
3. **Refactor when needed**
4. **Update documentation**
5. **Review code regularly**

---

## Development Resources

### Documentation

- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)

### Tools

- **PHP_CodeSniffer:** Code style checking
- **PHPStan:** Static analysis
- **Composer:** Dependency management (if used)
- **Git:** Version control

---

**Last Updated:** January 2025

