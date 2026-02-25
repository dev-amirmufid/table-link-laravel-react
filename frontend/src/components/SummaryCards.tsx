import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { formatCurrency, formatNumber } from '@/lib/utils'
import { DollarSign, ShoppingCart, Package, TrendingUp } from 'lucide-react'
import type { Summary } from '@/types'

interface SummaryCardsProps {
  data: Summary | null
  isLoading?: boolean
}

export function SummaryCards({ data, isLoading }: SummaryCardsProps) {
  if (isLoading) {
    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {[...Array(4)].map((_, i) => (
          <Card key={i}>
            <CardContent className="p-6">
              <div className="flex items-center justify-between space-x-4">
                <div className="space-y-2">
                  <Skeleton className="h-4 w-24" />
                  <Skeleton className="h-8 w-32" />
                  <Skeleton className="h-3 w-20" />
                </div>
                <Skeleton className="h-12 w-12 rounded-full" />
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    )
  }

  if (!data) {
    return null
  }

  const cards = [
    {
      title: 'Total Revenue',
      value: formatCurrency(data.total_revenue),
      icon: DollarSign,
      description: 'Total transaction revenue',
    },
    {
      title: 'Total Transactions',
      value: formatNumber(data.total_transactions),
      icon: ShoppingCart,
      description: 'Number of transactions',
    },
    {
      title: 'Total Quantity',
      value: formatNumber(data.total_quantity),
      icon: Package,
      description: 'Items traded',
    },
    {
      title: 'Average Price',
      value: formatCurrency(data.average_price),
      icon: TrendingUp,
      description: 'Average price per item',
    },
  ]

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      {cards.map((card) => (
        <Card key={card.title}>
          <CardContent className="p-6">
            <div className="flex items-center justify-between space-x-4">
              <div>
                <p className="text-sm font-medium text-muted-foreground">
                  {card.title}
                </p>
                <p className="text-2xl font-bold">{card.value}</p>
                <p className="text-xs text-muted-foreground mt-1">
                  {card.description}
                </p>
              </div>
              <div className="p-3 bg-primary/10 rounded-full">
                <card.icon className="h-6 w-6 text-primary" />
              </div>
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  )
}
