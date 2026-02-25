# Charts and Analytics

## Overview

The dashboard uses Recharts for data visualization. Each chart type is chosen based on the data characteristics and analytical purpose.

## Chart Types

### 1. Line Chart - Transaction Trends

**Component:** `TrendsChart.tsx`

**Purpose:** Show transaction volume and revenue over time.

**Data Structure:**

```typescript
interface Trend {
  period: string; // Date/Week/Month
  transactions: number;
  revenue: string;
}
```

**Implementation:**

```tsx
<LineChart data={formattedData}>
  <XAxis dataKey="period" />
  <YAxis yAxisId="left" />
  <YAxis yAxisId="right" orientation="right" />
  <Tooltip formatter={formatCurrency} />
  <Line yAxisId="left" dataKey="transactions" />
  <Line yAxisId="right" dataKey="revenue" />
</LineChart>
```

**Why Line Chart:**

- Shows continuous data over time
- Easy to identify trends and patterns
- Dual Y-axis allows comparing volume and revenue

---

### 2. Bar Chart - Trending Items

**Component:** `TrendingItemsChart.tsx`

**Purpose:** Compare revenue across top items.

**Data Structure:**

```typescript
interface TrendingItem {
  item_id: string;
  item_name: string;
  total_revenue: number;
  total_quantity: number;
}
```

**Implementation:**

```tsx
<BarChart data={chartData}>
  <XAxis dataKey="item_name" />
  <YAxis />
  <Tooltip formatter={formatCompactNumber} />
  <Bar dataKey="total_revenue" />
</BarChart>
```

**Why Bar Chart:**

- Compare discrete categories
- Easy to identify top performers
- Horizontal labels for item names

---

### 3. Pie Chart - User Classification

**Component:** `UserClassificationChart.tsx`

**Purpose:** Show distribution of domestic vs foreign users.

**Data Structure:**

```typescript
interface UserTypeDistribution {
  user_type: "domestic" | "foreign";
  count: number;
  percentage: number;
}
```

**Implementation:**

```tsx
<PieChart>
  <Pie
    data={data}
    dataKey="count"
    nameKey="user_type"
    cx="50%"
    cy="50%"
    outerRadius={80}
    label
  />
  <Tooltip />
</PieChart>
```

**Why Pie Chart:**

- Show proportion/percentage
- Limited categories (2)
- Easy to understand distribution

---

### 4. Bar Chart - Revenue Contribution

**Component:** `RevenueContributionChart.tsx`

**Purpose:** Show which items contribute most to revenue.

**Data Structure:**

```typescript
interface RevenueContribution {
  item_id: string;
  item_name: string;
  revenue: number;
  percentage: number;
}
```

**Implementation:**

```tsx
<BarChart data={chartData}>
  <XAxis dataKey="item_name" />
  <YAxis />
  <Tooltip formatter={formatCompactNumber} />
  <Bar dataKey="revenue" radius={[4, 4, 0, 0]} />
</BarChart>
```

**Why Bar Chart:**

- Show contribution percentage
- Rank items by importance
- Visual comparison of contributions

---

## Chart Configuration

### Responsive Container

All charts use ResponsiveContainer for responsive sizing:

```tsx
<ResponsiveContainer width="100%" height={300}>
  <LineChart>...</LineChart>
</ResponsiveContainer>
```

### Tooltips

Custom formatting for large numbers:

```tsx
<Tooltip formatter={(value, name) => [formatCompactNumber(value), name]} />
```

### Colors

Consistent color scheme:

| Data         | Color            |
| ------------ | ---------------- |
| Transactions | #3b82f6 (blue)   |
| Revenue      | #10b981 (green)  |
| Domestic     | #3b82f6 (blue)   |
| Foreign      | #f97316 (orange) |

---

## Data Formatting

### formatCurrency

```typescript
export function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
  }).format(amount);
}
```

### formatCompactNumber

```typescript
export function formatCompactNumber(num: number): string {
  if (num >= 1e12) return (num / 1e12).toFixed(1) + "T";
  if (num >= 1e9) return (num / 1e9).toFixed(1) + "B";
  if (num >= 1e6) return (num / 1e6).toFixed(1) + "M";
  if (num >= 1e3) return (num / 1e3).toFixed(1) + "K";
  return num.toString();
}
```

---

## Loading States

Each chart shows a Skeleton while loading:

```tsx
{
  loading.trends ? (
    <div className="h-[300px]">
      <Skeleton className="h-full w-full" />
    </div>
  ) : (
    <TrendsChart data={trends} />
  );
}
```

---

## Error Handling

Charts gracefully handle empty data:

```tsx
{
  data.length === 0 ? (
    <div className="h-[300px] flex items-center justify-center">
      <p className="text-muted-foreground">No data available</p>
    </div>
  ) : (
    <TrendsChart data={data} />
  );
}
```

---

## Accessibility

- Charts have proper ARIA labels
- Color contrast meets WCAG guidelines
- Keyboard navigation for interactive elements

---

## Performance

- Charts only re-render when data changes
- Memoized data transformations
- Debounced filter updates prevent rapid re-renders
