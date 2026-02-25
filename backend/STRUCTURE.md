# Structure Documentation - Backend

## 📂 Directory Structure

```
backend/
├── app/
│   ├── Console/
│   │   └── Kernel.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   └── Api/
│   │   │       ├── DashboardController.php
│   │   │       └── TransactionController.php
│   │   ├── Kernel.php
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Item.php
│   │   └── Transaction.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── ItemRepository.php
│   │   └── TransactionRepository.php
│   ├── Services/
│   │   ├── AnalyticsService.php
│   │   └── FilterService.php
│   └── View/
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── database.php
│   ├── cache.php
│   └── logging.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_items_table.php
│   │   └── 2024_01_01_000003_create_transactions_table.php
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── ItemFactory.php
│   │   └── TransactionFactory.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   ├── index.php
│   └── .htaccess
├── resources/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env
├── .env.example
├── artisan
├── composer.json
├── Dockerfile
├── docker-compose.yml
├── phpunit.xml
└── README.md
```

## 📄 File Descriptions

### Core Application Files

| File | Description |
|------|-------------|
| `artisan` | Laravel CLI entry point |
| `composer.json` | PHP dependencies |
| `phpunit.xml` | PHPUnit configuration |
| `Dockerfile` | Docker container configuration |
| `docker-compose.yml` | Docker compose configuration |

### Application Layer (`app/`)

| File/Directory | Description |
|----------------|-------------|
| `Http/Controllers/Api/` | API Controllers |
| `Models/` | Eloquent Models |
| `Repositories/` | Data access layer |
| `Services/` | Business logic layer |

### Database Layer (`database/`)

| File/Directory | Description |
|----------------|-------------|
| `migrations/` | Database schema |
| `factories/` | Test data factories |
| `seeders/` | Seed data generators |

## 🔗 Route Structure

```
/api/v1
├── /dashboard
│   ├── GET / (index)
│   ├── GET /summary
│   ├── GET /trends
│   ├── GET /trending-items
│   ├── GET /top-buyers
│   ├── GET /top-sellers
│   ├── GET /user-type-distribution
│   └── GET /user-classification
└── /transactions
    ├── GET /
    └── GET /{id}
```

## 🗄️ Database Tables

### users

| Column | Type | Constraints |
|--------|------|-------------|
| id | UUID | PK |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| email_verified_at | TIMESTAMP | NULLABLE |
| password | VARCHAR(255) | NOT NULL |
| type | ENUM | 'foreign', 'domestic' |
| remember_token | VARCHAR(100) | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### items

| Column | Type | Constraints |
|--------|------|-------------|
| id | UUID | PK |
| item_code | VARCHAR(255) | UNIQUE, NOT NULL |
| item_name | VARCHAR(255) | NOT NULL |
| minimum_price | INTEGER | NOT NULL |
| maximum_price | INTEGER | NOT NULL |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### transactions

| Column | Type | Constraints |
|--------|------|-------------|
| id | UUID | PK |
| buyer_id | UUID | FK → users.id |
| seller_id | UUID | FK → users.id |
| item_id | UUID | FK → items.id |
| quantity | INTEGER | NOT NULL |
| price | INTEGER | NOT NULL |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

## 🔧 Configuration Files

### `.env` Example

```env
APP_NAME=TransactionDashboard
APP_ENV=local
APP_KEY=base64:xxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tablelink_dashboard
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
```

## 📦 Dependencies

### Production

```json
{
  "laravel/framework": "^12.0",
  "laravel/sanctum": "^4.0",
  "ramsey/uuid": "^4.9"
}
```

### Development

```json
{
  "phpunit/phpunit": "^11.0",
  "fakerphp/faker": "^1.24"
}
```

## 🧪 Testing Structure

```
tests/
├── Feature/
│   ├── DashboardApiTest.php
│   └── TransactionApiTest.php
└── Unit/
    ├── RepositoryTest.php
    └── ServiceTest.php
```

## 📝 Artisan Commands

```bash
# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Development
php artisan serve
php artisan test
php artisan route:list
```
