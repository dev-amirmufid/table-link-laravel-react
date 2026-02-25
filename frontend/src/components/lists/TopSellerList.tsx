import { formatCompactNumber } from '@/lib/utils'

interface TopSeller {
  id: string
  user_name: string
  transaction_count: number
  total_earned?: number
}

interface TopSellerListProps {
  data: TopSeller[]
}

export function TopSellerList({ data }: TopSellerListProps) {
  return (
    <div className="space-y-2">
      {data.slice(0, 5).map((seller, idx) => (
        <div key={idx} className="flex justify-between items-center p-3 border rounded-lg">
          <div className="flex items-center gap-3 flex-1 min-w-0">
            <div className="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
              <span className="text-sm font-bold text-green-600 dark:text-green-400">
                {idx + 1}
              </span>
            </div>
            <div className="truncate flex-1 mr-2">
              <div className="font-medium text-sm">{seller.user_name}</div>
              <div className="text-xs text-muted-foreground">
                {formatCompactNumber(seller.transaction_count)} transactions
              </div>
            </div>
          </div>
          <div className="text-right flex-shrink-0">
            <div className="font-bold text-green-600 dark:text-green-400">
              {seller.total_earned ? formatCompactNumber(seller.total_earned) : '-'}
            </div>
            <div className="text-xs text-muted-foreground">earned</div>
          </div>
        </div>
      ))}
    </div>
  )
}
