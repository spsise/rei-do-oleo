import type {
  CreateServiceData,
  SearchServiceData,
  Service,
  ServiceFilters,
  UpdateServiceData,
} from '../types/service';
import { apiCall, httpClient, type ApiResponse } from './http-client';

class ServiceService {
  async getServices(filters?: ServiceFilters): Promise<ApiResponse<Service[]>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, String(value));
        }
      });
    }

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Service[]>>(
        `/services?${params.toString()}`
      )
    );
  }

  async getService(id: number): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Service>>(`/services/${id}`)
    );
  }

  async createService(data: CreateServiceData): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Service>>('/services', data)
    );
  }

  async updateService(
    id: number,
    data: UpdateServiceData
  ): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Service>>(`/services/${id}`, data)
    );
  }

  async deleteService(id: number): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.delete<ApiResponse<null>>(`/services/${id}`)
    );
  }

  async searchService(data: SearchServiceData): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Service>>('/services/search', data)
    );
  }

  async getDashboardStats(): Promise<ApiResponse<unknown>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<unknown>>('/services/dashboard/stats')
    );
  }
}

export const serviceService = new ServiceService();
