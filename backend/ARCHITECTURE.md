# Architecture Documentation - Backend

## 📐 Architectural Overview

Transaction Dashboard Backend menggunakan **Clean Architecture** dengan prinsip separation of concerns untuk memastikan code dapat di-maintain dan di-scale dengan baik.

## 🏗️ Layer Structure

```
┌─────────────────────────────────────────────────┐
│              Controller Layer                    │
│         (HTTP Request/Response)                 │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│               Service Layer                     │
│         (Business Logic)                        │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│             Repository Layer                    │
│           (Data Access)                         │
└──────────────────────┬──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│              Model Layer                        │
│          (Eloquent ORM)                         │
└─────────────────────────────────────────────────┘
```

## 📦 Components Detail

### 1. Controller Layer (`app/Http/Controllers/Api/`)

**Tanggung Jawab:**
- Handle HTTP requests
- Validate input
- Return HTTP responses
- Delegate business logic ke service layer

**Prinsip:**
- Thin controllers (< 50 lines)
- Single responsibility
- No business logic

**Implementasi:**
```php
class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected FilterService $filterService;

    public function index(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $data = $this->analyticsService->getDashboardData($filters);
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
```

### 2. Service Layer (`app/Services/`)

**Tanggung Jawab:**
- Business logic
- Data transformation
- Caching strategy
- Cross-cutting concerns

**Implementasi:**
```php
class AnalyticsService
{
    protected TransactionRepository $transactionRepository;
    protected ItemRepository $itemRepository;
    protected UserRepository $userRepository;

    public function getDashboardData(array $filters = []): array
    {
        $cacheKey = 'analytics_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return [
                'summary' => $this->getSummary($filters),
                'trends' => $this->getTrends($filters),
                // ...
            ];
        });
    }
}
```

### 3. Repository Layer (`app/Repositories/`)

**Tanggung Jawab:**
- Data access abstraction
- Query building
- Database operations
- Complex aggregations

**Prinsip:**
- Single responsibility per repository
- Eager loading untuk optimize N+1 queries
- Reusable query methods

**Implementasi:**
```php
class TransactionRepository
{
    public function getQuery(array $filters = []): Builder
    {
        $query = Transaction::query()
            ->with(['buyer', 'seller', 'item']);

        if (isset($filters['start_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        return $query;
    }

    public function getTrends(array $filters = [], string $period = 'daily'): array
    {
        // Complex aggregation queries
    }
}
```

### 4. Model Layer (`app/Models/`)

**Tanggung Jawab:**
- Database table representation
- Relationships
- Data validation

**Implementasi:**
```php
class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'item_id',
        'quantity',
        'price',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
```

## 🔑 Design Decisions

### 1. UUID sebagai Primary Key

**Alasan:**
- Security: Tidak expose sequential IDs
- Distributed systems: Unique across systems
- Prevention: Avoid ID collision

**Implementasi:**
```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Model
{
    use HasUuids;
    
    // UUID akan auto-generated pada saat create
}
```

### 2. Repository Pattern

**Keuntungan:**
- Testability: Easy mocking
- Flexibility: Change data source tanpa mengubah business logic
- Organization: Clear separation of concerns

### 3. Service Layer Caching

**Strategi:**
```php
// Cache selama 5 menit
Cache::remember($cacheKey, 300, function () {
    return $this->heavyComputation();
});
```

**Invalidation:**
- Manual invalidation saat ada data baru
- Time-based expiration
- Tag-based untuk related data

### 4. API Versioning

**Struktur URL:**
```
/api/v1/dashboard
/api/v1/transactions
```

**Keuntungan:**
- Backward compatibility
- Graceful deprecation
- Feature flags

## 📊 Database Design

### Schema (3NF)

```
┌─────────────┐     ┌─────────────┐     ┌───────────────┐
│    users    │     │   items     │     │ transactions  │
├─────────────┤     ├─────────────┤     ├───────────────┤
│ id (UUID)   │◄────│ id (UUID)   │     │ id (UUID)    │
│ name        │     │ item_code   │     │ buyer_id (FK)│
│ email       │     │ item_name   │     │ seller_id(FK)│
│ type        │     │ min_price   │     │ item_id (FK) │
│ password    │     │ max_price   │     │ quantity     │
│ timestamps  │     │ timestamps  │     │ price        │
└─────────────┘     └─────────────┘     │ timestamps   │
                                         └───────────────┘
```

### Indexes

| Table | Column | Type | Purpose |
|-------|--------|------|---------|
| users | email | Unique | Login lookup |
| users | type | Index | User classification |
| items | item_code | Unique | Item lookup |
| transactions | buyer_id | Index | Buyer queries |
| transactions | seller_id | Index | Seller queries |
| transactions | item_id | Index | Item queries |
| transactions | created_at | Index | Date range queries |

## 🔒 Security Considerations

1. **SQL Injection**: Using parameterized queries via Eloquent ORM
2. **Mass Assignment**: Using `$fillable` and `$guarded`
3. **UUID**: Sequential ID prevention
4. **CORS**: Configured untuk frontend access

## 📈 Performance Optimizations

1. **Eager Loading**: Preload relationships
2. **Caching**: Reduce database hits
3. **Indexing**: Optimize query performance
4. **Pagination**: Limit result set
5. **Query Optimization**: Select only needed columns

## 🧪 Testing Strategy

```bash
# Unit Tests
php artisan test --filter=RepositoryTest

# Feature Tests
php artisan test --filter=ApiTest

# Coverage
php artisan test --coverage
```

## 🔄 CI/CD Pipeline

```yaml
# .github/workflows/laravel.yml
name: Laravel

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run tests
        run: |
          cp .env.example .env
          composer install
          php artisan migrate
          php artisan test
```
