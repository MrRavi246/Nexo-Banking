# Transfer Money - Quick Start Guide

## 🚀 Quick Setup (3 Steps)

### Step 1: Update Database
Open **phpMyAdmin** and run this SQL:
```sql
-- Run this in your nexo_banking database
```
Then execute the file: `database/transfer_money_updates.sql`

### Step 2: Login to Dashboard
1. Start XAMPP (Apache + MySQL)
2. Go to: http://localhost/Nexo-Banking/Pages/auth/login.php
3. Login with your credentials

### Step 3: Test Transfer
1. Go to **Transfer Money** page
2. Select source account (loaded from your database)
3. Choose a recipient
4. Enter amount
5. Click **Send Money**

## ✅ What Works

- ✅ **Real account data** from database
- ✅ **Live balance updates** after transfers
- ✅ **Saved contacts** loaded dynamically
- ✅ **Transfer fees** automatically calculated
- ✅ **Instant transfers** between Nexo accounts
- ✅ **Transaction history** updated in real-time
- ✅ **Notifications** created for both parties

## 📊 Transfer Types

| Type | Fee | Processing Time |
|------|-----|----------------|
| Nexo to Nexo | **FREE** | Instant |
| External (ACH) | $2.99 | 1-3 business days |
| Wire Transfer | $15.00 | Same day |

## 🔧 How It Works

### Frontend → Backend Flow:
```
1. User fills form
2. JavaScript sends data to process_transfer.php
3. Backend validates session & account
4. Checks balance
5. Processes transaction in database
6. Updates both accounts (for internal transfers)
7. Creates notifications
8. Returns success/error to frontend
9. Frontend updates UI
```

### Database Changes:
```
Transactions Table:
- Debit from sender (-$150.00)
- Fee transaction (-$2.99)
- Credit to recipient (+$150.00) [if internal]

Accounts Table:
- Sender balance decreased
- Recipient balance increased [if internal]

Notifications Table:
- Notification for sender
- Notification for recipient [if internal]
```

## 📝 Testing Example

**Scenario:** Send $100 to Sarah Wilson (Nexo user)

**Before:**
- Your Checking: $12,450.75
- Sarah's Checking: $8,200.00

**After:**
- Your Checking: $12,350.75 (-$100)
- Sarah's Checking: $8,300.00 (+$100)
- Transaction recorded in both accounts
- Notifications sent to both users

## 🐛 Common Issues

**Issue:** Contacts not showing
**Fix:** Run `database/transfer_money_updates.sql` to create beneficiaries table

**Issue:** "Not authenticated" error
**Fix:** Make sure you're logged in. The page redirects if session is invalid.

**Issue:** Account dropdown empty
**Fix:** You need at least one active account. Check your database.

## 📂 Files Created/Modified

**Backend:**
- ✅ `backend/process_transfer.php` - Processes transfers
- ✅ `backend/get_transfer_data.php` - Loads accounts & contacts

**Frontend:**
- ✅ `Pages/dashboard/transfer-money.php` - Added PHP authentication
- ✅ `assets/js/transfer-money.js` - Added backend integration

**Database:**
- ✅ `database/transfer_money_updates.sql` - Schema updates

**Documentation:**
- ✅ `TRANSFER_MONEY_INTEGRATION.md` - Complete guide

## 🎯 API Endpoints

### Process Transfer
```
POST /backend/process_transfer.php

Request:
{
  "fromAccount": 123,
  "amount": 150,
  "transferMethod": "internal",
  "recipientType": "contact",
  "contactId": 45
}

Response:
{
  "success": true,
  "data": {
    "transaction_id": 789,
    "amount": 150.00,
    "new_balance": 12300.75
  }
}
```

### Get Transfer Data
```
GET /backend/get_transfer_data.php

Response:
{
  "success": true,
  "data": {
    "accounts": [...],
    "contacts": [...],
    "recent_recipients": [...]
  }
}
```

## 🔐 Security Features

- ✅ Session validation
- ✅ Account ownership verification
- ✅ SQL injection protection (prepared statements)
- ✅ Balance verification before transfer
- ✅ Database transactions (rollback on error)

---

**Ready to test!** Just run the SQL script and start transferring money! 💸
