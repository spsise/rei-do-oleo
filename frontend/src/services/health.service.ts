import { apiCall, httpClient, type ApiResponse } from './http-client';

class HealthService {
  async healthCheck(): Promise<ApiResponse> {
    return apiCall(() => httpClient.instance.get<ApiResponse>('/health'));
  }
}

export const healthService = new HealthService();
