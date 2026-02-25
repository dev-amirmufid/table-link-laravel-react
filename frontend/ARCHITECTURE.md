# Architecture Documentation - Frontend

## 📐 Architectural Overview

Frontend menggunakan **Component-Driven Architecture** dengan prinsip separation of concerns untuk memastikan code dapat di-maintain dan di-scale dengan baik.

## 🏗️ Architecture Layers

```
┌─────────────────────────────────────────────────┐
│              Pages Layer                         │
│              (Dashboard)                        │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│           Components Layer                       │
│  (FilterBar, Charts, Tables, Cards)            │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│            Services Layer                        │
│        (API Client, State Management)           │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│             Types Layer                          │
│           (TypeScript Interfaces)               │
└─────────────────────────────────────────────────┘
```

## 🎯 Design Principles

### 1. Component-Driven Design

Setiap komponen memiliki:
- Single Responsibility
- Reusability
- Composability
- Testability

**Contoh:**
```typescript
// LineChartComponent - Hanya tanggung jawab untuk line chart
export function LineChartComponent({ data, title }: LineChartComponentProps) {
  return (
    <ResponsiveContainer>
      <RechartsLineChart data={data}>
        {/* Chart logic */}
      </RechartsLineChart>
    </ResponsiveContainer>
  )
}
```

### 2. Separation of Concerns

```
src/
├── components/     # Presentational components
├── pages/         # Page components
├── lib/           # Utilities and API
├── types/         # TypeScript types
└── hooks/         # Custom React hooks
```

### 3. Data Flow

```
User Action → Component → API Client → Backend API
                ↑                      │
                └──────────────────────┘
                     (React Query)
```

## 🔌 State Management

### Tanstack Query (React Query)

**Kenapa Tanstack Query:**
- Automatic caching
- Background refetching
- Optimistic updates
- Error handling

**Implementasi:**
```typescript
import { useQuery } from '@tanstack/react-query'

function Dashboard() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['dashboard', filters],
    queryFn: () => dashboardApi.getAll(filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
  })

  if (isLoading) return <Skeleton />
  if (error) return <ErrorMessage error={error} />

  return <DashboardContent data={data} />
}
```

### Query Keys

```typescript
// Consistent query key pattern
queryKey: ['dashboard', filters]
queryKey: ['transactions', page, perPage]
queryKey: ['transactions', id]
```

## 📦 Component Architecture

### Component Types

#### 1. Presentational Components

Hanya bertanggung jawab untuk tampilan, tidak memiliki business logic.

```typescript
// SummaryCards.tsx
interface SummaryCardsProps {
  data: Summary
}

export function SummaryCards({ data }: SummaryCardsProps) {
  // Only rendering logic
  return <Card>{card.value}</Card>
}
```

#### 2. Container Components

Menghubungkan presentational components dengan data.

```typescript
// Dashboard.tsx (Page Component)
function Dashboard() {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard', filters],
    queryFn: () => dashboardApi.getAll(filters),
  })

  return (
    <>
      <FilterBar onFilterChange={setFilters} />
      <SummaryCards data={data.summary} />
      <Charts data={data} />
    </>
  )
}
```

### Component Composition

```typescript
// Dashboard menggunakan composition pattern
function Dashboard() {
  return (
    <div className="space-y-6">
      <FilterBar />
      <SummaryCards />
      <div className="grid grid-cols-2">
        <LineChartComponent />
        <BarChartComponent />
        <PieChartComponent />
      </div>
      <DataTable />
    </div>
  )
}
```

## 🔐 TypeScript Usage

### Type Definitions

```typescript
// types/index.ts
export interface DashboardData {
  summary: Summary
  trends: Trend[]
  trending_items: TrendingItem[]
  top_buyers: TopUser[]
  top_sellers: TopUser[]
  user_type_distribution: UserTypeDistribution[]
}

export interface DashboardFilters {
  start_date?: string
  end_date?: string
  period?: 'daily' | 'weekly' | 'monthly'
  user_type?: string
}
```

### Prop Types

```typescript
interface ChartProps {
  data: Trend[]
  title?: string
}

// Using generic types for reusability
interface BarChartProps<T> {
  data: T[]
  xKey: keyof T
  yKey: keyof T
}
```

## 🎨 UI Architecture

### Styling Approach

Menggunakan **Tailwind CSS** dengan approach:

1. **Utility-first**: Setiap class memiliki single purpose
2. **Responsive**: Mobile-first approach
3. **Themeable**: CSS variables untuk theming

```typescript
// Conditional classes dengan cn()
import { cn } from '@/lib/utils'

<div className={cn(
  "p-4 rounded-lg",
  isActive && "bg-primary text-white",
  isDisabled && "opacity-50"
)} />
```

### Component Variants

```typescript
// shadcn/ui pattern
const Card = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn("rounded-lg border bg-card", className)}
      {...props}
    />
  )
)
```

## 🔄 Data Flow Patterns

### Filter State

```typescript
// Global filter state
const [filters, setFilters] = useState<DashboardFilters>({
  period: 'daily',
})

// Passed to components
<FilterBar 
  filters={filters} 
  onFilterChange={setFilters} 
/>

// Used in API calls
const { data } = useQuery({
  queryKey: ['dashboard', filters],
  queryFn: () => dashboardApi.getAll(filters),
})
```

### Error Handling

```typescript
try {
  const response = await dashboardApi.getAll(filters)
  return response.data
} catch (error) {
  if (axios.isAxiosError(error)) {
    // Handle API error
    throw new Error(error.response?.data?.message)
  }
  throw error
}
```

## 🚀 Performance Optimizations

### 1. Code Splitting

```typescript
// Lazy load components
const Dashboard = lazy(() => import('./pages/Dashboard'))
```

### 2. Memoization

```typescript
// Memoize expensive calculations
const sortedData = useMemo(
  () => data.sort((a, b) => a.value - b.value),
  [data]
)
```

### 3. Virtualization

Untuk data table dengan banyak row:
```typescript
import { useVirtualizer } from '@tanstack/react-virtual'
```

## 🧪 Testing Strategy

```typescript
// Component testing
import { render, screen } from '@testing-library/react'

test('renders summary cards', () => {
  render(<SummaryCards data={mockData} />)
  expect(screen.getByText('Total Revenue')).toBeInTheDocument()
})

// Integration testing
test('filter updates dashboard', async () => {
  render(<Dashboard />)
  
  fireEvent.change(screen.getByLabelText('Period'), {
    target: { value: 'monthly' }
  })
  
  await waitFor(() => {
    expect(screen.getByText('Loading')).not.toBeInTheDocument()
  })
})
```

## 🔒 Security Considerations

1. **Environment Variables**: API keys di .env
2. **XSS Prevention**: React's default escaping
3. **CORS**: Konfigurasi di backend
4. **Authentication**: Via Laravel Sanctum tokens

## 📈 Scalability Patterns

### Feature Flags

```typescript
const features = {
  newCharts: import.meta.env.VITE_NEW_CHARTS === 'true',
}

{features.newCharts ? <NewChart /> : <OldChart />}
```

### Feature Modules

```
src/
├── features/
│   ├── dashboard/
│   │   ├── components/
│   │   ├── hooks/
│   │   └── api/
│   └── transactions/
│       ├── components/
│       ├── hooks/
│       └── api/
```
