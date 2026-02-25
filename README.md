# TableLink - Transaction Dashboard

Laravel 12 + React Analytics Dashboard for transaction analytics.

## Features

- 📊 **Dashboard Analytics** - KPI summary, trends, and visualizations
- 🔍 **Unified Filtering** - Filter across all analytics
- 📈 **Interactive Charts** - Line, Bar, and Pie charts using Recharts
- 🔄 **Real-time Updates** - Auto-refresh with caching
- 🌙 **Dark Mode** - Light/Dark theme support
- 📱 **Responsive Design** - Mobile-friendly UI

## Tech Stack

| Component | Technology            |
| --------- | --------------------- |
| Backend   | Laravel 12            |
| Frontend  | React 18 + TypeScript |
| Database  | MySQL 8.0             |
| Cache     | Database              |
| API Docs  | L5-Swagger            |
| Charts    | Recharts              |
| State     | Redux Toolkit         |
| Styling   | Tailwind CSS          |
| Docker    | Docker Compose        |

## Quick Start

### Prerequisites

- Docker
- Docker Compose

### Installation

1. **Clone the repository**

2. **Start the application**

   ```bash
   make build
   make up
   ```

3. **Seed the database (optional)**

   ```bash
   make fresh
   ```

4. **Access the application**
   - Frontend: http://localhost:8080
   - API: http://localhost:8080/api/v1

### Using Makefile

```bash
# Build images
make build

# Start containers
make up

# Stop containers
make down

# Run migrations
make migrate

# Seed database
make seed

# Fresh install (migrate + seed)
make fresh

# Enter backend shell
make shell

# View logs
make logs
```

## API Endpoints

### Dashboard Analytics (God Query)

```
GET /api/v1/dashboard/analytics
```

Query Parameters:

- `period` - daily, weekly, monthly (default: daily)
- `limit` - Number of items (default: 10)
- `start_date` - Start date (Y-m-d)
- `end_date` - End date (Y-m-d)
- `user_type` - domestic, foreign
- `include` - Comma-separated: summary, trends, trending_items, user_classification, top_buyers, top_sellers, revenue_contribution

### Transactions

```
GET /api/v1/transactions
GET /api/v1/transactions/{id}
```

### Authentication

```
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
POST /api/v1/auth/refresh
```

## Project Structure

```
.
├── backend/                 # Laravel 12 API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Models/
│   │   └── Filters/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
├── frontend/               # React 18 Frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── store/
│   │   └── lib/
│   └── public/
├── docs/                   # Documentation
├── docker-compose.yml
├── Makefile
└── nginx.conf
```

## Documentation

See the `/docs` folder for detailed documentation:

- [System Architecture](docs/SYSTEM_ARCHITECTURE.md)
- [Database Schema](docs/DATABASE_SCHEMA.md)
- [API Specification](docs/API_SPECIFICATION.md)
- [Backend Filtering](docs/BACKEND_FILTERING.md)
- [Data Classification](docs/DATA_CLASSIFICATION.md)
- [Analytics API Design](docs/ANALYTICS_API_DESIGN.md)
- [SQL Queries](docs/SQL_QUERIES.md)
- [Performance](docs/PERFORMANCE.md)
- [Frontend Dashboard](docs/FRONTEND_DASHBOARD.md)
- [Charts & Analytics](docs/CHARTS_AND_ANALYTICS.md)
- [Assumptions](docs/ASSUMPTIONS.md)
- [Limitations](docs/LIMITATIONS.md)

## Configuration

### Environment Variables

Backend `.env`:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tablelink_dashboard
DB_USERNAME=tablelink
DB_PASSWORD=tablelink_password
```

Frontend `.env`:

```
VITE_API_URL=http://localhost:8080/api/v1
```

## Development

### Running Tests

```bash
# Backend tests
docker exec tablelink_backend php artisan test

# Or use Makefile
make shell
php artisan test
```

### Clear Cache

```bash
docker exec tablelink_backend php artisan cache:clear
```

### View Logs

```bash
make logs
make logs-backend
make logs-mysql
```

## Docker Services

| Service  | Port | Description                  |
| -------- | ---- | ---------------------------- |
| nginx    | 8080 | Reverse proxy + static files |
| backend  | 9000 | PHP-FPM                      |
| mysql    | 3306 | MySQL database               |
| frontend | 80   | React build (proxied)        |

## License

MIT
