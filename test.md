# PT. TABLELINK DIGITAL INOVASI

Ruko Arcade, MG Office Tower 6th floor. Jl. Pantai Indah Utara 2 Blok 3 MA & 3 MB,

## Tablelink Technical Test Documentation

## 1. Overview

The  **Transaction Dashboard Application** is a full-stack system designed to analyze
transactional relationships between users and items.

The primary goal of this dashboard is to provide stakeholders with actionable insights into:

- Which items are currently  **trending**
- How transaction activity evolves over time
- What  **strategic actions** should be taken based on data patterns

Users can analyze data across flexible time ranges:

- **Daily**
- **Weekly**
- **Monthly**

This system transforms raw transactional data into meaningful business intelligence.

## 2. Project Objectives

Build a dashboard application that:

- Consumes transaction data via a RESTful API
- Clearly visualizes relationships between transactions and their actors (buyers &
    sellers)
- Enables interactive and dynamic data exploration

The dashboard empowers users to:

- Explore  **entity relationships**
- Discover  **market trends**
- Generate  **data-driven insights**
- Make  **strategic decisions** based on transaction patterns

## 3. Tech Stack

### Backend — Laravel

- RESTful API architecture
- Eloquent ORM
- MySQL database
- Clean and scalable service structure

### Frontend — React + TypeScript

- Component-driven architecture
- Fully customizable UI (shadcn/ui preferred)
- API-driven state management
- Reusable visualization components

### Database — MySQL

- Normalized structure (up to Third Normal Form / 3NF)
- UUID-based primary keys
- Proper foreign key constraints

## 4. Database Schema

### Users

Represents both buyers and sellers.

| Column | Type | Description |
| --- | --- | --- |
| id | UUID (PK) | Primary identifier |
| name | string | User full name |
| email | string (unique) | User email address |
| email_verified_at | timestamp (nullable) | Email verification timestamp |
| password | string | Encrypted password |
| type | enum (`foreign`, `domestic`) | User classification |
| remember_token | string | Authentication token |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

### Items

Represents tradable products in the system,

| Column | Type | Description |
| --- | --- | --- |
| id | UUID (PK) | Primary identifier |
| item_code | string (unique) | Unique item code |
| item_name | string | Name of the item |
| minimum_price | integer | Minimum allowed transaction price |
| maximum_price | integer | Maximum allowed transaction price |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

### Transactions

Represents trade activity between users.

| Column | Type | Description |
| --- | --- | --- |
| id | UUID (PK) | Primary identifier |
| buyer_id | UUID (FK → users.id) | Reference to buyer |
| seller_id | UUID (FK → users.id) | Reference to seller |
| item_id | UUID (FK → items.id) | Reference to traded item |
| quantity | integer | Quantity traded |
| price | integer | Final transaction price |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record last update timestamp |

## 5. Data Relationships

- A User can act as either a buyer or a seller
- A User can participate in multiple transactions
- An Item can appear in multiple transactions
- Each Transaction belongs to:
  - One buyer (User)
  - One seller (User)
  - One item (Item)

## 6. Backend Requirements (Laravel)

The API must:

- Support time-based filtering (Daily / Weekly / Monthly)
- Provide aggregated transaction metrics
- Support item trend analysis
- Enable relationship-based queries (buyer ↔ seller ↔ item)
- Be scalable and maintain clean separation of concerns

API structure and endpoint design are fully customizable.

## 7. Frontend Requirements (React + Typescript)

The frontend must:

- Use a customizable component library (shadcn/ui preferred)
- Implement the following visualization components:
  - Data Table
  - Line Chart
  - Bar Chart
  - Pie Chart

- Support dynamic filtering (date range, user type, item, etc.)
- Follow modular and reusable architecture principles
- Maintain clean state management and separation of concerns

## 7. Score Composition

The project will be evaluated based on the following criteria (Total Score – 100):

| Component | Points | Description |
| --- | --- | --- |
| Backend Implementation | 20 | API architecture, database design, code quality, and scalability |
| Support Filter at Backend | 10 | Time-based filtering and query parameter handling |
| Data Table | 7.5 | Interactive data table with sorting, pagination, and search functionality |
| Line Chart | 7.5 | Time-series visualization for trend analysis |
| Bar Chart | 7.5 | Comparative data visualization |
| Pie Chart | 7.5 | Proportional data representation |
| Minimum of 4 Data Classifications | 20 | Multiple data analytic methods |
| Support Filter for Whole Dashboard | 10 | Unified filtering system across all visualizations |
| Additional Improvements | 10 | Extra features, optimizations, UX enhancements, and creative implementations |
