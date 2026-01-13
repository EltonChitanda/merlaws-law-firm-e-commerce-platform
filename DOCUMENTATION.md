# MerLaws Legal Practice Management System - Production Documentation

**Version:** 3.0  
**Last Updated:** January 2025  
**Status:** Production Ready

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Documentation by Subsystem](#documentation-by-subsystem)
3. [Documentation by Audience](#documentation-by-audience)
4. [System Overview](#system-overview)
5. [Getting Help](#getting-help)

---

## Quick Start

### For End Users
- **Client Portal Users:** See [Client Portal User Guide](docs/USER_GUIDE_CLIENT.md)
- **Admin/Staff Users:** See [Admin Portal User Guide](docs/USER_GUIDE_ADMIN.md)

### For Technical Staff
- **System Administrators:** See [Installation Guide](docs/INSTALLATION.md) and [Configuration Guide](docs/CONFIGURATION.md)
- **Developers:** See [Development Guide](docs/DEVELOPMENT.md) and [Technical Staff Guide](docs/TECHNICAL_STAFF_GUIDE.md)
- **API Integration:** See [API Documentation](docs/API_DOCUMENTATION.md)

### For All Audiences
- **Troubleshooting:** See [Troubleshooting Guide](docs/TROUBLESHOOTING.md)
- **Maintenance:** See [Maintenance Guide](docs/MAINTENANCE.md)

---

## Documentation by Subsystem

The MerLaws system is organized into the following subsystems. Each subsystem has dedicated documentation:

### 1. Authentication & Authorization System
- **User Guide:** Authentication covered in [Client Portal Guide](docs/USER_GUIDE_CLIENT.md#authentication) and [Admin Portal Guide](docs/USER_GUIDE_ADMIN.md#authentication)
- **Technical:** Security implementation in [Security Documentation](docs/SECURITY.md#authentication)
- **Configuration:** Session and security settings in [Configuration Guide](docs/CONFIGURATION.md#security-settings)

### 2. Case Management System
- **User Guide:** [Client Portal Guide - Case Management](docs/USER_GUIDE_CLIENT.md#case-management)
- **Admin Guide:** [Admin Portal Guide - Case Management](docs/USER_GUIDE_ADMIN.md#case-management)
- **Technical:** Database schema in [Database Schema](docs/DATABASE_SCHEMA.md#case-management-tables)
- **API:** Case endpoints in [API Documentation](docs/API_DOCUMENTATION.md#case-management-apis)

### 3. Document Management System
- **User Guide:** [Client Portal Guide - Documents](docs/USER_GUIDE_CLIENT.md#document-management)
- **Admin Guide:** [Admin Portal Guide - Documents](docs/USER_GUIDE_ADMIN.md#document-management)
- **Technical:** File upload configuration in [Configuration Guide](docs/CONFIGURATION.md#file-upload-settings)
- **Security:** File upload security in [Security Documentation](docs/SECURITY.md#file-upload-security)

### 4. Service Request System
- **User Guide:** [Client Portal Guide - Service Requests](docs/USER_GUIDE_CLIENT.md#service-requests)
- **Admin Guide:** [Admin Portal Guide - Service Requests](docs/USER_GUIDE_ADMIN.md#service-requests)
- **Technical:** Service workflow in [Features Documentation](docs/FEATURES.md#service-request-system)
- **API:** Service endpoints in [API Documentation](docs/API_DOCUMENTATION.md#service-request-apis)

### 5. Appointment Scheduling System
- **User Guide:** [Client Portal Guide - Appointments](docs/USER_GUIDE_CLIENT.md#appointments)
- **Admin Guide:** [Admin Portal Guide - Appointments](docs/USER_GUIDE_ADMIN.md#appointments)
- **Technical:** Calendar integration in [Features Documentation](docs/FEATURES.md#appointment-scheduling)
- **API:** Appointment endpoints in [API Documentation](docs/API_DOCUMENTATION.md#appointment-apis)

### 6. Messaging & Communication System
- **User Guide:** [Client Portal Guide - Messaging](docs/USER_GUIDE_CLIENT.md#messaging)
- **Admin Guide:** [Admin Portal Guide - Messaging](docs/USER_GUIDE_ADMIN.md#messaging)
- **Technical:** Communication architecture in [Features Documentation](docs/FEATURES.md#messaging-and-communication)
- **Troubleshooting:** Communication issues in [Troubleshooting Guide](docs/TROUBLESHOOTING.md#communication-issues)

### 7. Invoice & Payment System
- **User Guide:** [Client Portal Guide - Payments](docs/USER_GUIDE_CLIENT.md#payments-and-invoices)
- **Admin Guide:** [Admin Portal Guide - Finance](docs/USER_GUIDE_ADMIN.md#finance-and-invoicing)
- **Technical:** Payment gateway integration in [Configuration Guide](docs/CONFIGURATION.md#payment-gateway-configuration)
- **API:** Payment endpoints in [API Documentation](docs/API_DOCUMENTATION.md#payment-apis)

### 8. Analytics & Reporting System
- **Admin Guide:** [Admin Portal Guide - Analytics](docs/USER_GUIDE_ADMIN.md#analytics-and-reporting)
- **Technical:** Analytics implementation in [Features Documentation](docs/FEATURES.md#analytics-and-reporting)
- **API:** Analytics endpoints in [API Documentation](docs/API_DOCUMENTATION.md#analytics-apis)

### 9. Audit & Security Logging System
- **Admin Guide:** [Admin Portal Guide - Audit Logs](docs/USER_GUIDE_ADMIN.md#audit-logs)
- **Technical:** Audit logging in [Security Documentation](docs/SECURITY.md#audit-logging)
- **Maintenance:** Log management in [Maintenance Guide](docs/MAINTENANCE.md#log-management)

### 10. User Management & RBAC System
- **Admin Guide:** [Admin Portal Guide - User Management](docs/USER_GUIDE_ADMIN.md#user-management)
- **Technical:** RBAC implementation in [Security Documentation](docs/SECURITY.md#role-based-access-control)
- **Database:** User tables in [Database Schema](docs/DATABASE_SCHEMA.md#user-management-tables)

### 11. Notification System
- **User Guide:** Notifications covered in both [Client](docs/USER_GUIDE_CLIENT.md#notifications) and [Admin](docs/USER_GUIDE_ADMIN.md#notifications) guides
- **Technical:** Notification architecture in [Features Documentation](docs/FEATURES.md#notification-system)
- **API:** Notification endpoints in [API Documentation](docs/API_DOCUMENTATION.md#notification-apis)

---

## Documentation by Audience

### End Users

#### Client Portal Users
Complete guide for clients using the system:
- **[Client Portal User Guide](docs/USER_GUIDE_CLIENT.md)**
  - Account registration and login
  - Case management
  - Document uploads
  - Service requests
  - Appointments
  - Messaging
  - Payments
  - Profile management

#### Admin Portal Users
Complete guide for staff and administrators:
- **[Admin Portal User Guide](docs/USER_GUIDE_ADMIN.md)**
  - Dashboard overview
  - User management
  - Case oversight
  - Service request approval
  - Appointment management
  - Messaging and communication
  - Finance and invoicing
  - Analytics and reporting
  - Audit logs
  - System administration

### Technical Staff

#### System Administrators
- **[Installation & Deployment Guide](docs/INSTALLATION.md)**
  - System requirements
  - Installation procedures
  - Database setup
  - Web server configuration
  - SSL/HTTPS setup
  - Post-installation verification

- **[Configuration Guide](docs/CONFIGURATION.md)**
  - Complete configuration reference
  - Database settings
  - Email configuration
  - File upload settings
  - Security settings
  - Payment gateway setup

- **[Maintenance Guide](docs/MAINTENANCE.md)**
  - Regular maintenance tasks
  - Backup procedures
  - Log management
  - Performance monitoring
  - Security updates
  - Disaster recovery

- **[Troubleshooting Guide](docs/TROUBLESHOOTING.md)**
  - Common issues and solutions
  - Error diagnosis
  - Performance problems
  - Security issues

#### Developers
- **[Development Guide](docs/DEVELOPMENT.md)**
  - Code structure
  - Development environment setup
  - Coding standards
  - Adding new features
  - Testing procedures
  - Deployment workflow

- **[Technical Staff Guide](docs/TECHNICAL_STAFF_GUIDE.md)**
  - System architecture
  - Technical implementation details
  - Database design
  - API architecture
  - Security implementation
  - Performance optimization

- **[API Documentation](docs/API_DOCUMENTATION.md)**
  - Complete API reference
  - Authentication
  - Request/response formats
  - Error handling
  - Rate limiting

- **[Database Schema Documentation](docs/DATABASE_SCHEMA.md)**
  - Complete database structure
  - Table relationships
  - Indexes and constraints
  - Stored procedures
  - Migration procedures

- **[Security Documentation](docs/SECURITY.md)**
  - Security architecture
  - Authentication mechanisms
  - Authorization (RBAC)
  - Data protection
  - Security best practices

- **[Features Documentation](docs/FEATURES.md)**
  - Detailed feature descriptions
  - System capabilities
  - Workflow documentation

---

## System Overview

### What is MerLaws?

MerLaws is a comprehensive web-based legal practice management system designed specifically for medical law attorneys. The system provides:

- **Public Website:** Showcases legal services and firm information
- **Client Portal:** Secure portal for clients to manage cases, upload documents, request services, and communicate with attorneys
- **Admin Dashboard:** Administrative interface for staff to manage cases, users, services, and system operations

### Key Capabilities

1. **Case Management:** Complete lifecycle management of legal cases
2. **Document Management:** Secure file upload, storage, and organization
3. **Service Requests:** Client-driven service request system with approval workflow
4. **Appointment Scheduling:** Calendar-based appointment management
5. **Messaging:** Internal messaging system for client-attorney communication
6. **Invoicing & Payments:** Automated invoice generation and payment processing via PayFast
7. **Analytics & Reporting:** Comprehensive analytics and report generation
8. **Audit Logging:** Complete audit trail for security and compliance
9. **User Management:** Role-based access control with granular permissions
10. **Notifications:** System-wide notification system

### Technology Stack

- **Backend:** PHP 8.0+
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** Bootstrap 5.3, HTML5, CSS3, JavaScript (ES2023)
- **Web Server:** Apache/Nginx
- **Payment Gateway:** PayFast
- **Email Service:** Resend API

### System Requirements

#### Server Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- 2GB+ RAM (4GB recommended)
- 10GB+ disk space
- SSL certificate (required for production)

#### Client Requirements
- Modern web browser (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- Internet connection

---

## Getting Help

### For End Users

**Client Portal Issues:**
- Review the [Client Portal User Guide](docs/USER_GUIDE_CLIENT.md)
- Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md#client-portal-issues)
- Contact your case manager or attorney

**Admin Portal Issues:**
- Review the [Admin Portal User Guide](docs/USER_GUIDE_ADMIN.md)
- Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md#admin-portal-issues)
- Contact system administrator or IT support

### For Technical Staff

**Installation Issues:**
- Review [Installation Guide](docs/INSTALLATION.md)
- Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md#installation-issues)
- Review error logs

**Configuration Issues:**
- Review [Configuration Guide](docs/CONFIGURATION.md)
- Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md#configuration-issues)
- Verify environment variables

**Development Questions:**
- Review [Development Guide](docs/DEVELOPMENT.md)
- Review [Technical Staff Guide](docs/TECHNICAL_STAFF_GUIDE.md)
- Check code comments and inline documentation

**API Integration:**
- Review [API Documentation](docs/API_DOCUMENTATION.md)
- Check API endpoint responses
- Review error codes and messages

**Security Concerns:**
- Review [Security Documentation](docs/SECURITY.md)
- Check audit logs
- Contact security team

---

## Version History

### Version 3.0 (Current - Production Ready)
- Complete case management system
- Document management with secure uploads
- Service request workflow
- Appointment scheduling
- Messaging system
- Invoice and payment processing
- Analytics and reporting
- Audit logging
- Role-based access control
- Notification system

### Previous Versions
- Version 2.0: Enhanced authentication and user management
- Version 1.0: Initial release with basic case management

---

## Document Maintenance

This documentation is maintained as part of the MerLaws system. When system updates are made:

1. Update relevant documentation sections
2. Update version number and date
3. Update changelog
4. Notify users of significant changes

---

## License

This is a proprietary legal practice management system. All rights reserved.

---

**Last Updated:** January 2025  
**Documentation Version:** 1.0  
**System Version:** 3.0

