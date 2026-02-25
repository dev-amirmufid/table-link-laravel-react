import { useState } from 'react'
import { format } from 'date-fns'
import { formatCurrency } from '@/lib/utils'
import type { Transaction } from '@/types'

interface DataTableProps {
  data: Transaction[]
  isLoading?: boolean
}

export function DataTable({ data, isLoading }: DataTableProps) {
  const [sortField, setSortField] = useState<keyof Transaction>('created_at')
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('desc')

  const handleSort = (field: keyof Transaction) => {
    if (sortField === field) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')
    } else {
      setSortField(field)
      setSortOrder('asc')
    }
  }

  const sortedData = [...data].sort((a, b) => {
    const aVal = a[sortField]
    const bVal = b[sortField]
    if (aVal === undefined || bVal === undefined) return 0
    if (aVal === bVal) return 0
    if (sortOrder === 'asc') {
      return aVal < bVal ? -1 : 1
    }
    return aVal > bVal ? -1 : 1
  })

  if (isLoading) {
    return (
      <div className="rounded-md border">
        <div className="p-4 text-center text-muted-foreground">Loading...</div>
      </div>
    )
  }

  return (
    <div className="rounded-md border">
      <table className="w-full">
        <thead className="bg-muted">
          <tr>
            <th
              className="p-3 text-left cursor-pointer hover:bg-muted/80"
              onClick={() => handleSort('created_at')}
            >
              Date {sortField === 'created_at' && (sortOrder === 'asc' ? '↑' : '↓')}
            </th>
            <th className="p-3 text-left">Buyer</th>
            <th className="p-3 text-left">Seller</th>
            <th className="p-3 text-left">Item</th>
            <th
              className="p-3 text-left cursor-pointer hover:bg-muted/80"
              onClick={() => handleSort('quantity')}
            >
              Quantity {sortField === 'quantity' && (sortOrder === 'asc' ? '↑' : '↓')}
            </th>
            <th
              className="p-3 text-left cursor-pointer hover:bg-muted/80"
              onClick={() => handleSort('price')}
            >
              Price {sortField === 'price' && (sortOrder === 'asc' ? '↑' : '↓')}
            </th>
            <th className="p-3 text-left">Total</th>
          </tr>
        </thead>
        <tbody>
          {sortedData.map((transaction) => (
            <tr key={transaction.id} className="border-t hover:bg-muted/50">
              <td className="p-3">
                {format(new Date(transaction.created_at), 'yyyy-MM-dd HH:mm')}
              </td>
              <td className="p-3">{transaction.buyer?.name || '-'}</td>
              <td className="p-3">{transaction.seller?.name || '-'}</td>
              <td className="p-3">{transaction.item?.item_name || '-'}</td>
              <td className="p-3">{transaction.quantity}</td>
              <td className="p-3">{formatCurrency(transaction.price)}</td>
              <td className="p-3">{formatCurrency(transaction.quantity * transaction.price)}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {data.length === 0 && (
        <div className="p-4 text-center text-muted-foreground">No data available</div>
      )}
    </div>
  )
}
