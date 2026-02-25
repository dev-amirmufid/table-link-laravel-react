import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import type { PayloadAction } from '@reduxjs/toolkit'
import { dashboardApi } from '@/lib/api'
import type { DashboardFilters, Summary, Trend, TopUser, UserTypeDistribution } from '@/types'

interface TrendStatistics {
  total_transactions: number
  total_revenue: number
  avg_transactions: number
  avg_revenue: number
  period_count: number
}

interface TopItem {
  id: string
  item_code: string
  item_name: string
  transaction_count: number
  total_quantity: number
  total_revenue: number
}

interface PriceDistribution {
  range: string
  count: number
  revenue: number
}

interface UserClassification {
  type: string
  count: number
}

interface DashboardState {
  summary: Summary | null
  trends: Trend[]
  trendStatistics: TrendStatistics | null
  topBuyers: TopUser[]
  topSellers: TopUser[]
  userTypeDistribution: UserTypeDistribution[]
  userClassification: UserClassification[]
  topItems: TopItem[]
  topItemsStatistics: { total_revenue: number; total_transactions: number } | null
  priceDistribution: PriceDistribution[]
  priceDistributionStatistics: { total_transactions: number; total_revenue: number } | null
  filters: DashboardFilters
  loading: {
    summary: boolean
    trends: boolean
    topBuyers: boolean
    topSellers: boolean
    userTypeDistribution: boolean
    userClassification: boolean
    topItems: boolean
    priceDistribution: boolean
  }
  error: string | null
}

const initialState: DashboardState = {
  summary: null,
  trends: [],
  trendStatistics: null,
  topBuyers: [],
  topSellers: [],
  userTypeDistribution: [],
  userClassification: [],
  topItems: [],
  topItemsStatistics: null,
  priceDistribution: [],
  priceDistributionStatistics: null,
  filters: { period: 'daily' },
  loading: {
    summary: true,
    trends: true,
    topBuyers: true,
    topSellers: true,
    userTypeDistribution: true,
    userClassification: true,
    topItems: true,
    priceDistribution: true,
  },
  error: null,
}

// Async thunks
export const fetchSummary = createAsyncThunk(
  'dashboard/fetchSummary',
  async (filters: DashboardFilters, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getSummary(filters)
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch summary')
    }
  }
)

export const fetchTrends = createAsyncThunk(
  'dashboard/fetchTrends',
  async (filters: DashboardFilters, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getTrends(filters)
      return {
        data: response.data.data,
        statistics: response.data.statistics,
      }
    } catch {
      return rejectWithValue('Failed to fetch trends')
    }
  }
)

export const fetchTopBuyers = createAsyncThunk(
  'dashboard/fetchTopBuyers',
  async ({ filters, limit = 10 }: { filters: DashboardFilters; limit?: number }, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getTopBuyers(filters, limit)
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch top buyers')
    }
  }
)

export const fetchTopSellers = createAsyncThunk(
  'dashboard/fetchTopSellers',
  async ({ filters, limit = 10 }: { filters: DashboardFilters; limit?: number }, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getTopSellers(filters, limit)
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch top sellers')
    }
  }
)

export const fetchUserTypeDistribution = createAsyncThunk(
  'dashboard/fetchUserTypeDistribution',
  async (filters: DashboardFilters, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getUserTypeDistribution(filters)
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch user type distribution')
    }
  }
)

export const fetchTopItems = createAsyncThunk(
  'dashboard/fetchTopItems',
  async ({ filters, limit = 10 }: { filters: DashboardFilters; limit?: number }, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getTopItems(filters, limit)
      return {
        data: response.data.data,
        statistics: response.data.statistics,
      }
    } catch {
      return rejectWithValue('Failed to fetch top items')
    }
  }
)

export const fetchPriceDistribution = createAsyncThunk(
  'dashboard/fetchPriceDistribution',
  async (filters: DashboardFilters, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getPriceDistribution(filters)
      return {
        data: response.data.data,
        statistics: response.data.statistics,
      }
    } catch {
      return rejectWithValue('Failed to fetch price distribution')
    }
  }
)

export const fetchUserClassification = createAsyncThunk(
  'dashboard/fetchUserClassification',
  async (_, { rejectWithValue }) => {
    try {
      const response = await dashboardApi.getUserClassification()
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch user classification')
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
    // Summary
    builder.addCase(fetchSummary.pending, (state) => {
      state.loading.summary = true
    })
    builder.addCase(fetchSummary.fulfilled, (state, action) => {
      state.loading.summary = false
      state.summary = action.payload
    })
    builder.addCase(fetchSummary.rejected, (state, action) => {
      state.loading.summary = false
      state.error = action.payload as string
    })

    // Trends
    builder.addCase(fetchTrends.pending, (state) => {
      state.loading.trends = true
    })
    builder.addCase(fetchTrends.fulfilled, (state, action) => {
      state.loading.trends = false
      state.trends = action.payload.data
      state.trendStatistics = action.payload.statistics
    })
    builder.addCase(fetchTrends.rejected, (state, action) => {
      state.loading.trends = false
      state.error = action.payload as string
    })

    // Top Buyers
    builder.addCase(fetchTopBuyers.pending, (state) => {
      state.loading.topBuyers = true
    })
    builder.addCase(fetchTopBuyers.fulfilled, (state, action) => {
      state.loading.topBuyers = false
      state.topBuyers = action.payload
    })
    builder.addCase(fetchTopBuyers.rejected, (state, action) => {
      state.loading.topBuyers = false
      state.error = action.payload as string
    })

    // Top Sellers
    builder.addCase(fetchTopSellers.pending, (state) => {
      state.loading.topSellers = true
    })
    builder.addCase(fetchTopSellers.fulfilled, (state, action) => {
      state.loading.topSellers = false
      state.topSellers = action.payload
    })
    builder.addCase(fetchTopSellers.rejected, (state, action) => {
      state.loading.topSellers = false
      state.error = action.payload as string
    })

    // User Type Distribution
    builder.addCase(fetchUserTypeDistribution.pending, (state) => {
      state.loading.userTypeDistribution = true
    })
    builder.addCase(fetchUserTypeDistribution.fulfilled, (state, action) => {
      state.loading.userTypeDistribution = false
      state.userTypeDistribution = action.payload
    })
    builder.addCase(fetchUserTypeDistribution.rejected, (state, action) => {
      state.loading.userTypeDistribution = false
      state.error = action.payload as string
    })

    // Top Items
    builder.addCase(fetchTopItems.pending, (state) => {
      state.loading.topItems = true
    })
    builder.addCase(fetchTopItems.fulfilled, (state, action) => {
      state.loading.topItems = false
      state.topItems = action.payload.data
      state.topItemsStatistics = action.payload.statistics
    })
    builder.addCase(fetchTopItems.rejected, (state, action) => {
      state.loading.topItems = false
      state.error = action.payload as string
    })

    // Price Distribution
    builder.addCase(fetchPriceDistribution.pending, (state) => {
      state.loading.priceDistribution = true
    })
    builder.addCase(fetchPriceDistribution.fulfilled, (state, action) => {
      state.loading.priceDistribution = false
      state.priceDistribution = action.payload.data
      state.priceDistributionStatistics = action.payload.statistics
    })
    builder.addCase(fetchPriceDistribution.rejected, (state, action) => {
      state.loading.priceDistribution = false
      state.error = action.payload as string
    })

    // User Classification
    builder.addCase(fetchUserClassification.pending, (state) => {
      state.loading.userClassification = true
    })
    builder.addCase(fetchUserClassification.fulfilled, (state, action) => {
      state.loading.userClassification = false
      state.userClassification = action.payload
    })
    builder.addCase(fetchUserClassification.rejected, (state, action) => {
      state.loading.userClassification = false
      state.error = action.payload as string
    })
  },
})

export const { setFilters, clearError } = dashboardSlice.actions
export default dashboardSlice.reducer
