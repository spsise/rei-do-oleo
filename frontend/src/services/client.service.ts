import type {
  Client,
  ClientFilters,
  ClientListResponse,
  CreateClientData,
  SearchByDocumentData,
  SearchByPhoneData,
  UpdateClientData,
} from '../types/client';
import { apiCall, httpClient, type ApiResponse } from './http-client';

class ClientService {
  async getClients(
    filters?: ClientFilters
  ): Promise<ApiResponse<ClientListResponse>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, String(value));
        }
      });
    }

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<ClientListResponse>>(
        `/clients?${params.toString()}`
      )
    );
  }

  async getClient(id: number): Promise<ApiResponse<Client>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<Client>>(`/clients/${id}`)
    );
  }

  async createClient(data: CreateClientData): Promise<ApiResponse<Client>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Client>>('/clients', data)
    );
  }

  async updateClient(
    id: number,
    data: UpdateClientData
  ): Promise<ApiResponse<Client>> {
    return apiCall(() =>
      httpClient.instance.put<ApiResponse<Client>>(`/clients/${id}`, data)
    );
  }

  async deleteClient(id: number): Promise<ApiResponse<null>> {
    return apiCall(() =>
      httpClient.instance.delete<ApiResponse<null>>(`/clients/${id}`)
    );
  }

  async searchClientByDocument(
    data: SearchByDocumentData
  ): Promise<ApiResponse<Client>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Client>>(
        '/clients/search/document',
        data
      )
    );
  }

  async searchClientByPhone(
    data: SearchByPhoneData
  ): Promise<ApiResponse<Client>> {
    return apiCall(() =>
      httpClient.instance.post<ApiResponse<Client>>(
        '/clients/search/phone',
        data
      )
    );
  }
}

export const clientService = new ClientService();
