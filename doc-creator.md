# ULTIMATE DOCUMENTATION GENERATOR INSTRUCTION
(Laravel 12 Backend + React Analytics Dashboard)

You are a Senior Software Engineer (10+ years experience).

Your job is to analyze the ENTIRE codebase and generate production-quality technical documentation.

You MUST scan:

- Laravel Controllers
- Form Requests
- Services
- Repositories
- Models
- Database Migrations
- Raw SQL Queries
- Routes (api.php + web.php)

AND

- React Components
- Dashboard Pages
- Axios API Calls
- Chart Components
- Filter State Management

DO NOT assume features.

Only document features that exist inside the codebase.

---

# PRIMARY OBJECTIVE

Generate complete documentation for:

1. API Endpoint Recommendation
2. Backend Filtering System
3. 4 Data Classification Analytics
4. Dashboard Analytics API Design

Documentation must reflect real implementation.

---

# DOCUMENTATION OUTPUT LOCATION

Create documentation inside:

/docs

Create or update the following files:

README.md

/docs:

SYSTEM_ARCHITECTURE.md
DATABASE_SCHEMA.md
API_SPECIFICATION.md
BACKEND_FILTERING.md
DATA_CLASSIFICATION.md
ANALYTICS_API_DESIGN.md
SQL_QUERIES.md
PERFORMANCE.md
FRONTEND_DASHBOARD.md
CHARTS_AND_ANALYTICS.md
ASSUMPTIONS.md
LIMITATIONS.md

---

# GLOBAL RULES

DO NOT hallucinate database tables.

Extract schema from:

- Laravel migrations
- Models

DO NOT invent endpoints.

Extract from:

routes/api.php

Controllers.

DO NOT assume filters.

Only document filters implemented in code.

---

# STEP 1 — SYSTEM ARCHITECTURE

Analyze:

Laravel Layers:

Controller
Service
Repository
Database

React:

Dashboard Pages
API Calls.

Generate:

SYSTEM_ARCHITECTURE.md

Include:

Request Flow.

Frontend → API → Service → Repository → Database.

---

# STEP 2 — DATABASE SCHEMA

Scan migrations.

Document:

Tables.

Columns.

Relationships.

Indexes.

Generate:

DATABASE_SCHEMA.md.

---

# STEP 3 — API SPECIFICATION

Scan:

routes/api.php.

Controllers.

FormRequest validation.

Generate:

API_SPECIFICATION.md.

For each endpoint include:

Method.

URL.

Query Parameters.

Validation rules.

Response Structure.

HTTP Status Codes.

---

# STEP 4 — BACKEND FILTERING

Analyze:

Query Builder usage.

Examples:

when()
whereBetween()
whereIn()

Document:

Supported filters.

Filtering strategy.

Performance consideration.

Generate:

BACKEND_FILTERING.md.

---

# STEP 5 — DATA CLASSIFICATION

Detect analytics grouping logic.

Example:

GROUP BY buyer.

GROUP BY region.

GROUP BY brand.

Generate:

DATA_CLASSIFICATION.md.

Explain:

Classification purpose.

Aggregation columns.

Sorting logic.

---

# STEP 6 — ANALYTICS API DESIGN

Identify:

Dashboard aggregation endpoints.

Explain:

Single endpoint strategy.

Why aggregation exists.

Response composition.

Generate:

ANALYTICS_API_DESIGN.md.

---

# STEP 7 — SQL QUERIES

Extract:

Raw SQL.

DB::select.

Query Builder aggregation.

CTE usage.

Document:

Summary Query.

Classification Query.

Recommendation Query.

Generate:

SQL_QUERIES.md.

Include SQL examples.

---

# STEP 8 — PERFORMANCE

Analyze:

Indexes usage.

Heavy aggregation.

Potential N+1 queries.

Cache usage.

Generate:

PERFORMANCE.md.

Explain:

Optimization opportunities.

---

# STEP 9 — FRONTEND DASHBOARD

Scan React:

Dashboard pages.

Charts.

Axios usage.

Filters.

Generate:

FRONTEND_DASHBOARD.md.

Explain:

Data fetch flow.

Loading state.

Error handling.

---

# STEP 10 — CHARTS ANALYTICS

Detect charts used.

Line.

Bar.

Pie.

Explain:

Why each chart type used.

Generate:

CHARTS_AND_ANALYTICS.md.

---

# STEP 11 — ASSUMPTIONS

If business logic ambiguity exists:

Document inferred assumptions.

Example:

Date timezone.

Calculation logic.

Generate:

ASSUMPTIONS.md.

---

# STEP 12 — LIMITATIONS

Identify:

Possible scaling issues.

Missing caching.

Large aggregation risks.

Generate:

LIMITATIONS.md.

---

# OUTPUT STYLE

Write in professional engineering documentation style.

Avoid marketing language.

Use:

Headings.

Code examples.

Tables when useful.

---

# QUALITY STANDARD

Documentation must look like:

Enterprise SaaS backend documentation.

Clear.

Concise.

Technical.

Senior level.
