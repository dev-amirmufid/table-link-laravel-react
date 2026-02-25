# Analytics API Design

## Overview

The analytics API follows a "God Query" pattern - a single endpoint that returns all dashboard data in one request. This design minimizes HTTP overhead and simplifies frontend data management.

## God Query Pattern

### Endpoint

```
GET /api/v1/dashboard/analytics
```

### Design Rationale

**Before (N+1 Problem):**

```
GET /api/dashboard/summary     → Summary data
GET /api/dashboard/trends     → Trends data
GET /api/dashboard/trending   → Trending items
GET /api/dashboard/buyers     → Top buyers
GET /api/dashboard/sellers   → Top sellers
GET /api/dashboard/classification → User classification
```

Total: 6 HTTP requests

**After (God Query):**

```
GET /api/dashboard/analytics?include=summary,trends,trending_items,...
```

Total: 1 HTTP request

### Benefits

1. **Reduced Network Overhead:** Single round-trip for all data
2. **Simplified State Management:** One response to process
3. **Atomic Consistency:** All data from same cache snapshot
4. **Easier Caching:** Single cache key for all data

## Response Structure

```json
{
  "success": true,
  "data": {
    "summary": { ... },
    "trends": [ ... ],
    "trending_items": [ ... ],
    "user_classification": [ ... ],
    "top_buyers": [ ... ],
    "top_sellers": [ ... ],
    "revenue_contribution": [ ... ]
  },
  "period": "daily",
  "limit": 10,
  "include": ["summary", "trends", ...],
  "cached": true
}
```

## Selective Data Fetching

The `include` parameter allows clients to request only specific data sections:

### Include Options

| Value                | Description                  | Default |
| -------------------- | ---------------------------- | ------- |
| summary              | KPI summary cards            | ✓       |
| trends               | Transaction trends over time | ✓       |
| trending_items       | Top selling items            | ✓       |
| user_classification  | Domestic vs foreign users    | ✓       |
| top_buyers           | Top buying users             | ✓       |
| top_sellers          | Top selling users            | ✓       |
| revenue_contribution | Revenue by item              | ✓       |

### Examples

```bash
# All data
GET /api/v1/dashboard/analytics

# Only summary and trends
GET /api/v1/dashboard/analytics?include=summary,trends

# Only top buyers and sellers
GET /api/v1/dashboard/analytics?include=top_buyers,top_sellers
```

## Implementation Details

### Query Building

The analytics method builds a single SQL query using CTE (Common Table Expressions):

```php
public function analytics(Request $request): JsonResponse
{
    $filters = $this->filterService->getFilters($request);
    $include = $request->input('include', 'all');
    $period = $request->input('period', 'daily');
    $limit = $request->input('limit', 10);

    $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use (...) {
        // Build WHERE conditions from filters
        // Execute conditional queries based on include parameter
        // Return combined result
    });

    return response()->json([
        'success' => true,
        'data' => $data,
        ...
    ]);
}
```

### Period Support

The API supports multiple time granularities:

| Period  | SQL Format                         | Use Case              |
| ------- | ---------------------------------- | --------------------- |
| daily   | `DATE(created_at)`                 | Detailed daily trends |
| weekly  | `DATE_FORMAT(created_at, '%Y-%u')` | Weekly aggregation    |
| monthly | `DATE_FORMAT(created_at, '%Y-%m')` | Monthly overview      |

### Caching Strategy

- **TTL:** 300 seconds (5 minutes)
- **Cache Key:** Based on filters + include + period + limit
- **Invalidation:** Manual via `/dashboard/cache/clear`

```php
$cacheKey = $this->generateCacheKey('dashboard.analytics', $filters, [
    'include' => $include,
    'period' => $period,
    'limit' => $limit,
]);
```

## Error Handling

All errors return JSON even on exceptions:

```php
try {
    // Analytics logic
} catch (\Throwable $e) {
    Log::error($e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Failed to fetch dashboard analytics',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
        'data' => [
            'summary' => null,
            'trends' => [],
            // ... empty defaults
        ]
    ], 200); // Returns 200 even on error for frontend stability
}
```

## Client-Side Integration

### Redux Action

```typescript
export const fetchDashboardAnalytics = createAsyncThunk(
  "dashboard/fetchAnalytics",
  async ({ filters, period = "daily", limit = 10 }) => {
    const params = new URLSearchParams({
      period,
      limit: limit.toString(),
      include:
        "summary,trends,trending_items,user_classification,top_buyers,top_sellers,revenue_contribution",
    });

    // Add filters
    if (filters.start_date) params.append("start_date", filters.start_date);
    if (filters.end_date) params.append("end_date", filters.end_date);
    if (filters.user_type) params.append("user_type", filters.user_type);

    const response = await api.getAnalytics(filters, period, limit);
    return response.data;
  },
);
```

### State Update

```typescript
extraReducers: (builder) => {
  builder
    .addCase(fetchDashboardAnalytics.pending, (state) => {
      state.loading.summary = true;
      state.loading.trends = true;
      // ... set all loading states
    })
    .addCase(fetchDashboardAnalytics.fulfilled, (state, action) => {
      state.summary = action.payload.data.summary;
      state.trends = action.payload.data.trends;
      state.trendingItems = action.payload.data.trending_items;
      // ... update all state
    });
};
```

## Trade-offs

### Advantages

1. **Single Request:** Reduces network latency
2. **Consistent Data:** All data from same snapshot
3. **Simple Caching:** One cache entry
4. **Flexible:** Selective include parameter

### Disadvantages

1. **Larger Response:** Returns more data than needed
2. **All-or-Nothing:** Can't partially update UI
3. **Cache Complexity:** Cache invalidation affects all data

### Mitigation

- Use `include` parameter to request only needed data
- Individual loading states for partial updates
- 5-minute cache balance freshness vs performance

## Future Enhancements

Potential improvements:

1. **Streaming Responses:** For very large datasets
2. **Delta Updates:** Only return changed data
3. **Parallel Queries:** Execute queries concurrently
4. **GraphQL:** More flexible data selection
