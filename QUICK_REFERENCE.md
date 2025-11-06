# 🚀 Nexo Banking - Quick Reference Card

## 📋 Setup Checklist

- [ ] Run `database/nexo_schema.sql` in phpMyAdmin
- [ ] Run `database/update_schema.sql` in phpMyAdmin
- [ ] Create `uploads/profiles/` folder with write permissions
- [ ] Verify XAMPP Apache and MySQL are running
- [ ] Test admin login
- [ ] Test user registration
- [ ] Test approval workflow
- [ ] **CHANGE DEFAULT ADMIN PASSWORD!**

## 🔑 Default Credentials

### Admin Access
- **URL:** http://localhost/Nexo-Banking/admin/login.php
- **Username:** admin
- **Password:** Admin@123
- ⚠️ **CHANGE THIS PASSWORD IMMEDIATELY!**

## 📍 Important URLs

### User Pages
- Registration: `/Pages/auth/register.php`
- Login: `/Pages/auth/login.php`
- Dashboard: `/Pages/dashboard/Dashboard.php`

### Admin Pages
- Admin Login: `/admin/login.php`
- User Management: `/admin/users.php`
- Dashboard: `/admin/dashboard.php`

### Backend API
- User Signup: `/backend/signup.php`
- User Login: `/backend/login.php`
- User Logout: `/backend/logout.php`
- Admin Login: `/backend/admin_login.php`
- Manage Users: `/backend/manage_users.php`

## 🔄 User Registration Flow

```
1. User Registers → Status: PENDING
   ↓
2. Cannot Login (shows "pending approval")
   ↓
3. Admin Reviews in /admin/users.php
   ↓
4. Admin Approves → Status: ACTIVE
   ↓
5. User Can Now Login
   ↓
6. Redirects to Dashboard
```

## 📊 User Status Values

| Status | Description | Can Login? |
|--------|-------------|------------|
| `pending` | Awaiting admin approval | ❌ No |
| `active` | Approved by admin | ✅ Yes |
| `rejected` | Rejected by admin | ❌ No |
| `suspended` | Temporarily disabled | ❌ No |
| `inactive` | Deactivated | ❌ No |

## 🎯 Testing Steps

### 1️⃣ Test Registration
```
1. Go to /Pages/auth/register.php
2. Fill all fields (use dummy data)
3. Submit → Should see "Pending approval" message
4. Try to login → Should see "pending" error
```

### 2️⃣ Test Admin Login
```
1. Go to /admin/login.php
2. Enter: admin / Admin@123
3. Should redirect to admin dashboard
```

### 3️⃣ Test Approval
```
1. In admin panel, click "Users"
2. See pending user in list
3. Click "Approve" button
4. Should see success message
5. User should now appear in "Active" tab
```

### 4️⃣ Test User Login
```
1. Go to /Pages/auth/login.php
2. Enter account number (from admin panel)
3. Enter password (from registration)
4. Should redirect to dashboard
```

## 🗄️ Database Tables

### Core Tables
- `users` - User accounts
- `accounts` - Bank accounts (checking, savings)
- `sessions` - User login sessions
- `admin_users` - Admin accounts
- `admin_sessions` - Admin login sessions
- `audit_logs` - Security audit trail
- `notifications` - User notifications

## 🛠️ Useful SQL Queries

### Check Pending Users
```sql
SELECT user_id, username, email, first_name, last_name 
FROM users 
WHERE status = 'pending';
```

### Get Account Number for Login
```sql
SELECT u.username, a.account_number 
FROM users u 
JOIN accounts a ON u.user_id = a.user_id 
WHERE a.account_type = 'checking';
```

### Manually Approve User
```sql
UPDATE users SET status = 'active', approved_by = 1 WHERE user_id = X;
UPDATE accounts SET status = 'active' WHERE user_id = X;
```

## 🔐 Password Requirements

- ✅ Minimum 8 characters
- ✅ At least 1 uppercase letter
- ✅ At least 1 lowercase letter
- ✅ At least 1 number
- ✅ At least 1 special character
- ✅ Example: `SecurePass@123`

## 📁 File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/profiles/
```

Or in Windows XAMPP:
- Right-click folder → Properties → Security
- Give "Users" full control

## ⚠️ Common Issues & Fixes

### Issue: Cannot connect to database
**Fix:** Check MySQL is running, verify credentials in `backend/config.php`

### Issue: Admin cannot login
**Fix:** Run `update_schema.sql` again to create admin_users table

### Issue: User registration fails
**Fix:** Create `uploads/profiles/` folder and set permissions

### Issue: Approval doesn't work
**Fix:** Check browser console for errors, verify admin is logged in

### Issue: Redirects to wrong page
**Fix:** Update URLs in `backend/config.php`

## 📝 Configuration Files

### Main Config: `backend/config.php`
```php
DB_HOST = 'localhost'
DB_NAME = 'nexo_banking'
DB_USER = 'root'
DB_PASS = ''

BASE_URL = 'http://localhost/Nexo-Banking'
```

## 🎨 Frontend Assets

### JavaScript Files
- `assets/js/login.js` - User login handling
- `assets/js/register.js` - Registration handling
- `assets/js/admin-users.js` - Admin user management

### CSS Files
- `assets/style/login.css` - Login page styles
- `assets/style/register.css` - Registration page styles
- `assets/style/admin.css` - Admin panel styles

## 📞 Support Resources

- **Detailed Docs:** `/backend/README.md`
- **Setup Guide:** `/SETUP.md`
- **Implementation Summary:** `/IMPLEMENTATION_SUMMARY.md`
- **Test Queries:** `/database/test_queries.sql`

## 🚨 Security Checklist

- [ ] Change default admin password
- [ ] Review database credentials
- [ ] Check file upload permissions
- [ ] Enable error logging (disable display in production)
- [ ] Set session timeout appropriately
- [ ] Regular backup of database
- [ ] Monitor audit_logs table
- [ ] Implement HTTPS in production

## 💡 Quick Tips

1. **Finding Account Number:** 
   - Check admin panel → Users → Click on user
   - Or query: `SELECT account_number FROM accounts WHERE user_id = X`

2. **Testing Quickly:**
   - Use `database/test_queries.sql` for common tasks
   - Browser dev tools → Network tab for API debugging
   - Check `audit_logs` table for login attempts

3. **Resetting Test User:**
   ```sql
   UPDATE users SET status = 'pending' WHERE user_id = X;
   UPDATE accounts SET status = 'inactive' WHERE user_id = X;
   ```

## 🎓 Learning Resources

- PHP PDO: https://www.php.net/manual/en/book.pdo.php
- Password Hashing: https://www.php.net/manual/en/function.password-hash.php
- Session Security: https://www.php.net/manual/en/session.security.php

---

**Remember:** This is a development setup. For production:
- Use HTTPS
- Change all default passwords
- Enable proper error handling
- Set up automated backups
- Implement rate limiting
- Add email verification
- Enable 2FA for admin

**Need Help?** Check the detailed documentation in `/backend/README.md`
