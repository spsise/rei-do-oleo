import { apiCall, httpClient, type ApiResponse } from './http-client';

class VehicleService {
  async getVehicles(): Promise<ApiResponse<unknown[]>> {
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<unknown[]>>('/vehicles')
    );
  }
}

export const vehicleService = new VehicleService();
