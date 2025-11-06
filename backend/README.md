# Nexo Banking - Backend Setup Guide

## Overview
This backend implementation includes:
- User registration with admin approval workflow
- Secure login system with session management
- Admin panel for user approval/rejection
- Database structure with proper relationships

## Database Setup

### Step 1: Run the Main Schema
1. Open phpMyAdmin or MySQL command line
2. Execute the file: `database/nexo_schema.sql`
   - This creates the database and all required tables

### Step 2: Run the Update Schema
1. Execute the file: `database/update_schema.sql`
   - This adds admin approval functionality
   - Creates admin users table
   - Adds default admin account

## Default Admin Credentials
```
Username: admin
Email: admin@nexo.com
Password: Admin@123
```
**IMPORTANT:** Change this password after first login!

## Features

### User Registration (`/Pages/auth/register.php`)
- Users fill out registration form
- Account status is set to `pending` by default
- Two accounts (checking and savings) are created but remain `inactive`
- Users cannot login until approved by admin

### User Login (`/Pages/auth/login.php`)
- Users login with their account number and password
- System checks user status:
  - `pending`: Shows "waiting for approval" message
  - `rejected`: Shows "registration rejected" message
  - `suspended` or `inactive`: Shows appropriate error
  - `active`: Allows login and redirects to dashboard
- Session is created and stored in database
- Last login timestamp is updated

### Admin Panel (`/admin/users.php`)
**Features:**
- View all pending user registrations
- Approve users (activates account and all associated accounts)
- Reject users (with reason)
- View all users by status (pending, active, rejected)
- Automatic notification to users on approval/rejection

### Admin Login (`/admin/login.php`)
- Separate admin authentication system
- Admin sessions tracked separately from user sessions

## Backend Files Structure

```
backend/
├── config.php              # Database and application configuration
├── functions.php           # Helper functions (validation, sessions, etc.)
├── signup.php              # User registration handler
├── login.php               # User login handler
├── logout.php              # User logout handler
├── auth_check.php          # User authentication middleware
├── admin_login.php         # Admin login handler
├── admin_auth_check.php    # Admin authentication middleware
└── manage_users.php        # User management API endpoints
```

## API Endpoints

### User Management (`/backend/manage_users.php`)

#### Get Pending Users
```
GET /backend/manage_users.php?action=get_pending_users
```
Returns all users with `pending` status.

#### Approve User
```
POST /backend/manage_users.php?action=approve_user
Parameters:
- user_id: ID of the user to approve
```
Approves user registration and activates accounts.

#### Reject User
```
POST /backend/manage_users.php?action=reject_user
Parameters:
- user_id: ID of the user to reject
- rejection_reason: Reason for rejection
```
Rejects user registration with reason.

#### Get All Users
```
GET /backend/manage_users.php?action=get_all_users&status={status}
Parameters:
- status: 'all', 'pending', 'active', 'rejected', 'suspended'
```
Returns users filtered by status.

## Security Features

1. **Password Security**
   - Minimum 8 characters
   - Must contain uppercase, lowercase, number, and special character
   - Hashed using bcrypt (PASSWORD_BCRYPT)

2. **Session Management**
   - Secure session tokens (64-character random hex)
   - Session expiration (24 hours default, 30 days with "Remember Me")
   - Database-backed sessions with automatic cleanup

3. **Input Validation**
   - All inputs sanitized with `htmlspecialchars()`
   - Email validation
   - Age verification (18+ required)
   - File upload restrictions (type and size)

4. **SQL Injection Protection**
   - PDO with prepared statements
   - Parameter binding for all queries

5. **Audit Logging**
   - All login attempts logged
   - User approvals/rejections logged
   - IP address and user agent tracked

## File Upload Configuration

```php
Max file size: 5MB
Allowed types: JPG, JPEG, PNG, GIF
Upload directory: /uploads/profiles/
```

## User Workflow

### Registration Process
1. User fills registration form
2. Backend validates all inputs
3. User account created with status `pending`
4. Two accounts created (checking & savings) with status `inactive`
5. User receives "pending approval" message
6. Admin is notified (can be enhanced with email)

### Approval Process
1. Admin logs into admin panel
2. Views pending users in "Users" section
3. Reviews user details
4. Approves or rejects with reason
5. System updates user and account statuses
6. Notification created for user

### Login Process
1. User enters account number and password
2. System validates credentials
3. Checks user status
4. If approved, creates session and redirects to dashboard
5. If pending/rejected, shows appropriate message

## Database Tables Modified

### users table
New columns added:
- `status`: ENUM('pending', 'active', 'inactive', 'suspended', 'rejected')
- `approved_by`: INT (references admin_users)
- `approved_at`: TIMESTAMP
- `rejection_reason`: TEXT

### New Tables Created
- `admin_users`: Admin user accounts
- `admin_sessions`: Admin session tracking

## Configuration

Edit `/backend/config.php` to customize:

```php
// Database
DB_HOST, DB_USER, DB_PASS, DB_NAME

// Session Lifetime
SESSION_LIFETIME = 3600 * 24; // 24 hours
REMEMBER_ME_LIFETIME = 3600 * 24 * 30; // 30 days

// File Upload
MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif']

// URLs
BASE_URL, DASHBOARD_URL, LOGIN_URL, ADMIN_DASHBOARD_URL, ADMIN_LOGIN_URL
```

## Testing

### Test User Registration
1. Navigate to `/Pages/auth/register.php`
2. Fill in all required fields
3. Submit form
4. Should see "pending approval" message

### Test Admin Approval
1. Login to admin panel `/admin/login.php`
2. Navigate to Users section
3. Click "Approve" on pending user
4. Check user can now login

### Test User Login
1. Try to login before approval (should fail)
2. After admin approval, login should succeed
3. Should redirect to dashboard

## Troubleshooting

### "Database connection failed"
- Check MySQL is running
- Verify database credentials in `config.php`
- Ensure database `nexo_banking` exists

### "Session expired"
- Clear browser cookies
- Check session table in database
- Verify session timeout settings

### "File upload failed"
- Check `/uploads/profiles/` directory exists and is writable
- Verify file size and type restrictions
- Check PHP upload_max_filesize in php.ini

### Admin can't login
- Verify admin account exists in `admin_users` table
- Check password: Admin@123
- Clear admin sessions table if locked out

## Next Steps

Recommended enhancements:
1. Email notifications on approval/rejection
2. Password reset functionality
3. Two-factor authentication
4. Enhanced admin dashboard with statistics
5. User profile editing
6. Account suspension/reactivation workflow
7. Bulk user approval
8. Export user data to CSV

## Support

For issues or questions:
- Check database error logs
- Enable error reporting in development: `error_reporting(E_ALL)`
- Review audit_logs table for security events
