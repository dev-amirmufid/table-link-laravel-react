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

interface RevenueContribution {
  id: string
  name: string
  revenue: number
  percentage: number
}

interface RevenueContributionChartProps {
  data: RevenueContribution[]
}

const COLORS = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1']

export function RevenueContributionChart({ data }: RevenueContributionChartProps) {
  const chartData = data.slice(0, 10).map((item, index) => ({
    name: item.name.length > 12 ? item.name.substring(0, 12) + '...' : item.name,
    revenue: Number(item.revenue),
    percentage: item.percentage,
    color: COLORS[index % COLORS.length],
    fullName: item.name,
  }))

  return (
    <div className="w-full h-[300px]">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
          <XAxis
            dataKey="name"
            tick={{ fontSize: 11 }}
            angle={-45}
            textAnchor="end"
            height={80}
          />
          <YAxis
            tick={{ fontSize: 12 }}
            tickFormatter={(value) => formatCompactNumber(value)}
          />
          <Tooltip
            formatter={(value, _name, props) => {
              const item = props.payload
              return [
                `${formatCompactNumber(Number(value))} (${item.percentage}%)`,
                'Revenue'
              ]
            }}
            labelFormatter={(_, payload) => payload[0]?.payload?.fullName || ''}
          />
          <Bar dataKey="revenue" name="Revenue" radius={[4, 4, 0, 0]}>
            {chartData.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={entry.color} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}
