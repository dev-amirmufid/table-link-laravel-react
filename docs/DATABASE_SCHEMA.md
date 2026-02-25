# Database Schema

## Overview

The database consists of 4 main tables: users, items, transactions, and personal_access_tokens. The transactions table is the central fact table with relationships to users (buyers and sellers) and items.

## Tables

### 1. users

Standard Laravel users table with type classification.

| Column            | Type                       | Constraints                  | Description                  |
| ----------------- | -------------------------- | ---------------------------- | ---------------------------- |
| id                | uuid                       | PRIMARY KEY                  | User UUID                    |
| name              | varchar(255)               | NOT NULL                     | Full name                    |
| email             | varchar(255)               | UNIQUE, NOT NULL             | Email address                |
| email_verified_at | timestamp                  | NULLABLE                     | Email verification timestamp |
| password          | varchar(255)               | NOT NULL                     | Hashed password              |
| type              | enum('domestic','foreign') | NOT NULL, DEFAULT 'domestic' | User classification          |
| remember_token    | varchar(100)               | NULLABLE                     | Remember me token            |
| created_at        | timestamp                  | NOT NULL                     | Creation timestamp           |
| updated_at        | timestamp                  | NOT NULL                     | Last update timestamp        |

**Indexes:**

- `users_email_unique` on `email`

### 2. items

Product catalog table.

| Column     | Type         | Constraints | Description           |
| ---------- | ------------ | ----------- | --------------------- |
| id         | uuid         | PRIMARY KEY | Item UUID             |
| name       | varchar(255) | NOT NULL    | Item name             |
| created_at | timestamp    | NOT NULL    | Creation timestamp    |
| updated_at | timestamp    | NOT NULL    | Last update timestamp |

**Indexes:**

- None additional

### 3. transactions

Central fact table storing all transaction data.

| Column     | Type      | Constraints            | Description                            |
| ---------- | --------- | ---------------------- | -------------------------------------- |
| id         | uuid      | PRIMARY KEY            | Transaction UUID                       |
| buyer_id   | uuid      | FOREIGN KEY → users.id | Buyer reference                        |
| seller_id  | uuid      | FOREIGN KEY → users.id | Seller reference                       |
| item_id    | uuid      | FOREIGN KEY → items.id | Item reference                         |
| quantity   | int       | NOT NULL               | Quantity purchased                     |
| price      | int       | NOT NULL               | Unit price (in smallest currency unit) |
| created_at | timestamp | NOT NULL               | Transaction timestamp                  |
| updated_at | timestamp | NOT NULL               | Last update timestamp                  |

**Foreign Keys:**

- `transactions_buyer_id_foreign` → `users(id)`
- `transactions_seller_id_foreign` → `users(id)`
- `transactions_item_id_foreign` → `items(id)`

**Indexes:**

- `transactions_buyer_id_index` on `buyer_id`
- `transactions_seller_id_index` on `seller_id`
- `transactions_item_id_index` on `item_id`
- `transactions_created_at_index` on `created_at`

### 4. personal_access_tokens

Laravel Sanctum tokens for API authentication.

| Column         | Type         | Constraints      | Description           |
| -------------- | ------------ | ---------------- | --------------------- |
| id             | bigint       | PRIMARY KEY      | Auto-increment ID     |
| tokenable_type | varchar(255) | NOT NULL         | Model class name      |
| tokenable_id   | bigint       | NOT NULL         | Model ID              |
| name           | varchar(255) | NOT NULL         | Token name            |
| token          | varchar(64)  | UNIQUE, NOT NULL | Hashed token          |
| abilities      | text         | NULLABLE         | Token permissions     |
| last_used_at   | timestamp    | NULLABLE         | Last usage timestamp  |
| expires_at     | timestamp    | NULLABLE         | Expiration timestamp  |
| created_at     | timestamp    | NOT NULL         | Creation timestamp    |
| updated_at     | timestamp    | NOT NULL         | Last update timestamp |

**Indexes:**

- `personal_access_tokens_token_unique` on `token`
- `personal_access_tokens_tokenable_type_tokenable_id_index` on `tokenable_type, tokenable_id`

## Relationships

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   users     │       │ transactions│       │   items     │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ (buyer)     │◄──────│ buyer_id    │       │             │
│             │       │             │       │             │
│ (seller)    │◄──────│ seller_id   │       │             │
│             │       │             │       │             │
└─────────────┘       │ item_id     │──────►│             │
                      └─────────────┘       └─────────────┘
```

### Transaction Relationships

- A Transaction has one Buyer (User)
- A Transaction has one Seller (User)
- A Transaction has one Item

## Data Model Notes

### User Type Classification

Users are classified as either:

- `domestic` - Domestic users
- `foreign` - Foreign/international users

This classification is used for analytics filtering and reporting.

### Price Storage

Prices are stored as integers (in the smallest currency unit, e.g., cents for USD, rupiah for IDR). This avoids floating-point precision issues.

### UUID Primary Keys

All tables use UUIDs as primary keys for:

- Better distributed database architecture
- Reduced guessability of IDs
- Support for merging data across databases

## Query Patterns

### Common Queries

1. **Get transactions by date range**

   ```sql
   SELECT * FROM transactions
   WHERE created_at BETWEEN '2025-01-01' AND '2025-12-31';
   ```

2. **Get transactions by user type**

   ```sql
   SELECT t.* FROM transactions t
   JOIN users buyer ON t.buyer_id = buyer.id
   WHERE buyer.type = 'domestic';
   ```

3. **Aggregate by period**

   ```sql
   SELECT DATE(created_at) as date,
          COUNT(*) as count,
          SUM(quantity * price) as revenue
   FROM transactions
   GROUP BY DATE(created_at);
   ```

4. **Top buyers**
   ```sql
   SELECT buyer_id, COUNT(*) as transaction_count
   FROM transactions
   GROUP BY buyer_id
   ORDER BY transaction_count DESC
   LIMIT 10;
   ```
