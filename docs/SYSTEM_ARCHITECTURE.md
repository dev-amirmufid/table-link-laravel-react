# System Architecture

## Overview

TableLink is a Laravel 12 + React Analytics Dashboard application for transaction analytics. The system follows a layered architecture pattern with clear separation of concerns.

## Architecture Layers

### Backend (Laravel 12)

```
┌─────────────────────────────────────────────────────────────┐
│                     API Layer                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Analytics   │  │ Transaction │  │ Auth               │  │
│  │ Controller  │  │ Controller  │  │ Controller         │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Service Layer                              │
│  ┌─────────────┐  ┌─────────────┐                           │
│  │ Filter      │  │ Analytics   │                           │
│  │ Service     │  │ Service     │                           │
│  └─────────────┘  └─────────────┘                           │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                 Repository Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Transaction │  │ Item        │  │ User                │  │
│  │ Repository  │  │ Repository  │  │ Repository          │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Database Layer                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ MySQL 8.0   │  │ Cache       │  │ Migration           │  │
│  │             │  │ (Database)  │  │ System              │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Frontend (React + TypeScript)

```
┌─────────────────────────────────────────────────────────────┐
│                    Pages                                    │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Dashboard Page                                      │    │
│  │  - Summary Cards                                    │    │
│  │  - Charts (Trends, Trending Items, etc.)            │    │
│  │  - Data Tables                                      │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Components                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ FilterBar   │  │ Charts      │  │ Tables/Lists       │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    State Management                          │
│  ┌─────────────┐  ┌─────────────┐                           │
│  │ dashboard   │  │ transaction │                           │
│  │ Slice      │  │ Slice       │                           │
│  └─────────────┘  └─────────────┘                           │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    API Layer                                 │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Axios Client + API Functions                         │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

## Request Flow

### Analytics Request

1. **Frontend**: User loads Dashboard or changes filters
2. **Redux**: Dispatch `fetchDashboardAnalytics` action
3. **API Client**: Call `/api/v1/dashboard/analytics`
4. **Controller**: `AnalyticsController::analytics()` receives request
5. **Filter Service**: Parse and validate filters via `DashboardFilter`
6. **Cache Check**: Check Laravel Cache (5-minute TTL)
7. **Database**: Execute aggregated SQL queries
8. **Response**: Return JSON with all analytics data
9. **Frontend**: Update Redux state, render charts

### Transaction List Request

1. **Frontend**: User navigates to transaction table
2. **Redux**: Dispatch `fetchTransactions` action
3. **API Client**: Call `/api/v1/transactions`
4. **Controller**: `TransactionController::index()` receives request
5. **Repository**: `TransactionRepository::getAll()` executes paginated query
6. **Response**: Return paginated transaction data

## Key Design Patterns

### 1. Repository Pattern

- Transaction, Item, User repositories encapsulate database queries
- Provides clean interface for data access

### 2. Service Layer

- FilterService handles filter parsing and validation
- AnalyticsService (partially used) encapsulates business logic

### 3. God Query Pattern

- Single endpoint `/api/v1/dashboard/analytics` returns all dashboard data
- Reduces HTTP requests from N to 1

### 4. Unified Filtering

- DashboardFilter is single source of truth for all filters
- Applied consistently across all endpoints

### 5. State Management (Frontend)

- Redux Toolkit slices for dashboard and transactions
- Centralized loading and error states

## Docker Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  nginx:alpine (Port 8080)                                  │
│  - Reverse proxy                                           │
│  - Static file serving                                     │
└─────────────────────────────────────────────────────────────┘
         │                    │
         ▼                    ▼
┌─────────────────┐   ┌─────────────────┐
│ frontend:80     │   │ backend:9000    │
│ (React build)   │   │ (PHP-FPM)       │
└─────────────────┘   └─────────────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │ mysql:8.0       │
                     │ (Port 3306)     │
                     └─────────────────┘
```

## Technology Stack

| Component          | Technology                           |
| ------------------ | ------------------------------------ |
| Backend Framework  | Laravel 12                           |
| Frontend Framework | React 18 + TypeScript                |
| Database           | MySQL 8.0                            |
| Cache              | Database (MySQL)                     |
| API Documentation  | L5-Swagger (OpenAPI 3.0)             |
| Authentication     | JWT (php-open-source-saver/jwt-auth) |
| Charts             | Recharts                             |
| State Management   | Redux Toolkit                        |
| Styling            | Tailwind CSS                         |
| Docker             | Docker Compose                       |
| Web Server         | Nginx                                |
| Process Manager    | Supervisor                           |
