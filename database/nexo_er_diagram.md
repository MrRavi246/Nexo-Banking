# Nexo Banking System - Entity Relationship Diagram

## Database Schema Design

### Entities and Relationships

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│     USERS       │         │    ACCOUNTS     │         │  TRANSACTIONS   │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ user_id (PK)    │◄────────┤ account_id (PK) │◄────────┤transaction_id(PK)│
│ username        │         │ user_id (FK)    │         │ account_id (FK) │
│ email           │         │ account_type    │         │ transaction_type│
│ password_hash   │         │ account_number  │         │ amount          │
│ first_name      │         │ balance         │         │ description     │
│ last_name       │         │ currency        │         │ recipient_info  │
│ phone_number    │         │ status          │         │ transaction_date│
│ date_of_birth   │         │ created_at      │         │ status          │
│ address         │         │ updated_at      │         │ reference_id    │
│ profile_image   │         │ credit_limit    │         │ category        │
│ member_type     │         │ interest_rate   │         │ created_at      │
│ created_at      │         │ last_activity   │         │ updated_at      │
│ updated_at      │         └─────────────────┘         └─────────────────┘
│ last_login      │
│ status          │
└─────────────────┘
         │
         │
         ▼
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│  CREDIT_SCORES  │         │ SAVINGS_GOALS   │         │   TRANSFERS     │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ score_id (PK)   │         │ goal_id (PK)    │         │ transfer_id (PK)│
│ user_id (FK)    │         │ user_id (FK)    │         │ from_account_id │
│ score_value     │         │ goal_name       │         │ to_account_id   │
│ score_range     │         │ target_amount   │         │ amount          │
│ factors         │         │ current_amount  │         │ transfer_type   │
│ last_updated    │         │ target_date     │         │ recipient_name  │
│ created_at      │         │ category        │         │ recipient_phone │
└─────────────────┘         │ status          │         │ message         │
                            │ created_at      │         │ fee_amount      │
                            │ updated_at      │         │ status          │
                            └─────────────────┘         │ processed_at    │
                                                        │ created_at      │
                                                        └─────────────────┘

┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│  BILL_PAYMENTS  │         │    CONTACTS     │         │   BUDGETS       │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ payment_id (PK) │         │ contact_id (PK) │         │ budget_id (PK)  │
│ user_id (FK)    │         │ user_id (FK)    │         │ user_id (FK)    │
│ account_id (FK) │         │ contact_name    │         │ month_year      │
│ biller_name     │         │ phone_number    │         │ total_budget    │
│ bill_type       │         │ email           │         │ spent_amount    │
│ amount          │         │ relationship    │         │ status          │
│ due_date        │         │ avatar_initials │         │ created_at      │
│ payment_date    │         │ is_favorite     │         │ updated_at      │
│ status          │         │ created_at      │         └─────────────────┘
│ reference_number│         │ updated_at      │
│ created_at      │         └─────────────────┘
└─────────────────┘

┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│BUDGET_CATEGORIES│         │  NOTIFICATIONS  │         │    SESSIONS     │
├─────────────────┤         ├─────────────────┤         ├─────────────────┤
│ category_id (PK)│         │notification_id  │         │ session_id (PK) │
│ budget_id (FK)  │         │ user_id (FK)    │         │ user_id (FK)    │
│ category_name   │         │ title           │         │ session_token   │
│ allocated_amount│         │ message         │         │ ip_address      │
│ spent_amount    │         │ type            │         │ user_agent      │
│ created_at      │         │ is_read         │         │ created_at      │
│ updated_at      │         │ created_at      │         │ expires_at      │
└─────────────────┘         │ read_at         │         │ last_activity   │
                            └─────────────────┘         └─────────────────┘

┌─────────────────┐         ┌─────────────────┐
│   AUDIT_LOGS    │         │ SYSTEM_SETTINGS │
├─────────────────┤         ├─────────────────┤
│ log_id (PK)     │         │ setting_id (PK) │
│ user_id (FK)    │         │ setting_key     │
│ action_type     │         │ setting_value   │
│ table_name      │         │ description     │
│ record_id       │         │ is_active       │
│ old_values      │         │ created_at      │
│ new_values      │         │ updated_at      │
│ ip_address      │         └─────────────────┘
│ user_agent      │
│ created_at      │
└─────────────────┘
```

## Relationships

### Primary Relationships:
1. **USERS** (1) ←→ (N) **ACCOUNTS** - One user can have multiple accounts
2. **ACCOUNTS** (1) ←→ (N) **TRANSACTIONS** - One account can have multiple transactions
3. **USERS** (1) ←→ (N) **SAVINGS_GOALS** - One user can have multiple savings goals
4. **USERS** (1) ←→ (1) **CREDIT_SCORES** - One user has one current credit score
5. **USERS** (1) ←→ (N) **TRANSFERS** - One user can initiate multiple transfers
6. **USERS** (1) ←→ (N) **BILL_PAYMENTS** - One user can have multiple bill payments
7. **USERS** (1) ←→ (N) **CONTACTS** - One user can have multiple contacts
8. **USERS** (1) ←→ (N) **BUDGETS** - One user can have multiple monthly budgets
9. **BUDGETS** (1) ←→ (N) **BUDGET_CATEGORIES** - One budget has multiple categories

### Secondary Relationships:
1. **ACCOUNTS** ←→ **TRANSFERS** (from_account_id, to_account_id)
2. **ACCOUNTS** ←→ **BILL_PAYMENTS** (payment source)
3. **USERS** ←→ **NOTIFICATIONS** (system alerts)
4. **USERS** ←→ **SESSIONS** (login tracking)
5. **USERS** ←→ **AUDIT_LOGS** (activity tracking)

## Key Constraints

### Primary Keys:
- All tables have auto-incrementing primary keys

### Foreign Keys:
- user_id references USERS.user_id in all user-related tables
- account_id references ACCOUNTS.account_id in transaction tables
- budget_id references BUDGETS.budget_id in BUDGET_CATEGORIES

### Unique Constraints:
- USERS.email (unique)
- USERS.username (unique)
- ACCOUNTS.account_number (unique)
- SESSIONS.session_token (unique)

### Indexes (Recommended):
- ACCOUNTS.user_id
- TRANSACTIONS.account_id
- TRANSACTIONS.transaction_date
- TRANSFERS.from_account_id, to_account_id
- NOTIFICATIONS.user_id
- SESSIONS.user_id, session_token

## Account Types Supported:
- CHECKING
- SAVINGS
- CREDIT
- LOAN (future)

## Transaction Types:
- DEPOSIT
- WITHDRAWAL
- TRANSFER
- PAYMENT
- REFUND
- FEE
- INTEREST

## Transfer Types:
- INTERNAL (between own accounts)
- EXTERNAL (to other users)
- WIRE
- ACH

## Bill Payment Types:
- UTILITIES
- CREDIT_CARD
- LOAN
- SUBSCRIPTION
- MOBILE_RECHARGE
- INSURANCE

## Notification Types:
- TRANSACTION_ALERT
- LOW_BALANCE
- PAYMENT_DUE
- GOAL_ACHIEVED
- SECURITY_ALERT
- SYSTEM_MAINTENANCE
