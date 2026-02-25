<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $emailVerifiedAt = now();
        $hashedPassword = Hash::make('password');

        $totalUsers = 20_000;
        $perBatch = 1_000;

        $users = [];

        foreach (range(1, $totalUsers) as $index) {
            $name = $faker->name;
            $users[] = [
                'id' => Str::uuid(),
                'name' => $name,
                'email' => Str::snake($name) . "_" . $index . "@example.com",
                'password' => $hashedPassword,
                'email_verified_at' => $emailVerifiedAt,
                'type' => $faker->randomElement(['foreign', 'domestic']),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($users, $perBatch) as $chunk) {
            User::insert($chunk);
        }
    }
}
