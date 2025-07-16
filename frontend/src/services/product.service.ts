import type {
  CreateProductData,
  Product,
  ProductFilters,
  ProductListResponse,
  SearchProductData,
  UpdateProductData,
  UpdateStockData,
} from '../types/product';
import { apiCall, httpClient, type ApiResponse } from './http-client';

class ProductService {
  async getProducts(
    filters?: ProductFilters
  ): Promise<ApiResponse<ProductListResponse>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, String(value));
        }
      });
    }

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<ProductListResponse>>(
        `/products${params.toString() ? `?${params.toString()}` : ''}`
      )
    );
  }

  async getProduct(id: number): Promise<ApiResponse<Product>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Product>>(`/products/${id}`)
    );
  }

  async createProduct(data: CreateProductData): Promise<ApiResponse<Product>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Product>>('/products', data)
    );
  }

  async updateProduct(
    id: number,
    data: UpdateProductData
  ): Promise<ApiResponse<Product>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Product>>(`/products/${id}`, data)
    );
  }

  async deleteProduct(id: number): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.delete<ApiResponse<null>>(`/products/${id}`)
    );
  }

  async searchProduct(
    data: SearchProductData
  ): Promise<ApiResponse<Product[]>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Product[]>>(
        '/products/search/name',
        data
      )
    );
  }

  async getActiveProducts(): Promise<ApiResponse<Product[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Product[]>>('/products/active/list')
    );
  }

  async getLowStockProducts(): Promise<ApiResponse<Product[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Product[]>>('/products/stock/low')
    );
  }

  async updateProductStock(
    id: number,
    data: UpdateStockData
  ): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<null>>(`/products/${id}/stock`, data)
    );
  }

  async getProductsByCategory(
    categoryId: number
  ): Promise<ApiResponse<Product[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Product[]>>(
        `/products/category/${categoryId}`
      )
    );
  }
}

export const productService = new ProductService();
