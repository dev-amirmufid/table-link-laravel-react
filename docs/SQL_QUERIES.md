# SQL Queries

## Overview

This document contains the main SQL queries used in the analytics system.

## Summary Query

### KPI Summary

```sql
SELECT
    COUNT(*) as total_transactions,
    COALESCE(SUM(t.quantity), 0) as total_quantity,
    COALESCE(SUM(t.quantity * t.price), 0) as total_revenue,
    COUNT(DISTINCT t.buyer_id) as active_buyers,
    COUNT(DISTINCT t.seller_id) as active_sellers
FROM transactions t
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
```

### Average Transaction Price

Calculated in application:

```php
$avgPrice = $summary->total_transactions > 0
    ? round($summary->total_revenue / $summary->total_transactions)
    : 0;
```

---

## Trends Query

### Daily Trends

```sql
SELECT
    DATE(t.created_at) as period,
    COUNT(*) as transactions,
    SUM(t.quantity * t.price) as revenue
FROM transactions t
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY DATE(t.created_at)
ORDER BY period ASC
```

### Weekly Trends

```sql
SELECT
    DATE_FORMAT(t.created_at, '%Y-%u') as period,
    COUNT(*) as transactions,
    SUM(t.quantity * t.price) as revenue
FROM transactions t
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY DATE_FORMAT(t.created_at, '%Y-%u')
ORDER BY period ASC
```

### Monthly Trends

```sql
SELECT
    DATE_FORMAT(t.created_at, '%Y-%m') as period,
    COUNT(*) as transactions,
    SUM(t.quantity * t.price) as revenue
FROM transactions t
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY DATE_FORMAT(t.created_at, '%Y-%m')
ORDER BY period ASC
```

---

## User Classification Query

### Domestic vs Foreign

```sql
SELECT
    buyer.type as user_type,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users), 2) as percentage
FROM transactions t
JOIN users buyer ON t.buyer_id = buyer.id
GROUP BY buyer.type
```

---

## Top Buyers Query

```sql
SELECT
    t.buyer_id as user_id,
    buyer.name as user_name,
    COUNT(*) as total_transactions,
    SUM(t.quantity * t.price) as total_spent
FROM transactions t
JOIN users buyer ON t.buyer_id = buyer.id
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY t.buyer_id, buyer.name
ORDER BY total_transactions DESC
LIMIT 10
```

---

## Top Sellers Query

```sql
SELECT
    t.seller_id as user_id,
    seller.name as user_name,
    COUNT(*) as total_transactions,
    SUM(t.quantity * t.price) as total_earned
FROM transactions t
JOIN users seller ON t.seller_id = seller.id
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY t.seller_id, seller.name
ORDER BY total_transactions DESC
LIMIT 10
```

---

## Trending Items Query

```sql
SELECT
    t.item_id,
    item.name as item_name,
    SUM(t.quantity) as total_quantity,
    SUM(t.quantity * t.price) as total_revenue
FROM transactions t
JOIN items ON t.item_id = items.id
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY t.item_id, item.name
ORDER BY total_revenue DESC
LIMIT 10
```

---

## Revenue Contribution Query

```sql
SELECT
    t.item_id,
    item.name as item_name,
    SUM(t.quantity * t.price) as revenue,
    ROUND(
        SUM(t.quantity * t.price) * 100.0 / (
            SELECT SUM(quantity * price)
            FROM transactions
            WHERE created_at BETWEEN '2025-01-01' AND '2025-12-31'
        ),
        2
    ) as percentage
FROM transactions t
JOIN items ON t.item_id = items.id
WHERE t.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY t.item_id, item.name
ORDER BY revenue DESC
LIMIT 10
```

---

## Transaction List Query (Paginated)

### With Filters

```sql
SELECT
    t.*,
    buyer.name as buyer_name,
    buyer.email as buyer_email,
    buyer.type as buyer_type,
    seller.name as seller_name,
    seller.email as seller_email,
    seller.type as seller_type,
    item.name as item_name
FROM transactions t
JOIN users buyer ON t.buyer_id = buyer.id
JOIN users seller ON t.seller_id = seller.id
JOIN items ON t.item_id = items.id
WHERE
    (t.created_at BETWEEN '2025-01-01' AND '2025-12-31')
    AND (buyer.type = 'domestic')
ORDER BY t.created_at DESC
LIMIT 15 OFFSET 0
```

### With Search

```sql
SELECT
    t.*,
    buyer.name as buyer_name,
    seller.name as seller_name,
    item.name as item_name
FROM transactions t
JOIN users buyer ON t.buyer_id = buyer.id
JOIN users seller ON t.seller_id = seller.id
JOIN items ON t.item_id = items.id
WHERE
    (buyer.name LIKE '%john%'
     OR seller.name LIKE '%john%'
     OR item.name LIKE '%john%')
ORDER BY t.created_at DESC
LIMIT 15
```

---

## Query Patterns Used

### 1. Date Range Filtering

```sql
WHERE created_at BETWEEN :start_date AND :end_date
```

### 2. User Type Join

```sql
JOIN users buyer ON t.buyer_id = buyer.id
WHERE buyer.type = :user_type
```

### 3. Aggregation with JOIN

```sql
SELECT ...
FROM transactions t
JOIN users buyer ON t.buyer_id = buyer.id
GROUP BY ...
```

### 4. Percentage Calculation

```sql
ROUND(value * 100.0 / (SELECT SUM(...) FROM ...), 2) as percentage
```

### 5. Pagination

```sql
LIMIT :per_page OFFSET :offset
```

---

## Index Usage

The following indexes are used by these queries:

| Query Type    | Index Used                      |
| ------------- | ------------------------------- |
| Date range    | `transactions_created_at_index` |
| Buyer filter  | `transactions_buyer_id_index`   |
| Seller filter | `transactions_seller_id_index`  |
| Item grouping | `transactions_item_id_index`    |
