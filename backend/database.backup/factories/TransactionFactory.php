<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userIds = User::pluck('id')->toArray();
        $itemIds = Item::pluck('id')->toArray();

        if (empty($userIds) || empty($itemIds)) {
            return [
                'buyer_id' => null,
                'seller_id' => null,
                'item_id' => null,
                'quantity' => fake()->numberBetween(1, 10),
                'price' => fake()->numberBetween(10000, 150000),
                'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            ];
        }

        $buyerId = fake()->randomElement($userIds);
        $sellerId = fake()->randomElement(array_filter($userIds, fn ($id) => $id !== $buyerId));
        $itemId = fake()->randomElement($itemIds);

        return [
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'item_id' => $itemId,
            'quantity' => fake()->numberBetween(1, 10),
            'price' => fake()->numberBetween(10000, 150000),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
