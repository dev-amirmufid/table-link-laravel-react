import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Cell,
} from 'recharts'
import { formatCompactNumber } from '@/lib/utils'

interface TrendingItem {
  id: string
  item_code: string
  item_name: string
  total_quantity: number
  total_revenue: number
}

interface TrendingItemsChartProps {
  data: TrendingItem[]
}

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1']

export function TrendingItemsChart({ data }: TrendingItemsChartProps) {
  const chartData = data.slice(0, 10).map((item, index) => ({
    name: item.item_name.length > 15 ? item.item_name.substring(0, 15) + '...' : item.item_name,
    quantity: Number(item.total_quantity),
    revenue: Number(item.total_revenue),
    color: COLORS[index % COLORS.length],
    fullName: item.item_name,
  }))

  return (
    <div className="w-full h-[300px]">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData} layout="vertical">
          <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
          <XAxis
            type="number"
            tick={{ fontSize: 12 }}
            tickFormatter={(value) => formatCompactNumber(value)}
          />
          <YAxis
            type="category"
            dataKey="name"
            width={120}
            tick={{ fontSize: 11 }}
          />
          <Tooltip
            formatter={(value, name) => {
              return [formatCompactNumber(Number(value)), name === 'revenue' ? 'Revenue' : 'Quantity Sold']
            }}
            labelFormatter={(_, payload) => {
              return payload[0]?.payload?.fullName || ''
            }}
          />
          <Bar dataKey="quantity" name="Quantity Sold" radius={[0, 4, 4, 0]}>
            {chartData.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={entry.color} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}
