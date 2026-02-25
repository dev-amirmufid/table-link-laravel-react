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
import { formatCurrency, formatNumber } from '@/lib/utils'

interface ItemPerformance {
  id: string
  item_code: string
  item_name: string
  transaction_count: number
  total_quantity: number
  total_revenue: number
}

interface ItemPerformanceChartProps {
  data: ItemPerformance[]
  statistics?: {
    total_revenue: number
    total_transactions: number
  } | null
  title?: string
}

// Custom tooltip - declared outside to avoid re-creation
function CustomTooltip({ active, payload }: { active?: boolean; payload?: Array<{ payload: { fullName: string; itemCode: string; revenue: number; transactions: number } }> }) {
  if (active && payload && payload.length) {
    const data = payload[0].payload
    return (
      <div className="bg-background border border-border rounded-lg p-3 shadow-lg">
        <p className="font-semibold mb-1">{data.fullName}</p>
        <p className="text-xs text-muted-foreground mb-2">Code: {data.itemCode}</p>
        <div className="space-y-1">
          <p className="text-sm">
            <span className="text-muted-foreground">Revenue:</span>{' '}
            <span className="font-medium">{formatCurrency(data.revenue)}</span>
          </p>
          <p className="text-sm">
            <span className="text-muted-foreground">Transactions:</span>{' '}
            <span className="font-medium">{formatNumber(data.transactions)}</span>
          </p>
        </div>
      </div>
    )
  }
  return null
}

export function ItemPerformanceChart({ data, title }: ItemPerformanceChartProps) {
  const chartData = data.map(item => ({
    name: item.item_name.length > 15 ? item.item_name.substring(0, 15) + '...' : item.item_name,
    revenue: item.total_revenue,
    transactions: item.transaction_count,
    fullName: item.item_name,
    itemCode: item.item_code,
  }))

  const colors = ['#8884d8', '#82ca9d', '#ffc658', '#ff7300', '#0088FE', '#00C49F', '#FFBB28', '#FF8042']

  return (
    <div className="w-full">
      {title && <h3 className="text-lg font-semibold mb-4">{title}</h3>}
      

      <div className="h-[300px]">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart
            data={chartData}
            layout="vertical"
            margin={{ top: 5, right: 30, left: 80, bottom: 5 }}
          >
            <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
            <XAxis
              type="number"
              tick={{ fontSize: 11 }}
              className="fill-muted-foreground"
              tickFormatter={(value) => `Rp ${(value / 1000000).toFixed(1)}M`}
            />
            <YAxis
              type="category"
              dataKey="itemCode"
              tick={{ fontSize: 11 }}
              className="fill-muted-foreground"
              width={80}
            />
            <Tooltip content={<CustomTooltip />} />
            <Bar dataKey="revenue" name="Revenue" radius={[0, 4, 4, 0]}>
              {chartData.map((_, index) => (
                <Cell key={`cell-${index}`} fill={colors[index % colors.length]} />
              ))}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}
