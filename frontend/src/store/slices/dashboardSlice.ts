import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import type { PayloadAction } from '@reduxjs/toolkit'
import { dashboardApi } from '@/lib/api'
import type { DashboardFilters, Summary, Trend, TopUser, UserTypeDistribution } from '@/types'

interface DashboardState {
  summary: Summary | null
  trends: Trend[]
  topBuyers: TopUser[]
  topSellers: TopUser[]
  userTypeDistribution: UserTypeDistribution[]
  filters: DashboardFilters
  loading: {
    summary: boolean
    trends: boolean
    topBuyers: boolean
    topSellers: boolean
    userTypeDistribution: boolean
  }
  error: string | null
}

const initialState: DashboardState = {
  summary: null,
  trends: [],
  topBuyers: [],
  topSellers: [],
  userTypeDistribution: [],
  filters: { period: 'daily' },
  loading: {
    summary: true,
    trends: true,
    topBuyers: true,
    topSellers: true,
    userTypeDistribution: true,
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
      return response.data.data
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
      state.trends = action.payload
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
  },
})

export const { setFilters, clearError } = dashboardSlice.actions
export default dashboardSlice.reducer
