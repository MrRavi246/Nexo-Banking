# Transfer Money - Backend Integration Guide

## Overview
Complete backend functionality has been added to the Transfer Money page, enabling real money transfers between Nexo accounts and external banks.

## Files Modified/Created

### Backend Files
1. **`backend/process_transfer.php`** - Main API endpoint for processing transfers
   - Validates user authentication and session
   - Verifies account ownership and sufficient balance
   - Processes internal (Nexo-to-Nexo) and external transfers
   - Handles transaction fees
   - Creates transaction records
   - Sends notifications

2. **`backend/get_transfer_data.php`** - API endpoint for loading transfer data
   - Fetches user's accounts
   - Loads saved beneficiaries/contacts
   - Retrieves recent transfer recipients

### Frontend Files
3. **`Pages/dashboard/transfer-money.php`** - Updated with PHP authentication
   - Session validation
   - Database connection
   - Pre-loads user accounts from database
   - Dynamic account dropdown population

4. **`assets/js/transfer-money.js`** - Enhanced with backend integration
   - `loadTransferData()` - Fetches contacts and accounts from API
   - `processTransfer()` - Sends transfer request to backend
   - `updateContactsList()` - Dynamically updates contacts from database
   - `updateAccountBalance()` - Updates balance after successful transfer

### Database Files
5. **`database/transfer_money_updates.sql`** - Database schema updates
   - Adds `recipient_name` and `recipient_account` columns to transactions
   - Creates `beneficiaries` table for saved contacts
   - Includes sample data for testing

## Features Implemented

### 1. Transfer Methods
- ✅ **Nexo to Nexo** - Instant transfers between Nexo accounts (Free)
- ✅ **External Transfer** - ACH transfers to other banks ($2.99 fee)
- ✅ **Wire Transfer** - International transfers ($15.00 fee)

### 2. Recipient Options
- ✅ **Saved Contacts** - Select from previously saved beneficiaries
- ✅ **New Recipient** - Add new recipient details on-the-fly
- ✅ **Dynamic Contact Loading** - Contacts loaded from database

### 3. Transaction Processing
- ✅ **Balance Verification** - Checks sufficient funds before transfer
- ✅ **Account Ownership** - Validates user owns the source account
- ✅ **Database Transactions** - Uses SQL transactions for data integrity
- ✅ **Fee Calculation** - Automatically calculates and applies fees
- ✅ **Dual Entry** - For internal transfers, credits recipient account

### 4. Security Features
- ✅ **Session Validation** - Verifies active user session
- ✅ **Authentication Check** - Redirects to login if not authenticated
- ✅ **SQL Injection Protection** - Uses prepared statements
- ✅ **Amount Validation** - Server-side validation of transfer amounts

### 5. User Experience
- ✅ **Real-time Balance Updates** - Account balances update after transfer
- ✅ **Success/Error Notifications** - Clear feedback messages
- ✅ **Loading States** - Visual feedback during processing
- ✅ **Recent Transfers** - New transfers added to history

## Database Schema

### Transactions Table Updates
```sql
ALTER TABLE transactions 
ADD COLUMN recipient_name VARCHAR(255) DEFAULT NULL,
ADD COLUMN recipient_account VARCHAR(50) DEFAULT NULL;
```

### New Beneficiaries Table
```sql
CREATE TABLE beneficiaries (
    beneficiary_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    beneficiary_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    routing_number VARCHAR(20),
    account_type ENUM('checking', 'savings'),
    email VARCHAR(255),
    phone_number VARCHAR(20),
    status ENUM('active', 'inactive', 'deleted'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

## API Endpoints

### 1. Process Transfer
**Endpoint:** `POST /backend/process_transfer.php`

**Request Body:**
```json
{
  "fromAccount": "123",
  "amount": 150.00,
  "transferMethod": "internal",
  "recipientType": "contact",
  "contactId": "45",
  "transferDate": "2025-11-07",
  "memo": "Rent payment",
  "securityMethod": "sms"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Transfer processed successfully",
  "data": {
    "transaction_id": 789,
    "amount": 150.00,
    "fee": 0.00,
    "total": 150.00,
    "recipient": "Sarah Wilson",
    "status": "completed",
    "date": "2025-11-07",
    "new_balance": 12300.00
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Insufficient funds in source account"
}
```

### 2. Get Transfer Data
**Endpoint:** `GET /backend/get_transfer_data.php`

**Response:**
```json
{
  "success": true,
  "message": "Transfer data retrieved successfully",
  "data": {
    "accounts": [
      {
        "account_id": 123,
        "account_type": "checking",
        "account_number": "1234567890",
        "balance": 12450.75,
        "currency": "USD",
        "status": "active"
      }
    ],
    "contacts": [
      {
        "beneficiary_id": 1,
        "beneficiary_name": "Sarah Wilson",
        "account_number": "9876543210",
        "bank_name": "Chase Bank",
        "email": "sarah@email.com"
      }
    ],
    "recent_recipients": [...]
  }
}
```

## Setup Instructions

### Step 1: Database Updates
Run the SQL script to add necessary tables and columns:
```bash
# In phpMyAdmin, run:
database/transfer_money_updates.sql
```

Or via command line:
```bash
mysql -u root -p nexo_banking < database/transfer_money_updates.sql
```

### Step 2: Test the Feature
1. **Login** to your account
2. Navigate to **Transfer Money** page
3. The page will automatically load your accounts and contacts
4. Select a source account
5. Choose a recipient (saved contact or new)
6. Enter amount and transfer details
7. Click "Send Money"

### Step 3: Verify Database
After a successful transfer, check:

```sql
-- View recent transactions
SELECT * FROM transactions 
WHERE account_id IN (SELECT account_id FROM accounts WHERE user_id = 1)
ORDER BY transaction_date DESC 
LIMIT 5;

-- Check account balances
SELECT account_type, balance FROM accounts WHERE user_id = 1;

-- View beneficiaries
SELECT * FROM beneficiaries WHERE user_id = 1;
```

## Transfer Flow

### Internal Transfer (Nexo to Nexo)
1. User submits transfer form
2. Backend validates session and authentication
3. Verifies source account belongs to user
4. Checks sufficient balance (amount + fee)
5. Starts database transaction
6. Deducts from sender's account
7. Records outgoing transaction
8. Credits recipient's account (if Nexo user)
9. Records incoming transaction for recipient
10. Creates notifications for both parties
11. Commits database transaction
12. Returns success response

### External Transfer
1. Same steps 1-6 as internal
2. Records transaction with "pending" status
3. Records fee transaction
4. Creates notification for sender
5. Transaction marked for external processing
6. Returns success response

## Error Handling

The backend handles these error cases:
- ❌ Not authenticated → Redirects to login
- ❌ Invalid session → Redirects to login
- ❌ Invalid source account → Error message
- ❌ Insufficient funds → Error message
- ❌ Invalid recipient → Error message
- ❌ Missing required fields → Error message
- ❌ Database errors → Rolls back transaction, error message

## Testing Scenarios

### Test Case 1: Internal Transfer
```
From: Checking Account
To: Saved Contact (Nexo user)
Amount: $100
Expected: Immediate completion, both balances updated
```

### Test Case 2: External Transfer
```
From: Savings Account
To: New Recipient (External Bank)
Amount: $500
Expected: Pending status, fee charged ($2.99)
```

### Test Case 3: Insufficient Funds
```
From: Account with $50
To: Any recipient
Amount: $100
Expected: Error message about insufficient funds
```

### Test Case 4: Invalid Account
```
From: Account belonging to different user
Expected: "Invalid source account" error
```

## Security Notes

- ✅ All database queries use prepared statements (SQL injection protected)
- ✅ Session validation on every API call
- ✅ Account ownership verified before any transaction
- ✅ Balance checks prevent overdrafts
- ✅ Database transactions ensure data integrity (all-or-nothing)
- ✅ Output buffering prevents JSON corruption
- ✅ Error messages don't expose sensitive data

## Future Enhancements

Potential improvements:
- [ ] Two-factor authentication for transfers
- [ ] Transfer scheduling (future-dated transfers)
- [ ] Recurring transfers
- [ ] Transfer limits and daily caps
- [ ] Email/SMS confirmations
- [ ] Transfer approval workflow for large amounts
- [ ] International currency conversion
- [ ] Transfer templates for common payments

## Troubleshooting

### Issue: "Not authenticated" error
**Solution:** Ensure you're logged in. Clear browser cache and login again.

### Issue: "Beneficiaries table doesn't exist"
**Solution:** Run `database/transfer_money_updates.sql` in phpMyAdmin

### Issue: Contacts not showing
**Solution:** 
1. Check if beneficiaries table exists
2. If not, the system will show other Nexo users as contacts
3. Run the SQL script to create the table

### Issue: Balance not updating
**Solution:** Refresh the page or check browser console for JavaScript errors

### Issue: Transfer appears successful but balance unchanged
**Solution:** Check database transactions table for errors. Verify SQL transaction wasn't rolled back.

## Support

For issues or questions:
1. Check browser console for JavaScript errors
2. Check PHP error logs: `xampp/apache/logs/error.log`
3. Verify database structure matches schema
4. Ensure XAMPP MySQL and Apache are running

---

**Last Updated:** November 7, 2025
**Version:** 1.0
**Status:** ✅ Fully Functional
