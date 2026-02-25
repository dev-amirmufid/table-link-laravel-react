🚀 ANALYTICS API REQUIREMENTS — Dashboard Recreate


Goal:
Dashboard harus bisa jawab:

✅ apa yang lagi trending?
✅ revenue naik atau turun?
✅ siapa buyer besar?
✅ siapa seller dominan?
✅ foreign vs domestic impact?

⭐ 1️⃣ Dashboard KPI Summary (WAJIB ADA)
Chart Type

👉 Summary Cards.

Atas dashboard.

API
GET /api/dashboard/summary
Data Ditampilkan

Minimal:

Total Transactions.

Total Quantity Sold.

Total Revenue.

Active Buyers.

Active Sellers.

Optional killer:

Avg Transaction Price.

Kenapa?

Stakeholder langsung lihat:

minggu ini sehat gak bisnisnya?

Visual Example
Total Revenue : Rp 250M ↑ 12%
Transactions : 4,200 ↑
Quantity : 12,000 ↓

🔥 Tambahkan:

trend indicator.

HR langsung notice UX thinking.

⭐ 2️⃣ Transaction Trend Analysis (SUPER WAJIB)
Chart

👉 Line Chart.

API
GET /api/dashboard/trends

Query:

?period=daily
Data

total transactions.

revenue.

Example
[
 {
  "period":"2026-02-01",
  "revenue":20000000,
  "transactions":120
 }
]
Insight

Jawab:

growth atau drop?

Senior Suggestion

Double line.

Line 1:

Revenue.

Line 2:

Transactions.

SUPER BAGUS.

⭐ 3️⃣ Trending Items (HIGH VALUE)
Chart

👉 Horizontal Bar Chart.

Kenapa horizontal?

nama item panjang.

API
GET /api/items/trending
Data

Top:

5 atau 10 item.

Metric:

SUM(quantity)
Insight

Jawab:

produk apa paling laku?

Optional Killer:

Top Revenue Item.

beda insight.

⭐ 4️⃣ Domestic vs Foreign Users
Chart

👉 Pie Chart.

atau:

👉 Donut Chart.

lebih modern.

API
GET /api/users/classification
Insight

Jawab:

foreign dominate gak?

Tambahan:

persentase.

Domestic 70%
Foreign 30%
⭐ 5️⃣ Buyer Seller Relationship (SENIOR BANGET)

Ini jarang dibuat kandidat.

Kalau kamu bikin:

HR langsung:

👀.

Chart

👉 Bar Chart.

API
GET /api/relations
Data

Top Buyers:

Buyer A — 200 transaksi.
Buyer B — 180 transaksi.

atau:

Top Seller.

Insight

Jawab:

siapa power user?

⭐ 6️⃣ Revenue Contribution (RECOMMENDED)

Ini tambahan killer.

Chart

👉 Pie Chart.

atau:

Stacked Bar.

API
GET /api/revenue/contribution
Data

Revenue per item.

atau:

Revenue per seller.

Insight:

siapa revenue maker.

Senior banget.

⭐ 7️⃣ Data Table (MANDATORY)

Chart bagus.

Tapi:

manager suka tabel.

API
GET /api/transactions
Features

Sorting.

Pagination.

Search.

Kolom:

buyer.

seller.

item.

quantity.

price.

date.

⭐ 8️⃣ Filter System (SUPER IMPORTANT)

Semua chart harus satu filter.

Filter:

date range.

period.

item.

user type.

Global.

⭐ 9️⃣ Dashboard Layout Recommendation

Best Flow:

Filter Bar

↓

Summary Cards

↓

Line Chart (Trend)

↓

Trending Items

↓

User Classification

↓

Buyer Seller Analysis

↓

Data Table.

Natural storytelling.