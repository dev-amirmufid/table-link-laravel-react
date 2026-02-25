import axios from 'axios'
import type {
  DashboardData,
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

// Dashboard API (public - no auth required)
export const dashboardApi = {
  getAll: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: DashboardData }>('/v1/dashboard', { params: filters }),

  getSummary: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: any }>('/v1/dashboard/summary', { params: filters }),

  getTrends: (filters?: DashboardFilters) =>
    api.get<{ 
      success: boolean; 
      data: any[]; 
      statistics: {
        total_transactions: number;
        total_revenue: number;
        avg_transactions: number;
        avg_revenue: number;
        period_count: number;
      }
    }>('/v1/dashboard/trends', { params: filters }),

  getTrendingItems: (filters?: DashboardFilters, limit = 10) =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/trending-items', {
      params: { ...filters, limit },
    }),

  getTopBuyers: (filters?: DashboardFilters, limit = 10) =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/top-buyers', {
      params: { ...filters, limit },
    }),

  getTopSellers: (filters?: DashboardFilters, limit = 10) =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/top-sellers', {
      params: { ...filters, limit },
    }),

  getUserTypeDistribution: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/user-type-distribution', {
      params: filters,
    }),

  getUserClassification: () =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/user-classification'),

  clearCache: () =>
    api.post<{ success: boolean; message: string }>('/v1/dashboard/cache/clear'),

  getTopItems: (filters?: DashboardFilters, limit = 10) =>
    api.get<{ 
      success: boolean; 
      data: any[];
      statistics: { total_revenue: number; total_transactions: number }
    }>('/v1/dashboard/top-items', {
      params: { ...filters, limit },
    }),

  getPriceDistribution: (filters?: DashboardFilters) =>
    api.get<{ 
      success: boolean; 
      data: any[];
      statistics: { total_transactions: number; total_revenue: number }
    }>('/v1/dashboard/price-distribution', {
      params: filters,
    }),
}

// Transaction API (public - no auth required)
export const transactionApi = {
  getAll: (filters?: DashboardFilters, perPage = 15, page = 1) =>
    api.get<{ success: boolean; data: PaginatedResponse<Transaction> }>('/v1/transactions', {
      params: { ...filters, per_page: perPage, page },
    }),

  getById: (id: string) =>
    api.get<{ success: boolean; data: Transaction }>(`/v1/transactions/${id}`),
}

export default api
