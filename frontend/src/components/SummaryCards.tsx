import { Card, CardContent } from '@/components/ui/card'
import { formatCurrency, formatNumber } from '@/lib/utils'
import { DollarSign, ShoppingCart, Package, TrendingUp } from 'lucide-react'
import type { Summary } from '@/types'

interface SummaryCardsProps {
  data: Summary
}

export function SummaryCards({ data }: SummaryCardsProps) {
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
