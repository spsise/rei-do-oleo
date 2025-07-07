import { apiCall, httpClient, type ApiResponse } from './http-client';

export interface DashboardOverview {
  total_clients: number;
  total_vehicles: number;
  total_services: number;
  total_products: number;
  total_revenue: number;
  services_this_month: number;
  revenue_this_month: number;
  low_stock_products: number;
  pending_services: number;
  completed_services_today: number;
  recent_services: Array<{
    id: number;
    service_number: string;
    client_name: string;
    vehicle_plate: string;
    status: string;
    total: number;
    created_at: string;
  }>;
  top_products: Array<{
    id: number;
    name: string;
    sales_count: number;
    revenue: number;
  }>;
  service_trends: Array<{
    date: string;
    completed: number;
    pending: number;
    cancelled: number;
  }>;
  revenue_trends: Array<{
    date: string;
    revenue: number;
  }>;
}

export interface DashboardCharts {
  services_chart: Array<{
    date: string;
    completed: number;
    pending: number;
    cancelled: number;
  }>;
  revenue_chart: Array<{
    date: string;
    revenue: number;
  }>;
  products_chart: Array<{
    name: string;
    sales: number;
    revenue: number;
  }>;
}

export interface DashboardAlert {
  type: string;
  title: string;
  message: string;
  severity: 'info' | 'warning' | 'error';
  created_at: string;
}

class DashboardService {
  /**
   * Obter visão geral do dashboard
   */
  async getOverview(params?: {
    service_center_id?: number;
    period?: 'today' | 'week' | 'month';
  }): Promise<ApiResponse<DashboardOverview>> {
    const queryParams = new URLSearchParams();

    if (params?.service_center_id) {
      queryParams.append(
        'service_center_id',
        params.service_center_id.toString()
      );
    }

    if (params?.period) {
      queryParams.append('period', params.period);
    }

    const url = `/dashboard/overview${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<DashboardOverview>>(url)
    );
  }

  /**
   * Obter dados para gráficos do dashboard
   */
  async getCharts(params?: {
    service_center_id?: number;
    period?: '7d' | '30d' | '90d';
  }): Promise<ApiResponse<DashboardCharts>> {
    const queryParams = new URLSearchParams();

    if (params?.service_center_id) {
      queryParams.append(
        'service_center_id',
        params.service_center_id.toString()
      );
    }

    if (params?.period) {
      queryParams.append('period', params.period);
    }

    const url = `/dashboard/charts${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<DashboardCharts>>(url)
    );
  }

  /**
   * Obter alertas do dashboard
   */
  async getAlerts(params?: {
    service_center_id?: number;
  }): Promise<ApiResponse<DashboardAlert[]>> {
    const queryParams = new URLSearchParams();

    if (params?.service_center_id) {
      queryParams.append(
        'service_center_id',
        params.service_center_id.toString()
      );
    }

    const url = `/dashboard/alerts${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;

    return apiCall(() =>
      httpClient.instance.get<ApiResponse<DashboardAlert[]>>(url)
    );
  }
}

export const dashboardService = new DashboardService();
