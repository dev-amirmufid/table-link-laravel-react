# Assumptions

## Overview

This document outlines the assumptions made during the system design and implementation.

## Business Logic Assumptions

### 1. User Type Classification

**Assumption:** Users are classified as either "domestic" or "foreign" at account creation.

**Evidence:** The `users.type` column is an enum with only these two values.

**Implication:**

- No mixed or unknown user types
- No way to change user type after creation

### 2. Transaction Pricing

**Assumption:** Prices are stored as integers in the smallest currency unit (e.g., cents for USD, rupiah for IDR).

**Evidence:** `transactions.price` is stored as `integer`.

**Implication:**

- No floating-point precision issues
- Division needed for average calculations
- Frontend formatting required for display

### 3. Quantity Units

**Assumption:** Transaction quantity represents individual units.

**Evidence:** `transactions.quantity` stored as integer.

**Implication:**

- No fractional quantities
- Total = quantity × price

### 4. Transaction Timestamps

**Assumption:** All transaction timestamps are in UTC.

**Evidence:** Laravel's `created_at` uses UTC by default.

**Implication:**

- Date filtering may need timezone adjustment
- Frontend displays based on user's local timezone

### 5. Data Volume

**Assumption:** The system handles up to several million transactions.

**Evidence:** Seeded with 300,000 transactions for testing.

**Implication:**

- Current indexes are sufficient
- May need optimization for larger datasets

## Technical Assumptions

### 1. Single Timezone Display

**Assumption:** All users view data in the same timezone.

**Evidence:** No timezone selector in the UI.

**Implication:**

- Single timezone for all date displays
- Server timezone (UTC) used for calculations

### 2. JSON API Format

**Assumption:** Clients expect JSON responses.

**Evidence:** All endpoints return JSON.

**Implication:**

- No XML or other format support
- Standard HTTP status codes used

### 3. Client-Side Caching

**Assumption:** Clients will not cache API responses.

**Evidence:** No cache headers in responses.

**Implication:**

- Server-side caching (5 min) is primary cache
- Repeated requests hit cache

### 4. No Real-Time Updates

**Assumption:** Analytics data doesn't need real-time updates.

**Evidence:** 5-minute cache TTL.

**Implication:**

- Acceptable latency for dashboard
- Manual cache clear available

### 5. Pagination Assumptions

**Assumption:** Users will typically view first few pages of transactions.

**Evidence:** Default per_page=15, max=100.

**Implication:**

- Deep pagination may be slow
- No cursor-based pagination

## Design Assumptions

### 1. God Query Pattern

**Assumption:** Frontend needs all analytics data at once.

**Evidence:** Single `/dashboard/analytics` endpoint returns everything.

**Implication:**

- Simpler frontend state management
- All-or-nothing data fetching

### 2. Unified Filters

**Assumption:** All analytics use the same filter parameters.

**Evidence:** Single `DashboardFilter` class used everywhere.

\*\*Implication across dashboard

- Limited customization:\*\*
- Consistent UX per endpoint

### 3. Database Caching

**Assumption:** Database cache is sufficient for production.

**Evidence:** Using MySQL for Laravel cache.

**Implication:**

- May need Redis for high-traffic
- Simple deployment

### 4. No Rate Limiting

**Assumption:** No API rate limiting required.

**Evidence:** No rate limit middleware.

**Implication:**

- Vulnerable to abuse
- Needs implementation for public API

### 5. JWT Authentication

**Assumption:** JWT tokens are the appropriate auth method.

**Evidence:** Using php-open-source-saver/jwt-auth.

**Implication:**

- Stateless authentication
- Token refresh mechanism needed

## Future Considerations

These assumptions may need revisiting:

1. **Multi-timezone support:** Add timezone selector
2. **Rate limiting:** Implement for API protection
3. **Real-time:** WebSocket for live updates
4. **Cursor pagination:** For deep transaction lists
5. **Redis cache:** For better performance
