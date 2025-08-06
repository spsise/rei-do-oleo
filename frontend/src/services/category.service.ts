import type {
  Category,
  CategoryFilters,
  CreateCategoryData,
  UpdateCategoryData,
} from '../types/category';
import { apiCall, httpClient, type ApiResponse } from './http-client';

class CategoryService {
  async getCategories(
    filters?: CategoryFilters
  ): Promise<ApiResponse<Category[]>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, String(value));
        }
      });
    }

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Category[]>>(
        `/categories?${params.toString()}`
      )
    );
  }

  async getCategory(id: number): Promise<ApiResponse<Category>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Category>>(`/categories/${id}`)
    );
  }

  async createCategory(
    data: CreateCategoryData
  ): Promise<ApiResponse<Category>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Category>>('/categories', data)
    );
  }

  async updateCategory(
    id: number,
    data: UpdateCategoryData
  ): Promise<ApiResponse<Category>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Category>>(`/categories/${id}`, data)
    );
  }

  async deleteCategory(id: number): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.delete<ApiResponse<null>>(`/categories/${id}`)
    );
  }

  async getActiveCategories(): Promise<ApiResponse<Category[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Category[]>>(
        '/categories/active/list'
      )
    );
  }
}

export const categoryService = new CategoryService();
