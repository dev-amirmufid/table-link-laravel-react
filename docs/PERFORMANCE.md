# Performance

## Overview

This document outlines the performance characteristics and optimization strategies of the analytics system.

## Current Performance Metrics

### Response Times

| Endpoint               | Typical Response | With Cache |
| ---------------------- | ---------------- | ---------- |
| `/dashboard/analytics` | 500-2000ms       | 10-50ms    |
| `/transactions`        | 100-500ms        | N/A        |

### Load Testing Results

- **300,000 transactions:** ~2s for full analytics
- **Cached requests:** ~20ms response time
- **Pagination:** ~100ms for 15 items

## Optimization Strategies

### 1. Database Indexing

The following indexes exist on the transactions table:

```sql
CREATE INDEX transactions_buyer_id_index ON transactions(buyer_id);
CREATE INDEX transactions_seller_id_index ON transactions(seller_id);
CREATE INDEX transactions_item_id_index ON transactions(item_id);
CREATE INDEX transactions_created_at_index ON transactions(created_at);
```

**Impact:**

- Date range queries: Full table scan avoided
- User lookups: O(1) instead of O(n)
- Aggregations: Faster GROUP BY operations

### 2. Caching Strategy

**Implementation:**

```php
Cache::remember($cacheKey, 300, function () {
    // Expensive queries
});
```

**Cache Configuration:**

- TTL: 300 seconds (5 minutes)
- Store: Database (MySQL)
- Key generation: MD5 of filters + parameters

**Cache Invalidation:**

- Automatic: TTL expiration
- Manual: `/dashboard/cache/clear` endpoint

### 3. Query Optimization

#### Use of Clone

```php
$query = DB::table('transactions');

// Clone for reuse
$totals = $query->clone()
    ->selectRaw('COUNT(*) as total')
    ->first();
```

#### Conditional Building

```php
$query->when($condition, function ($q) {
    return $q->where(...);
});
```

#### Limit Usage

```php
->limit(10)  // Top 10 queries
->limit(100) // Maximum for performance
```

### 4. Frontend Optimizations

#### Debouncing

```typescript
// Debounce filter changes
const fetchTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

useEffect(() => {
  if (fetchTimerRef.current) {
    clearTimeout(fetchTimerRef.current);
  }
  fetchTimerRef.current = setTimeout(() => {
    dispatch(fetchDashboardAnalytics({ filters, period, limit }));
  }, 300);
}, [filters, period, limit]);
```

#### Loading States

Individual loading states per section allow partial UI updates.

### 5. Resource Optimization

#### Docker Resources

- PHP-FPM: 2-4 workers
- MySQL: Memory-optimized
- Nginx: Gzip compression enabled

#### Static Assets

- Frontend bundled with Vite
- Asset caching headers
- Gzip compression for JSON responses

## Performance Bottlenecks

### Identified Issues

1. **Full Table Scans**
   - When no date filter is applied
   - `user_type` column not indexed

2. **Large Aggregations**
   - COUNT(DISTINCT) on millions of rows
   - SUM operations on large datasets

3. **N+1 Potential**
   - Transaction list includes related data
   - Eager loading could help

4. **Cache Stampede**
   - Multiple requests during cache miss
   - No locking mechanism

## Recommendations

### High Priority

1. **Add Index on user_type**

   ```sql
   CREATE INDEX transactions_buyer_type_index ON transactions(buyer_id, created_at);
   ```

2. **Implement Cache Locking**
   - Prevent cache stampede
   - Use Redis for distributed locking

3. **Query Result Limits**
   - Enforce maximum limit values
   - Implement pagination for large result sets

### Medium Priority

1. **Use Redis Cache**
   - Faster than MySQL cache
   - Support for cache tags

2. **Add Database Views**
   - Pre-computed aggregations
   - Materialized views for reports

3. **Optimize JOINs**
   - Use proper join types
   - Add covering indexes

### Low Priority

1. **CQRS Pattern**
   - Separate read/write models
   - Optimized read replicas

2. **Time-Series Database**
   - For trend data specifically
   - TimescaleDB or InfluxDB

## Monitoring

### Key Metrics to Track

- Response time percentiles (p50, p95, p99)
- Cache hit rate
- Database query count per request
- CPU/Memory usage

### Logging

```php
Log::info('Analytics query', [
    'filters' => $filters,
    'duration' => microtime(true) - $start,
    'cached' => $fromCache
]);
```

## Scaling Considerations

### Horizontal Scaling

- Add more PHP-FPM workers
- Use read replicas for MySQL
- CDN for static assets

### Vertical Scaling

- Increase MySQL buffer pool
- More PHP-FPM process managers
- Larger container memory limits
