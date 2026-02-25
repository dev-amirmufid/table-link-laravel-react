import axios from 'axios'
import type {
  DashboardFilters,
  PaginatedResponse,
  Transaction,
} from '@/types'

const API_URL = import.meta.env.VITE_API_URL || '/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Dashboard Analytics API - GOD QUERY
export const analyticsApi = {
  getAnalytics: (
    filters?: DashboardFilters,
    options?: {
      include?: string
      period?: string
      limit?: number
    }
  ) =>
    api.get<{
      success: boolean
      data: {
        summary?: any
        trends?: any[]
        trending_items?: any[]
        user_classification?: any[]
        top_buyers?: any[]
        top_sellers?: any[]
        revenue_contribution?: any[]
      }
      period: string
      limit: number
      include: string[]
      cached: boolean
    }>('/v1/dashboard/analytics', {
      params: {
        ...filters,
        include: options?.include || 'summary,trends,trending_items,user_classification,top_buyers,top_sellers,revenue_contribution',
        period: options?.period || 'daily',
        limit: options?.limit || 10,
      },
    }),

  clearCache: () =>
    api.post<{ success: boolean; message: string }>('/v1/dashboard/cache/clear'),
}

// Transaction API
export const transactionApi = {
  getAll: (filters?: DashboardFilters, perPage = 15, page = 1, search = '') =>
    api.get<{ success: boolean; data: PaginatedResponse<Transaction> }>('/v1/transactions', {
      params: { ...filters, per_page: perPage, page, search },
    }),

  getById: (id: string) =>
    api.get<{ success: boolean; data: Transaction }>(`/v1/transactions/${id}`),
}

export default api
