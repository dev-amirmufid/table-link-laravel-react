import { useEffect } from 'react'
import { useAppDispatch, useAppSelector } from '@/store/hooks'
import {
  fetchDashboardAnalytics,
  setFilters
} from '@/store/slices/dashboardSlice'
import { useTheme } from '@/contexts/ThemeContext'
import type { DashboardFilters } from '@/types'
import { FilterBar } from '@/components/FilterBar'
import { SummaryCards } from '@/components/SummaryCards'
import { TrendsChart } from '@/components/charts/TrendsChart'
import { TrendingItemsChart } from '@/components/charts/TrendingItemsChart'
import { UserClassificationChart } from '@/components/charts/UserClassificationChart'
import { RevenueContributionChart } from '@/components/charts/RevenueContributionChart'
import { TopBuyerList } from '@/components/lists/TopBuyerList'
import { TopSellerList } from '@/components/lists/TopSellerList'
import { TransactionsTable } from '@/components/TransactionsTable'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Moon, Sun, RefreshCw } from 'lucide-react'

export function Dashboard() {
  const dispatch = useAppDispatch()
  const { theme, toggleTheme } = useTheme()
  
  const { 
    summary, 
    trends, 
    trendingItems, 
    userClassification,
    relations,
    revenueContribution,
    filters, 
    loading 
  } = useAppSelector((state) => state.dashboard)

  // Fetch data on mount and when filters change - GOD QUERY (single endpoint)
  useEffect(() => {
    dispatch(fetchDashboardAnalytics({ filters, period: filters.period || 'daily', limit: 10 }))
  }, [dispatch, filters])

  const handleFilterChange = (newFilters: DashboardFilters) => {
    dispatch(setFilters(newFilters))
  }

  const handleRefresh = () => {
    dispatch(fetchDashboardAnalytics({ filters, period: filters.period || 'daily', limit: 10 }))
  }

  const isAnyLoading = Object.values(loading).some(v => v)

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Transaction Dashboard</h1>
        <div className="flex items-center gap-2">
          <Button 
            variant="outline" 
            size="icon" 
            onClick={toggleTheme} 
            title="Toggle theme"
          >
            {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
          </Button>
          <Button 
            variant="outline" 
            size="icon" 
            onClick={handleRefresh} 
            title="Refresh data"
            disabled={isAnyLoading}
          >
            <RefreshCw className={`h-4 w-4 ${isAnyLoading ? 'animate-spin' : ''}`} />
          </Button>

        </div>
      </div>

      <FilterBar filters={filters} onFilterChange={handleFilterChange} />

      {/* Summary Cards */}
      <SummaryCards 
        data={summary} 
        isLoading={loading.summary} 
      />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Transaction Trends - Line Chart */}
        <Card>
          <CardHeader>
            <CardTitle>Transaction Trends</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.trends ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <TrendsChart data={trends} />
            )}
          </CardContent>
        </Card>

        {/* Trending Items - Bar Chart */}
        <Card>
          <CardHeader>
            <CardTitle>Trending Items</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.trendingItems ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <TrendingItemsChart data={trendingItems} />
            )}
          </CardContent>
        </Card>

        {/* User Classification - Pie/Donut Chart */}
        <Card>
          <CardHeader>
            <CardTitle>Domestic vs Foreign Users</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.userClassification ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <UserClassificationChart data={userClassification} />
            )}
          </CardContent>
        </Card>
        
        {/* Revenue Contribution - Bar Chart */}
        <Card>
          <CardHeader>
            <CardTitle>Revenue Contribution</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.revenueContribution ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <RevenueContributionChart data={revenueContribution} />
            )}
          </CardContent>
        </Card>

        {/* Top Buyers */}
        <Card>
          <CardHeader>
            <CardTitle>Top Buyers</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.relations ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <TopBuyerList data={relations.top_buyers || []} />
            )}
          </CardContent>
        </Card>

        {/* Top Sellers */}
        <Card>
          <CardHeader>
            <CardTitle>Top Sellers</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.relations ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <TopSellerList data={relations.top_sellers || []} />
            )}
          </CardContent>
        </Card>
      </div>

      {/* Transactions Table - Unified filtering with dashboard */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
        </CardHeader>
        <CardContent>
          <TransactionsTable filters={filters} />
        </CardContent>
      </Card>
    </div>
  )
}
