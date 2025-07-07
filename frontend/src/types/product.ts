import type { Category } from './category';

export interface Product {
  id: number;
  name: string;
  description?: string;
  sku?: string;
  barcode?: string;
  price: number;
  price_formatted: string;
  cost_price?: number;
  cost_price_formatted?: string;
  profit_margin?: number;
  stock_quantity: number;
  min_stock: number;
  stock_status: 'out_of_stock' | 'low_stock' | 'in_stock';
  stock_status_label: string;
  unit?: string;
  brand?: string;
  supplier?: string;
  location?: string;
  weight?: number;
  weight_formatted?: string;
  dimensions?: string;
  warranty_months?: number;
  warranty_label?: string;
  active: boolean;
  active_label: string;
  featured?: boolean;
  observations?: string;
  category?: Category;
  category_id: number;
  usage_count?: number;
  total_sold?: number;
  created_at: string;
  updated_at: string;
}

export interface ProductFilters {
  search?: string;
  category_id?: number;
  active?: boolean;
  low_stock?: boolean;
  featured?: boolean;
  brand?: string;
  supplier?: string;
  per_page?: number;
  page?: number;
}

export interface CreateProductData {
  name: string;
  description?: string;
  sku: string;
  barcode?: string;
  price: number;
  stock_quantity: number;
  min_stock?: number;
  unit: string;
  brand?: string;
  supplier?: string;
  location?: string;
  weight?: number;
  dimensions?: string;
  warranty_months?: number;
  active?: boolean;
  featured?: boolean;
  observations?: string;
  category_id: number;
}

export interface UpdateProductData {
  name?: string;
  description?: string;
  sku?: string;
  barcode?: string;
  price?: number;
  stock_quantity?: number;
  min_stock?: number;
  unit?: string;
  brand?: string;
  supplier?: string;
  location?: string;
  weight?: number;
  dimensions?: string;
  warranty_months?: number;
  active?: boolean;
  featured?: boolean;
  observations?: string;
  category_id?: number;
}

export interface SearchProductData {
  name?: string;
  sku?: string;
  barcode?: string;
  category_id?: number;
}

export interface UpdateStockData {
  quantity: number;
  type: 'add' | 'subtract' | 'set';
  reason?: string;
}

export interface ProductListResponse {
  data: Product[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
