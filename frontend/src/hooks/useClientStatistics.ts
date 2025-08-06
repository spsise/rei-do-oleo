import { useMemo } from 'react';
import { type TechnicianSearchResult } from '../types/technician';

interface ClientStatistics {
  vehiclesCount: number;
  servicesCount: number;
  completedServicesCount: number;
  totalAmount: number;
}

export const useClientStatistics = (searchResult: TechnicianSearchResult): ClientStatistics => {
  return useMemo(() => {
    const vehiclesCount = searchResult.vehicles?.length || 0;
    const servicesCount = searchResult.recent_services?.length || 0;
    const completedServicesCount = searchResult.recent_services?.filter(
      (s) => s.status === 'completed'
    ).length || 0;
    const totalAmount = searchResult.recent_services?.reduce(
      (sum, s) => sum + (s.total_amount || 0),
      0
    ) || 0;

    return {
      vehiclesCount,
      servicesCount,
      completedServicesCount,
      totalAmount,
    };
  }, [searchResult]);
}; 