import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'
import { formatCurrency, formatNumber } from '@/lib/utils'

interface PriceDistribution {
  range: string
  count: number
  revenue: number
}

interface PriceDistributionChartProps {
  data: PriceDistribution[]
  statistics?: {
    total_transactions: number
    total_revenue: number
  } | null
  title?: string
}

// Custom tooltip - declared outside to avoid re-creation
function CustomTooltip({ active, payload }: { active?: boolean; payload?: Array<{ payload: { range: string; count: number; revenue: number } }> }) {
  if (active && payload && payload.length) {
    const data = payload[0].payload
    return (
      <div className="bg-background border border-border rounded-lg p-3 shadow-lg">
        <p className="font-semibold mb-2">{data.range}</p>
        <div className="space-y-1">
          <p className="text-sm">
            <span className="text-muted-foreground">Transactions:</span>{' '}
            <span className="font-medium">{formatNumber(data.count)}</span>
          </p>
          <p className="text-sm">
            <span className="text-muted-foreground">Revenue:</span>{' '}
            <span className="font-medium">{formatCurrency(data.revenue)}</span>
          </p>
        </div>
      </div>
    )
  }
  return null
}

export function PriceDistributionChart({ data, title }: PriceDistributionChartProps) {
  const chartData = data.map(item => ({
    range: item.range.replace('Rp ', '').replace(' - ', '\n- '),
    count: item.count,
    revenue: item.revenue,
  }))

  return (
    <div className="w-full">
      {title && <h3 className="text-lg font-semibold mb-4">{title}</h3>}
      
      <div className="h-[300px]">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart
            data={chartData}
            margin={{ top: 20, right: 30, left: 20, bottom: 40 }}
          >
            <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
            <XAxis
              dataKey="range"
              tick={{ fontSize: 10 }}
              className="fill-muted-foreground"
              tickLine={false}
              interval={0}
              height={60}
            />
            <YAxis
              yAxisId="left"
              tick={{ fontSize: 11 }}
              className="fill-muted-foreground"
              tickFormatter={(value) => formatNumber(value)}
            />
            <YAxis
              yAxisId="right"
              orientation="right"
              tick={{ fontSize: 11 }}
              className="fill-muted-foreground"
              tickFormatter={(value) => `Rp ${(value / 1000000).toFixed(1)}M`}
            />
            <Tooltip content={<CustomTooltip />} />
            <Bar yAxisId="left" dataKey="count" name="Transactions" fill="#8884d8" radius={[4, 4, 0, 0]} />
            <Bar yAxisId="right" dataKey="revenue" name="Revenue" fill="#82ca9d" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}
