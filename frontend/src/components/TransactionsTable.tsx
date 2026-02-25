import { useEffect, useMemo, useState, useRef } from 'react'
import { format } from 'date-fns'
import { useAppDispatch, useAppSelector } from '@/store/hooks'
import { 
  fetchTransactions, 
  setSearch, 
  setSort, 
  setPage, 
  setPerPage,
  setSelectedTransaction
} from '@/store/slices/transactionsSlice'
import { formatCurrency } from '@/lib/utils'
import { Skeleton } from '@/components/ui/skeleton'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { 
  ChevronLeft, 
  ChevronRight, 
  Search, 
  Eye,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  X
} from 'lucide-react'
import type { Transaction, DashboardFilters } from '@/types'

interface TransactionsTableProps {
  filters?: DashboardFilters
}

// Modal Component
function TransactionModal({ 
  transaction, 
  onClose 
}: { 
  transaction: Transaction | null
  onClose: () => void 
}) {
  if (!transaction) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="bg-background rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-auto">
        <div className="flex items-center justify-between p-4 border-b">
          <h2 className="text-lg font-semibold">Transaction Details</h2>
          <Button variant="ghost" size="icon" onClick={onClose}>
            <X className="h-4 w-4" />
          </Button>
        </div>
        <div className="p-4 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm text-muted-foreground">Transaction ID</label>
              <p className="font-mono text-sm">{transaction.id}</p>
            </div>
            <div>
              <label className="text-sm text-muted-foreground">Date</label>
              <p>{format(new Date(transaction.created_at), 'yyyy-MM-dd HH:mm:ss')}</p>
            </div>
          </div>
          
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm text-muted-foreground">Buyer</label>
              <p className="font-medium">{transaction.buyer?.name || '-'}</p>
              <p className="text-sm text-muted-foreground">{transaction.buyer?.email || '-'}</p>
              <p className="text-xs text-muted-foreground">Type: {transaction.buyer?.type || '-'}</p>
            </div>
            <div>
              <label className="text-sm text-muted-foreground">Seller</label>
              <p className="font-medium">{transaction.seller?.name || '-'}</p>
              <p className="text-sm text-muted-foreground">{transaction.seller?.email || '-'}</p>
              <p className="text-xs text-muted-foreground">Type: {transaction.seller?.type || '-'}</p>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm text-muted-foreground">Item</label>
              <p className="font-medium">{transaction.item?.item_name || '-'}</p>
              <p className="text-sm text-muted-foreground">Code: {transaction.item?.item_code || '-'}</p>
            </div>
            <div>
              <label className="text-sm text-muted-foreground">Pricing</label>
              <p>Price: {formatCurrency(transaction.price)}</p>
              <p>Quantity: {transaction.quantity.toLocaleString('id-ID')}</p>
              <p className="font-semibold">Total: {formatCurrency(transaction.quantity * transaction.price)}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export function TransactionsTable({ filters: externalFilters }: TransactionsTableProps) {
  const dispatch = useAppDispatch()
  const { 
    items, 
    currentPage, 
    totalPages, 
    total, 
    perPage, 
    search, 
    sortField, 
    sortOrder,
    selectedTransaction,
    loading
  } = useAppSelector((state) => state.transactions)
  
  // Use filters from props (unified with dashboard) or default to empty
  const effectiveFilters = externalFilters ?? {}
  
  // Local state for debounced search
  const [searchInput, setSearchInput] = useState(search)
  
  const fetchTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  // Debounce search
  useEffect(() => {
    const timer = setTimeout(() => {
      if (search !== searchInput) {
        dispatch(setSearch(searchInput))
        dispatch(setPage(1))
      }
    }, 500)

    return () => clearTimeout(timer)
  }, [searchInput, search, dispatch])

  // Fetch transactions when parameters change - with debounce to prevent rapid clicks
  useEffect(() => {
    // Clear existing timer if any
    if (fetchTimerRef.current) {
      clearTimeout(fetchTimerRef.current)
    }
    
    // Debounce the fetch to handle rapid pagination/filter clicks
    fetchTimerRef.current = setTimeout(() => {
      dispatch(fetchTransactions({ 
        filters: effectiveFilters, 
        page: currentPage, 
        perPage, 
        search,
        sortField: sortField as string,
        sortOrder 
      }))
    }, 300)
    
    return () => {
      if (fetchTimerRef.current) {
        clearTimeout(fetchTimerRef.current)
      }
    }
  }, [dispatch, currentPage, perPage, search, sortField, sortOrder, effectiveFilters])

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchInput(e.target.value)
  }

  const handleSort = (field: keyof Transaction) => {
    const newOrder = sortField === field && sortOrder === 'desc' ? 'asc' : 'desc'
    dispatch(setSort({ field, order: newOrder }))
  }

  const handlePageChange = (page: number) => {
    if (page >= 1 && page <= totalPages) {
      dispatch(setPage(page))
    }
  }

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    dispatch(setPerPage(Number(e.target.value)))
  }

  const handleViewDetail = (transaction: Transaction) => {
    dispatch(setSelectedTransaction(transaction))
  }

  const handleCloseModal = () => {
    dispatch(setSelectedTransaction(null))
  }

  const getSortIcon = (field: keyof Transaction) => {
    if (sortField !== field) return <ArrowUpDown className="h-4 w-4 ml-1 inline" />
    return sortOrder === 'asc' 
      ? <ArrowUp className="h-4 w-4 ml-1 inline" />
      : <ArrowDown className="h-4 w-4 ml-1 inline" />
  }

  // Sort items client-side
  const sortedItems = useMemo(() => {
    if (!sortField) return items
    return [...items].sort((a, b) => {
      const aVal = a[sortField as keyof Transaction]
      const bVal = b[sortField as keyof Transaction]
      if (aVal === undefined || bVal === undefined) return 0
      if (aVal === bVal) return 0
      const comparison = aVal < bVal ? -1 : 1
      return sortOrder === 'asc' ? comparison : -comparison
    })
  }, [items, sortField, sortOrder])

  // Skeleton row component
  const SkeletonRow = () => (
    <tr className="border-t">
      <td className="p-3"><Skeleton className="h-4 w-32" /></td>
      <td className="p-3"><Skeleton className="h-4 w-40" /></td>
      <td className="p-3"><Skeleton className="h-4 w-40" /></td>
      <td className="p-3"><Skeleton className="h-4 w-24" /></td>
      <td className="p-3"><Skeleton className="h-4 w-16" /></td>
      <td className="p-3"><Skeleton className="h-4 w-24" /></td>
      <td className="p-3"><Skeleton className="h-4 w-24" /></td>
      <td className="p-3"><Skeleton className="h-8 w-8" /></td>
    </tr>
  )

  return (
    <div className="space-y-4">
      {/* Search and Controls */}
      <div className="flex items-center justify-between gap-4">
        <div className="relative flex-1 max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search transactions..."
            value={searchInput}
            onChange={handleSearchChange}
            className="pl-9"
          />
        </div>
        <select 
          value={perPage} 
          onChange={handlePerPageChange}
          className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          <option value={15}>15 per page</option>
          <option value={25}>25 per page</option>
          <option value={50}>50 per page</option>
          <option value={100}>100 per page</option>
        </select>
      </div>

      {/* Table */}
      <div className="rounded-md border">
        <table className="w-full">
          <thead className="bg-muted">
            <tr>
              <th 
                className="p-3 text-left cursor-pointer hover:bg-muted/80"
                onClick={() => handleSort('created_at')}
              >
                Date {getSortIcon('created_at')}
              </th>
              <th className="p-3 text-left">Buyer</th>
              <th className="p-3 text-left">Seller</th>
              <th className="p-3 text-left">Item</th>
              <th 
                className="p-3 text-left cursor-pointer hover:bg-muted/80"
                onClick={() => handleSort('quantity')}
              >
                Quantity {getSortIcon('quantity')}
              </th>
              <th 
                className="p-3 text-left cursor-pointer hover:bg-muted/80"
                onClick={() => handleSort('price')}
              >
                Price {getSortIcon('price')}
              </th>
              <th className="p-3 text-left">Total</th>
              <th className="p-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              [...Array(perPage)].map((_, i) => (
                <SkeletonRow key={i} />
              ))
            ) : sortedItems.length === 0 ? (
              <tr>
                <td colSpan={8} className="p-8 text-center text-muted-foreground">
                  No transactions found
                </td>
              </tr>
            ) : (
              sortedItems.map((transaction) => (
                <tr key={transaction.id} className="border-t hover:bg-muted/50">
                  <td className="p-3 text-sm">
                    {format(new Date(transaction.created_at), 'yyyy-MM-dd HH:mm')}
                  </td>
                  <td className="p-3 text-sm">{transaction.buyer?.name || '-'}</td>
                  <td className="p-3 text-sm">{transaction.seller?.name || '-'}</td>
                  <td className="p-3 text-sm">{transaction.item?.item_name || '-'}</td>
                  <td className="p-3 text-sm">{transaction.quantity.toLocaleString('id-ID')}</td>
                  <td className="p-3 text-sm">{formatCurrency(transaction.price)}</td>
                  <td className="p-3 text-sm font-medium">
                    {formatCurrency(transaction.quantity * transaction.price)}
                  </td>
                  <td className="p-3 text-center">
                    <Button 
                      variant="ghost" 
                      size="icon"
                      onClick={() => handleViewDetail(transaction)}
                      title="View details"
                    >
                      <Eye className="h-4 w-4" />
                    </Button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {total > 0 && (
        <div className="flex items-center justify-between">
          <div className="text-sm text-muted-foreground">
            Showing {((currentPage - 1) * perPage) + 1} to {Math.min(currentPage * perPage, total)} of {total.toLocaleString('id-ID')} results
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <div className="text-sm">
              Page {currentPage} of {totalPages}
            </div>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === totalPages}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        </div>
      )}

      {/* Detail Modal */}
      <TransactionModal 
        transaction={selectedTransaction} 
        onClose={handleCloseModal} 
      />
    </div>
  )
}
