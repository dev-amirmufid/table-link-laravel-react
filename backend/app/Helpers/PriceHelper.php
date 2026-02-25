<?php

namespace App\Helpers;

use Faker\Generator as Faker;

class PriceHelper
{
    /**
     * Generate a price following the step rules based on price range.
     *
     * Step rules:
     * - 50-200: step by 1
     * - 200-500: step by 2
     * - 500-2000: step by 5
     * - 2000-5000: step by 10
     * - 5000-10000: step by 25
     *
     * @param Faker $faker The Faker instance
     * @param int $min Minimum price (default: 50)
     * @param int $max Maximum price (default: 10000)
     * @return int The generated price following step rules
     */
    public static function generatePrice(Faker $faker, int $min = 50, int $max = 10000): int
    {
        $price = $faker->numberBetween($min, $max);

        // Apply step rules based on price range
        if ($price >= 50 && $price < 200) {
            // 50-200: step by 1 (no rounding needed)
            return $price;
        } elseif ($price >= 200 && $price < 500) {
            // 200-500: step by 2
            return round($price / 2) * 2;
        } elseif ($price >= 500 && $price < 2000) {
            // 500-2000: step by 5
            return round($price / 5) * 5;
        } elseif ($price >= 2000 && $price < 5000) {
            // 2000-5000: step by 10
            return round($price / 10) * 10;
        } else {
            // 5000-10000: step by 25
            return round($price / 25) * 25;
        }
    }

    /**
     * Apply step rounding to an existing price value.
     *
     * @param int|float $price The price to round
     * @return int The rounded price following step rules
     */
    public static function applyStepRounding($price): int
    {
        $price = (int) $price;

        if ($price >= 50 && $price < 200) {
            return $price;
        } elseif ($price >= 200 && $price < 500) {
            return round($price / 2) * 2;
        } elseif ($price >= 500 && $price < 2000) {
            return round($price / 5) * 5;
        } elseif ($price >= 2000 && $price < 5000) {
            return round($price / 10) * 10;
        } else {
            return round($price / 25) * 25;
        }
    }
}
