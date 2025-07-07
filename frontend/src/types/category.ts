export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  sort_order?: number;
  active: boolean;
  active_label?: string;
  products_count?: number;
  active_products_count?: number;
  created_at: string;
  updated_at: string;
}

export interface CategoryFilters {
  search?: string;
  active?: boolean;
  per_page?: number;
  page?: number;
}

export interface CreateCategoryData {
  name: string;
  description?: string;
  active?: boolean;
}

export interface UpdateCategoryData {
  name?: string;
  description?: string;
  active?: boolean;
}
