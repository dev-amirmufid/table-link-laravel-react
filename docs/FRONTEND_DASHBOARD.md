# Frontend Dashboard

## Overview

The React frontend provides an interactive dashboard for visualizing transaction analytics.

## Tech Stack

| Component        | Technology           |
| ---------------- | -------------------- |
| Framework        | React 19             |
| Language         | TypeScript           |
| State Management | Redux Toolkit        |
| HTTP Client      | Axios                |
| Charts           | Recharts             |
| Styling          | Tailwind CSS         |
| UI Components    | Radix UI (shadcn/ui) |

## Project Structure

```
frontend/
├── src/
│   ├── components/
│   │   ├── charts/          # Chart components
│   │   │   ├── TrendsChart.tsx
│   │   │   ├── TrendingItemsChart.tsx
│   │   │   ├── UserClassificationChart.tsx
│   │   │   └── RevenueContributionChart.tsx
│   │   ├── lists/           # List components
│   │   │   ├── TopBuyerList.tsx
│   │   │   └── TopSellerList.tsx
│   │   ├── ui/              # Base UI components
│   │   ├── FilterBar.tsx
│   │   ├── SummaryCards.tsx
│   │   └── TransactionsTable.tsx
│   ├── pages/
│   │   └── Dashboard.tsx
│   ├── store/
│   │   ├── slices/
│   │   │   ├── dashboardSlice.ts
│   │   │   └── transactionsSlice.ts
│   │   └── hooks.ts
│   ├── lib/
│   │   ├── api.ts           # API functions
│   │   └── utils.ts         # Utility functions
│   └── types/
│       └── index.ts         # TypeScript interfaces
```

## Dashboard Page

### Main Component

```tsx
// Dashboard.tsx
export function Dashboard() {
  const dispatch = useAppDispatch();
  const { summary, trends, ... } = useAppSelector(state => state.dashboard);

  useEffect(() => {
    dispatch(fetchDashboardAnalytics({ filters, period, limit }));
  }, [filters]);

  return (
    <div className="container mx-auto p-6 space-y-6">
      <FilterBar filters={filters} onFilterChange={handleFilterChange} />
      <SummaryCards data={summary} isLoading={loading.summary} />
      <TrendsChart data={trends} />
      {/* ... more charts and tables */}
    </div>
  );
}
```

## State Management

### Dashboard Slice

```typescript
// store/slices/dashboardSlice.ts
interface DashboardState {
  summary: Summary;
  trends: Trend[];
  trendingItems: TrendingItem[];
  userClassification: UserTypeDistribution[];
  topBuyers: TopUser[];
  topSellers: TopUser[];
  revenueContribution: RevenueContribution[];
  filters: DashboardFilters;
  loading: Record<string, boolean>;
  error: string | null;
}

export const fetchDashboardAnalytics = createAsyncThunk(
  "dashboard/fetchAnalytics",
  async ({ filters, period, limit }) => {
    const response = await api.getAnalytics(filters, period, limit);
    return response.data;
  },
);
```

### Loading States

Individual loading states for each section:

```typescript
const loading = {
  summary: false,
  trends: false,
  trendingItems: false,
  userClassification: false,
  topBuyers: false,
  topSellers: false,
  revenueContribution: false,
};
```

## Data Flow

### 1. Initial Load

```
User visits Dashboard
         │
         ▼
useEffect triggers
         │
         ▼
dispatch(fetchDashboardAnalytics({filters, period, limit}))
         │
         ▼
API call to /api/v1/dashboard/analytics
         │
         ▼
Response received
         │
         ▼
Redux state updated
         │
         ▼
Components re-render with new data
```

### 2. Filter Change

```
User changes filter
         │
         ▼
FilterBar onFilterChange
         │
         ▼
dispatch(setFilters(newFilters))
         │
         ▼
useEffect [filters] triggers
         │
         ▼
New API call with updated filters
         │
         ▼
State updated, charts re-render
```

## Components

### Summary Cards

Shows KPI metrics:

- Total Transactions
- Total Revenue
- Active Buyers
- Active Sellers
- Average Transaction Price

### FilterBar

Unified filter controls:

- Date Range (start_date, end_date)
- Period (daily, weekly, monthly)
- User Type (domestic, foreign)

### Charts

| Chart                    | Type      | Data                         |
| ------------------------ | --------- | ---------------------------- |
| TrendsChart              | LineChart | Transaction trends over time |
| TrendingItemsChart       | BarChart  | Top items by revenue         |
| UserClassificationChart  | PieChart  | Domestic vs foreign          |
| RevenueContributionChart | BarChart  | Revenue contribution         |

### TransactionsTable

Paginated transaction list with:

- Search functionality
- Pagination (15, 30, 50, 100 per page)
- Related data (buyer, seller, item)

## API Integration

### Axios Configuration

```typescript
// lib/api.ts
const api = axios.create({
  baseURL: "/api/v1",
  headers: {
    "Content-Type": "application/json",
  },
});

export const getAnalytics = (filters, period, limit) => {
  return api.get("/dashboard/analytics", {
    params: { ...filters, period, limit },
  });
};
```

### Debouncing

Filter changes are debounced to prevent rapid API calls:

```typescript
useEffect(() => {
  const timer = setTimeout(() => {
    dispatch(fetchDashboardAnalytics({ filters, period, limit }));
  }, 300);
  return () => clearTimeout(timer);
}, [filters, period, limit]);
```

## Error Handling

### API Errors

```typescript
extraReducers: (builder) => {
  builder.addCase(fetchDashboardAnalytics.rejected, (state, action) => {
    state.error = action.error.message;
  });
};
```

### Error Display

Errors are logged but UI continues to function with empty states.

## Performance Optimizations

1. **Debouncing:** 300ms delay on filter changes
2. **Individual Loading States:** Partial updates possible
3. **Memoization:** useMemo for sorted/filtered data
4. **Code Splitting:** Component-based lazy loading

## TypeScript Types

### DashboardFilters

```typescript
interface DashboardFilters {
  start_date?: string;
  end_date?: string;
  period?: "daily" | "weekly" | "monthly";
  user_type?: "domestic" | "foreign";
}
```

### Summary

```typescript
interface Summary {
  total_transactions: number;
  total_quantity: number;
  total_revenue: number;
  active_buyers: number;
  active_sellers: number;
  avg_transaction_price: number;
}
```

## Styling

Uses Tailwind CSS with dark mode support:

```tsx
<div className="container mx-auto p-6 space-y-6">
  <Card>
    <CardHeader>
      <CardTitle>Transaction Trends</CardTitle>
    </CardHeader>
    <CardContent>
      <TrendsChart data={trends} />
    </CardContent>
  </Card>
</div>
```
