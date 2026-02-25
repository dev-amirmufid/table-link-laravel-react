import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import type { PayloadAction } from '@reduxjs/toolkit'
import { analyticsApi } from '@/lib/api'
import type { DashboardFilters } from '@/types'

interface TrendData {
  period: string
  transactions: number
  revenue: number
}

interface TrendingItem {
  id: string
  item_code: string
  item_name: string
  total_quantity: number
  total_revenue: number
}

interface UserClassification {
  type: string
  user_count: number
  revenue: number
  percentage: number
}

interface RelationData {
  id: string
  user_name: string
  transaction_count: number
  total_spent?: number
  total_earned?: number
}

interface RevenueContribution {
  id: string
  name: string
  revenue: number
  percentage: number
}

interface DashboardState {
  summary: {
    total_transactions: number
    total_quantity: number
    total_revenue: number
    active_buyers: number
    active_sellers: number
    avg_transaction_price: number
  } | null
  trends: TrendData[]
  trendingItems: TrendingItem[]
  userClassification: UserClassification[]
  relations: {
    top_buyers?: RelationData[]
    top_sellers?: RelationData[]
  }
  revenueContribution: RevenueContribution[]
  filters: DashboardFilters
  loading: {
    summary: boolean
    trends: boolean
    trendingItems: boolean
    userClassification: boolean
    relations: boolean
    revenueContribution: boolean
  }
  error: string | null
}

const initialState: DashboardState = {
  summary: null,
  trends: [],
  trendingItems: [],
  userClassification: [],
  relations: { top_buyers: [], top_sellers: [] },
  revenueContribution: [],
  filters: { period: 'daily' },
  loading: {
    summary: true,
    trends: true,
    trendingItems: true,
    userClassification: true,
    relations: true,
    revenueContribution: true,
  },
  error: null,
}

// GOD QUERY - Single endpoint for all dashboard data
export const fetchDashboardAnalytics = createAsyncThunk(
  'dashboard/fetchAnalytics',
  async (
    {
      filters,
      period = 'daily',
      limit = 10,
    }: {
      filters: DashboardFilters
      period?: string
      limit?: number
    },
    { rejectWithValue }
  ) => {
    try {
      const response = await analyticsApi.getAnalytics(filters, { period, limit })
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch dashboard analytics')
    }
  }
)

const dashboardSlice = createSlice({
  name: 'dashboard',
  initialState,
  reducers: {
    setFilters: (state, action: PayloadAction<DashboardFilters>) => {
      state.filters = action.payload
    },
    clearError: (state) => {
      state.error = null
    },
  },
  extraReducers: (builder) => {
    // GOD QUERY - Dashboard Analytics (Single endpoint)
    builder.addCase(fetchDashboardAnalytics.pending, (state) => {
      state.loading.summary = true
      state.loading.trends = true
      state.loading.trendingItems = true
      state.loading.userClassification = true
      state.loading.relations = true
      state.loading.revenueContribution = true
    })
    builder.addCase(fetchDashboardAnalytics.fulfilled, (state, action) => {
      state.loading.summary = false
      state.loading.trends = false
      state.loading.trendingItems = false
      state.loading.userClassification = false
      state.loading.relations = false
      state.loading.revenueContribution = false

      // Map data from single response
      if (action.payload.summary) {
        state.summary = action.payload.summary
      }
      if (action.payload.trends) {
        state.trends = action.payload.trends.map((t: TrendData) => ({
          period: t.period,
          transactions: Number(t.transactions),
          revenue: Number(t.revenue),
        }))
      }
      if (action.payload.trending_items) {
        state.trendingItems = action.payload.trending_items
      }
      if (action.payload.user_classification) {
        state.userClassification = action.payload.user_classification.map((u: UserClassification) => ({
          type: u.type,
          user_count: Number(u.user_count),
          revenue: Number(u.revenue),
          percentage: Number(u.percentage),
        }))
      }
      if (action.payload.top_buyers || action.payload.top_sellers) {
        state.relations = {
          top_buyers: action.payload.top_buyers || [],
          top_sellers: action.payload.top_sellers || [],
        }
      }
      if (action.payload.revenue_contribution) {
        state.revenueContribution = action.payload.revenue_contribution.map((r: RevenueContribution) => ({
          id: r.id,
          name: r.name,
          revenue: Number(r.revenue),
          percentage: r.percentage,
        }))
      }
    })
    builder.addCase(fetchDashboardAnalytics.rejected, (state, action) => {
      state.loading.summary = false
      state.loading.trends = false
      state.loading.trendingItems = false
      state.loading.userClassification = false
      state.loading.relations = false
      state.loading.revenueContribution = false
      state.error = action.payload as string
    })
  },
})

export const { setFilters, clearError } = dashboardSlice.actions
export default dashboardSlice.reducer
