import type { Service } from '../types/service';
import { apiCall, httpClient, type ApiResponse } from './http-client';

// Interfaces para o technician
export interface TechnicianSearchData {
  search_type: 'license_plate' | 'document';
  search_value: string;
}

export interface TechnicianSearchResult {
  client: {
    id: number;
    name: string;
    email: string;
    phone: string;
    document: string;
  };
  vehicles: Array<{
    id: number;
    brand: string;
    model: string;
    year: number;
    license_plate: string;
    color: string;
    mileage: number;
  }>;
  recent_services: Array<{
    id: number;
    service_number: string;
    description: string;
    status: string;
    total_amount: number;
    created_at: string;
  }>;
  found_by: string;
}

export interface CreateServiceData {
  client_id: number;
  vehicle_id: number;
  description: string;
  estimated_duration: number;
  priority: 'low' | 'medium' | 'high';
  notes?: string;
}

export interface TechnicianDashboard {
  today_services: number;
  pending_services: number;
  completed_today: number;
  recent_services: Array<{
    id: number;
    description: string;
    status: string;
    created_at: string;
    total_amount: number;
  }>;
}

export interface UpdateServiceStatusData {
  status: 'in_progress' | 'completed' | 'cancelled';
  notes?: string;
}

class TechnicianService {
  async searchClient(
    data: TechnicianSearchData
  ): Promise<ApiResponse<TechnicianSearchResult>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<TechnicianSearchResult>>(
        '/technician/search',
        data
      )
    );
  }

  async createService(data: CreateServiceData): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Service>>(
        '/technician/services',
        data
      )
    );
  }

  async getDashboard(): Promise<ApiResponse<TechnicianDashboard>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<TechnicianDashboard>>(
        '/technician/dashboard'
      )
    );
  }

  async getMyServices(filters?: {
    status?: string;
    per_page?: number;
  }): Promise<
    ApiResponse<{
      data: Service[];
      pagination: { current_page: number; total: number; per_page: number };
    }>
  > {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.per_page)
      params.append('per_page', filters.per_page.toString());

    return apiCall(() =>
      httpClient.instance.get<
        ApiResponse<{
          data: Service[];
          pagination: { current_page: number; total: number; per_page: number };
        }>
      >(`/technician/services/my?${params.toString()}`)
    );
  }

  async updateServiceStatus(
    serviceId: number,
    data: UpdateServiceStatusData
  ): Promise<ApiResponse<Service>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Service>>(
        `/technician/services/${serviceId}/status`,
        data
      )
    );
  }
}

export const technicianService = new TechnicianService();
