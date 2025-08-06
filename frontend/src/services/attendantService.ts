import {
  type ApiResponse,
  type AttendantConfig,
  type AttendantService,
  type CreateCompleteServiceData,
  type CreateQuickServiceData,
  type PaginatedResponse,
  type QuickStats,
  type ServiceFormData,
  type ServiceReport,
  type ServiceSuggestion,
  type ServiceTemplate,
  type ServiceTemplateFilters,
  type ServiceValidationData,
  type ServiceValidationResult,
} from '../types/attendant';
import { httpClient } from './http-client';

export class AttendantServiceAPI {
  private baseUrl = '/api/v1/attendant/services';

  // Criação de Serviços
  async createQuickService(
    data: CreateQuickServiceData
  ): Promise<AttendantService> {
    const response = await httpClient.instance.post<
      ApiResponse<AttendantService>
    >(`${this.baseUrl}/quick`, data);
    return response.data.data;
  }

  async createCompleteService(
    data: CreateCompleteServiceData
  ): Promise<AttendantService> {
    const response = await httpClient.instance.post<
      ApiResponse<AttendantService>
    >(`${this.baseUrl}/complete`, data);
    return response.data.data;
  }

  // Templates de Serviços
  async getTemplates(
    filters?: ServiceTemplateFilters
  ): Promise<ServiceTemplate[]> {
    const params = new URLSearchParams();

    if (filters?.category) {
      params.append('category', filters.category);
    }
    if (filters?.priority) {
      params.append('priority', filters.priority);
    }
    if (filters?.search) {
      params.append('search', filters.search);
    }

    const response = await httpClient.instance.get<
      ApiResponse<ServiceTemplate[]>
    >(`${this.baseUrl}/templates?${params.toString()}`);
    return response.data.data;
  }

  async getTemplateById(id: number): Promise<ServiceTemplate> {
    const response = await httpClient.instance.get<
      ApiResponse<ServiceTemplate>
    >(`${this.baseUrl}/templates/${id}`);
    return response.data.data;
  }

  // Validação e Sugestões
  async validateService(
    data: ServiceValidationData
  ): Promise<ServiceValidationResult> {
    const response = await httpClient.instance.post<
      ApiResponse<ServiceValidationResult>
    >(`${this.baseUrl}/validate`, data);
    return response.data.data;
  }

  async getSuggestions(
    clientId: number,
    vehicleId: number
  ): Promise<ServiceSuggestion> {
    const response = await httpClient.instance.get<
      ApiResponse<ServiceSuggestion>
    >(
      `${this.baseUrl}/suggestions?client_id=${clientId}&vehicle_id=${vehicleId}`
    );
    return response.data.data;
  }

  // Estatísticas
  async getQuickStats(): Promise<QuickStats> {
    const response = await httpClient.instance.get<ApiResponse<QuickStats>>(
      `${this.baseUrl}/quick-stats`
    );
    return response.data.data;
  }

  // Listagem de Serviços
  async getServices(params?: {
    page?: number;
    per_page?: number;
    search?: string;
    status?: string;
    priority?: string;
    client_id?: number;
    vehicle_id?: number;
    date_from?: string;
    date_to?: string;
  }): Promise<PaginatedResponse<AttendantService>> {
    const queryParams = new URLSearchParams();

    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.per_page)
      queryParams.append('per_page', params.per_page.toString());
    if (params?.search) queryParams.append('search', params.search);
    if (params?.status) queryParams.append('status', params.status);
    if (params?.priority) queryParams.append('priority', params.priority);
    if (params?.client_id)
      queryParams.append('client_id', params.client_id.toString());
    if (params?.vehicle_id)
      queryParams.append('vehicle_id', params.vehicle_id.toString());
    if (params?.date_from) queryParams.append('date_from', params.date_from);
    if (params?.date_to) queryParams.append('date_to', params.date_to);

    const response = await httpClient.instance.get<
      PaginatedResponse<AttendantService>
    >(`${this.baseUrl}?${queryParams.toString()}`);
    return response.data;
  }

  async getServiceById(id: number): Promise<AttendantService> {
    const response = await httpClient.instance.get<
      ApiResponse<AttendantService>
    >(`${this.baseUrl}/${id}`);
    return response.data.data;
  }

  // Atualização de Serviços
  async updateService(
    id: number,
    data: Partial<ServiceFormData>
  ): Promise<AttendantService> {
    const response = await httpClient.instance.put<
      ApiResponse<AttendantService>
    >(`${this.baseUrl}/${id}`, data);
    return response.data.data;
  }

  // Configurações
  async getConfig(): Promise<AttendantConfig> {
    const response = await httpClient.instance.get<
      ApiResponse<AttendantConfig>
    >(`${this.baseUrl}/config`);
    return response.data.data;
  }

  async updateConfig(
    config: Partial<AttendantConfig>
  ): Promise<AttendantConfig> {
    const response = await httpClient.instance.put<
      ApiResponse<AttendantConfig>
    >(`${this.baseUrl}/config`, config);
    return response.data.data;
  }

  // Relatórios
  async getReport(params: {
    period: string;
    date_from?: string;
    date_to?: string;
  }): Promise<ServiceReport> {
    const queryParams = new URLSearchParams();
    queryParams.append('period', params.period);
    if (params.date_from) queryParams.append('date_from', params.date_from);
    if (params.date_to) queryParams.append('date_to', params.date_to);

    const response = await httpClient.instance.get<ApiResponse<ServiceReport>>(
      `${this.baseUrl}/report?${queryParams.toString()}`
    );
    return response.data.data;
  }

  // Exportação
  async exportServices(params: {
    format: 'csv' | 'excel' | 'pdf';
    date_from?: string;
    date_to?: string;
    status?: string;
  }): Promise<Blob> {
    const queryParams = new URLSearchParams();
    queryParams.append('format', params.format);
    if (params.date_from) queryParams.append('date_from', params.date_from);
    if (params.date_to) queryParams.append('date_to', params.date_to);
    if (params.status) queryParams.append('status', params.status);

    const response = await httpClient.instance.get(
      `${this.baseUrl}/export?${queryParams.toString()}`,
      { responseType: 'blob' }
    );
    return response.data;
  }

  // Cache Management
  async clearCache(): Promise<void> {
    await httpClient.instance.post(`${this.baseUrl}/clear-cache`);
  }

  // Health Check
  async healthCheck(): Promise<{ status: string; timestamp: string }> {
    const response = await httpClient.instance.get<
      ApiResponse<{ status: string; timestamp: string }>
    >(`${this.baseUrl}/health`);
    return response.data.data;
  }
}

// Instância singleton
export const attendantServiceAPI = new AttendantServiceAPI();
