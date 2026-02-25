<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Info(
 *      title="Transaction Dashboard API",
 *      version="1.0",
 *      description="API Documentation for Transaction Dashboard"
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000/api/v1",
 *      description="API Server"
 * )
 */
class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected FilterService $filterService;

    // Cache TTL in seconds (5 minutes)
    protected const CACHE_TTL = 300;

    public function __construct(
        AnalyticsService $analyticsService,
        FilterService $filterService
    ) {
        $this->analyticsService = $analyticsService;
        $this->filterService = $filterService;
    }

    /**
     * Get all dashboard data
     * @OA\Get(
     *      path="/dashboard",
     *      tags={"Dashboard"},
     *      summary="Get all dashboard data",
     *      description="Returns all dashboard analytics data",
     *      @OA\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="Start date (Y-m-d H:i:s)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="End date (Y-m-d H:i:s)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="period",
     *          in="query",
     *          description="Period: daily, weekly, monthly",
     *          @OA\Schema(type="string", enum={"daily", "weekly", "monthly"})
     *      ),
     *      @OA\Parameter(
     *          name="user_type",
     *          in="query",
     *          description="User type: domestic, foreign",
     *          @OA\Schema(type="string", enum={"domestic", "foreign"})
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $period = $request->input('period', 'daily');

        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        // Create cache key based on filters and period
        $cacheKey = $this->generateCacheKey('dashboard', $filters, ['period' => $period]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $period) {
            return $this->analyticsService->getDashboardData($filters, $period);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filters' => $filters,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get summary metrics
     * @OA\Get(
     *      path="/dashboard/summary",
     *      tags={"Dashboard"},
     *      summary="Get summary metrics",
     *      description="Returns summary metrics"
     * )
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $cacheKey = $this->generateCacheKey('dashboard.summary', $filters);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            return $this->analyticsService->getSummary($filters);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get trends data
     * @OA\Get(
     *      path="/dashboard/trends",
     *      tags={"Dashboard"},
     *      summary="Get trends data",
     *      description="Returns transaction trends"
     * )
     */
    public function trends(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $period = $request->input('period', 'daily');

        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        $cacheKey = $this->generateCacheKey('dashboard.trends', $filters, ['period' => $period]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $period) {
            return $this->analyticsService->getTrends($filters, $period);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get trending items
     * @OA\Get(
     *      path="/dashboard/trending-items",
     *      tags={"Dashboard"},
     *      summary="Get trending items",
     *      description="Returns trending items"
     * )
     */
    public function trendingItems(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = $this->generateCacheKey('dashboard.trending', $filters, ['limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $filters) {
            return $this->analyticsService->getTrendingItems($limit, $filters);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get top buyers
     * @OA\Get(
     *      path="/dashboard/top-buyers",
     *      tags={"Dashboard"},
     *      summary="Get top buyers",
     *      description="Returns top buyers"
     * )
     */
    public function topBuyers(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = $this->generateCacheKey('dashboard.top_buyers', $filters, ['limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $filters) {
            return $this->analyticsService->getTopBuyers($limit, $filters);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get top sellers
     * @OA\Get(
     *      path="/dashboard/top-sellers",
     *      tags={"Dashboard"},
     *      summary="Get top sellers",
     *      description="Returns top sellers"
     * )
     */
    public function topSellers(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = $this->generateCacheKey('dashboard.top_sellers', $filters, ['limit' => $limit]);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $filters) {
            return $this->analyticsService->getTopSellers($limit, $filters);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get user type distribution
     * @OA\Get(
     *      path="/dashboard/user-type-distribution",
     *      tags={"Dashboard"},
     *      summary="Get user type distribution",
     *      description="Returns user type distribution"
     * )
     */
    public function userTypeDistribution(Request $request): JsonResponse
    {
        $filters = $this->filterService->getFilters($request);
        $cacheKey = $this->generateCacheKey('dashboard.user_type_dist', $filters);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            return $this->analyticsService->getUserTypeDistribution($filters);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Get user classification
     * @OA\Get(
     *      path="/dashboard/user-classification",
     *      tags={"Dashboard"},
     *      summary="Get user classification",
     *      description="Returns user classification"
     * )
     */
    public function userClassification(): JsonResponse
    {
        $cacheKey = 'dashboard.user_classification';

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->analyticsService->getUserClassification();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Clear all dashboard cache
     * @OA\Post(
     *      path="/dashboard/cache/clear",
     *      tags={"Dashboard"},
     *      summary="Clear dashboard cache",
     *      description="Clears all cached dashboard data"
     * )
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget('dashboard.summary');
        Cache::forget('dashboard.trends');
        Cache::forget('dashboard.trending');
        Cache::forget('dashboard.top_buyers');
        Cache::forget('dashboard.top_sellers');
        Cache::forget('dashboard.user_type_dist');
        Cache::forget('dashboard.user_classification');

        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache cleared successfully',
        ]);
    }

    /**
     * Generate cache key based on filters and additional parameters
     */
    private function generateCacheKey(string $prefix, array $filters, array $additional = []): string
    {
        $filterKey = json_encode($filters);
        $additionalKey = json_encode($additional);
        return "{$prefix}:" . md5($filterKey . $additionalKey);
    }
}
