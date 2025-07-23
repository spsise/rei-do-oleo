import type {
  CreateServiceItemData,
  UpdateServiceItemData,
} from '../types/service';
import { apiCall, httpClient, type ApiResponse } from './http-client';

interface ServiceItemResponse {
  id: number;
  service_id: number;
  product_id: number;
  quantity: number;
  unit_price: number;
  discount?: number;
  total_price: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  product?: {
    id: number;
    name: string;
    sku: string;
    brand?: string;
    category: string;
    unit: string;
    current_stock: number;
  };
}

interface ServiceTotalResponse {
  service_id: number;
  items_total: number;
  formatted_total: string;
}

class ServiceItemService {
  async getServiceItems(
    serviceId: number
  ): Promise<ApiResponse<ServiceItemResponse[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<ServiceItemResponse[]>>(
        `/services/${serviceId}/items`
      )
    );
  }

  async createServiceItem(
    serviceId: number,
    data: CreateServiceItemData
  ): Promise<ApiResponse<ServiceItemResponse>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<ServiceItemResponse>>(
        `/services/${serviceId}/items`,
        data
      )
    );
  }

  async updateServiceItem(
    serviceId: number,
    itemId: number,
    data: UpdateServiceItemData
  ): Promise<ApiResponse<ServiceItemResponse>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<ServiceItemResponse>>(
        `/services/${serviceId}/items/${itemId}`,
        data
      )
    );
  }

  async deleteServiceItem(
    serviceId: number,
    itemId: number
  ): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.delete<ApiResponse<null>>(
        `/services/${serviceId}/items/${itemId}`
      )
    );
  }

  async bulkCreateServiceItems(
    serviceId: number,
    items: CreateServiceItemData[]
  ): Promise<ApiResponse<ServiceItemResponse[]>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<ServiceItemResponse[]>>(
        `/services/${serviceId}/items/bulk`,
        {
          items,
        }
      )
    );
  }

  async updateServiceItems(
    serviceId: number,
    items: Array<{
      id?: number;
      product_id: number;
      quantity: number;
      unit_price: number;
      notes?: string;
    }>
  ): Promise<ApiResponse<ServiceItemResponse[]>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<ServiceItemResponse[]>>(
        `/services/${serviceId}/items/bulk`,
        { items }
      )
    );
  }

  async getServiceTotal(
    serviceId: number
  ): Promise<ApiResponse<ServiceTotalResponse>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<ServiceTotalResponse>>(
        `/services/${serviceId}/items/total/calculate`
      )
    );
  }
}

export const serviceItemService = new ServiceItemService();
