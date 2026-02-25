# Transaction Dashboard - Frontend

## 📋 Overview

React + TypeScript frontend untuk Transaction Dashboard Application dengan visualisasi interaktif menggunakan Recharts dan UI components berbasis shadcn/ui.

## 🛠️ Tech Stack

- **Framework**: React 19
- **Language**: TypeScript
- **Build Tool**: Vite
- **UI Library**: shadcn/ui (Radix UI + Tailwind CSS)
- **Charts**: Recharts
- **State Management**: Tanstack Query (React Query)
- **HTTP Client**: Axios
- **Icons**: Lucide React

## 📁 Struktur Project

```
frontend/
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
├── public/
├── package.json
├── tsconfig.json
├── vite.config.ts
├── tailwind.config.js
├── Dockerfile
└── docker-compose.yml
```

## 🚀 Cara Install (Manual)

### Prerequisites

- Node.js 18+
- npm atau yarn

### Langkah Install

1. **Clone dan Install Dependencies**
```bash
cd frontend
npm install
```

2. **Install Additional Dependencies**
```bash
npm install axios @tanstack/react-query recharts lucide-react \
  tailwindcss @tailwindcss/vite class-variance-authority \
  clsx tailwind-merge @radix-ui/react-slot date-fns
```

3. **Setup Environment Variables**
Buat file `.env`:
```env
VITE_API_URL=http://localhost:8000/api/v1
```

4. **Jalankan Development Server**
```bash
npm run dev
```

Frontend akan tersedia di `http://localhost:5173`

## 🐳 Cara Install (Docker)

### Prerequisites

- Docker Desktop
- Docker Compose

### Langkah Install

1. **Jalankan Container**
```bash
docker-compose up -d
```

2. **Akses Frontend**
- Frontend: http://localhost:5173

## 🎨 Components

### FilterBar

Komponen untuk filtering data dashboard.

**Props:**
```typescript
interface FilterBarProps {
  filters: DashboardFilters
  onFilterChange: (filters: DashboardFilters) => void
}
```

**Fitur:**
- Date range picker (start date, end date)
- Period selector (daily, weekly, monthly)
- User type filter (all, domestic, foreign)
- Apply dan Reset buttons

### SummaryCards

Menampilkan 4 metrik utama:
- Total Revenue
- Total Transactions
- Total Quantity
- Average Price

### Charts

#### LineChartComponent
Visualisasi tren transaksi berdasarkan waktu.

**Props:**
```typescript
interface LineChartComponentProps {
  data: Trend[]
  title?: string
}
```

#### BarChartComponent
Visualisasi top buyers dan sellers.

**Props:**
```typescript
interface BarChartComponentProps {
  data: TopUser[]
  title?: string
  dataKey?: string
}
```

#### PieChartComponent
Visualisasi distribusi user type (domestic vs foreign).

**Props:**
```typescript
interface PieChartComponentProps {
  data: UserTypeDistribution[]
  title?: string
}
```

### DataTable

Tabel interaktif untuk menampilkan data transaksi.

**Fitur:**
- Sorting (ascending/descending)
- Pagination
- Responsive design

## 🔌 API Integration

### Setup API Client

```typescript
// src/lib/api.ts
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
})

export const dashboardApi = {
  getAll: (filters?: DashboardFilters) =>
    api.get('/dashboard', { params: filters }),
  // ...
}
```

### Using React Query

```typescript
import { useQuery } from '@tanstack/react-query'
import { dashboardApi } from '@/lib/api'

function Dashboard() {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard', filters],
    queryFn: () => dashboardApi.getAll(filters),
  })

  if (isLoading) return <Loading />

  return <DashboardContent data={data} />
}
```

## 🎯 Features

### Dashboard Visualizations

1. **Summary Cards**: 4 metrik utama
2. **Line Chart**: Tren transaksi (daily/weekly/monthly)
3. **Bar Chart**: Top buyers dan sellers
4. **Pie Chart**: Distribusi user type
5. **Data Table**: Tabel transaksi dengan sorting

### Filtering

- Global filter yang mempengaruhi semua komponen
- Date range filtering
- Period selection
- User type filtering

### Responsive Design

- Mobile-friendly layout
- Grid-based charts
- Collapsible sidebar

## 📦 Dependencies

```json
{
  "dependencies": {
    "react": "^19.0.0",
    "react-dom": "^19.0.0",
    "@tanstack/react-query": "^5.0.0",
    "axios": "^1.6.0",
    "recharts": "^2.10.0",
    "lucide-react": "^0.300.0",
    "date-fns": "^3.0.0",
    "clsx": "^2.1.0",
    "tailwind-merge": "^2.2.0"
  },
  "devDependencies": {
    "@types/react": "^19.0.0",
    "@types/react-dom": "^19.0.0",
    "@vitejs/plugin-react": "^4.2.0",
    "typescript": "^5.3.0",
    "vite": "^5.0.0",
    "tailwindcss": "^4.0.0"
  }
}
```

## 🔧 Configuration

### Vite Config

```typescript
// vite.config.ts
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
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})
```

### Tailwind CSS

Tailwind v4 menggunakan CSS-based configuration:

```css
/* src/index.css */
@import "tailwindcss";

@theme {
  --color-primary: #222.2 47.4% 11.2%;
}
```

## 📁 File Descriptions

| File | Description |
|------|-------------|
| `src/App.tsx` | Main application component |
| `src/main.tsx` | Entry point |
| `src/pages/Dashboard.tsx` | Dashboard page |
| `src/lib/api.ts` | API client |
| `src/lib/utils.ts` | Utility functions |
| `src/types/index.ts` | TypeScript types |
| `src/components/FilterBar.tsx` | Filter component |
| `src/components/SummaryCards.tsx` | Summary metrics |
| `src/components/charts/*.tsx` | Chart components |
| `src/components/DataTable.tsx` | Data table |

## 🚦 Troubleshooting

### Module not found errors
```bash
npm install
```

### Tailwind CSS not working
```bash
npm install -D tailwindcss @tailwindcss/vite
```

### TypeScript errors
```bash
npx tsc --noEmit
```

### Port already in use
```bash
# Change port in vite.config.ts
npm run dev -- --port 3000
```

## 📄 License

MIT License
