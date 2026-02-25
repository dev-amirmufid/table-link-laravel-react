import {
  LineChart as RechartsLineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from 'recharts'
import type { Trend } from '@/types'
import { formatCurrency, formatNumber } from '@/lib/utils'

interface LineChartComponentProps {
  data: Trend[]
  title?: string
}

// Custom tooltip component - declared outside to avoid re-creation
function CustomTooltip({ active, payload, label }: { active?: boolean; payload?: Array<{ color: string; name: string; value: number }>; label?: string }) {
  if (active && payload && payload.length) {
    return (
      <div className="bg-background border border-border rounded-lg p-3 shadow-lg">
        <p className="font-semibold mb-2">{label}</p>
        {payload.map((entry, index) => (
          <div key={index} className="flex items-center gap-2 text-sm">
            <div 
              className="w-3 h-3 rounded-full" 
              style={{ backgroundColor: entry.color }}
            />
            <span className="text-muted-foreground">{entry.name}:</span>
            <span className="font-medium">
              {entry.name === 'Revenue' 
                ? formatCurrency(entry.value) 
                : formatNumber(entry.value)}
            </span>
          </div>
        ))}
      </div>
    )
  }
  return null
}

export function LineChartComponent({ data, title }: LineChartComponentProps) {
  // Get statistics from Redux store (calculated in backend)

  return (
    <div className="w-full">
      {title && <h3 className="text-lg font-semibold mb-4">{title}</h3>}

      <div className="h-[300px]">
        <ResponsiveContainer width="100%" height="100%">
          <RechartsLineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
            <XAxis
              dataKey="date"
              tick={{ fontSize: 11 }}
              className="fill-muted-foreground"
              angle={-45}
              textAnchor="end"
              height={60}
              interval="preserveStartEnd"
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
            <Legend />
            <Line
              yAxisId="left"
              type="monotone"
              dataKey="count"
              name="Transactions"
              stroke="#8884d8"
              strokeWidth={2}
              dot={{ r: 3, fill: '#8884d8' }}
              activeDot={{ r: 5 }}
            />
            <Line
              yAxisId="right"
              type="monotone"
              dataKey="revenue"
              name="Revenue"
              stroke="#82ca9d"
              strokeWidth={2}
              dot={{ r: 3, fill: '#82ca9d' }}
              activeDot={{ r: 5 }}
            />
          </RechartsLineChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}
