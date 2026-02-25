<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        User::factory()
            ->count(100)
            ->create();

        // Create items
        Item::factory()
            ->count(50)
            ->create();

        // Create transactions
        Transaction::factory()
            ->count(1000)
            ->create();
    }
}
