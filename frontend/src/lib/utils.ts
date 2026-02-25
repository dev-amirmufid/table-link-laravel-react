import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount)
}

export function formatNumber(num: number): string {
  return new Intl.NumberFormat('id-ID').format(num)
}

/**
 * Convert large numbers to short format with Indonesian abbreviations
 * Examples: 1,500,000 -> 1,5Jt | 2,500,000,000 -> 2,5M | 1,500,000,000,000 -> 1,5T
 */
export function formatCompactNumber(num: number): string {
  const absNum = Math.abs(num)
  
  if (absNum >= 1e12) {
    // Trillions (Triliun)
    return (num / 1e12).toFixed(1).replace('.', ',') + 'T'
  } else if (absNum >= 1e9) {
    // Billions (Miliar)
    return (num / 1e9).toFixed(1).replace('.', ',') + 'M'
  } else if (absNum >= 1e6) {
    // Millions (Juta)
    return (num / 1e6).toFixed(1).replace('.', ',') + 'Jt'
  } else if (absNum >= 1e4) {
    // Thousands (Ribu)
    return (num / 1e3).toFixed(1).replace('.', ',') + 'Rb'
  } else {
    return num.toLocaleString('id-ID')
  }
}
