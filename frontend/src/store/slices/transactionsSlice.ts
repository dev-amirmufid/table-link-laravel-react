import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import { transactionApi } from '@/lib/api'
import type { Transaction, DashboardFilters } from '@/types'

interface TransactionsState {
  items: Transaction[]
  currentPage: number
  totalPages: number
  total: number
  perPage: number
  search: string
  sortField: keyof Transaction | ''
  sortOrder: 'asc' | 'desc'
  selectedTransaction: Transaction | null
  filters: DashboardFilters
  loading: boolean
  error: string | null
}

const initialState: TransactionsState = {
  items: [],
  currentPage: 1,
  totalPages: 1,
  total: 0,
  perPage: 15,
  search: '',
  sortField: '',
  sortOrder: 'desc',
  selectedTransaction: null,
  filters: {},
  loading: false,
  error: null,
}

export const fetchTransactions = createAsyncThunk(
  'transactions/fetch',
  async ({ 
    filters, 
    page = 1, 
    perPage = 15,
    search = '',
    sortField = '',
    sortOrder = 'desc'
  }: { 
    filters?: DashboardFilters
    page?: number
    perPage?: number
    search?: string
    sortField?: string
    sortOrder?: 'asc' | 'desc'
  }, { rejectWithValue }) => {
    try {
      // Build search params - in a real app, you'd have a search endpoint
      // For now, we'll filter after fetching or pass it to backend if supported
      const searchFilters = search ? { ...filters, search } : filters
      const response = await transactionApi.getAll(searchFilters, perPage, page)
      return {
        ...response.data.data,
        search,
        sortField,
        sortOrder,
      }
    } catch {
      return rejectWithValue('Failed to fetch transactions')
    }
  }
)

export const fetchTransactionById = createAsyncThunk(
  'transactions/fetchById',
  async (id: string, { rejectWithValue }) => {
    try {
      const response = await transactionApi.getById(id)
      return response.data.data
    } catch {
      return rejectWithValue('Failed to fetch transaction details')
    }
  }
)

const transactionsSlice = createSlice({
  name: 'transactions',
  initialState,
  reducers: {
    setSearch: (state, action: PayloadAction<string>) => {
      state.search = action.payload
      state.currentPage = 1
    },
    setSort: (state, action: PayloadAction<{ field: keyof Transaction | ''; order: 'asc' | 'desc' }>) => {
      state.sortField = action.payload.field
      state.sortOrder = action.payload.order
      state.currentPage = 1
    },
    setPage: (state, action: PayloadAction<number>) => {
      state.currentPage = action.payload
    },
    setPerPage: (state, action: PayloadAction<number>) => {
      state.perPage = action.payload
      state.currentPage = 1
    },
    setFilters: (state, action: PayloadAction<DashboardFilters>) => {
      state.filters = action.payload
      state.currentPage = 1
    },
    setSelectedTransaction: (state, action: PayloadAction<Transaction | null>) => {
      state.selectedTransaction = action.payload
    },
    clearError: (state) => {
      state.error = null
    },
  },
  extraReducers: (builder) => {
    // Fetch transactions
    builder.addCase(fetchTransactions.pending, (state) => {
      state.loading = true
      state.error = null
    })
    builder.addCase(fetchTransactions.fulfilled, (state, action) => {
      state.loading = false
      state.items = action.payload.data
      state.currentPage = action.payload.current_page
      state.totalPages = action.payload.last_page
      state.total = action.payload.total
      if (action.payload.search !== undefined) state.search = action.payload.search
      if (action.payload.sortField !== undefined) state.sortField = action.payload.sortField as keyof Transaction | ''
      if (action.payload.sortOrder !== undefined) state.sortOrder = action.payload.sortOrder
    })
    builder.addCase(fetchTransactions.rejected, (state, action) => {
      state.loading = false
      state.error = action.payload as string
    })

    // Fetch transaction by ID
    builder.addCase(fetchTransactionById.pending, (state) => {
      state.loading = true
    })
    builder.addCase(fetchTransactionById.fulfilled, (state, action) => {
      state.loading = false
      state.selectedTransaction = action.payload
    })
    builder.addCase(fetchTransactionById.rejected, (state, action) => {
      state.loading = false
      state.error = action.payload as string
    })
  },
})

export const { 
  setSearch, 
  setSort, 
  setPage, 
  setPerPage, 
  setFilters, 
  setSelectedTransaction,
  clearError 
} = transactionsSlice.actions

export default transactionsSlice.reducer

// Helper type for PayloadAction
import type { PayloadAction } from '@reduxjs/toolkit'
