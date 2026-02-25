import {
  PieChart,
  Pie,
  Cell,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts'
import { formatCompactNumber } from '@/lib/utils'

interface UserClassification {
  type: string
  user_count: number
  revenue: number
  percentage: number
}

interface UserClassificationChartProps {
  data: UserClassification[]
}

const COLORS = ['#3b82f6', '#f59e0b']

export function UserClassificationChart({ data }: UserClassificationChartProps) {
  const chartData = data.map(item => ({
    name: item.type === 'domestic' ? 'Domestic' : 'Foreign',
    value: item.user_count,
    revenue: item.revenue,
    percentage: item.percentage,
  }))

  return (
    <div className="w-full h-[300px]">
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={chartData}
            cx="50%"
            cy="50%"
            innerRadius={60}
            outerRadius={100}
            paddingAngle={5}
            dataKey="value"
            nameKey="name"
            label={({ name, payload }) => `${name}: ${payload.percentage}%`}
            labelLine={true}
          >
            {chartData.map((_, index) => (
              <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
            ))}
          </Pie>
          <Tooltip
            formatter={(value, name, props) => {
              const revenue = Number(props.payload.revenue)
              return [
                `${formatCompactNumber(Number(value))} users (${formatCompactNumber(revenue)})`,
                name
              ]
            }}
          />
          <Legend />
        </PieChart>
      </ResponsiveContainer>
    </div>
  )
}
