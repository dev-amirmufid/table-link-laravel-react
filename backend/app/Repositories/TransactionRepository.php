<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    public function getQuery(array $filters = []): Builder
    {
        $query = Transaction::query()
            ->with(['buyer', 'seller', 'item']);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        if (isset($filters['buyer_id'])) {
            $query->where('buyer_id', $filters['buyer_id']);
        }

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (isset($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        // Search by buyer name, seller name, or item name
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('buyer', function ($bq) use ($search) {
                    $bq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('seller', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('item', function ($iq) use ($search) {
                    $iq->where('item_name', 'like', "%{$search}%")
                       ->orWhere('item_code', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->getQuery($filters)->paginate($perPage);
    }

    public function getTotalRevenue(array $filters = []): int
    {
        return $this->getQuery($filters)
            ->sum(DB::raw('quantity * price'));
    }

    public function getTotalTransactions(array $filters = []): int
    {
        return $this->getQuery($filters)->count();
    }

    public function getTotalQuantity(array $filters = []): int
    {
        return $this->getQuery($filters)->sum('quantity');
    }

    public function getAveragePrice(array $filters = []): float
    {
        return $this->getQuery($filters)->avg('price') ?? 0;
    }

    public function getTrends(array $filters = [], string $period = 'daily'): array
    {
        $query = $this->getQuery($filters);

        $dateFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%W',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return $query
            ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"))
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(quantity * price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getTopBuyers(int $limit = 10, array $filters = []): array
    {
        return $this->getQuery($filters)
            ->select('buyer_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(quantity * price) as total_spent')
            ->with('buyer:id,name,email,type')
            ->groupBy('buyer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTopSellers(int $limit = 10, array $filters = []): array
    {
        return $this->getQuery($filters)
            ->select('seller_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(quantity * price) as total_earned')
            ->with('seller:id,name,email,type')
            ->groupBy('seller_id')
            ->orderByDesc('total_earned')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getUserTypeDistribution(array $filters = []): array
    {
        $query = Transaction::query()
            ->join('users as buyers', 'transactions.buyer_id', '=', 'buyers.id');

        // Apply date filters
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('transactions.created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        // Apply user_type filter on the joined users table
        if (isset($filters['user_type'])) {
            $query->where('buyers.type', $filters['user_type']);
        }

        // Apply item_id filter
        if (isset($filters['item_id'])) {
            $query->where('transactions.item_id', $filters['item_id']);
        }

        // Apply buyer_id filter
        if (isset($filters['buyer_id'])) {
            $query->where('transactions.buyer_id', $filters['buyer_id']);
        }

        // Apply seller_id filter
        if (isset($filters['seller_id'])) {
            $query->where('transactions.seller_id', $filters['seller_id']);
        }

        return $query
            ->select('buyers.type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(transactions.quantity * transactions.price) as revenue')
            ->groupBy('buyers.type')
            ->get()
            ->toArray();
    }

    /**
     * Get top items by revenue
     */
    public function getTopItemsByRevenue(int $limit = 10, array $filters = []): array
    {
        $query = Transaction::query()
            ->join('items', 'transactions.item_id', '=', 'items.id');

        // Apply date filters
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('transactions.created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        // Apply user_type filter via buyer
        if (isset($filters['user_type'])) {
            $query->join('users as buyers', 'transactions.buyer_id', '=', 'buyers.id')
                  ->where('buyers.type', $filters['user_type']);
        }

        return $query
            ->select('items.id')
            ->selectRaw('items.item_code as item_code')
            ->selectRaw('items.item_name as item_name')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(transactions.quantity) as total_quantity')
            ->selectRaw('SUM(transactions.quantity * transactions.price) as total_revenue')
            ->groupBy('items.id', 'items.item_code', 'items.item_name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get price distribution (histogram buckets)
     */
    public function getPriceDistribution(array $filters = []): array
    {
        $query = Transaction::query();

        // Apply date filters
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        // Get min and max price for dynamic buckets
        $minPrice = $query->min('price') ?? 0;
        $maxPrice = $query->max('price') ?? 1000000;

        // Create 5 equal buckets
        $bucketSize = max(1, ceil(($maxPrice - $minPrice) / 5));
        $buckets = [];
        
        for ($i = 0; $i < 5; $i++) {
            $start = $minPrice + ($i * $bucketSize);
            $end = $minPrice + (($i + 1) * $bucketSize);
            $buckets[] = [
                'min' => $start,
                'max' => $end,
                'count' => 0,
                'revenue' => 0,
            ];
        }

        // Get distribution
        $distribution = Transaction::query()
            ->selectRaw('price')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(quantity * price) as revenue')
            ->when(isset($filters['start_date']) && isset($filters['end_date']), function ($q) use ($filters) {
                return $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            })
            ->groupBy('price')
            ->get();

        // Assign to buckets
        foreach ($distribution as $item) {
            $bucketIndex = min(4, floor(($item->price - $minPrice) / max(1, $bucketSize)));
            $bucketIndex = max(0, $bucketIndex);
            if (isset($buckets[$bucketIndex])) {
                $buckets[$bucketIndex]['count'] += $item->count;
                $buckets[$bucketIndex]['revenue'] += $item->revenue;
            }
        }

        // Format bucket labels
        return array_map(function ($bucket, $index) use ($bucketSize, $minPrice) {
            $start = $minPrice + ($index * $bucketSize);
            $end = $minPrice + (($index + 1) * $bucketSize);
            return [
                'range' => 'Rp ' . number_format($start, 0, ',', '.') . ' - Rp ' . number_format($end, 0, ',', '.'),
                'count' => $bucket['count'],
                'revenue' => $bucket['revenue'],
            ];
        }, $buckets, array_keys($buckets));
    }
}
