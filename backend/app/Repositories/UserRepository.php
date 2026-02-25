<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function getQuery(array $filters = []): Builder
    {
        $query = User::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->getQuery($filters)->paginate($perPage);
    }

    public function getClassification(array $filters = []): array
    {
        return User::query()
            ->select('type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->toArray();
    }

    public function getById(string $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
