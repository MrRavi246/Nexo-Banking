# Nexo Banking - Quick Setup Instructions

## Step 1: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create the database and tables:
   - Run: `database/nexo_schema.sql`
   - Run: `database/update_schema.sql`

## Step 2: Test the System

### Test User Registration
1. Go to: http://localhost/Nexo-Banking/Pages/auth/register.php
2. Fill out the registration form
3. Submit - You should see "pending approval" message
4. **Note:** You cannot login yet

### Test Admin Login
1. Go to: http://localhost/Nexo-Banking/admin/login.php
2. Login with:
   - Username: `admin`
   - Password: `Admin@123`
3. You should be redirected to the admin dashboard

### Approve the User
1. In admin panel, click on "Users" in the sidebar
2. You should see the pending user registration
3. Click "Approve" button
4. User will now be able to login

### Test User Login
1. Go to: http://localhost/Nexo-Banking/Pages/auth/login.php
2. Login with:
   - Account Number: (from the pending user card in admin)
   - Password: (the password you set during registration)
3. You should be redirected to the dashboard

## Important Notes

### Default Admin Account
- **Username:** admin
- **Email:** admin@nexo.com
- **Password:** Admin@123
- **CHANGE THIS PASSWORD IMMEDIATELY IN PRODUCTION!**

### User Registration Flow
1. User registers → Status: `pending`
2. Admin approves → Status: `active`
3. User can now login and access dashboard

### User Account Status
- **pending**: Waiting for admin approval (cannot login)
- **active**: Approved by admin (can login)
- **rejected**: Rejected by admin (cannot login)
- **suspended**: Temporarily disabled
- **inactive**: Deactivated account

## File Structure

```
Nexo-Banking/
├── backend/
│   ├── config.php              # Configuration
│   ├── functions.php           # Helper functions
│   ├── signup.php              # Registration handler
│   ├── login.php               # Login handler
│   ├── logout.php              # Logout handler
│   ├── auth_check.php          # User auth middleware
│   ├── admin_login.php         # Admin login handler
│   ├── admin_auth_check.php    # Admin auth middleware
│   ├── manage_users.php        # User management API
│   └── README.md               # Detailed documentation
├── database/
│   ├── nexo_schema.sql         # Main database schema
│   └── update_schema.sql       # Admin approval updates
├── Pages/
│   └── auth/
│       ├── login.php           # User login page
│       └── register.php        # User registration page
├── admin/
│   ├── login.php               # Admin login page
│   └── users.php               # User management page
└── assets/
    └── js/
        ├── login.js            # User login scripts
        ├── register.js         # User registration scripts
        └── admin-users.js      # Admin user management scripts
```

## Troubleshooting

### Cannot connect to database
- Make sure XAMPP MySQL is running
- Check database credentials in `/backend/config.php`

### Admin cannot login
- Verify `admin_users` table exists
- Check if default admin was inserted (run update_schema.sql)
- Try clearing browser cookies

### User registration fails
- Check if `uploads/profiles/` directory is writable
- Verify all required fields are filled
- Check browser console for JavaScript errors

### Approval not working
- Check if admin is logged in
- Verify `admin_sessions` table exists
- Check browser console for API errors

## Security Checklist

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Set proper file permissions on uploads directory
- [ ] Enable HTTPS in production
- [ ] Configure proper session security
- [ ] Review and update allowed file types
- [ ] Set up email notifications
- [ ] Implement rate limiting for login attempts
- [ ] Add CSRF protection
- [ ] Regular security audits

## Next Steps

1. Change admin password
2. Test complete user flow (register → approve → login)
3. Customize email templates for notifications
4. Add more admin users if needed
5. Configure backup system
6. Set up monitoring and logging

## Support

For detailed documentation, see: `/backend/README.md`
