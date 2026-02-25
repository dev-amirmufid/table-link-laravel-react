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
        $query = $this->getQuery($filters);

        return $query
            ->join('users as buyers', 'transactions.buyer_id', '=', 'buyers.id')
            ->select('buyers.type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(transactions.quantity * transactions.price) as revenue')
            ->groupBy('buyers.type')
            ->get()
            ->toArray();
    }
}
