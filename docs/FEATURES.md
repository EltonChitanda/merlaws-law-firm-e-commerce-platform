# Features Documentation

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** All Users, Stakeholders

---

## Table of Contents

1. [Feature Overview](#feature-overview)
2. [Case Management System](#case-management-system)
3. [Document Management](#document-management)
4. [Service Request System](#service-request-system)
5. [Appointment Scheduling](#appointment-scheduling)
6. [Messaging and Communication](#messaging-and-communication)
7. [Invoice and Payment Processing](#invoice-and-payment-processing)
8. [Analytics and Reporting](#analytics-and-reporting)
9. [Audit Logging](#audit-logging)
10. [Notification System](#notification-system)
11. [User Management and RBAC](#user-management-and-rbac)
12. [Support Ticket System](#support-ticket-system)

---

## Feature Overview

MerLaws provides comprehensive legal practice management capabilities:

### Core Features

- ✅ Case Management
- ✅ Document Management
- ✅ Service Requests
- ✅ Appointment Scheduling
- ✅ Messaging System
- ✅ Invoice & Payments
- ✅ Analytics & Reporting
- ✅ Audit Logging
- ✅ Notifications
- ✅ User Management
- ✅ Role-Based Access Control

---

## Case Management System

### Features

**Case Creation:**
- Create new cases with detailed information
- Assign case types (Medical Negligence, MVA, etc.)
- Set priority levels
- Add descriptions and notes

**Case Tracking:**
- View case status (Draft, Active, Under Review, Closed)
- Track case progress
- View case timeline and activities
- Monitor case updates

**Case Assignment:**
- Assign cases to attorneys
- Track assigned cases
- View case workload

**Case Activities:**
- Automatic activity logging
- Manual activity notes
- Document upload tracking
- Service request tracking
- Status change history

### Use Cases

- Clients create and manage their cases
- Attorneys track and update cases
- Administrators oversee all cases
- Case managers coordinate case activities

---

## Document Management

### Features

**Document Upload:**
- Secure file upload
- Multiple file formats (PDF, JPEG, JPG)
- File size limit: 100MB
- Automatic file validation

**Document Organization:**
- Case-specific document storage
- Document categorization
- Document descriptions
- Upload date tracking

**Document Security:**
- Access control by case ownership
- SHA256 checksum verification
- Secure file storage
- Unique filename generation

**Document Access:**
- View document list
- Download documents
- View document metadata
- Track document history

### Use Cases

- Clients upload case-related documents
- Attorneys review client documents
- Document specialists manage document workflow
- Secure document storage and retrieval

---

## Service Request System

### Features

**Service Catalog:**
- Browse available services
- Service descriptions
- Service categories
- Estimated durations

**Service Cart:**
- Add services to cart
- Remove services from cart
- Persist cart across sessions
- Case-specific carts

**Service Request Workflow:**
1. Client adds services to cart
2. Client submits for approval
3. Admin reviews request
4. Admin approves or rejects
5. Client notified of decision

**Service Types:**
- Consultations
- Doctor visits
- Transportation
- Document reviews
- Other legal services

### Use Cases

- Clients request additional services
- Administrators approve/reject requests
- Track service request processing
- Monitor service usage

---

## Appointment Scheduling

### Features

**Appointment Creation:**
- Schedule appointments
- Set date and time
- Select location (Office, Phone, Video)
- Assign to attorneys
- Add descriptions

**Appointment Management:**
- View appointment calendar
- List view and calendar view
- Filter by case, attorney, date
- Reschedule appointments
- Cancel appointments

**Appointment Status:**
- Scheduled
- Completed
- Cancelled

**Appointment Reminders:**
- Automatic reminders (if configured)
- Email notifications (if configured)
- In-app notifications

### Use Cases

- Schedule client-attorney meetings
- Manage attorney availability
- Track appointment history
- Coordinate case meetings

---

## Messaging and Communication

### Features

**Internal Messaging:**
- Send messages to attorneys/staff
- Receive messages from attorneys
- Threaded conversations
- Case-linked messages

**Message Features:**
- Read receipts
- Unread message indicators
- Message search
- Message filtering

**Support Tickets:**
- Create support tickets
- Priority levels (Low, Medium, High, Urgent)
- Ticket status tracking
- IT admin assignment

### Use Cases

- Client-attorney communication
- Internal staff communication
- Technical support requests
- Case-related discussions

---

## Invoice and Payment Processing

### Features

**Invoice Creation:**
- Create invoices for cases
- Automatic invoice numbering
- Line items and descriptions
- Due date setting
- Invoice status tracking

**Fee Calculation:**
- Automatic 25% fee calculation
- Case win amount input
- Compensation management
- Invoice generation

**Payment Processing:**
- PayFast integration
- Online payment gateway
- Payment status tracking
- Payment history
- Receipt generation

**Invoice Management:**
- View all invoices
- Filter by status
- Search invoices
- Edit invoices (before payment)
- Void invoices

### Use Cases

- Generate case fee invoices
- Process client payments
- Track payment status
- Financial reporting

---

## Analytics and Reporting

### Features

**Dashboard Analytics:**
- Key metrics display
- User activity trends
- Case statistics
- Service performance
- Financial metrics

**Interactive Charts:**
- User activity charts
- Case distribution charts
- Monthly trends
- Service performance graphs

**Report Generation:**
- Overview reports
- User activity reports
- Case statistics reports
- Service performance reports
- Financial reports (restricted)
- System health reports

**Report Export:**
- CSV export
- JSON export
- Date range filtering
- Role-based data filtering

### Use Cases

- Monitor system performance
- Track user activity
- Analyze case outcomes
- Generate business reports
- Performance monitoring

---

## Audit Logging

### Features

**Event Logging:**
- Login/logout events
- Data creation/updates/deletes
- Permission changes
- Security events
- System events

**Log Information:**
- Event type and category
- User and role
- IP address
- Timestamp
- Entity information
- Old/new values
- Metadata

**Log Filtering:**
- Filter by user
- Filter by event type
- Filter by date range
- Filter by severity
- Keyword search

**Log Export:**
- CSV export
- Log archiving
- Compliance reporting

### Use Cases

- Security monitoring
- Compliance reporting
- Troubleshooting
- System auditing
- Incident investigation

---

## Notification System

### Features

**Notification Types:**
- Case updates
- Service request status changes
- New messages
- Appointment reminders
- Invoice notifications
- Document uploads
- System announcements

**Notification Delivery:**
- In-app notifications
- Email notifications (if configured)
- Unread badge counts
- Action links

**Notification Management:**
- Mark as read
- Mark all as read
- View notification history
- Filter notifications

### Use Cases

- Alert users to important events
- Notify clients of case updates
- Remind users of appointments
- Inform users of system changes

---

## User Management and RBAC

### Features

**User Management:**
- Create user accounts
- Edit user information
- Activate/deactivate accounts
- Reset passwords
- View user activity

**Role-Based Access Control:**
- Multiple system roles
- Granular permissions
- Role-based data filtering
- Permission inheritance

**Roles:**
- Client
- Super Admin
- Admin
- Attorney
- Paralegal
- Case Manager
- Billing
- Receptionist
- IT Admin
- Compliance
- And more...

**Permissions:**
- Case permissions
- User management permissions
- Invoice permissions
- System permissions
- Report permissions

### Use Cases

- Manage user accounts
- Control system access
- Assign appropriate permissions
- Maintain security

---

## Support Ticket System

### Features

**Ticket Creation:**
- Create support tickets
- Set priority levels
- Add descriptions
- Attach files (if enabled)

**Ticket Management:**
- Assign to IT admin
- Update ticket status
- Add responses
- Track ticket history

**Ticket Priorities:**
- Low
- Medium
- High
- Urgent

**Ticket Status:**
- Open
- In Progress
- Resolved
- Closed

### Use Cases

- Technical support requests
- System issue reporting
- User assistance
- IT help desk

---

## Feature Status

### Production Ready Features

✅ All core features are production ready:
- Case Management
- Document Management
- Service Requests
- Appointments
- Messaging
- Invoices & Payments
- Analytics & Reporting
- Audit Logging
- Notifications
- User Management

### Future Enhancements

Potential future enhancements:
- Real-time messaging (WebSocket)
- Mobile app
- Advanced reporting
- Document versioning
- Email template system
- SMS notifications
- Advanced search
- Document collaboration

---

**Last Updated:** January 2025

