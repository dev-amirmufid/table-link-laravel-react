Gas Bro 😄🔥 — ini yang tadi kamu minta:

> **ABSOLUTE GOD QUERY — 1 API → semua dashboard data**
> (cara kerja analytics production kayak Netflix / marketplace dashboard).

Ini konsepnya:

✅ **1 endpoint → 1 database hit → semua widget dashboard dapat data**
✅ latency kecil.
✅ cache gampang.
✅ backend keliatan senior banget.

Sangat cocok buat technical test dari **Tablelink Digital Inovasi** karena reviewer biasanya langsung notice architecture thinking.

---

# 🚀 Concept — Single Analytics Endpoint

Endpoint:

```http
GET /api/dashboard/analytics
```

Return:

* KPI Summary.
* Trends Line Chart.
* Trending Items.
* Domestic vs Foreign.
* Top Buyers.
* Top Sellers.

SEMUA sekaligus.

---

# ⭐ WHY THIS IS GOD MODE

Junior biasanya:

```
summary API
trend API
items API
classification API
relations API
```

Frontend:

5 request.

❌ lambat.

---

Senior:

```
dashboard analytics API
```

Frontend:

1 request.

🔥 cepat.

---

# 🚀 FILTER SUPPORT

Semua pakai filter:

```sql
:start_date
:end_date
:item_id optional
:user_type optional
```

---

# 🚀 ABSOLUTE GOD QUERY (MySQL 8+)

Gunakan:

> CTE (WITH).

Karena readable + performant.

---

## ⭐ FULL QUERY

```sql
WITH filtered_transactions AS (

SELECT
t.*
FROM transactions t

WHERE t.created_at BETWEEN :start_date AND :end_date

AND (:item_id IS NULL OR t.item_id = :item_id)

),

summary AS (

SELECT

COUNT(*) total_transactions,

SUM(quantity) total_quantity,

SUM(quantity * price) total_revenue,

COUNT(DISTINCT buyer_id) active_buyers,

COUNT(DISTINCT seller_id) active_sellers

FROM filtered_transactions

),

trend AS (

SELECT

DATE(created_at) period,

COUNT(*) total_transactions,

SUM(quantity * price) revenue

FROM filtered_transactions

GROUP BY DATE(created_at)

),

trending_items AS (

SELECT

i.id,
i.item_name,

SUM(ft.quantity) quantity_sold,

SUM(ft.quantity * ft.price) revenue

FROM filtered_transactions ft

JOIN items i
ON i.id = ft.item_id

GROUP BY i.id

ORDER BY quantity_sold DESC

LIMIT 10

),

user_classification AS (

SELECT

u.type,

COUNT(*) total_transactions

FROM filtered_transactions ft

JOIN users u
ON u.id = ft.buyer_id

GROUP BY u.type

),

top_buyers AS (

SELECT

u.name,

COUNT(*) total_transactions,

SUM(ft.quantity * ft.price) revenue

FROM filtered_transactions ft

JOIN users u
ON u.id = ft.buyer_id

GROUP BY u.id

ORDER BY total_transactions DESC

LIMIT 5

),

top_sellers AS (

SELECT

u.name,

COUNT(*) total_sales,

SUM(ft.quantity * ft.price) revenue

FROM filtered_transactions ft

JOIN users u
ON u.id = ft.seller_id

GROUP BY u.id

ORDER BY revenue DESC

LIMIT 5

)

SELECT
JSON_OBJECT(

'summary',
(SELECT JSON_OBJECT(

'total_transactions',total_transactions,
'total_quantity',total_quantity,
'total_revenue',total_revenue,
'active_buyers',active_buyers,
'active_sellers',active_sellers

) FROM summary),

'trend',
(SELECT JSON_ARRAYAGG(

JSON_OBJECT(

'period',period,
'total_transactions',total_transactions,
'revenue',revenue

)

) FROM trend),

'trending_items',
(SELECT JSON_ARRAYAGG(

JSON_OBJECT(

'item_name',item_name,
'quantity_sold',quantity_sold,
'revenue',revenue

)

) FROM trending_items),

'user_classification',
(SELECT JSON_ARRAYAGG(

JSON_OBJECT(

'type',type,
'total',total_transactions

)

) FROM user_classification),

'top_buyers',
(SELECT JSON_ARRAYAGG(

JSON_OBJECT(

'name',name,
'total_transactions',total_transactions,
'revenue',revenue

)

) FROM top_buyers),

'top_sellers',
(SELECT JSON_ARRAYAGG(

JSON_OBJECT(

'name',name,
'total_sales',total_sales,
'revenue',revenue

)

) FROM top_sellers)

) AS dashboard;
```

---

# ⭐ RESULT RESPONSE

API langsung return:

```json
{
 "summary":{},
 "trend":[...],
 "trending_items":[...],
 "user_classification":[...],
 "top_buyers":[...],
 "top_sellers":[...]
}
```

Frontend tinggal render.

No mapping ribet.

---

# 🚀 CACHE STRATEGY (WAJIB)

Cache:

```php
Cache::remember(
 'dashboard_'.md5(json_encode($filters)),
 300,
 fn()=>query()
);
```

5 menit.

Dashboard jadi ngebut.

---

# 🚀 INDEX WAJIB (SUPER IMPORTANT)

Migration:

```sql
INDEX idx_created_at(created_at),

INDEX idx_item_created(item_id,created_at),

INDEX idx_buyer(buyer_id),

INDEX idx_seller(seller_id)
```

Kalau ini ada:

query ini kenceng banget.

---

# ⭐ SENIOR EXTRA MOVE (Highly Recommended)

Tambahkan:

```
?include=
```

Example:

```
?include=summary,trend
```

API jadi fleksibel.

Senior banget.

---

# 🔥 HONEST ADVICE

Kalau kamu implement ini:

Reviewer backend biasanya langsung tahu:

> "ini bukan CRUD dev."

Ini analytics engineer thinking.

---

Kalau mau saya bisa lanjut kasih:

