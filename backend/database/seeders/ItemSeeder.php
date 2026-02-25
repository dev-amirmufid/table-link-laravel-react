<?php

namespace Database\Seeders;

use App\Helpers\PriceHelper;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $items = [];

        $totalItems = 300;
        $perBatch = 100;

        foreach (range(1, $totalItems) as $_) {
            $minimumPrice = PriceHelper::generatePrice($faker);
            $maximumPrice = PriceHelper::generatePrice($faker, $minimumPrice, 10000);

            $items[] = [
                'id' => Str::uuid(),
                'item_code' => $faker->unique()->regexify('[A-Z]{4}'),
                'item_name' => $faker->word(),
                'minimum_price' => $minimumPrice,
                'maximum_price' => $maximumPrice,
                'created_at' => $faker->dateTimeBetween('-3 year', '-1 year'),
            ];
        }

        foreach (array_chunk($items, $perBatch) as $chunk) {
            Item::insert($chunk);
        }
    }
}

