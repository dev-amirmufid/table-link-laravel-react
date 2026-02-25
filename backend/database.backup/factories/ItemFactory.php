<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_code' => strtoupper(fake()->unique()->bothify('ITEM-####')),
            'item_name' => fake()->words(3, true),
            'minimum_price' => fake()->numberBetween(10000, 50000),
            'maximum_price' => fake()->numberBetween(50000, 200000),
        ];
    }
}
