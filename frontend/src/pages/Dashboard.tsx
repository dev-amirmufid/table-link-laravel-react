import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { dashboardApi } from '@/lib/api'
import { useAuth } from '@/contexts/AuthContext'
import { useTheme } from '@/contexts/ThemeContext'
import { exportToCSV } from '@/lib/csv'
import type { DashboardData, DashboardFilters } from '@/types'
import { FilterBar } from '@/components/FilterBar'
import { SummaryCards } from '@/components/SummaryCards'
import { LineChartComponent } from '@/components/charts/LineChartComponent'
import { BarChartComponent } from '@/components/charts/BarChartComponent'
import { PieChartComponent } from '@/components/charts/PieChartComponent'
import { DataTable } from '@/components/DataTable'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { LogOut, User, Moon, Sun, Download } from 'lucide-react'

export function Dashboard() {
  const [data, setData] = useState<DashboardData | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [filters, setFilters] = useState<DashboardFilters>({
    period: 'daily',
  })
  const { user, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  useEffect(() => {
    fetchDashboardData()
  }, [filters])

  const fetchDashboardData = async () => {
    setIsLoading(true)
    try {
      const response = await dashboardApi.getAll(filters)
      setData(response.data.data)
    } catch (error) {
      console.error('Error fetching dashboard data:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const handleFilterChange = (newFilters: DashboardFilters) => {
    setFilters(newFilters)
  }

  const handleExportCSV = () => {
    if (!data) return
    
    // Export trends data
    exportToCSV(data.trends.map(t => ({
      date: t.date,
      count: t.count,
      revenue: t.revenue,
    })), 'transaction-trends', [
      { key: 'date', header: 'Date' },
      { key: 'count', header: 'Transaction Count' },
      { key: 'revenue', header: 'Revenue' },
    ])
  }

  const LoadingSkeleton = () => (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <Skeleton className="h-8 w-64" />
        <div className="flex gap-2">
          <Skeleton className="h-10 w-10" />
          <Skeleton className="h-10 w-10" />
          <Skeleton className="h-10 w-24" />
        </div>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {[...Array(4)].map((_, i) => (
          <Skeleton key={i} className="h-32" />
        ))}
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {[...Array(4)].map((_, i) => (
          <Skeleton key={i} className="h-80" />
        ))}
      </div>
    </div>
  )

  if (isLoading) {
    return (
      <div className="container mx-auto p-6">
        <LoadingSkeleton />
      </div>
    )
  }

  if (!data) {
    return (
      <div className="container mx-auto p-6">
        <div className="flex items-center justify-center h-64">
          <div className="text-lg">Failed to load dashboard data</div>
        </div>
      </div>
    )
  }

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Transaction Dashboard</h1>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="icon" onClick={toggleTheme} title="Toggle theme">
            {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
          </Button>
          <Button variant="outline" size="sm" onClick={handleExportCSV}>
            <Download className="h-4 w-4 mr-2" />
            Export CSV
          </Button>
          <div className="flex items-center gap-2 text-sm text-muted-foreground ml-2">
            <User className="h-4 w-4" />
            <span>{user?.name}</span>
          </div>
          <Button variant="outline" size="sm" onClick={handleLogout}>
            <LogOut className="h-4 w-4 mr-2" />
            Keluar
          </Button>
        </div>
      </div>

      <FilterBar filters={filters} onFilterChange={handleFilterChange} />

      <SummaryCards data={data.summary} />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Transaction Trends</CardTitle>
          </CardHeader>
          <CardContent>
            <LineChartComponent data={data.trends} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top Buyers</CardTitle>
          </CardHeader>
          <CardContent>
            <BarChartComponent data={data.top_buyers} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top Sellers</CardTitle>
          </CardHeader>
          <CardContent>
            <BarChartComponent data={data.top_sellers} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>User Type Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <PieChartComponent data={data.user_type_distribution} />
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
        </CardHeader>
        <CardContent>
          <DataTable data={[]} />
        </CardContent>
      </Card>
    </div>
  )
}
