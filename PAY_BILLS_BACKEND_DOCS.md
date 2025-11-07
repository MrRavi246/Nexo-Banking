# Pay Bills Backend Integration - Complete Documentation

## Overview
Complete backend system for paying bills with support for one-time and recurring payments, account balance management, and transaction tracking.

---

## Features Implemented

### ✅ Core Functionality
- **Account Selection**: Users can pay bills from any of their active accounts
- **Saved Billers**: Previously used billers are saved and can be quickly selected
- **Custom Payees**: Add new billers on the fly
- **One-Time Payments**: Immediate or scheduled payments
- **Recurring Payments**: Auto-schedule future payments (daily, weekly, monthly, etc.)
- **Balance Validation**: Ensures sufficient funds before processing
- **Real-Time Data**: Dynamic loading of upcoming bills and recent payments

### ✅ Bill Types Supported
- Utilities (electric, water, gas)
- Credit Card
- Loan
- Subscription
- Mobile Recharge
- Insurance

### ✅ Payment Status
- **Completed**: Immediate payments processed today
- **Scheduled**: Future payments not yet processed
- **Processing**: Payment in progress
- **Failed**: Payment couldn't be completed
- **Cancelled**: User cancelled the payment

---

## File Structure

```
backend/
├── get_bill_data.php          # Load accounts, billers, bills, summary
├── process_bill_payment.php   # Process one-time and recurring payments

Pages/dashboard/
├── pay-bills.php              # Main bill payment page (updated with auth)

assets/js/
├── pay-bills.js               # Frontend logic (updated for backend)

assets/style/
├── pay-bills.css              # Styling (added spinner animation)
```

---

## API Endpoints

### 1. GET `/backend/get_bill_data.php`

**Purpose**: Load all bill payment data for the logged-in user

**Authentication**: Required (session-based)

**Response Format**:
```json
{
  "success": true,
  "data": {
    "accounts": [
      {
        "account_id": 1,
        "account_type": "checking",
        "account_number": "1234567890",
        "balance": 5000.00,
        "currency": "USD"
      }
    ],
    "savedBillers": [
      {
        "biller_name": "Electric Co.",
        "bill_type": "utilities"
      }
    ],
    "recentPayments": [
      {
        "payment_id": 1,
        "biller_name": "Electric Co.",
        "bill_type": "utilities",
        "amount": 120.50,
        "due_date": "2025-11-15",
        "payment_date": "2025-11-05",
        "status": "completed",
        "reference_number": "BP20251105ABC123",
        "account_type": "checking",
        "account_number": "1234567890"
      }
    ],
    "upcomingBills": [
      {
        "payment_id": 2,
        "biller_name": "Internet Provider",
        "bill_type": "utilities",
        "amount": 45.00,
        "due_date": "2025-11-20",
        "status": "scheduled",
        "days_until_due": 13
      }
    ],
    "summary": {
      "totalDue": 165.50,
      "billsDueCount": 2,
      "nextDueDate": "2025-11-15",
      "nextDueBiller": "Electric Co.",
      "nextDueAmount": 120.50
    }
  }
}
```

**What It Does**:
1. Validates user session
2. Fetches user's active accounts
3. Gets list of saved billers (from past payments)
4. Retrieves last 10 payments
5. Gets upcoming scheduled bills
6. Calculates summary statistics

---

### 2. POST `/backend/process_bill_payment.php`

**Purpose**: Process a bill payment (one-time or recurring)

**Authentication**: Required (session-based)

**Request Format**:
```json
{
  "accountId": 1,
  "billerName": "Electric Co.",
  "billType": "utilities",
  "amount": 120.50,
  "paymentDate": "2025-11-07",
  "dueDate": "2025-11-15",
  "memo": "Invoice #12345",
  "paymentType": "one-time",
  "frequency": null,
  "endDate": null
}
```

**For Recurring Payments**:
```json
{
  "accountId": 1,
  "billerName": "Netflix",
  "billType": "subscription",
  "amount": 15.99,
  "paymentDate": "2025-11-07",
  "dueDate": "2025-11-07",
  "memo": "",
  "paymentType": "recurring",
  "frequency": "monthly",
  "endDate": "2026-11-07"
}
```

**Response Format**:
```json
{
  "success": true,
  "message": "Payment of $120.50 to Electric Co. completed successfully!",
  "data": {
    "paymentId": 42,
    "referenceNumber": "BP20251107XYZ789",
    "status": "completed",
    "billerName": "Electric Co.",
    "amount": 120.50,
    "paymentDate": "2025-11-07",
    "newBalance": 4879.50,
    "recurringCount": 0
  }
}
```

**What It Does**:
1. Validates session and input data
2. Verifies account ownership and balance
3. If payment date is today or past:
   - Deducts amount from account balance
   - Creates transaction record
   - Marks as "completed"
4. If payment date is future:
   - Creates scheduled payment
   - Marks as "scheduled"
5. For recurring payments:
   - Creates initial payment
   - Generates future scheduled payments based on frequency
   - Supports: daily, weekly, bi-weekly, monthly, quarterly, yearly
6. Creates notification for user
7. Returns reference number for tracking

**Error Responses**:
```json
{
  "success": false,
  "message": "Insufficient balance in selected account"
}
```

---

## Database Schema

### `bill_payments` Table
```sql
CREATE TABLE `bill_payments` (
  `payment_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `biller_name` varchar(100) NOT NULL,
  `bill_type` enum('utilities','credit_card','loan','subscription','mobile_recharge','insurance'),
  `amount` decimal(15,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `payment_date` timestamp NOT NULL,
  `status` enum('scheduled','processing','completed','failed','cancelled') DEFAULT 'scheduled',
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (account_id) REFERENCES accounts(account_id)
);
```

### Related Tables Used
- `accounts`: Source account for payment, balance updates
- `transactions`: Payment transaction records
- `notifications`: Payment confirmations
- `users`: User authentication and ownership

---

## Frontend Integration

### Page Load Flow
```javascript
1. Page loads → initializePayBillsPage()
2. Set default date to today
3. Call loadBillData() → fetch /backend/get_bill_data.php
4. Update UI:
   - Summary stats (total due, next payment)
   - Populate billers dropdown
   - Display upcoming bills
   - Show recent payments
```

### Payment Submission Flow
```javascript
1. User fills form and clicks "Pay Now"
2. processPayment() validates inputs
3. POST to /backend/process_bill_payment.php
4. Backend processes payment:
   - Validates account and balance
   - Deducts funds if immediate
   - Creates bill_payments record
   - Creates transaction record
   - Generates recurring schedules (if applicable)
   - Creates notification
5. Frontend receives response
6. Shows success/error notification
7. Reloads bill data to refresh UI
```

---

## Key Functions

### Backend Functions

#### `generateRecurringSchedule($startDate, $frequency, $endDate, $maxCount)`
Generates array of future payment dates based on frequency.

**Frequencies**:
- `daily`: +1 day
- `weekly`: +1 week
- `bi-weekly`: +2 weeks
- `monthly`: +1 month
- `quarterly`: +3 months
- `yearly`: +1 year

**Parameters**:
- `$startDate`: First payment date
- `$frequency`: One of the supported frequencies
- `$endDate`: Optional end date (null = no end)
- `$maxCount`: Maximum schedules to create (default 12)

**Returns**: Array of date strings `['2025-12-07', '2026-01-07', ...]`

### Frontend Functions

#### `loadBillData()`
Fetches all bill data from backend and updates UI

#### `displayUpcomingBills(bills)`
Renders list of upcoming scheduled bills

#### `displayRecentPayments(payments)`
Shows last 5 payments with time ago formatting

#### `quickPayBill(billerName, amount, billType)`
Pre-fills payment form from "Pay Now" button

#### `getTimeAgo(date)`
Converts timestamp to human-readable format
- "Just now"
- "5 minutes ago"
- "2 hours ago"
- "3 days ago"

---

## Testing Guide

### Test Case 1: One-Time Payment (Immediate)
1. Go to Pay Bills page
2. Select an account with sufficient balance
3. Select or enter a biller name
4. Enter amount (e.g., $50.00)
5. Leave date as today
6. Keep "One-time Payment" selected
7. Click "Pay Now"

**Expected**:
- ✅ Payment processed immediately
- ✅ Account balance decreases
- ✅ Transaction appears in recent payments
- ✅ Notification created
- ✅ Success message shown

### Test Case 2: Scheduled Payment (Future)
1. Select account and biller
2. Enter amount
3. Change date to future (e.g., 5 days from now)
4. Click "Pay Now"

**Expected**:
- ✅ Payment scheduled (status: "scheduled")
- ✅ Account balance unchanged
- ✅ Appears in upcoming bills
- ✅ No transaction record yet

### Test Case 3: Recurring Payment
1. Select account and biller
2. Enter amount (e.g., $15.99)
3. Select "Recurring Payment"
4. Choose frequency (e.g., "Monthly")
5. Optional: Set end date
6. Click "Pay Now"

**Expected**:
- ✅ First payment processed
- ✅ Multiple future payments created (up to 12)
- ✅ Each with correct date based on frequency
- ✅ All marked as "scheduled"
- ✅ Success message mentions recurring count

### Test Case 4: Insufficient Balance
1. Select account with low balance
2. Enter amount greater than balance
3. Click "Pay Now"

**Expected**:
- ❌ Error message: "Insufficient balance in selected account"
- ❌ No payment created
- ❌ Balance unchanged

### Test Case 5: Custom Payee
1. Select "+ Add Custom Payee" from dropdown
2. Enter custom payee name
3. Select bill type
4. Enter amount and date
5. Click "Pay Now"

**Expected**:
- ✅ Payment processed with custom name
- ✅ Biller saved for future use
- ✅ Appears in saved billers on reload

---

## Error Handling

### Backend Validation
- ✅ Session authentication
- ✅ Account ownership verification
- ✅ Balance sufficiency check
- ✅ Required field validation
- ✅ Bill type enumeration validation
- ✅ Database transaction rollback on error

### Frontend Validation
- ✅ Required field checks
- ✅ Amount > 0 validation
- ✅ Account selection required
- ✅ Network error handling
- ✅ User-friendly error messages

---

## Security Features

1. **Session Validation**: Every request validates user session
2. **Account Ownership**: Users can only pay from their own accounts
3. **SQL Injection Prevention**: Prepared statements throughout
4. **CSRF Protection**: Same-origin credentials required
5. **Input Sanitization**: All inputs validated and sanitized
6. **Transaction Integrity**: Database transactions ensure atomic operations
7. **Error Logging**: Sensitive errors logged, generic messages shown to users

---

## Future Enhancements

### Potential Additions
- [ ] Auto-pay setup (automatic processing on due date)
- [ ] Bill reminders via email/SMS
- [ ] Payment history export (PDF/CSV)
- [ ] Bill splitting (pay partial amount)
- [ ] Bill categories and budgeting
- [ ] Biller verification (check routing numbers)
- [ ] Payment confirmation receipts
- [ ] Cancel/modify scheduled payments
- [ ] Payment disputes and refunds

---

## Troubleshooting

### Issue: Bills not loading
**Solution**: Check browser console for errors. Verify `/backend/get_bill_data.php` is accessible and session is valid.

### Issue: Payment fails silently
**Solution**: Check PHP error log. Verify database connection and table structure.

### Issue: Balance not updating
**Solution**: Ensure transaction is being committed. Check SQL logs for rollback issues.

### Issue: Recurring payments not created
**Solution**: Verify `generateRecurringSchedule()` function. Check frequency parameter is valid.

### Issue: "Not authenticated" error
**Solution**: User session expired. Redirect to login page.

---

## Database Queries Reference

### Get upcoming bills for user
```sql
SELECT 
    bp.payment_id,
    bp.biller_name,
    bp.amount,
    bp.due_date,
    DATEDIFF(bp.due_date, CURDATE()) as days_until_due
FROM bill_payments bp
WHERE bp.user_id = ? 
AND bp.status = 'scheduled' 
AND bp.due_date >= CURDATE()
ORDER BY bp.due_date ASC;
```

### Get payment history
```sql
SELECT 
    bp.payment_id,
    bp.biller_name,
    bp.amount,
    bp.payment_date,
    bp.status,
    a.account_type
FROM bill_payments bp
JOIN accounts a ON bp.account_id = a.account_id
WHERE bp.user_id = ?
ORDER BY bp.created_at DESC
LIMIT 10;
```

### Process immediate payment
```sql
-- Deduct from account
UPDATE accounts 
SET balance = balance - ? 
WHERE account_id = ?;

-- Record transaction
INSERT INTO transactions (
    account_id, transaction_type, amount, 
    description, category, status
) VALUES (?, 'payment', ?, ?, 'bills', 'completed');

-- Record bill payment
INSERT INTO bill_payments (
    user_id, account_id, biller_name, 
    amount, status, reference_number
) VALUES (?, ?, ?, ?, 'completed', ?);
```

---

## Success! 🎉

The Pay Bills backend is fully integrated and ready to use. Users can now:
- Pay bills from their accounts
- Schedule future payments
- Set up recurring payments
- View payment history
- Track upcoming bills
- All with real-time balance updates and transaction records!
