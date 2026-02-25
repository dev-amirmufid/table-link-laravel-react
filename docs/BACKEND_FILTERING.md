# Backend Filtering System

## Overview

The backend implements a unified filtering system that applies consistently across all analytics endpoints. The system is designed around a single source of truth - the `DashboardFilter` class.

## Filter Architecture

```
Request Query Parameters
         │
         ▼
┌─────────────────────┐
│  DashboardFilter    │  ← Validates and parses filters
└─────────────────────┘
         │
         ▼
┌─────────────────────┐
│   FilterService     │  ← Provides filter data to controllers
└─────────────────────┘
         │
         ▼
┌─────────────────────┐
│   Controllers       │  ← Apply filters to queries
└─────────────────────┘
```

## Supported Filters

### 1. Date Range Filter

Filters data by date range.

| Parameter  | Type   | Format | Required |
| ---------- | ------ | ------ | -------- |
| start_date | string | Y-m-d  | No\*     |
| end_date   | string | Y-m-d  | No\*     |

\*Both must be present to be applied.

**Implementation:**

```php
if (isset($filters['start_date']) && isset($filters['end_date'])) {
    $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
}
```

### 2. Period Filter

Groups trends data by time period.

| Parameter | Type   | Values                 | Default |
| --------- | ------ | ---------------------- | ------- |
| period    | string | daily, weekly, monthly | daily   |

**Implementation:**

```php
$dateFormat = match ($period) {
    'daily' => 'DATE(t.created_at)',
    'weekly' => "DATE_FORMAT(t.created_at, '%Y-%u')",
    'monthly' => "DATE_FORMAT(t.created_at, '%Y-%m')",
    default => 'DATE(t.created_at)',
};
```

### 3. User Type Filter

Filters data by buyer/seller user type.

| Parameter | Type   | Values            | Required |
| --------- | ------ | ----------------- | -------- |
| user_type | string | domestic, foreign | No       |

**Implementation:**

```php
if (isset($filters['user_type'])) {
    $query->whereHas('buyer', function ($q) use ($filters) {
        $q->where('type', $filters['user_type']);
    });
}
```

### 4. Search Filter

Searches across buyer name, seller name, and item name.

| Parameter | Type   | Min Length   | Required |
| --------- | ------ | ------------ | -------- |
| search    | string | 2 characters | No       |

**Implementation:**

```php
if (isset($filters['search'])) {
    $query->whereHas('buyer', function ($q) use ($filters) {
        $q->where('name', 'like', "%{$filters['search']}%");
    });
}
```

### 5. Item ID Filter

Filters transactions by specific item.

| Parameter | Type          | Required |
| --------- | ------------- | -------- |
| item_id   | string (UUID) | No       |

### 6. Buyer ID Filter

Filters transactions by specific buyer.

| Parameter | Type          | Required |
| --------- | ------------- | -------- |
| buyer_id  | string (UUID) | No       |

### 7. Seller ID Filter

Filters transactions by specific seller.

| Parameter | Type          | Required |
| --------- | ------------- | -------- |
| seller_id | string (UUID) | No       |

## FilterService

The `FilterService` acts as an intermediary between controllers and `DashboardFilter`.

```php
class FilterService
{
    public function getFilters(Request $request): array
    {
        return $this->dashboardFilter->filters($request);
    }
}
```

## DashboardFilter

Located at `app/Filters/DashboardFilter.php`, this class:

1. **Parses** query parameters from Request
2. **Validates** filter values against allowed lists
3. **Returns** clean filter array

### Validation Rules

| Filter     | Validation                         |
| ---------- | ---------------------------------- |
| start_date | Valid date, <= end_date            |
| end_date   | Valid date, >= start_date          |
| period     | Must be: daily, weekly, or monthly |
| user_type  | Must be: domestic or foreign       |
| search     | Minimum 2 characters               |

### Usage Example

```php
// In Controller
$filters = $this->filterService->getFilters($request);

// Use in query
$query = DB::table('transactions')
    ->when(isset($filters['start_date']), function ($q) use ($filters) {
        $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
    })
    ->when(isset($filters['user_type']), function ($q) use ($filters) {
        $q->whereHas('buyer', function ($sub) use ($filters) {
            $sub->where('type', $filters['user_type']);
        });
    });
```

## Filtering Strategy

### Strategy Pattern

The filtering system uses the Query Builder's `when()` method for conditional filtering:

```php
$query->when(condition, callback);
```

This allows:

- Conditional clause application
- Readable, chainable code
- Efficient SQL generation

### Raw SQL vs Query Builder

For complex analytics queries, raw SQL is used in `AnalyticsController`:

```php
$whereConditions = [];
if (isset($filters['start_date']) && isset($filters['end_date'])) {
    $whereConditions[] = "t.created_at BETWEEN '{$filters['start_date']}' AND '{$filters['end_date']}'";
}
$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
```

## Performance Considerations

### 1. Index Usage

Filters use indexed columns:

- `created_at` - indexed for date range queries
- `buyer_id` - indexed for user lookups
- `seller_id` - indexed for user lookups
- `user_type` - not indexed (consider adding for large datasets)

### 2. Caching

All analytics endpoints use Laravel Cache with 5-minute TTL:

```php
$data = Cache::remember($cacheKey, self::CACHE_TTL, function () use (...) {
    // Complex queries
});
```

### 3. Query Optimization

- Filters are applied before aggregations
- DISTINCT counts use indexed columns
- Date formats are pre-computed in SELECT

## Filter Application Matrix

| Endpoint             | start_date | end_date | period | user_type | search |
| -------------------- | ---------- | -------- | ------ | --------- | ------ |
| /dashboard/analytics | ✓          | ✓        | ✓      | ✓         | -      |
| /transactions        | ✓          | ✓        | -      | ✓         | ✓      |
| /transactions/{id}   | -          | -        | -      | -         | -      |

## Error Handling

Invalid filter values are silently ignored (filtered out rather than throwing errors):

```php
if ($this->validatePeriod($period)) {
    $filters['period'] = $period;
}
// Invalid values are simply not added to the filters array
```

This design decision ensures API robustness - invalid filters don't break the request, they just aren't applied.
