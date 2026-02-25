<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'type' => fake()->randomElement(['foreign', 'domestic']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is foreign.
     */
    public function foreign(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'foreign',
        ]);
    }

    /**
     * Indicate that the user is domestic.
     */
    public function domestic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'domestic',
        ]);
    }
}
