import { useEffect } from 'react'
import { useAppDispatch, useAppSelector } from '@/store/hooks'
import { 
  fetchSummary, 
  fetchTrends, 
  fetchTopBuyers, 
  fetchTopSellers, 
  fetchUserTypeDistribution,
  fetchTopItems,
  fetchPriceDistribution,
  setFilters 
} from '@/store/slices/dashboardSlice'
import { useTheme } from '@/contexts/ThemeContext'
import { exportToCSV } from '@/lib/csv'
import type { DashboardFilters } from '@/types'
import { FilterBar } from '@/components/FilterBar'
import { SummaryCards } from '@/components/SummaryCards'
import { LineChartComponent } from '@/components/charts/LineChartComponent'
import { BarChartComponent } from '@/components/charts/BarChartComponent'
import { PieChartComponent } from '@/components/charts/PieChartComponent'
import { ItemPerformanceChart } from '@/components/charts/BarHorizontalChartComponent'
import { PriceDistributionChart } from '@/components/charts/PriceDistributionChart'
import { TransactionsTable } from '@/components/TransactionsTable'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Moon, Sun, Download, RefreshCw } from 'lucide-react'

export function Dashboard() {
  const dispatch = useAppDispatch()
  const { theme, toggleTheme } = useTheme()
  
  const { 
    summary, 
    trends, 
    topBuyers, 
    topSellers, 
    userTypeDistribution, 
    topItems,
    topItemsStatistics,
    priceDistribution,
    priceDistributionStatistics,
    filters, 
    loading 
  } = useAppSelector((state) => state.dashboard)

  // Fetch data on mount and when filters change
  useEffect(() => {
    dispatch(fetchSummary(filters))
    dispatch(fetchTrends(filters))
    dispatch(fetchTopBuyers({ filters, limit: 10 }))
    dispatch(fetchTopSellers({ filters, limit: 10 }))
    dispatch(fetchUserTypeDistribution(filters))
    dispatch(fetchTopItems({ filters, limit: 10 }))
    dispatch(fetchPriceDistribution(filters))
  }, [dispatch, filters])

  const handleFilterChange = (newFilters: DashboardFilters) => {
    dispatch(setFilters(newFilters))
  }

  const handleExportCSV = () => {
    if (!trends.length) return
    
    exportToCSV(trends.map(t => ({
      date: t.date,
      count: t.count,
      revenue: t.revenue,
    })), 'transaction-trends', [
      { key: 'date', header: 'Date' },
      { key: 'count', header: 'Transaction Count' },
      { key: 'revenue', header: 'Revenue' },
    ])
  }

  const handleRefresh = () => {
    dispatch(fetchSummary(filters))
    dispatch(fetchTrends(filters))
    dispatch(fetchTopBuyers({ filters, limit: 10 }))
    dispatch(fetchTopSellers({ filters, limit: 10 }))
    dispatch(fetchUserTypeDistribution(filters))
    dispatch(fetchTopItems({ filters, limit: 10 }))
    dispatch(fetchPriceDistribution(filters))
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
          <Button variant="outline" size="sm" onClick={handleExportCSV}>
            <Download className="h-4 w-4 mr-2" />
            Export CSV
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
        {/* Transaction Trends */}
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
              <LineChartComponent data={trends} />
            )}
          </CardContent>
        </Card>

        {/* Top Buyers */}
        <Card>
          <CardHeader>
            <CardTitle>Top Buyers</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.topBuyers ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <BarChartComponent data={topBuyers} />
            )}
          </CardContent>
        </Card>

        {/* Top Sellers */}
        <Card>
          <CardHeader>
            <CardTitle>Top Sellers</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.topSellers ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <BarChartComponent data={topSellers} />
            )}
          </CardContent>
        </Card>

        {/* User Type Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>User Type Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.userTypeDistribution ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <PieChartComponent data={userTypeDistribution} />
            )}
          </CardContent>
        </Card>

        {/* Item Performance - Top Items by Revenue */}
        <Card>
          <CardHeader>
            <CardTitle>Top Items by Revenue</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.topItems ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <ItemPerformanceChart 
                data={topItems} 
                statistics={topItemsStatistics}
              />
            )}
          </CardContent>
        </Card>

        {/* Price Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Price Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            {loading.priceDistribution ? (
              <div className="h-[300px] flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <PriceDistributionChart 
                data={priceDistribution}
                statistics={priceDistributionStatistics}
              />
            )}
          </CardContent>
        </Card>
      </div>

      {/* Transactions Table */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
        </CardHeader>
        <CardContent>
          <TransactionsTable />
        </CardContent>
      </Card>
    </div>
  )
}
