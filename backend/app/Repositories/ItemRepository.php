<?php

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;

class ItemRepository
{
    public function getQuery(array $filters = []): Builder
    {
        $query = Item::query();

        if (isset($filters['item_name'])) {
            $query->where('item_name', 'like', '%' . $filters['item_name'] . '%');
        }

        if (isset($filters['item_code'])) {
            $query->where('item_code', 'like', '%' . $filters['item_code'] . '%');
        }

        return $query;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->getQuery($filters)->paginate($perPage);
    }

    public function getTrending(int $limit = 10, array $filters = []): array
    {
        return Item::query()
            ->withCount(['transactions' => function ($query) use ($filters) {
                if (isset($filters['start_date']) && isset($filters['end_date'])) {
                    $query->whereBetween('created_at', [
                        $filters['start_date'],
                        $filters['end_date']
                    ]);
                }
            }])
            ->with(['transactions' => function ($query) use ($filters) {
                if (isset($filters['start_date']) && isset($filters['end_date'])) {
                    $query->whereBetween('created_at', [
                        $filters['start_date'],
                        $filters['end_date']
                    ]);
                }
            }])
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getById(string $id): ?Item
    {
        return Item::find($id);
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function update(Item $item, array $data): Item
    {
        $item->update($data);
        return $item;
    }

    public function delete(Item $item): bool
    {
        return $item->delete();
    }
}
