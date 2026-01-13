# API Documentation

**Version:** 3.0  
**Last Updated:** January 2025  
**Target Audience:** Developers, API Integrators

---

## Table of Contents

1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Authentication APIs](#authentication-apis)
6. [Case Management APIs](#case-management-apis)
7. [User Management APIs](#user-management-apis)
8. [Service Request APIs](#service-request-apis)
9. [Appointment APIs](#appointment-apis)
10. [Messaging APIs](#messaging-apis)
11. [Notification APIs](#notification-apis)
12. [Payment and Invoice APIs](#payment-and-invoice-apis)
13. [Analytics APIs](#analytics-apis)
14. [Rate Limiting](#rate-limiting)

---

## API Overview

### Base URL

All API endpoints are relative to the application root:

```
https://www.merlaws.com/app/api/
```

### Content Type

All API endpoints return JSON:

```
Content-Type: application/json
```

### HTTP Methods

- **GET:** Retrieve data
- **POST:** Create or submit data
- **PUT:** Update data (if implemented)
- **DELETE:** Delete data (if implemented)

---

## Authentication

### Session-Based Authentication

Most API endpoints require an active session. The system uses PHP sessions with secure cookies.

### Authentication Requirements

1. **Login First:** Use `/app/api/auth-login.php` to authenticate
2. **Session Cookie:** Session cookie is automatically sent with requests
3. **CSRF Token:** POST requests require CSRF token

### CSRF Protection

For POST requests, include CSRF token:

```javascript
// Get CSRF token from page
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Include in POST request
fetch('/app/api/endpoint.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        'csrf_token': csrfToken,
        // ... other data
    })
});
```

---

## Response Format

### Success Response

```json
{
    "success": true,
    "data": {
        "key": "value"
    },
    "message": "Optional success message"
}
```

### Error Response

```json
{
    "success": false,
    "error": "Error message",
    "errors": ["Error 1", "Error 2"]
}
```

---

## Error Handling

### HTTP Status Codes

- **200 OK:** Request successful
- **400 Bad Request:** Invalid request data
- **401 Unauthorized:** Authentication required
- **403 Forbidden:** Insufficient permissions
- **404 Not Found:** Resource not found
- **500 Internal Server Error:** Server error

### Error Response Format

```json
{
    "success": false,
    "error": "Human-readable error message",
    "errors": ["Detailed error 1", "Detailed error 2"],
    "code": "ERROR_CODE"
}
```

---

## Authentication APIs

### Login

**Endpoint:** `POST /app/api/auth-login.php`

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Login successful",
    "redirect": "/app/dashboard.php"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid email or password",
    "errors": ["Valid email is required.", "Password must be at least 8 characters."]
}
```

**Notes:**
- Requires CSRF token
- Rate limited (5 attempts per 15 minutes per IP)
- Creates session on success
- Logs audit event

### Logout

**Endpoint:** `POST /app/api/logout.php` (or page-based logout)

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## Case Management APIs

### Get Case Attorneys

**Endpoint:** `GET /app/api/cases.php?action=get_attorneys&case_id={id}`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "name": "Attorney Name",
            "email": "attorney@example.com"
        }
    ]
}
```

### Get Active Cases

**Endpoint:** `GET /app/api/get_cases.php`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 456,
            "title": "Case Title",
            "status": "active",
            "case_type": "medical_negligence"
        }
    ]
}
```

---

## User Management APIs

### Get User Session

**Endpoint:** `GET /app/api/session.php`

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 123,
        "name": "User Name",
        "email": "user@example.com",
        "role": "client"
    }
}
```

### Get Users

**Endpoint:** `GET /app/api/users.php` (Admin only)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "name": "User Name",
            "email": "user@example.com",
            "role": "client",
            "is_active": true
        }
    ]
}
```

---

## Service Request APIs

### Get Cart Items

**Endpoint:** `GET /app/api/cart.php?case_id={id}`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 789,
            "service_id": 10,
            "service_name": "Consultation",
            "status": "cart",
            "notes": "Optional notes"
        }
    ]
}
```

---

## Appointment APIs

### Get Appointments

**Endpoint:** `GET /app/api/appointments.php`

**Query Parameters:**
- `case_id` (optional): Filter by case
- `status` (optional): Filter by status
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 101,
            "case_id": 456,
            "title": "Consultation",
            "start_time": "2025-01-15 10:00:00",
            "end_time": "2025-01-15 11:00:00",
            "status": "scheduled",
            "location": "Office"
        }
    ]
}
```

### Create Appointment

**Endpoint:** `POST /app/api/appointments.php`

**Request:**
```json
{
    "case_id": 456,
    "title": "Consultation",
    "description": "Initial consultation",
    "start_time": "2025-01-15 10:00:00",
    "end_time": "2025-01-15 11:00:00",
    "location": "Office",
    "assigned_to": 123
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "appointment_id": 101
    },
    "message": "Appointment created successfully"
}
```

---

## Messaging APIs

Messaging APIs are primarily handled through the web interface. API endpoints may be available for specific operations.

---

## Notification APIs

### Get Notifications

**Endpoint:** `GET /app/api/notifications.php`

**Query Parameters:**
- `unread_only` (optional): Return only unread notifications

**Response:**
```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "id": 201,
                "type": "case_update",
                "title": "Case Updated",
                "message": "Your case has been updated",
                "is_read": false,
                "created_at": "2025-01-15 10:00:00",
                "action_url": "/app/cases/view.php?id=456"
            }
        ],
        "unread_count": 5
    }
}
```

### Mark Notification as Read

**Endpoint:** `POST /app/api/notifications.php?action=mark_read`

**Request:**
```json
{
    "notification_id": 201
}
```

**Response:**
```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

---

## Payment and Invoice APIs

### Get Invoices

**Endpoint:** `GET /app/api/invoices.php`

**Query Parameters:**
- `case_id` (optional): Filter by case
- `status` (optional): Filter by status

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 301,
            "invoice_number": "INV-2025-001",
            "case_id": 456,
            "amount": 1000.00,
            "status": "sent",
            "due_date": "2025-02-15",
            "created_at": "2025-01-15"
        }
    ]
}
```

### Get Payments

**Endpoint:** `GET /app/api/payments.php`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 401,
            "invoice_id": 301,
            "amount": 1000.00,
            "payment_method": "payfast",
            "status": "completed",
            "payment_date": "2025-01-20"
        }
    ]
}
```

---

## Analytics APIs

### Get Dashboard Statistics

**Endpoint:** `GET /app/api/admin-analytics.php?action=dashboard_stats`

**Authentication:** Admin only

**Response:**
```json
{
    "success": true,
    "data": {
        "total_users": 150,
        "active_cases": 45,
        "pending_requests": 12,
        "avg_processing_hours": 48
    }
}
```

### Get User Activity

**Endpoint:** `GET /app/api/admin-analytics.php?action=user_activity&days=30`

**Query Parameters:**
- `days` (optional): Number of days (default: 30)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "date": "2025-01-15",
            "event_type": "login",
            "count": 25
        }
    ]
}
```

### Get Case Statistics

**Endpoint:** `GET /app/api/admin-analytics.php?action=case_statistics`

**Response:**
```json
{
    "success": true,
    "data": {
        "total_cases": 200,
        "active_cases": 45,
        "closed_cases": 150,
        "by_type": {
            "medical_negligence": 100,
            "mva": 50,
            "other": 50
        }
    }
}
```

### Get Service Performance

**Endpoint:** `GET /app/api/admin-analytics.php?action=service_performance`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "service_name": "Consultation",
            "total_requests": 50,
            "approved": 45,
            "rejected": 5,
            "avg_processing_hours": 24
        }
    ]
}
```

### Get Monthly Trends

**Endpoint:** `GET /app/api/admin-analytics.php?action=monthly_trends`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "month": "2025-01",
            "new_cases": 20,
            "new_users": 15,
            "completed_cases": 10
        }
    ]
}
```

---

## Report Export API

### Export Report

**Endpoint:** `GET /app/api/export-report.php`

**Query Parameters:**
- `type`: Report type (overview, users, cases, services, financial, system_health)
- `format`: Export format (csv, json)
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:**
- **CSV:** Returns CSV file with appropriate headers
- **JSON:** Returns JSON response

**Example:**
```
GET /app/api/export-report.php?type=overview&format=csv&date_from=2025-01-01&date_to=2025-01-31
```

**Notes:**
- Financial reports restricted to billing/partner/super_admin roles
- Exports are logged in audit trail
- CSV includes BOM for Excel compatibility

---

## Rate Limiting

### Login Rate Limiting

Login endpoint has rate limiting:
- **Limit:** 5 failed attempts per 15 minutes per IP/email combination
- **Storage:** Temporary files in system temp directory
- **Response:** "Too many failed attempts. Please try again later."

### General API Rate Limiting

Currently, most API endpoints do not have explicit rate limiting beyond session-based authentication. Consider implementing rate limiting for production use.

---

## API Usage Examples

### JavaScript/Fetch Example

```javascript
// Login
async function login(email, password, csrfToken) {
    const response = await fetch('/app/api/auth-login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'email': email,
            'password': password,
            'csrf_token': csrfToken
        })
    });
    
    const data = await response.json();
    if (data.success) {
        window.location.href = data.redirect;
    } else {
        console.error(data.error);
    }
}

// Get notifications
async function getNotifications() {
    const response = await fetch('/app/api/notifications.php');
    const data = await response.json();
    if (data.success) {
        return data.data.notifications;
    }
    return [];
}
```

### cURL Example

```bash
# Login
curl -X POST https://www.merlaws.com/app/api/auth-login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=user@example.com&password=password123&csrf_token=TOKEN" \
  -c cookies.txt

# Get notifications (with session cookie)
curl -X GET https://www.merlaws.com/app/api/notifications.php \
  -b cookies.txt
```

---

## API Versioning

Currently, the API does not use versioning. All endpoints are under `/app/api/`. Consider implementing versioning (e.g., `/app/api/v1/`) for future updates.

---

## Best Practices

1. **Always use HTTPS** in production
2. **Include CSRF tokens** in POST requests
3. **Handle errors gracefully** in client code
4. **Validate data** on both client and server
5. **Use appropriate HTTP methods**
6. **Follow RESTful conventions** where possible
7. **Implement rate limiting** for public endpoints
8. **Log API usage** for monitoring

---

## Support

For API integration questions:
1. Review this documentation
2. Check [Technical Staff Guide](TECHNICAL_STAFF_GUIDE.md)
3. Review source code in `app/api/`
4. Contact development team

---

**Last Updated:** January 2025

