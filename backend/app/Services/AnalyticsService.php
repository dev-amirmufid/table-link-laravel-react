<?php

namespace App\Services;

use App\Repositories\ItemRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    protected TransactionRepository $transactionRepository;
    protected ItemRepository $itemRepository;
    protected UserRepository $userRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        ItemRepository $itemRepository,
        UserRepository $userRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get dashboard summary data
     */
    public function getSummary(array $filters = []): array
    {
        $cacheKey = 'analytics_summary_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return [
                'total_revenue' => $this->transactionRepository->getTotalRevenue($filters),
                'total_transactions' => $this->transactionRepository->getTotalTransactions($filters),
                'total_quantity' => $this->transactionRepository->getTotalQuantity($filters),
                'average_price' => $this->transactionRepository->getAveragePrice($filters),
            ];
        });
    }

    /**
     * Get transaction trends
     */
    public function getTrends(array $filters = [], string $period = 'daily'): array
    {
        $cacheKey = 'analytics_trends_' . md5(serialize($filters) . $period);

        return Cache::remember($cacheKey, 300, function () use ($filters, $period) {
            return $this->transactionRepository->getTrends($filters, $period);
        });
    }

    /**
     * Get trending items
     */
    public function getTrendingItems(int $limit = 10, array $filters = []): array
    {
        $cacheKey = 'analytics_trending_items_' . md5(serialize($filters)) . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit, $filters) {
            return $this->itemRepository->getTrending($limit, $filters);
        });
    }

    /**
     * Get top buyers
     */
    public function getTopBuyers(int $limit = 10, array $filters = []): array
    {
        $cacheKey = 'analytics_top_buyers_' . md5(serialize($filters)) . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit, $filters) {
            return $this->transactionRepository->getTopBuyers($limit, $filters);
        });
    }

    /**
     * Get top sellers
     */
    public function getTopSellers(int $limit = 10, array $filters = []): array
    {
        $cacheKey = 'analytics_top_sellers_' . md5(serialize($filters)) . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit, $filters) {
            return $this->transactionRepository->getTopSellers($limit, $filters);
        });
    }

    /**
     * Get user type distribution (for pie chart)
     */
    public function getUserTypeDistribution(array $filters = []): array
    {
        $cacheKey = 'analytics_user_type_distribution_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return $this->transactionRepository->getUserTypeDistribution($filters);
        });
    }

    /**
     * Get user classification
     */
    public function getUserClassification(): array
    {
        $cacheKey = 'analytics_user_classification';

        return Cache::remember($cacheKey, 300, function () {
            return $this->userRepository->getClassification();
        });
    }

    /**
     * Get top items by revenue
     */
    public function getTopItemsByRevenue(int $limit = 10, array $filters = []): array
    {
        $cacheKey = 'analytics_top_items_' . md5(serialize($filters)) . $limit;

        return Cache::remember($cacheKey, 300, function () use ($limit, $filters) {
            return $this->transactionRepository->getTopItemsByRevenue($limit, $filters);
        });
    }

    /**
     * Get price distribution (histogram)
     */
    public function getPriceDistribution(array $filters = []): array
    {
        $cacheKey = 'analytics_price_distribution_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return $this->transactionRepository->getPriceDistribution($filters);
        });
    }

    /**
     * Get all analytics data for dashboard
     */
    public function getDashboardData(array $filters = [], string $period = 'daily'): array
    {
        return [
            'summary' => $this->getSummary($filters),
            'trends' => $this->getTrends($filters, $period),
            'trending_items' => $this->getTrendingItems(10, $filters),
            'top_buyers' => $this->getTopBuyers(10, $filters),
            'top_sellers' => $this->getTopSellers(10, $filters),
            'user_type_distribution' => $this->getUserTypeDistribution($filters),
            'user_classification' => $this->getUserClassification(),
        ];
    }
}
