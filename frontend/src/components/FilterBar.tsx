import { useState } from 'react'
import { Calendar } from 'lucide-react'
import type { DashboardFilters } from '@/types'

interface FilterBarProps {
  filters: DashboardFilters
  onFilterChange: (filters: DashboardFilters) => void
}

export function FilterBar({ filters, onFilterChange }: FilterBarProps) {
  const [startDate, setStartDate] = useState(filters.start_date || '')
  const [endDate, setEndDate] = useState(filters.end_date || '')
  const [period, setPeriod] = useState<string>(filters.period || 'daily')
  const [userType, setUserType] = useState(filters.user_type || '')

  const handleApplyFilters = () => {
    onFilterChange({
      start_date: startDate || undefined,
      end_date: endDate || undefined,
      period: period as 'daily' | 'weekly' | 'monthly',
      user_type: userType || undefined,
    })
  }

  const handleResetFilters = () => {
    setStartDate('')
    setEndDate('')
    setPeriod('daily')
    setUserType('')
    onFilterChange({})
  }

  return (
    <div className="flex flex-wrap items-center gap-4 p-4 bg-card rounded-lg border">
      <div className="flex items-center gap-2">
        <Calendar className="h-4 w-4 text-muted-foreground" />
        <span className="text-sm font-medium">Filter</span>
      </div>

      <div className="flex items-center gap-2">
        <label className="text-sm text-muted-foreground">Start:</label>
        <input
          type="date"
          value={startDate}
          onChange={(e) => setStartDate(e.target.value)}
          className="px-3 py-1 text-sm border rounded-md bg-background"
        />
      </div>

      <div className="flex items-center gap-2">
        <label className="text-sm text-muted-foreground">End:</label>
        <input
          type="date"
          value={endDate}
          onChange={(e) => setEndDate(e.target.value)}
          className="px-3 py-1 text-sm border rounded-md bg-background"
        />
      </div>

      <div className="flex items-center gap-2">
        <label className="text-sm text-muted-foreground">Period:</label>
        <select
          value={period}
          onChange={(e) => setPeriod(e.target.value)}
          className="px-3 py-1 text-sm border rounded-md bg-background"
        >
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
        </select>
      </div>

      <div className="flex items-center gap-2">
        <label className="text-sm text-muted-foreground">User Type:</label>
        <select
          value={userType}
          onChange={(e) => setUserType(e.target.value)}
          className="px-3 py-1 text-sm border rounded-md bg-background"
        >
          <option value="">All</option>
          <option value="domestic">Domestic</option>
          <option value="foreign">Foreign</option>
        </select>
      </div>

      <div className="flex items-center gap-2">
        <button
          onClick={handleApplyFilters}
          className="px-4 py-1 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/90"
        >
          Apply
        </button>
        <button
          onClick={handleResetFilters}
          className="px-4 py-1 text-sm border rounded-md hover:bg-accent"
        >
          Reset
        </button>
      </div>
    </div>
  )
}
