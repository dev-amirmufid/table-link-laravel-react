# Transaction Dashboard Application

A comprehensive Laravel + React application for analyzing transaction data with interactive dashboards and analytics.

## Features

- **Dashboard Analytics**: Real-time transaction data visualization
- **Charts**: Line, Bar, and Pie charts for data representation
- **Filtering**: Global filter system with date range, period, and user type filters
- **CSV Export**: Export data to CSV format
- **Dark Mode**: Toggle between light and dark themes
- **Skeleton Loading**: Loading states for better UX
- **API Caching**: Laravel cache for improved performance
- **Swagger Documentation**: API documentation at `/api/docs`

## Tech Stack

### Backend
- Laravel 12
- MySQL Database
- JWT Authentication (for admin)
- OpenAPI/Swagger Documentation

### Frontend
- React 19 + TypeScript
- Vite
- TailwindCSS
- Recharts
- Tanstack Query

## Getting Started

### Prerequisites
- PHP 8.2+
- Node.js 18+
- Composer
- MySQL 8.0+

### Installation

1. **Backend Setup**
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

2. **Frontend Setup**
```bash
cd frontend
npm install
npm run dev
```

### Running with Docker

```bash
docker-compose up -d
```

## Default Admin Credentials

- Email: `admin@tablelink.com`
- Password: `admin123`

## API Documentation

Access Swagger UI at: `http://localhost:8000/api/docs`

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/dashboard` | Get all dashboard data |
| GET | `/api/v1/dashboard/summary` | Get summary metrics |
| GET | `/api/v1/dashboard/trends` | Get transaction trends |
| GET | `/api/v1/dashboard/trending-items` | Get trending items |
| GET | `/api/v1/dashboard/top-buyers` | Get top buyers |
| GET | `/api/v1/dashboard/top-sellers` | Get top sellers |
| GET | `/api/v1/dashboard/user-type-distribution` | Get user type distribution |
| GET | `/api/v1/dashboard/user-classification` | Get user classification |
| GET | `/api/v1/transactions` | Get all transactions |
| POST | `/api/auth/login` | Admin login |

## Project Structure

```
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   └── Services/
│   ├── config/
│   └── database/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── contexts/
│   │   ├── lib/
│   │   └── pages/
│   └── package.json
└── docker-compose.yml
```

## Filtering

The dashboard supports the following filter parameters:
- `start_date`: Start date (Y-m-d)
- `end_date`: End date (Y-m-d)
- `period`: daily, weekly, or monthly
- `user_type`: domestic or foreign
- `item_id`: Filter by specific item

Example:
```
GET /api/v1/dashboard?start_date=2024-01-01&end_date=2024-12-31&period=monthly
```

## License

MIT
