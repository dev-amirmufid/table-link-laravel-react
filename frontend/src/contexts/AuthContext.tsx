import { createContext, useContext, useEffect, useState, type ReactNode } from 'react'
import { authApi, getAccessToken, getUser, removeTokens, removeUser, setTokens, setUser as setUserToStorage } from '@/lib/api'
import type { AuthUser, LoginRequest, RegisterRequest } from '@/types'

interface AuthContextType {
  user: AuthUser | null
  isLoading: boolean
  isAuthenticated: boolean
  login: (data: LoginRequest) => Promise<void>
  register: (data: RegisterRequest) => Promise<void>
  logout: () => Promise<void>
  error: string | null
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  // Check auth status on mount
  useEffect(() => {
    const checkAuth = async () => {
      const token = getAccessToken()
      const storedUser = getUser()

      if (token && storedUser) {
        setUser(storedUser)
        // Optionally verify token is still valid
        try {
          const response = await authApi.me()
          setUser(response.data.data)
          setUserToStorage(response.data.data)
        } catch (err) {
          // Token invalid, clear auth
          removeTokens()
          removeUser()
          setUser(null)
        }
      }
      setIsLoading(false)
    }

    checkAuth()
  }, [])

  const login = async (data: LoginRequest) => {
    setError(null)
    setIsLoading(true)
    try {
      const response = await authApi.login(data)
      const { user: userData, access_token } = response.data.data

      setTokens(access_token)
      setUserToStorage(userData)
      setUser(userData)
    } catch (err: any) {
      const message = err.response?.data?.message || err.message || 'Login failed'
      setError(message)
      throw new Error(message)
    } finally {
      setIsLoading(false)
    }
  }

  const register = async (data: RegisterRequest) => {
    setError(null)
    setIsLoading(true)
    try {
      const response = await authApi.register(data)
      const { user: userData, access_token } = response.data.data

      setTokens(access_token)
      setUserToStorage(userData)
      setUser(userData)
    } catch (err: any) {
      const message = err.response?.data?.message || err.message || 'Registration failed'
      setError(message)
      throw new Error(message)
    } finally {
      setIsLoading(false)
    }
  }

  const logout = async () => {
    setIsLoading(true)
    try {
      await authApi.logout()
    } catch (err) {
      // Ignore logout errors
      console.error('Logout error:', err)
    } finally {
      removeTokens()
      removeUser()
      setUser(null)
      setIsLoading(false)
    }
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        isAuthenticated: !!user,
        login,
        register,
        logout,
        error,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}
