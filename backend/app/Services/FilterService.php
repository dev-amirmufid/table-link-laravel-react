<?php

namespace App\Services;

use Illuminate\Http\Request;

class FilterService
{
    /**
     * Extract and validate filter parameters from request
     */
    public function getFilters(Request $request): array
    {
        $filters = [];

        // Date range filters
        if ($request->has('start_date') && $request->has('end_date')) {
            $filters['start_date'] = $request->input('start_date');
            $filters['end_date'] = $request->input('end_date');
        }

        // Period filter (daily, weekly, monthly)
        if ($request->has('period')) {
            $filters['period'] = $request->input('period');
        }

        // User type filter
        if ($request->has('user_type')) {
            $filters['user_type'] = $request->input('user_type');
        }

        // Item ID filter
        if ($request->has('item_id')) {
            $filters['item_id'] = $request->input('item_id');
        }

        // Buyer ID filter
        if ($request->has('buyer_id')) {
            $filters['buyer_id'] = $request->input('buyer_id');
        }

        // Seller ID filter
        if ($request->has('seller_id')) {
            $filters['seller_id'] = $request->input('seller_id');
        }

        return $filters;
    }

    /**
     * Get default date range (last 30 days)
     */
    public function getDefaultDateRange(): array
    {
        return [
            'start_date' => now()->subDays(30)->startOfDay()->toDateTimeString(),
            'end_date' => now()->endOfDay()->toDateTimeString(),
        ];
    }

    /**
     * Validate date format
     */
    public function validateDateRange(string $startDate, string $endDate): bool
    {
        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            return $start <= $end;
        } catch (\Exception $e) {
            return false;
        }
    }
}
