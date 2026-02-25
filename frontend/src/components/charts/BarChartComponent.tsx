import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from 'recharts'

interface BarChartComponentProps {
  data: any[]
  title?: string
  dataKey?: string
  nameKey?: string
}

export function BarChartComponent({
  data,
  title,
  dataKey = 'transaction_count',
  nameKey: _nameKey = 'name',
}: BarChartComponentProps) {
  void _nameKey // Suppress unused variable warning
  const chartData = data.map((item) => ({
    name: item.buyer?.name || item.seller?.name || 'Unknown',
    [dataKey]: item[dataKey] || 0,
    total: item.total_spent || item.total_earned || 0,
  }))

  return (
    <div className="w-full h-[300px]">
      {title && <h3 className="text-lg font-semibold mb-4">{title}</h3>}
      <ResponsiveContainer width="100%" height="100%">
        <RechartsBarChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
          <XAxis
            dataKey="name"
            tick={{ fontSize: 10 }}
            className="fill-muted-foreground"
            angle={-45}
            textAnchor="end"
            height={80}
          />
          <YAxis
            tick={{ fontSize: 12 }}
            className="fill-muted-foreground"
          />
          <Tooltip
            contentStyle={{
              backgroundColor: 'var(--card)',
              border: '1px solid var(--border)',
              borderRadius: '8px',
            }}
          />
          <Legend />
          <Bar
            dataKey={dataKey}
            name="Transactions"
            fill="#8884d8"
            radius={[4, 4, 0, 0]}
          />
        </RechartsBarChart>
      </ResponsiveContainer>
    </div>
  )
}
