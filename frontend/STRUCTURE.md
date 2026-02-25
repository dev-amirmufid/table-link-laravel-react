# Structure Documentation - Frontend

## 📂 Directory Structure

```
frontend/
├── public/
│   └── vite.svg
├── src/
│   ├── components/
│   │   ├── ui/
│   │   │   └── card.tsx
│   │   ├── charts/
│   │   │   ├── LineChartComponent.tsx
│   │   │   ├── BarChartComponent.tsx
│   │   │   └── PieChartComponent.tsx
│   │   ├── FilterBar.tsx
│   │   ├── SummaryCards.tsx
│   │   └── DataTable.tsx
│   ├── lib/
│   │   ├── api.ts
│   │   └── utils.ts
│   ├── pages/
│   │   └── Dashboard.tsx
│   ├── types/
│   │   └── index.ts
│   ├── App.tsx
│   ├── main.tsx
│   └── index.css
├── index.html
├── package.json
├── tsconfig.json
├── tsconfig.app.json
├── tsconfig.node.json
├── vite.config.ts
├── Dockerfile
└── README.md
```

## 📄 File Descriptions

### Root Files

| File | Description |
|------|-------------|
| `index.html` | HTML entry point |
| `package.json` | Dependencies dan scripts |
| `tsconfig.json` | TypeScript configuration |
| `tsconfig.app.json` | App-specific TypeScript config |
| `tsconfig.node.json` | Node-specific TypeScript config |
| `vite.config.ts` | Vite build configuration |
| `Dockerfile` | Docker container configuration |

### Source Files (`src/`)

| File/Directory | Description |
|----------------|-------------|
| `main.tsx` | React application entry point |
| `App.tsx` | Main application component |
| `index.css` | Global styles dan Tailwind directives |

### Components (`src/components/`)

```
src/components/
├── ui/                    # Base UI components
│   └── card.tsx          # Card component (shadcn/ui)
├── charts/               # Chart components
│   ├── LineChartComponent.tsx
│   ├── BarChartComponent.tsx
│   └── PieChartComponent.tsx
├── FilterBar.tsx        # Filter component
├── SummaryCards.tsx      # Summary metrics cards
└── DataTable.tsx        # Data table component
```

### Library Files (`src/lib/`)

```
src/lib/
├── api.ts               # API client configuration
└── utils.ts             # Utility functions
```

#### api.ts

```typescript
// API client setup
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
})

// API endpoints
export const dashboardApi = {
  getAll: (filters?: DashboardFilters) => ...
  getSummary: (filters?: DashboardFilters) => ...
  // ...
}

export const transactionApi = {
  getAll: (filters?: DashboardFilters, perPage?: number) => ...
  getById: (id: string) => ...
}
```

#### utils.ts

```typescript
// Class name merger (shadcn/ui pattern)
export function cn(...inputs: ClassValue[]): string

// Currency formatter (IDR)
export function formatCurrency(amount: number): string

// Number formatter
export function formatNumber(num: number): string
```

### Pages (`src/pages/`)

```
src/pages/
└── Dashboard.tsx       # Main dashboard page
```

### Types (`src/types/`)

```
src/types/
└── index.ts           # TypeScript interfaces
```

#### Type Definitions

```typescript
// User types
export interface User {
  id: string
  name: string
  email: string
  type: 'foreign' | 'domestic'
}

// Item types
export interface Item {
  id: string
  item_code: string
  item_name: string
  minimum_price: number
  maximum_price: number
}

// Transaction types
export interface Transaction {
  id: string
  buyer_id: string
  seller_id: string
  item_id: string
  quantity: number
  price: number
  created_at: string
  buyer?: User
  seller?: User
  item?: Item
}

// Dashboard types
export interface Summary {
  total_revenue: number
  total_transactions: number
  total_quantity: number
  average_price: number
}

export interface Trend {
  date: string
  count: number
  revenue: number
}

export interface DashboardData {
  summary: Summary
  trends: Trend[]
  trending_items: TrendingItem[]
  top_buyers: TopUser[]
  top_sellers: TopUser[]
  user_type_distribution: UserTypeDistribution[]
  user_classification: UserTypeDistribution[]
}

export interface DashboardFilters {
  start_date?: string
  end_date?: string
  period?: 'daily' | 'weekly' | 'monthly'
  user_type?: string
  item_id?: string
  buyer_id?: string
  seller_id?: string
}
```

## 🎯 Component Hierarchy

```
App
└── QueryClientProvider
    └── Dashboard
        ├── FilterBar
        ├── SummaryCards
        │   └── Card (ui)
        ├── Grid Layout
        │   ├── Card
        │   │   └── LineChartComponent
        │   ├── Card
        │   │   └── BarChartComponent (Top Buyers)
        │   ├── Card
        │   │   └── BarChartComponent (Top Sellers)
        │   └── Card
        │       └── PieChartComponent
        └── Card
            └── DataTable
```

## 📦 Dependencies

### Production Dependencies

```json
{
  "dependencies": {
    "react": "^19.0.0",
    "react-dom": "^19.0.0",
    "@tanstack/react-query": "^5.59.0",
    "axios": "^1.7.7",
    "recharts": "^2.12.7",
    "lucide-react": "^0.454.0",
    "date-fns": "^4.1.0",
    "clsx": "^2.1.1",
    "tailwind-merge": "^2.5.4",
    "class-variance-authority": "^0.7.0",
    "@radix-ui/react-slot": "^1.1.0"
  }
}
```

### Development Dependencies

```json
{
  "devDependencies": {
    "@types/react": "^19.0.0",
    "@types/react-dom": "^19.0.0",
    "@vitejs/plugin-react": "^4.3.3",
    "typescript": "~5.6.2",
    "vite": "^5.4.10",
    "tailwindcss": "^4.0.0",
    "@tailwindcss/vite": "^4.0.0"
  }
}
```

## 🔧 Configuration Files

### tsconfig.app.json

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["ES2022", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["src"]
}
```

### vite.config.ts

```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
})
```

## 🚀 NPM Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc -b && vite build",
    "lint": "eslint .",
    "preview": "vite preview"
  }
}
```

## 🎨 CSS Structure

### index.css

```css
@import "tailwindcss";

:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  /* ... color variables */
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  /* ... dark mode colors */
}

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}
```

## 📁 Environment Variables

```env
# API URL
VITE_API_URL=http://localhost:8080/api/v1
```

## 🧪 Testing Files

```
frontend/
├── src/
│   └── __tests__/
│       ├── Dashboard.test.tsx
│       ├── FilterBar.test.tsx
│       └── api.test.ts
└── vitest.config.ts
```

## 🔄 API Integration

### Endpoint Mapping

```
GET /api/v1/dashboard                 → dashboardApi.getAll()
GET /api/v1/dashboard/summary         → dashboardApi.getSummary()
GET /api/v1/dashboard/trends          → dashboardApi.getTrends()
GET /api/v1/dashboard/trending-items  → dashboardApi.getTrendingItems()
GET /api/v1/dashboard/top-buyers      → dashboardApi.getTopBuyers()
GET /api/v1/dashboard/top-sellers     → dashboardApi.getTopSellers()
GET /api/v1/transactions               → transactionApi.getAll()
```

## 📱 Responsive Breakpoints

Tailwind CSS breakpoints:
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px

Example usage:
```typescript
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  {/* Responsive grid */}
</div>
```
