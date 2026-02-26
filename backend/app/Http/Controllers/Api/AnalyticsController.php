<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Transaction Dashboard API",
    version: "1.0",
    description: "API Documentation for Transaction Dashboard"
)]
#[OA\PathItem(path: "/")]
class AnalyticsController extends Controller
{
    protected FilterService $filterService;

    // Cache TTL in seconds (5 minutes)
    protected const CACHE_TTL = 300;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Generate cache key based on filters
     */
    private function generateCacheKey(string $prefix, array $filters, array $additional = []): string
    {
        $filterKey = json_encode($filters);
        $additionalKey = json_encode($additional);
        return "{$prefix}:" . md5($filterKey . $additionalKey);
    }

    /**
     * POST /api/dashboard/cache/clear
     * Clear dashboard cache
     */
    #[OA\Post(
        path: "/dashboard/cache/clear",
        tags: ["Dashboard"],
        summary: "Clear dashboard cache",
        description: "Clears all cached dashboard data",
        responses: [
            new OA\Response(response: 200, description: "Cache cleared successfully")
        ]
    )]
    public function clearCache(): JsonResponse
    {
        Cache::forget('dashboard.summary');
        Cache::forget('dashboard.trends');
        Cache::forget('items.trending');
        Cache::forget('users.classification');
        Cache::forget('relations');
        Cache::forget('revenue.contribution');
        Cache::forget('dashboard.analytics');

        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache cleared successfully',
        ]);
    }

    /**
     * GET /api/dashboard/analytics
     * ABSOLUTE GOD QUERY - Single endpoint for all dashboard data
     * Returns summary, trends, trending items, user classification, top buyers, and top sellers in ONE request
     */
    #[OA\Get(
        path: "/dashboard/analytics",
        tags: ["Dashboard"],
        summary: "Dashboard Analytics - God Query",
        description: "Returns all dashboard data in a single request using CTE. Supports optional 'include' parameter to fetch specific data sections.",
        parameters: [
            new OA\Parameter(name: "include", in: "query", description: "Comma-separated list of data to include: summary,trends,trending_items,user_classification,top_buyers,top_sellers. Default: all", schema: new OA\Schema(type: "string", example: "summary,trends,trending_items")),
            new OA\Parameter(name: "period", in: "query", description: "Trends period: daily, weekly, monthly (default: daily)", schema: new OA\Schema(type: "string", enum: ["daily", "weekly", "monthly"], default: "daily")),
            new OA\Parameter(name: "limit", in: "query", description: "Limit for trending items and top users (default: 10)", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function analytics(Request $request): JsonResponse
    {
        try {
            $filters = $this->filterService->getFilters($request);
            $include = $request->input('include', 'summary,trends,trending_items,user_classification,top_buyers,top_sellers,revenue_contribution');
            $period = $request->input('period', 'daily');
            $limit = $request->input('limit', 10);

            // Parse include parameter
            $includeArray = array_map('trim', explode(',', strtolower($include)));

            // Validate period
            if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
                $period = 'daily';
            }

            // Build cache key based on all parameters
            $cacheKey = $this->generateCacheKey('dashboard.analytics', $filters, [
                'include' => $include,
                'period' => $period,
                'limit' => $limit,
            ]);

            $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $includeArray, $period, $limit) {
            // Prepare filter conditions for raw SQL
            $whereConditions = [];
            $joinConditions = [];

            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $whereConditions[] = "t.created_at BETWEEN '{$filters['start_date']}' AND '{$filters['end_date']}'";
            }

            if (isset($filters['user_type'])) {
                $whereConditions[] = "buyer.type = '{$filters['user_type']}'";
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            $joinClause = !empty($joinConditions) ? ' ' . implode(' ', $joinConditions) : '';

            // Determine date format based on period
            $dateFormat = match ($period) {
                'daily' => 'DATE(t.created_at)',
                'weekly' => "DATE_FORMAT(t.created_at, '%Y-%u')",
                'monthly' => "DATE_FORMAT(t.created_at, '%Y-%m')",
                default => 'DATE(t.created_at)',
            };

            $result = [];

            // 1. SUMMARY - Always include for KPI cards
            if (in_array('summary', $includeArray)) {
                $summaryQuery = "
                    SELECT
                        COUNT(*) as total_transactions,
                        COALESCE(SUM(t.quantity), 0) as total_quantity,
                        COALESCE(SUM(t.quantity * t.price), 0) as total_revenue,
                        COUNT(DISTINCT t.buyer_id) as active_buyers,
                        COUNT(DISTINCT t.seller_id) as active_sellers
                    FROM transactions t
                    {$whereClause}
                ";
                $summaryData = DB::select($summaryQuery);
                $summary = $summaryData[0] ?? null;

                $result['summary'] = [
                    'total_transactions' => (int) ($summary->total_transactions ?? 0),
                    'total_quantity' => (int) ($summary->total_quantity ?? 0),
                    'total_revenue' => (int) ($summary->total_revenue ?? 0),
                    'active_buyers' => (int) ($summary->active_buyers ?? 0),
                    'active_sellers' => (int) ($summary->active_sellers ?? 0),
                    'avg_transaction_price' => $summary && $summary->total_transactions > 0
                        ? round($summary->total_revenue / $summary->total_transactions)
                        : 0,
                ];
            }

            // 2. TRENDS - Line chart data
            if (in_array('trends', $includeArray)) {
                $trendsQuery = "
                    SELECT
                        {$dateFormat} as period,
                        COUNT(*) as transactions,
                        COALESCE(SUM(t.quantity * t.price), 0) as revenue
                    FROM transactions t
                    {$whereClause}
                    GROUP BY period
                    ORDER BY period
                ";
                $result['trends'] = DB::select($trendsQuery);
            }

            // 3. TRENDING_ITEMS - Top selling items
            if (in_array('trending_items', $includeArray)) {
                $trendingQuery = "
                    SELECT
                        i.id,
                        i.item_code,
                        i.item_name,
                        SUM(t.quantity) as total_quantity,
                        COALESCE(SUM(t.quantity * t.price), 0) as total_revenue
                    FROM transactions t
                    JOIN items i ON t.item_id = i.id
                    {$whereClause}
                    GROUP BY i.id, i.item_code, i.item_name
                    ORDER BY total_quantity DESC
                    LIMIT {$limit}
                ";
                $result['trending_items'] = DB::select($trendingQuery);
            }

            // 4. USER_CLASSIFICATION - Domestic vs Foreign
            if (in_array('user_classification', $includeArray)) {
                $userClassQuery = "
                    SELECT
                        buyer.type,
                        COUNT(DISTINCT t.buyer_id) as user_count,
                        COALESCE(SUM(t.quantity * t.price), 0) as revenue
                    FROM transactions t
                    JOIN users buyer ON t.buyer_id = buyer.id
                    {$whereClause}
                    GROUP BY buyer.type
                ";
                $userClassData = DB::select($userClassQuery);
                $totalUsers = array_sum(array_column($userClassData, 'user_count'));

                $result['user_classification'] = array_map(function ($item) use ($totalUsers) {
                    return [
                        'type' => $item->type,
                        'user_count' => (int) $item->user_count,
                        'revenue' => (int) $item->revenue,
                        'percentage' => $totalUsers > 0 ? round(($item->user_count / $totalUsers) * 100, 2) : 0,
                    ];
                }, $userClassData);
            }

            // 5. TOP_BUYERS - Top buyers by transactions
            if (in_array('top_buyers', $includeArray)) {
                $topBuyersQuery = "
                    SELECT
                        buyer.id,
                        buyer.name as user_name,
                        COUNT(*) as transaction_count,
                        COALESCE(SUM(t.quantity * t.price), 0) as total_spent
                    FROM transactions t
                    JOIN users buyer ON t.buyer_id = buyer.id
                    {$whereClause}
                    GROUP BY buyer.id, buyer.name
                    ORDER BY transaction_count DESC
                    LIMIT {$limit}
                ";
                $result['top_buyers'] = DB::select($topBuyersQuery);
            }

            // 6. TOP_SELLERS - Top sellers by revenue
            if (in_array('top_sellers', $includeArray)) {
                $topSellersQuery = "
                    SELECT
                        seller.id,
                        seller.name as user_name,
                        COUNT(*) as transaction_count,
                        COALESCE(SUM(t.quantity * t.price), 0) as total_earned
                    FROM transactions t
                    JOIN users seller ON t.seller_id = seller.id
                    {$whereClause}
                    GROUP BY seller.id, seller.name
                    ORDER BY total_earned DESC
                    LIMIT {$limit}
                ";
                $result['top_sellers'] = DB::select($topSellersQuery);
            }

            // 7. REVENUE_CONTRIBUTION - Revenue contribution by item
            if (in_array('revenue_contribution', $includeArray)) {
                $revenueContribQuery = "
                    SELECT
                        i.id,
                        i.item_code as name,
                        COALESCE(SUM(t.quantity * t.price), 0) as revenue
                    FROM transactions t
                    JOIN items i ON t.item_id = i.id
                    {$whereClause}
                    GROUP BY i.id, i.item_code
                    ORDER BY revenue DESC
                    LIMIT {$limit}
                ";
                $revenueData = DB::select($revenueContribQuery);
                $totalRevenue = array_sum(array_map(function ($item) {
                    return $item->revenue;
                }, $revenueData));

                $result['revenue_contribution'] = array_map(function ($item) use ($totalRevenue) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'revenue' => (int) $item->revenue,
                        'percentage' => $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 2) : 0,
                    ];
                }, $revenueData);
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period,
            'limit' => $limit,
            'include' => $includeArray,
            'cached' => Cache::has($cacheKey),
        ]);
        } catch (\Throwable $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Dashboard Analytics Error: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty data structure on error - never crash
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard analytics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => [
                    'summary' => null,
                    'trends' => [],
                    'trending_items' => [],
                    'user_classification' => [],
                    'top_buyers' => [],
                    'top_sellers' => [],
                    'revenue_contribution' => [],
                ],
                'period' => $period,
                'limit' => $limit,
                'include' => $includeArray,
                'cached' => false,
            ], 200); // Return 200 with error info so frontend can handle gracefully
        }
    }
}
