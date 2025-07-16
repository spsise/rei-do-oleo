import { useQuery } from '@tanstack/react-query';
import { dashboardService } from '../services';

export const useDashboardOverview = (params?: {
  service_center_id?: number;
  period?: 'today' | 'week' | 'month';
}) => {
  return useQuery({
    queryKey: ['dashboard-overview', params],
    queryFn: async () => {
      const response = await dashboardService.getOverview(params);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(
        response.message || 'Erro ao carregar visão geral do dashboard'
      );
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

export const useDashboardCharts = (params?: {
  service_center_id?: number;
  period?: '7d' | '30d' | '90d';
}) => {
  return useQuery({
    queryKey: ['dashboard-charts', params],
    queryFn: async () => {
      const response = await dashboardService.getCharts(params);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(
        response.message || 'Erro ao carregar dados dos gráficos'
      );
    },
    staleTime: 10 * 60 * 1000, // 10 minutes
  });
};

export const useDashboardAlerts = (params?: { service_center_id?: number }) => {
  return useQuery({
    queryKey: ['dashboard-alerts', params],
    queryFn: async () => {
      const response = await dashboardService.getAlerts(params);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar alertas');
    },
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
};
