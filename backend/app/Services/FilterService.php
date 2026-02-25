<?php

namespace App\Services;

use App\Filters\DashboardFilter;
use Illuminate\Http\Request;

class FilterService
{
    protected DashboardFilter $dashboardFilter;

    public function __construct(DashboardFilter $dashboardFilter)
    {
        $this->dashboardFilter = $dashboardFilter;
    }

    /**
     * Extract and validate filter parameters from request
     * Uses DashboardFilter as single source of truth
     */
    public function getFilters(Request $request): array
    {
        return $this->dashboardFilter->filters($request);
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
        } catch (\Exception) {
            return false;
        }
    }
}
