<?php

namespace Database\Seeders;

use App\Helpers\PriceHelper;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M');

        // Disable query logging to save memory
        DB::connection()->disableQueryLog();

        $faker = Faker::create('id_ID');
        $userIds = User::all()->pluck('id')->toArray();
        $items = Item::select('id', 'minimum_price', 'maximum_price')->get()->toArray();

        $totalTransactions = 300_000;
        $perBatch = 2_000;

        $transactions = [];

        for ($index = 1; $index <= $totalTransactions; $index++) {
            $buyerId = $faker->randomElement($userIds);

            do {
                $sellerId = $faker->randomElement($userIds);
            } while ($sellerId === $buyerId && count($userIds) > 1);

            $item = $faker->randomElement($items);
            $quantity = $faker->numberBetween(1, 10_000);
            $price = PriceHelper::generatePrice($faker, $item['minimum_price'], $item['maximum_price']);

            $transactions[] = [
                'id' => Str::uuid(),
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'item_id' => $item['id'],
                'quantity' => $quantity,
                'price' => $price,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ];

            if ($index % $perBatch === 0) {
                Transaction::insert($transactions);
                $transactions = [];

                if ($index % 5_000 === 0) {
                    $this->command->info("Inserted {$index} transactions...");
                    gc_collect_cycles();
                }
            }
        }

        if (count($transactions) > 0) {
            Transaction::insert($transactions);
        }

        $this->command->info("Successfully seeded {$totalTransactions} transactions!");
    }
}
