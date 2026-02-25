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
     * GET /api/dashboard/summary
     * KPI Summary - Total Transactions, Quantity, Revenue, Active Buyers/Sellers
     */
    #[OA\Get(
        path: "/dashboard/summary",
        tags: ["Dashboard"],
        summary: "Dashboard KPI Summary",
        description: "Returns total transactions, quantity, revenue, active buyers and sellers",
        parameters: [
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function summary(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $cacheKey = $this->generateCacheKey('dashboard.summary', $filters);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = DB::table('transactions');

            // Apply date filters
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            }

            // Apply user_type filter
            if (isset($filters['user_type'])) {
                $query->whereHas('buyer', function ($q) use ($filters) {
                    $q->where('type', $filters['user_type']);
                });
            }

            // Get totals
            $totals = $query->clone()
                ->selectRaw('COUNT(*) as total_transactions')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->selectRaw('SUM(quantity * price) as total_revenue')
                ->first();

            // Get active buyers count
            $activeBuyers = DB::table('transactions')
                ->when(isset($filters['start_date']) && isset($filters['end_date']), function ($q) use ($filters) {
                    $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
                })
                ->when(isset($filters['user_type']), function ($q) use ($filters) {
                    $q->whereHas('buyer', function ($sub) use ($filters) {
                        $sub->where('type', $filters['user_type']);
                    });
                })
                ->distinct('buyer_id')
                ->count('buyer_id');

            // Get active sellers count
            $activeSellers = DB::table('transactions')
                ->when(isset($filters['start_date']) && isset($filters['end_date']), function ($q) use ($filters) {
                    $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
                })
                ->when(isset($filters['user_type']), function ($q) use ($filters) {
                    $q->whereHas('buyer', function ($sub) use ($filters) {
                        $sub->where('type', $filters['user_type']);
                    });
                })
                ->distinct('seller_id')
                ->count('seller_id');

            return [
                'total_transactions' => (int) ($totals->total_transactions ?? 0),
                'total_quantity' => (int) ($totals->total_quantity ?? 0),
                'total_revenue' => (int) ($totals->total_revenue ?? 0),
                'active_buyers' => $activeBuyers,
                'active_sellers' => $activeSellers,
                'avg_transaction_price' => $totals->total_transactions > 0 
                    ? round($totals->total_revenue / $totals->total_transactions) 
                    : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * GET /api/dashboard/trends
     * Transaction Trend Analysis - Line Chart
     */
    #[OA\Get(
        path: "/dashboard/trends",
        tags: ["Dashboard"],
        summary: "Transaction Trend Analysis",
        description: "Returns transaction trends with revenue and transaction count",
        parameters: [
            new OA\Parameter(name: "period", in: "query", description: "Period: daily, weekly, monthly", schema: new OA\Schema(type: "string", enum: ["daily", "weekly", "monthly"], default: "daily")),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function trends(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $period = $request->input('period', 'daily');
        
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        $cacheKey = $this->generateCacheKey('dashboard.trends', $filters, ['period' => $period]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $period) {
            $query = DB::table('transactions');

            // Apply date filters
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            }

            // Apply user_type filter
            if (isset($filters['user_type'])) {
                $query->whereHas('buyer', function ($q) use ($filters) {
                    $q->where('type', $filters['user_type']);
                });
            }

            // Date grouping based on period
            $dateSelect = match ($period) {
                'daily' => 'DATE(created_at) as period',
                'weekly' => 'YEARWEEK(created_at, 1) as period',
                'monthly' => 'DATE_FORMAT(created_at, \'%Y-%m\') as period',
                default => 'DATE(created_at) as period',
            };

            return $query
                ->clone()
                ->selectRaw($dateSelect)
                ->selectRaw('COUNT(*) as transactions')
                ->selectRaw('SUM(quantity * price) as revenue')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * GET /api/items/trending
     * Trending Items - Horizontal Bar Chart
     */
    #[OA\Get(
        path: "/items/trending",
        tags: ["Items"],
        summary: "Trending Items",
        description: "Returns top trending items by quantity sold",
        parameters: [
            new OA\Parameter(name: "limit", in: "query", description: "Number of items (default 10)", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function trendingItems(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = $this->generateCacheKey('items.trending', $filters, ['limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $limit) {
            $query = DB::table('transactions')
                ->join('items', 'transactions.item_id', '=', 'items.id');

            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('transactions.created_at', [$filters['start_date'], $filters['end_date']]);
            }

            if (isset($filters['user_type'])) {
                $query->join('users as buyers', 'transactions.buyer_id', '=', 'buyers.id')
                    ->where('buyers.type', $filters['user_type']);
            }

            return $query
                ->select('items.id', 'items.item_code', 'items.item_name')
                ->selectRaw('SUM(transactions.quantity) as total_quantity')
                ->selectRaw('SUM(transactions.quantity * transactions.price) as total_revenue')
                ->groupBy('items.id', 'items.item_code', 'items.item_name')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * GET /api/users/classification
     * Domestic vs Foreign Users - Pie/Donut Chart
     */
    #[OA\Get(
        path: "/users/classification",
        tags: ["Users"],
        summary: "User Classification",
        description: "Returns domestic vs foreign user distribution with percentages",
        parameters: [
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function userClassification(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $cacheKey = $this->generateCacheKey('users.classification', $filters);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = DB::table('transactions')
                ->join('users', 'transactions.buyer_id', '=', 'users.id');

            // Apply date filters
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('transactions.created_at', [$filters['start_date'], $filters['end_date']]);
            }

            $distribution = $query
                ->clone()
                ->select('users.type')
                ->selectRaw('COUNT(DISTINCT transactions.buyer_id) as user_count')
                ->selectRaw('SUM(transactions.quantity * transactions.price) as revenue')
                ->groupBy('users.type')
                ->get();

            $totalUsers = $distribution->sum('user_count');

            return $distribution->map(function ($item) use ($totalUsers) {
                return [
                    'type' => $item->type,
                    'user_count' => (int) $item->user_count,
                    'revenue' => (int) $item->revenue,
                    'percentage' => $totalUsers > 0 ? round(($item->user_count / $totalUsers) * 100, 2) : 0,
                ];
            })->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * GET /api/relations
     * Buyer-Seller Relationship - Bar Chart
     */
    #[OA\Get(
        path: "/relations",
        tags: ["Relations"],
        summary: "Buyer-Seller Relations",
        description: "Returns top buyers and sellers by transaction count",
        parameters: [
            new OA\Parameter(name: "type", in: "query", description: "Type: buyers, sellers, or both (default)", schema: new OA\Schema(type: "string", enum: ["buyers", "sellers", "both"], default: "both")),
            new OA\Parameter(name: "limit", in: "query", description: "Number of results (default 10)", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function relations(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $type = $request->input('type', 'both');
        $limit = $request->input('limit', 10);
        
        if (!in_array($type, ['buyers', 'sellers', 'both'])) {
            $type = 'both';
        }

        $cacheKey = $this->generateCacheKey('relations', $filters, ['type' => $type, 'limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $type, $limit) {
            $result = [];

            // Get top buyers
            if (in_array($type, ['buyers', 'both'])) {
                $buyersQuery = DB::table('transactions')
                    ->join('users', 'transactions.buyer_id', '=', 'users.id');

                // Apply date filters
                if (isset($filters['start_date']) && isset($filters['end_date'])) {
                    $buyersQuery->whereBetween('transactions.created_at', [$filters['start_date'], $filters['end_date']]);
                }

                // Apply user_type filter
                if (isset($filters['user_type'])) {
                    $buyersQuery->where('users.type', $filters['user_type']);
                }

                $result['top_buyers'] = $buyersQuery
                    ->clone()
                    ->select('users.id')
                    ->selectRaw('users.name as user_name')
                    ->selectRaw('COUNT(*) as transaction_count')
                    ->selectRaw('SUM(transactions.quantity * transactions.price) as total_spent')
                    ->groupBy('users.id', 'users.name')
                    ->orderByDesc('transaction_count')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            // Get top sellers
            if (in_array($type, ['sellers', 'both'])) {
                $sellersQuery = DB::table('transactions')
                    ->join('users', 'transactions.seller_id', '=', 'users.id');

                // Apply date filters
                if (isset($filters['start_date']) && isset($filters['end_date'])) {
                    $sellersQuery->whereBetween('transactions.created_at', [$filters['start_date'], $filters['end_date']]);
                }

                // Apply user_type filter
                if (isset($filters['user_type'])) {
                    $sellersQuery->whereHas('buyer', function ($q) use ($filters) {
                        $q->where('type', $filters['user_type']);
                    });
                }

                $result['top_sellers'] = $sellersQuery
                    ->clone()
                    ->select('users.id')
                    ->selectRaw('users.name as user_name')
                    ->selectRaw('COUNT(*) as transaction_count')
                    ->selectRaw('SUM(transactions.quantity * transactions.price) as total_earned')
                    ->groupBy('users.id', 'users.name')
                    ->orderByDesc('transaction_count')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * GET /api/revenue/contribution
     * Revenue Contribution - Pie/Stacked Bar Chart
     */
    #[OA\Get(
        path: "/revenue/contribution",
        tags: ["Revenue"],
        summary: "Revenue Contribution",
        description: "Returns revenue contribution by item or seller",
        parameters: [
            new OA\Parameter(name: "group_by", in: "query", description: "Group by: item or seller (default item)", schema: new OA\Schema(type: "string", enum: ["item", "seller"], default: "item")),
            new OA\Parameter(name: "limit", in: "query", description: "Number of results (default 10)", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "start_date", in: "query", description: "Start date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "end_date", in: "query", description: "End date (Y-m-d)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "user_type", in: "query", description: "User type: domestic, foreign", schema: new OA\Schema(type: "string", enum: ["domestic", "foreign"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function revenueContribution(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $groupBy = $request->input('group_by', 'item');
        $limit = $request->input('limit', 10);
        
        if (!in_array($groupBy, ['item', 'seller'])) {
            $groupBy = 'item';
        }

        $cacheKey = $this->generateCacheKey('revenue.contribution', $filters, ['group_by' => $groupBy, 'limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $groupBy, $limit) {
            $query = DB::table('transactions');

            // Apply date filters
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            }

            // Apply user_type filter
            if (isset($filters['user_type'])) {
                $query->whereHas('buyer', function ($q) use ($filters) {
                    $q->where('type', $filters['user_type']);
                });
            }

            if ($groupBy === 'item') {
                $query->join('items', 'transactions.item_id', '=', 'items.id');
                
                $data = $query
                    ->clone()
                    ->select('items.id')
                    ->selectRaw('items.item_code as name')
                    ->selectRaw('SUM(transactions.quantity * transactions.price) as revenue')
                    ->groupBy('items.id', 'items.item_code')
                    ->orderByDesc('revenue')
                    ->limit($limit)
                    ->get();
            } else {
                $query->join('users as sellers', 'transactions.seller_id', '=', 'sellers.id');
                
                $data = $query
                    ->clone()
                    ->select('sellers.id')
                    ->selectRaw('sellers.name as name')
                    ->selectRaw('SUM(transactions.quantity * transactions.price) as revenue')
                    ->groupBy('sellers.id', 'sellers.name')
                    ->orderByDesc('revenue')
                    ->limit($limit)
                    ->get();
            }

            // Calculate percentage
            $totalRevenue = $data->sum('revenue');
            
            return $data->map(function ($item) use ($totalRevenue) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'revenue' => (int) $item->revenue,
                    'percentage' => $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 2) : 0,
                ];
            })->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'group_by' => $groupBy,
            'cached' => Cache::has($cacheKey),
        ]);
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
