# Data Classification Analytics

## Overview

The system implements multiple data classification analytics for understanding transaction patterns, user behavior, and revenue distribution.

## Classification Types

### 1. User Type Classification

**Purpose:** Understand the distribution of domestic vs foreign users.

**Data Source:** `users.type` column (enum: 'domestic', 'foreign')

**Query Logic:**

```sql
SELECT
    type as user_type,
    COUNT(*) as count
FROM users
GROUP BY type
```

**Response Format:**

```json
[
  {
    "user_type": "domestic",
    "count": 10000,
    "percentage": 50
  },
  {
    "user_type": "foreign",
    "count": 10000,
    "percentage": 50
  }
]
```

**Frontend Visualization:** Pie chart showing domestic vs foreign distribution

---

### 2. Transaction Trends (Time-Based Classification)

**Purpose:** Analyze transaction volume and revenue over time.

**Grouping Options:**

- Daily: `DATE(created_at)`
- Weekly: `DATE_FORMAT(created_at, '%Y-%u')`
- Monthly: `DATE_FORMAT(created_at, '%Y-%m')`

**Query Logic:**

```sql
SELECT
    DATE(created_at) as period,
    COUNT(*) as transactions,
    SUM(quantity * price) as revenue
FROM transactions
WHERE created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY DATE(created_at)
ORDER BY period ASC
```

**Response Format:**

```json
[
  {
    "period": "2025-11-25",
    "transactions": 138,
    "revenue": "4497693790"
  }
]
```

**Frontend Visualization:** Line chart showing trends over time

---

### 3. Top Buyers Classification

**Purpose:** Identify highest-volume buyers.

**Query Logic:**

```sql
SELECT
    buyer_id,
    buyer.name as user_name,
    COUNT(*) as total_transactions,
    SUM(quantity * price) as total_spent
FROM transactions
JOIN users buyer ON transactions.buyer_id = buyer.id
GROUP BY buyer_id
ORDER BY total_transactions DESC
LIMIT 10
```

**Sorting Logic:**

- Primary: Total transactions (descending)
- Secondary: Total spent (descending)

**Response Format:**

```json
[
  {
    "user_id": "uuid",
    "user_name": "John Doe",
    "total_transactions": 50,
    "total_spent": 5000000000
  }
]
```

**Frontend Visualization:** Top buyer list with transaction count and total spent

---

### 4. Top Sellers Classification

**Purpose:** Identify highest-volume sellers.

**Query Logic:**

```sql
SELECT
    seller_id,
    seller.name as user_name,
    COUNT(*) as total_transactions,
    SUM(quantity * price) as total_earned
FROM transactions
JOIN users seller ON transactions.seller_id = seller.id
GROUP BY seller_id
ORDER BY total_transactions DESC
LIMIT 10
```

**Sorting Logic:**

- Primary: Total transactions (descending)
- Secondary: Total earned (descending)

**Response Format:**

```json
[
  {
    "user_id": "uuid",
    "user_name": "Jane Smith",
    "total_transactions": 45,
    "total_earned": 4500000000
  }
]
```

**Frontend Visualization:** Top seller list with transaction count and total earned

---

### 5. Trending Items Classification

**Purpose:** Identify items with highest revenue or quantity sold.

**Query Logic:**

```sql
SELECT
    item_id,
    item.name as item_name,
    SUM(quantity) as total_quantity,
    SUM(quantity * price) as total_revenue
FROM transactions
JOIN items ON transactions.item_id = items.id
GROUP BY item_id
ORDER BY total_revenue DESC
LIMIT 10
```

**Sorting Logic:** Total revenue (descending)

**Response Format:**

```json
[
  {
    "item_id": "uuid",
    "item_name": "Product Name",
    "total_revenue": 5000000000,
    "total_quantity": 50000
  }
]
```

**Frontend Visualization:** Bar chart showing top items by revenue

---

### 6. Revenue Contribution Classification

**Purpose:** Understand which items contribute most to total revenue.

**Query Logic:**

```sql
SELECT
    item_id,
    item.name as item_name,
    SUM(quantity * price) as revenue,
    (SUM(quantity * price) / (SELECT SUM(quantity * price) FROM transactions) * 100) as percentage
FROM transactions
JOIN items ON transactions.item_id = items.id
GROUP BY item_id
ORDER BY revenue DESC
LIMIT 10
```

**Sorting Logic:** Revenue contribution percentage (descending)

**Response Format:**

```json
[
  {
    "item_id": "uuid",
    "item_name": "Product Name",
    "revenue": 5000000000,
    "percentage": 5.3
  }
]
```

**Frontend Visualization:** Bar chart showing revenue contribution by item

---

## Aggregation Methods

### Count Aggregations

- `COUNT(*)` - Total records
- `COUNT(DISTINCT buyer_id)` - Unique buyers
- `COUNT(DISTINCT seller_id)` - Unique sellers

### Sum Aggregations

- `SUM(quantity)` - Total quantity
- `SUM(quantity * price)` - Total revenue

### Average Aggregations

- `AVG(quantity * price)` - Average transaction value
- Custom calculation: `total_revenue / total_transactions`

## Filtering Impact

All classifications respect the unified filter system:

| Filter              | Impact                                              |
| ------------------- | --------------------------------------------------- |
| start_date/end_date | Restricts data to date range                        |
| user_type           | Only includes transactions from specified user type |
| period              | Changes time grouping granularity                   |

## Data Freshness

- All classification data is cached for 5 minutes
- Cache key includes filter parameters
- Manual cache clear available via `/dashboard/cache/clear`

## Performance Notes

- Top-N queries use `LIMIT` for efficiency
- Aggregations are computed at database level
- Index usage on `created_at`, `buyer_id`, `seller_id`
- Percentage calculations done in application for flexibility
