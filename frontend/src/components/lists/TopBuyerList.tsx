import { formatCompactNumber } from '@/lib/utils'

interface TopBuyer {
  id: string
  user_name: string
  transaction_count: number
  total_spent?: number
}

interface TopBuyerListProps {
  data: TopBuyer[]
}

export function TopBuyerList({ data }: TopBuyerListProps) {
  return (
    <div className="space-y-2">
      {data.slice(0, 5).map((buyer, idx) => (
        <div key={idx} className="flex justify-between items-center p-3 border rounded-lg">
          <div className="flex items-center gap-3 flex-1 min-w-0">
            <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
              <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                {idx + 1}
              </span>
            </div>
            <div className="truncate flex-1 mr-2">
              <div className="font-medium text-sm">{buyer.user_name}</div>
              <div className="text-xs text-muted-foreground">
                {formatCompactNumber(buyer.transaction_count)} transactions
              </div>
            </div>
          </div>
          <div className="text-right flex-shrink-0">
            <div className="font-bold text-blue-600 dark:text-blue-400">
              {buyer.total_spent ? formatCompactNumber(buyer.total_spent) : '-'}
            </div>
            <div className="text-xs text-muted-foreground">spent</div>
          </div>
        </div>
      ))}
    </div>
  )
}
