export function exportToCSV<T extends Record<string, unknown>>(
  data: T[],
  filename: string,
  columns?: { key: keyof T; header: string }[]
): void {
  if (data.length === 0) {
    console.warn('No data to export')
    return
  }

  // If columns are not specified, use all keys from the first row
  const cols = columns || (Object.keys(data[0]) as (keyof T)[]).map(key => ({
    key,
    header: String(key),
  }))

  // Create CSV header
  const headers = cols.map(col => col.header).join(',')
  
  // Create CSV rows
  const rows = data.map(row => {
    return cols.map(col => {
      const value = row[col.key]
      // Handle values that need quoting
      if (value === null || value === undefined) {
        return ''
      }
      const stringValue = String(value)
      // Quote if contains comma, newline, or quote
      if (stringValue.includes(',') || stringValue.includes('\n') || stringValue.includes('"')) {
        return `"${stringValue.replace(/"/g, '""')}"`
      }
      return stringValue
    }).join(',')
  })

  // Combine header and rows
  const csv = [headers, ...rows].join('\n')

  // Create blob and download
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${filename}-${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}
