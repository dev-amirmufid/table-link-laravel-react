import {
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from 'recharts'
import type { UserTypeDistribution } from '@/types'

interface PieChartComponentProps {
  data: UserTypeDistribution[]
  title?: string
}

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042']

export function PieChartComponent({ data, title }: PieChartComponentProps) {
  const chartData = data.map((item) => ({
    name: item.type === 'domestic' ? 'Domestic' : 'Foreign',
    value: item.count,
    revenue: item.revenue,
  }))

  return (
    <div className="w-full h-[300px]">
      {title && <h3 className="text-lg font-semibold mb-4">{title}</h3>}
      <ResponsiveContainer width="100%" height="100%">
        <RechartsPieChart>
          <Pie
            data={chartData}
            cx="50%"
            cy="50%"
            labelLine={false}
            label={({ name, percent }) =>
              `${name}: ${((percent ?? 0) * 100).toFixed(0)}%`
            }
            outerRadius={80}
            fill="#8884d8"
            dataKey="value"
          >
            {chartData.map((_, index) => (
              <Cell
                key={`cell-${index}`}
                fill={COLORS[index % COLORS.length]}
              />
            ))}
          </Pie>
          <Tooltip
            contentStyle={{
              backgroundColor: 'var(--card)',
              border: '1px solid var(--border)',
              borderRadius: '8px',
            }}
            formatter={(value, name) => [
              value ?? 0,
              name === 'value' ? 'Count' : 'Revenue',
            ]}
          />
          <Legend />
        </RechartsPieChart>
      </ResponsiveContainer>
    </div>
  )
}
