<?php

namespace App\Filters;

use Illuminate\Http\Request;

/**
 * DashboardFilter - Single source of truth for dashboard filters
 * 
 * Responsibilities:
 * - Parse request query
 * - Validate filter values
 * - Apply conditions
 * 
 * Filters:
 * - start_date
 * - end_date
 * - period
 * - item_id
 * - user_type
 * - buyer_id
 * - seller_id
 * - search
 */
class DashboardFilter
{
    /**
     * Allowed period values
     */
    private const ALLOWED_PERIODS = ['daily', 'weekly', 'monthly'];

    /**
     * Allowed user types
     */
    private const ALLOWED_USER_TYPES = ['domestic', 'foreign'];

    /**
     * Parse and validate filters from request
     */
    public function filters(Request $request): array
    {
        $filters = [];

        // Date range filters
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if ($this->validateDateRange($startDate, $endDate)) {
                $filters['start_date'] = $startDate;
                $filters['end_date'] = $endDate;
            }
        }

        // Period filter
        if ($request->has('period')) {
            $period = $request->input('period');
            if ($this->validatePeriod($period)) {
                $filters['period'] = $period;
            }
        }

        // User type filter
        if ($request->has('user_type')) {
            $userType = $request->input('user_type');
            if ($this->validateUserType($userType)) {
                $filters['user_type'] = $userType;
            }
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

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            if (!empty($search) && strlen($search) >= 2) {
                $filters['search'] = $search;
            }
        }

        return $filters;
    }

    /**
     * Validate period value
     */
    private function validatePeriod(string $period): bool
    {
        return in_array($period, self::ALLOWED_PERIODS);
    }

    /**
     * Validate user type value
     */
    private function validateUserType(string $userType): bool
    {
        return in_array($userType, self::ALLOWED_USER_TYPES);
    }

    /**
     * Validate date range
     */
    private function validateDateRange(string $startDate, string $endDate): bool
    {
        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            return $start <= $end;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get default filters
     */
    public function getDefaults(): array
    {
        return [
            'period' => 'daily',
        ];
    }

    /**
     * Get allowed periods
     */
    public function getAllowedPeriods(): array
    {
        return self::ALLOWED_PERIODS;
    }

    /**
     * Get allowed user types
     */
    public function getAllowedUserTypes(): array
    {
        return self::ALLOWED_USER_TYPES;
    }
}
