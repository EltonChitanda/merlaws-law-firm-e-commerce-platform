# MerLaws - Legal Practice Management System

## Overview

MerLaws is a comprehensive web-based legal practice management system designed specifically for medical law attorneys. The system provides both a public-facing website showcasing legal services and a secure client portal for case management, document handling, and service requests.

## 🏗️ System Architecture

### Frontend Components
- **Public Website**: Static HTML pages showcasing legal services and firm information
- **Client Portal**: Secure PHP-based application for authenticated users
- **Admin Dashboard**: Administrative interface for staff and attorneys

### Backend Components
- **PHP 8+**: Server-side application logic
- **MySQL Database**: Data persistence and management
- **Bootstrap 5.3**: Responsive UI framework
- **Modern JavaScript (ES2023)**: Client-side functionality

## 📁 Project Structure

```
www.merlaws.com/
├── app/                          # Main application directory
│   ├── admin/                    # Admin dashboard and management
│   ├── api/                      # API endpoints
│   ├── assets/                   # CSS, JS, and static assets
│   ├── cases/                    # Case management functionality
│   ├── documents/                # Document handling
│   ├── services/                 # Service catalog and requests
│   ├── models/                   # Data models
│   ├── config.php               # Application configuration
│   ├── dashboard.php            # Client dashboard
│   ├── login.php                # Authentication
│   └── register.php             # User registration
├── database/                     # Database schemas and migrations
├── uploads/                      # File storage
├── css/                         # Public website styles
├── image/                       # Website images
├── include/                     # Shared components
└── [service-pages].html         # Individual service pages
```

## 🚀 Key Features

### Client Portal
- **Case Management**: Create, view, and track legal cases
- **Document Upload**: Secure file upload and management
- **Service Requests**: Request additional legal services
- **Progress Tracking**: Real-time case status updates
- **Notifications**: System-generated alerts and updates

### Admin Dashboard
- **User Management**: Manage client accounts and permissions
- **Case Oversight**: Monitor all active cases
- **Service Approval**: Review and approve service requests
- **Analytics**: Performance metrics and reporting
- **Document Management**: Centralized file handling

### Security Features
- **Role-Based Access Control (RBAC)**: Granular permissions system
- **CSRF Protection**: Cross-site request forgery prevention
- **Secure Authentication**: Password hashing and session management
- **File Upload Security**: Type validation and secure storage
- **Input Sanitization**: XSS and injection prevention

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Database Setup
1. Import the database schema:
   ```sql
   mysql -u username -p < database/medlaw_v3.sql
   ```

2. Configure database connection in `app/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'medlaw');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/cases/
chmod 755 uploads/cases/documents/
```

### Web Server Configuration
- Document root: `/path/to/www.merlaws.com/`
- Enable URL rewriting for clean URLs
- Configure HTTPS for production

## 📊 Database Schema

### Core Tables
- **users**: User accounts and profiles
- **cases**: Legal case records
- **services**: Available legal services
- **service_requests**: Client service requests
- **case_documents**: File attachments
- **case_activities**: Activity logs
- **user_notifications**: System notifications

### Authentication & Security
- **password_resets**: Password reset tokens
- **permissions**: System permissions
- **role_permissions**: Role-based access control

## 🎯 User Roles & Permissions

### Client Role
- Create and manage personal cases
- Upload documents
- Request services
- View case progress
- Receive notifications

### Admin Roles
- **Super Admin**: Full system access
- **Admin**: User and case management
- **Manager**: Oversight and reporting
- **Attorney**: Case handling and legal work
- **Paralegal**: Case support and documentation
- **Receptionist**: Client communication

## 🔧 Configuration

### Environment Settings
Located in `app/config.php`:
- Database credentials
- File upload limits (10MB default)
- Allowed file types
- Timezone (Africa/Johannesburg)
- Security settings

### File Upload Configuration
```php
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', [
    'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'rtf'
]);
```

## 🎨 Frontend Features

### Responsive Design
- Mobile-first approach
- Bootstrap 5.3 framework
- Custom CSS with CSS variables
- High contrast mode support

### JavaScript Features
- Modern ES2023 syntax
- Form validation with real-time feedback
- Password strength meter
- Toast notifications
- File upload progress
- AJAX functionality

## 🔒 Security Considerations

### Authentication
- Secure password hashing (PHP's `password_hash()`)
- Session management
- Login attempt monitoring
- Password reset functionality

### Data Protection
- Input validation and sanitization
- SQL injection prevention (PDO prepared statements)
- XSS protection
- CSRF token validation
- Secure file upload handling

### Access Control
- Role-based permissions
- Route protection
- Admin area segregation
- Client data isolation

## 📱 API Endpoints

### Authentication
- `POST /api/auth-login.php` - User authentication
- `POST /api/logout.php` - Session termination

### Admin APIs
- `GET /api/admin-analytics.php` - Dashboard analytics
- Various admin management endpoints

## 🧪 Testing

### Manual Testing Checklist
1. User registration and login
2. Case creation and management
3. Document upload functionality
4. Service request workflow
5. Admin dashboard access
6. Mobile responsiveness
7. Security validations

### Browser Support
- Modern browsers with ES2023 support
- Bootstrap 5.3 compatible browsers
- Mobile Safari and Chrome

## 📈 Performance Optimization

### Database
- Indexed columns for fast queries
- Optimized table relationships
- Connection pooling via PDO

### Frontend
- Minified CSS and JavaScript
- Image optimization (WebP format)
- CDN usage for Bootstrap

## 🚨 Troubleshooting

### Common Issues
1. **Database Connection**: Check credentials in `config.php`
2. **File Uploads**: Verify directory permissions
3. **Login Issues**: Clear browser cache and cookies
4. **Admin Access**: Ensure correct role assignment

### Error Logging
- PHP errors logged to system error log
- Custom application logging in `config.php`
- Database query error handling

## 🔄 Maintenance

### Regular Tasks
- Database backups
- Log file rotation
- Security updates
- Performance monitoring

### Updates
- Test in staging environment
- Database migration scripts
- Version control with Git

## 📞 Support & Contact

For technical support or questions about the MerLaws system:
- Review this documentation
- Check error logs for specific issues
- Contact system administrator

## 📄 License

This is a proprietary legal practice management system. All rights reserved.

---

**Version**: 3.0  
**Last Updated**: October 2025  
**Compatibility**: PHP 8+, MySQL 5.7+, Modern Browsers 