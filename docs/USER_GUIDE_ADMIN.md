# Admin Portal User Guide

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** Administrators, Staff, and Attorneys using the MerLaws system

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [User Management](#user-management)
4. [Case Management](#case-management)
5. [Service Requests](#service-requests)
6. [Appointments](#appointments)
7. [Messaging and Communication](#messaging-and-communication)
8. [Finance and Invoicing](#finance-and-invoicing)
9. [Analytics and Reporting](#analytics-and-reporting)
10. [Audit Logs](#audit-logs)
11. [System Administration](#system-administration)
12. [Role-Based Access](#role-based-access)

---

## Getting Started

### Admin Login

1. **Access Admin Login:**
   - Navigate to `/app/admin-login.php`
   - Or use the admin login link

2. **Enter Credentials:**
   - **Email:** Your admin email address
   - **Password:** Your admin password
   - **Remember Me:** (Optional) Stay logged in

3. **Login:**
   - Click "Login" or "Sign In"
   - You'll be redirected to the admin dashboard

### Admin Roles

The system supports multiple admin roles with different permissions:

- **Super Admin:** Full system access
- **Admin:** User and case management
- **Manager:** Oversight and reporting
- **Partner:** Senior attorney access
- **Attorney:** Case handling and legal work
- **Paralegal:** Case support and documentation
- **Case Manager:** Case coordination
- **Billing:** Finance and invoicing
- **Receptionist:** Client communication
- **IT Admin:** System administration
- **Compliance:** Compliance and audit

---

## Dashboard Overview

### Admin Dashboard Features

The admin dashboard provides comprehensive system overview:

- **Key Metrics:**
  - Total clients
  - Active cases
  - Pending service requests
  - Overdue tasks
  - Recent activity

- **Quick Actions:**
  - Create new user
  - View all cases
  - Approve service requests
  - View messages
  - Generate reports

- **Role-Specific Widgets:**
  - Different widgets based on your role
  - Customized statistics
  - Relevant quick actions

### Dashboard Sections

1. **Statistics Cards:** Key metrics at a glance
2. **Recent Activity:** Latest system events
3. **Pending Items:** Items requiring attention
4. **Quick Links:** Common tasks and shortcuts
5. **Charts and Graphs:** Visual data representation

---

## User Management

### Creating a New User

1. **Access User Management:**
   - Click "User Management" in admin menu
   - Click "Create New User" button

2. **Fill in User Details:**
   - **Full Name:** User's full name
   - **Email:** Valid email address
   - **Password:** Temporary password (user should change)
   - **Role:** Select appropriate role
   - **Phone:** (Optional) Contact number
   - **Active Status:** Enable/disable account

3. **Set Permissions:**
   - Select role-based permissions
   - Customize access as needed

4. **Create User:**
   - Click "Create User"
   - User will receive login credentials via email (if configured)

### Managing Users

1. **View User List:**
   - Go to "User Management"
   - View all users in the system

2. **User Information:**
   - Name and email
   - Role and status
   - Last login
   - Account creation date

3. **User Actions:**
   - **Edit:** Update user information
   - **Deactivate:** Disable user account
   - **Reset Password:** Send password reset
   - **View Activity:** See user's activity log

### Editing User Information

1. **Select User:**
   - Click on user from the list
   - Or search for specific user

2. **Edit Details:**
   - Update name, email, phone
   - Change role (if permitted)
   - Update permissions
   - Activate/deactivate account

3. **Save Changes:**
   - Click "Save" or "Update User"
   - Changes take effect immediately

### Role Management

1. **View Roles:**
   - Go to "RBAC" or "Role Management"
   - View all system roles

2. **Role Permissions:**
   - View permissions for each role
   - Edit role permissions (if permitted)
   - Create custom roles (if permitted)

---

## Case Management

### Viewing All Cases

1. **Access Cases:**
   - Click "Cases" in admin menu
   - View all cases in the system

2. **Case List Features:**
   - Filter by status, type, priority
   - Search by case number, title, or client
   - Sort by date, status, priority
   - View case statistics

### Case Details

1. **Open Case:**
   - Click on a case from the list
   - View complete case information

2. **Case Information:**
   - Client information
   - Case details and description
   - Status and priority
   - Assigned attorney
   - Documents
   - Service requests
   - Messages
   - Activity timeline
   - Invoices

### Assigning Cases

1. **Select Case:**
   - Open the case you want to assign
   - Click "Assign" or "Edit Assignment"

2. **Assign Attorney:**
   - Select attorney from dropdown
   - Add assignment notes
   - Set assignment date

3. **Save Assignment:**
   - Click "Assign" or "Save"
   - Attorney will be notified

### Updating Case Status

1. **Change Status:**
   - Open case
   - Click "Update Status"
   - Select new status:
     - Draft
     - Active
     - Under Review
     - Closed

2. **Add Notes:**
   - Add status change notes
   - Explain reason for change

3. **Update:**
   - Click "Update Status"
   - Client will be notified

### Case Activities

- View complete activity timeline
- See all actions taken on case
- View document uploads
- Track status changes
- Monitor service requests

---

## Service Requests

### Viewing Service Requests

1. **Access Service Requests:**
   - Click "Service Requests" in admin menu
   - View all pending requests

2. **Request Information:**
   - Client and case information
   - Service requested
   - Request date
   - Urgency level
   - Client notes

### Approving Service Requests

1. **Review Request:**
   - Open service request
   - Review client notes
   - Check case details

2. **Approve Request:**
   - Click "Approve" button
   - Add admin notes (optional)
   - Set processing date

3. **Confirmation:**
   - Request status changes to "Approved"
   - Client receives notification
   - Service can be scheduled

### Rejecting Service Requests

1. **Review Request:**
   - Open service request
   - Determine if rejection is appropriate

2. **Reject Request:**
   - Click "Reject" button
   - **Required:** Add rejection reason
   - Explain why request was rejected

3. **Notification:**
   - Request status changes to "Rejected"
   - Client receives notification with reason
   - Request can be reviewed later

### Service Request Workflow

1. **Client Submits:** Request status = "Pending"
2. **Admin Reviews:** Request is reviewed
3. **Admin Decision:**
   - **Approve:** Status = "Approved", service scheduled
   - **Reject:** Status = "Rejected", client notified
4. **Completion:** Service completed, request closed

---

## Appointments

### Viewing Appointments

1. **Access Appointments:**
   - Click "Appointments" in admin menu
   - Or use calendar view

2. **Appointment Views:**
   - **List View:** All appointments in list
   - **Calendar View:** Visual calendar display
   - **Day View:** Appointments for specific day
   - **Week View:** Weekly schedule

### Creating Appointments

1. **Create Appointment:**
   - Click "Create Appointment" or "Schedule"
   - Select case
   - Fill in details:
     - Title and description
     - Date and time
     - Location
     - Assigned attorney
     - Duration

2. **Save:**
   - Click "Schedule Appointment"
   - Client and attorney notified

### Managing Appointments

- **Reschedule:** Change date/time
- **Cancel:** Cancel appointment with reason
- **Complete:** Mark as completed
- **Add Notes:** Add meeting notes after completion

### Calendar Features

- **Availability View:** See attorney availability
- **Conflict Detection:** Warns about scheduling conflicts
- **Reminders:** Automatic reminders (if configured)
- **Recurring Appointments:** (If enabled)

---

## Messaging and Communication

### Admin Messaging

1. **Access Messages:**
   - Click "Messages" in admin menu
   - View all message threads

2. **Message Features:**
   - Filter by case, user, or status
   - Search messages
   - View unread count
   - Threaded conversations

### Sending Messages

1. **Compose Message:**
   - Click "Compose" or "New Message"
   - Select recipient (client or staff)
   - Link to case (optional)
   - Enter subject and message

2. **Send:**
   - Click "Send"
   - Message delivered immediately

### Support Tickets

1. **View Tickets:**
   - Go to "Support" or "Tickets"
   - View all support tickets

2. **Ticket Management:**
   - Assign tickets to IT admin
   - Update ticket status
   - Add responses
   - Close resolved tickets

3. **Ticket Priorities:**
   - Low
   - Medium
   - High
   - Urgent

---

## Finance and Invoicing

### Creating Invoices

1. **Access Finance:**
   - Click "Finance" in admin menu
   - Or "Invoices"

2. **Create Invoice:**
   - Click "Create Invoice"
   - Select case
   - Enter invoice details:
     - Invoice number (auto-generated)
     - Amount
     - Description
     - Due date
     - Payment terms

3. **Add Line Items:**
   - Add service items
   - Set quantities and prices
   - Calculate totals

4. **Generate Invoice:**
   - Click "Create Invoice"
   - Invoice sent to client
   - Client receives notification

### Fee Calculation (Compensation Management)

1. **Access Compensation:**
   - Go to "Finance" → "Compensation Management"
   - Or use case-specific fee calculation

2. **Calculate Case Fee:**
   - Select closed case
   - Enter total won amount
   - System calculates 25% fee automatically
   - Generate invoice

3. **Create Invoice:**
   - Invoice automatically created
   - Sent to client
   - Payment link included

### Managing Invoices

- **View All Invoices:** List all invoices
- **Filter by Status:** Sent, Paid, Overdue
- **Search Invoices:** By number, client, case
- **Edit Invoices:** Update details (before payment)
- **Void Invoices:** Cancel invoices
- **Record Payments:** Manual payment entry

### Payment Processing

1. **View Payments:**
   - Go to "Payments" section
   - View all payment transactions

2. **Payment Methods:**
   - PayFast online payments (automatic)
   - Manual payment entry
   - Bank transfer recording
   - Cash/cheque recording

3. **Payment Status:**
   - Pending
   - Completed
   - Failed
   - Refunded

---

## Analytics and Reporting

### Dashboard Analytics

1. **Access Analytics:**
   - Click "Analytics" in admin menu
   - View comprehensive analytics dashboard

2. **Key Metrics:**
   - User activity trends
   - Case statistics
   - Service performance
   - Financial metrics
   - System health

### Generating Reports

1. **Access Reports:**
   - Go to "Analytics" → "Reports"
   - Or use quick report buttons

2. **Report Types:**
   - **Overview:** Key metrics summary
   - **Users:** User activity report
   - **Cases:** Case statistics report
   - **Services:** Service performance report
   - **Financial:** Financial/invoice report (restricted)
   - **System Health:** System metrics

3. **Export Reports:**
   - Select report type
   - Choose date range
   - Click "Export"
   - Download CSV or JSON format

### Analytics Features

- **Interactive Charts:** Visual data representation
- **Date Range Filtering:** Custom time periods
- **Role-Based Filtering:** Data filtered by role
- **Real-Time Updates:** Live data loading
- **Export Options:** CSV, JSON formats

---

## Audit Logs

### Viewing Audit Logs

1. **Access Audit Logs:**
   - Click "Audit" or "Audit Logs" in admin menu
   - View all system audit events

2. **Log Information:**
   - Event type and category
   - User and role
   - IP address
   - Timestamp
   - Event details
   - Severity level
   - Status

### Filtering Audit Logs

- **By User:** Filter by specific user
- **By Event Type:** Login, logout, create, update, delete
- **By Date Range:** Custom date filtering
- **By Severity:** Low, medium, high, critical
- **By Status:** Success, failure, warning
- **Keyword Search:** Search in messages

### Exporting Audit Logs

1. **Export Logs:**
   - Apply filters if needed
   - Click "Export CSV"
   - Download log file

2. **Use Cases:**
   - Security investigations
   - Compliance reporting
   - System monitoring
   - Troubleshooting

---

## System Administration

### System Health

1. **Access System Health:**
   - Go to "System" → "System Health"
   - View system status

2. **Health Metrics:**
   - Database connection status
   - File system status
   - Email service status
   - Payment gateway status
   - Performance metrics

### System Settings

1. **Access Settings:**
   - Go to "System" → "Settings"
   - Configure system-wide settings

2. **Settings Categories:**
   - General settings
   - Email configuration
   - Payment settings
   - Security settings
   - Notification settings

### Content Management

1. **Access Content:**
   - Go to "Content" in admin menu
   - Manage website content

2. **Content Types:**
   - News articles
   - Service descriptions
   - Static pages
   - Announcements

---

## Role-Based Access

### Understanding Roles

Different roles have different access levels:

- **Super Admin:** Full system access
- **Admin:** User and case management
- **Attorney:** Case handling, messaging
- **Paralegal:** Case support, documents
- **Billing:** Finance, invoices, payments
- **Receptionist:** Appointments, messaging
- **IT Admin:** System administration, support tickets

### Permission System

1. **View Permissions:**
   - Go to "RBAC" or "Permissions"
   - View all system permissions

2. **Permission Categories:**
   - Case permissions
   - User permissions
   - Invoice permissions
   - System permissions
   - Report permissions

### Access Restrictions

- Some features are role-restricted
- Financial reports restricted to billing/partner/super_admin
- System settings restricted to IT admin/super admin
- User management restricted to admin roles

---

## Best Practices

### Case Management

- Update case status promptly
- Assign cases to appropriate attorneys
- Document all case activities
- Communicate regularly with clients

### Service Requests

- Review requests promptly
- Provide clear approval/rejection reasons
- Track request processing times
- Follow up on approved requests

### Communication

- Respond to messages promptly
- Use professional language
- Link messages to cases
- Document important communications

### Security

- Use strong passwords
- Don't share login credentials
- Review audit logs regularly
- Report suspicious activity

---

## Troubleshooting

### Common Issues

#### Cannot Access Feature

**Problem:** Feature not visible or access denied

**Solutions:**
1. Check your role and permissions
2. Verify feature is enabled
3. Contact system administrator
4. Check audit logs for access attempts

#### Reports Not Generating

**Problem:** Reports fail to generate or export

**Solutions:**
1. Check date range is valid
2. Verify you have permission for report type
3. Check system logs
4. Try different export format

#### Invoice Issues

**Problem:** Cannot create or edit invoices

**Solutions:**
1. Verify billing permissions
2. Check case status
3. Verify invoice data is complete
4. Contact billing department

---

**Last Updated:** January 2025

