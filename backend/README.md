# Transaction Dashboard - Backend

## 📋 Overview

Laravel REST API untuk Transaction Dashboard Application yang menyediakan endpoint analytics untuk menganalisis hubungan transaksi antara pengguna dan item.

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Database**: MySQL 8.0
- **PHP Version**: 8.2+
- **Authentication**: Laravel Sanctum

## 📁 Struktur Project

```
backend/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── DashboardController.php
│   │           └── TransactionController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Item.php
│   │   └── Transaction.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── ItemRepository.php
│   │   └── TransactionRepository.php
│   └── Services/
│       ├── AnalyticsService.php
│       └── FilterService.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php
├── Dockerfile
└── docker-compose.yml
```

## 🚀 Cara Install (Manual)

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0
- Node.js 18+

### Langkah Install

1. **Clone dan Setup**
```bash
cd backend
composer install
cp .env.example .env
```

2. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tablelink_dashboard
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. **Generate Application Key**
```bash
php artisan key:generate
```

4. **Jalankan Migration**
```bash
php artisan migrate
```

5. **Seed Data Dummy**
```bash
php artisan db:seed
```

6. **Jalankan Server**
```bash
php artisan serve
```

API akan tersedia di `http://localhost:8000`

## 🐳 Cara Install (Docker)

### Prerequisites

- Docker Desktop
- Docker Compose

### Langkah Install

1. **Jalankan Container**
```bash
docker-compose up -d
```

2. **Setup Database**
```bash
# Masuk ke container backend
docker-compose exec backend bash

# Jalankan migration
php artisan migrate

# Seed data
php artisan db:seed

# Keluar dari container
exit
```

3. **Akses API**
- Backend API: http://localhost:8000
- MySQL: localhost:3306

## 📡 API Endpoints

### Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/dashboard` | All dashboard data |
| GET | `/api/v1/dashboard/summary` | Summary metrics |
| GET | `/api/v1/dashboard/trends` | Transaction trends |
| GET | `/api/v1/dashboard/trending-items` | Trending items |
| GET | `/api/v1/dashboard/top-buyers` | Top buyers |
| GET | `/api/v1/dashboard/top-sellers` | Top sellers |
| GET | `/api/v1/dashboard/user-type-distribution` | User type distribution |
| GET | `/api/v1/dashboard/user-classification` | User classification |

### Transactions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/transactions` | List all transactions |
| GET | `/api/v1/transactions/{id}` | Get single transaction |

### Filter Parameters

Semua endpoint dashboard mendukung parameter filter:

| Parameter | Type | Description |
|-----------|------|-------------|
| `start_date` | string | Start date (Y-m-d H:i:s) |
| `end_date` | string | End date (Y-m-d H:i:s) |
| `period` | string | daily, weekly, monthly |
| `user_type` | string | domestic, foreign |
| `item_id` | string | Filter by item UUID |
| `buyer_id` | string | Filter by buyer UUID |
| `seller_id` | string | Filter by seller UUID |

### Example Request

```bash
# Get dashboard data with date range
curl "http://localhost:8000/api/v1/dashboard?start_date=2024-01-01&end_date=2024-12-31&period=monthly"

# Get trends
curl "http://localhost:8000/api/v1/dashboard/trends?period=weekly"
```

## 🏗️ Architecture

### Clean Architecture Layers

1. **Controller Layer** (`app/Http/Controllers/Api/`)
   - Thin controllers
   - Delegate logic ke service layer
   - Handle HTTP requests/responses

2. **Service Layer** (`app/Services/`)
   - Business logic
   - Data transformation
   - Caching

3. **Repository Layer** (`app/Repositories/`)
   - Data access abstraction
   - Query building
   - Database operations

### Database Schema

- **users**: id (UUID), name, email, type (enum: foreign/domestic)
- **items**: id (UUID), item_code, item_name, minimum_price, maximum_price
- **transactions**: id (UUID), buyer_id, seller_id, item_id, quantity, price

### Indexes

- `users`: email, type
- `items`: item_code
- `transactions`: buyer_id, seller_id, item_id, created_at

## 🔧 Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### MySQL Connection Issues
Pastikan MySQL service berjalan dan credentials di `.env` benar.

## 📄 License

MIT License
