export interface User {
  id: string
  name: string
  email: string
  type: 'foreign' | 'domestic'
}

export interface Item {
  id: string
  item_code: string
  item_name: string
  minimum_price: number
  maximum_price: number
}

export interface Transaction {
  id: string
  buyer_id: string
  seller_id: string
  item_id: string
  quantity: number
  price: number
  created_at: string
  updated_at: string
  buyer?: User
  seller?: User
  item?: Item
}

export interface Summary {
  total_transactions: number
  total_quantity: number
  total_revenue: number
  active_buyers: number
  active_sellers: number
  avg_transaction_price: number
}

export interface Trend {
  period: string
  transactions: number
  revenue: number
}

export interface TrendingItem {
  id: string
  item_code: string
  item_name: string
  transactions_count: number
}

export interface TopUser {
  buyer_id?: string
  seller_id?: string
  transaction_count: number
  total_spent?: number
  total_earned?: number
  buyer?: User
  seller?: User
}

export interface UserTypeDistribution {
  type?: string
  name?: string
  count?: number
  value?: number
  revenue?: number
}

export interface DashboardFilters {
  start_date?: string
  end_date?: string
  period?: 'daily' | 'weekly' | 'monthly'
  user_type?: string
  item_id?: string
  buyer_id?: string
  seller_id?: string
}

export interface DashboardData {
  summary: Summary
  trends: Trend[]
  trending_items: TrendingItem[]
  top_buyers: TopUser[]
  top_sellers: TopUser[]
  user_type_distribution: UserTypeDistribution[]
  user_classification: UserTypeDistribution[]
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Auth Types
export interface AuthUser {
  id: string
  name: string
  email: string
  type: 'foreign' | 'domestic'
  created_at: string
  updated_at: string
}

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  success: boolean
  message: string
  data: {
    user: AuthUser
    access_token: string
    token_type: string
    expires_in: number
  }
}

export interface MeResponse {
  success: boolean
  data: AuthUser
}
