import axios from 'axios'
import type {
  AuthResponse,
  AuthUser,
  DashboardData,
  DashboardFilters,
  LoginRequest,
  MeResponse,
  PaginatedResponse,
  RegisterRequest,
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

// Token storage keys
const ACCESS_TOKEN_KEY = 'access_token'
const REFRESH_TOKEN_KEY = 'refresh_token'
const USER_KEY = 'user'

// Get token from storage
export const getAccessToken = (): string | null => {
  return localStorage.getItem(ACCESS_TOKEN_KEY)
}

// Set tokens to storage
export const setTokens = (accessToken: string, refreshToken?: string): void => {
  localStorage.setItem(ACCESS_TOKEN_KEY, accessToken)
  if (refreshToken) {
    localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken)
  }
}

// Remove tokens from storage
export const removeTokens = (): void => {
  localStorage.removeItem(ACCESS_TOKEN_KEY)
  localStorage.removeItem(REFRESH_TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

// Set user to storage
export const setUser = (user: AuthUser): void => {
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

// Get user from storage
export const getUser = (): AuthUser | null => {
  const userStr = localStorage.getItem(USER_KEY)
  return userStr ? JSON.parse(userStr) : null
}

// Remove user from storage
export const removeUser = (): void => {
  localStorage.removeItem(USER_KEY)
}

// Setup axios interceptor for JWT
api.interceptors.request.use(
  (config) => {
    const token = getAccessToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config
    
    // If 401 error and not already retrying
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      
      try {
        // JWT refresh doesn't require refresh token - it uses current token
        const response = await axios.post(`${API_URL}/auth/refresh`)
        
        const { token } = response.data.data
        const currentToken = getAccessToken()
        setTokens(token, currentToken || undefined)
        
        originalRequest.headers.Authorization = `Bearer ${token}`
        return api(originalRequest)
      } catch (refreshError) {
        // Refresh failed, logout user
        removeTokens()
        removeUser()
        window.location.href = '/login'
        return Promise.reject(refreshError)
      }
    }
    
    return Promise.reject(error)
  }
)

// Auth API (no v1 prefix)
export const authApi = {
  login: (data: LoginRequest) =>
    api.post<AuthResponse>('/auth/login', data),
  
  register: (data: RegisterRequest) =>
    api.post<AuthResponse>('/auth/register', data),
  
  logout: () =>
    api.post('/auth/logout'),
  
  me: () =>
    api.get<MeResponse>('/auth/me'),
  
  refresh: () =>
    api.post<AuthResponse>('/auth/refresh'),
}

// Dashboard API (v1 prefix)
export const dashboardApi = {
  getAll: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: DashboardData }>('/v1/dashboard', { params: filters }),

  getSummary: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: any }>('/v1/dashboard/summary', { params: filters }),

  getTrends: (filters?: DashboardFilters) =>
    api.get<{ success: boolean; data: any[] }>('/v1/dashboard/trends', { params: filters }),

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
}

// Transaction API (v1 prefix)
export const transactionApi = {
  getAll: (filters?: DashboardFilters, perPage = 15) =>
    api.get<{ success: boolean; data: PaginatedResponse<Transaction> }>('/v1/transactions', {
      params: { ...filters, per_page: perPage },
    }),

  getById: (id: string) =>
    api.get<{ success: boolean; data: Transaction }>(`/v1/transactions/${id}`),
}

export default api
