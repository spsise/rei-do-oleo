import React from 'react';
import { useClientStatistics } from '../../hooks/useClientStatistics';
import { type TechnicianSearchResult } from '../../types/technician';
import { StatisticItem } from './StatisticItem';
import { StatisticsIcon } from './StatisticsIcon';

interface ClientStatisticsCardProps {
  searchResult: TechnicianSearchResult;
}

export const ClientStatisticsCard: React.FC<ClientStatisticsCardProps> = ({
  searchResult,
}) => {
  const { vehiclesCount, servicesCount, completedServicesCount } =
    useClientStatistics(searchResult);

  return (
    <div className="p-4 sm:p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
      <h3 className="text-base sm:text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
        <StatisticsIcon />
        Resumo do Cliente
      </h3>
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-3">
        <StatisticItem value={vehiclesCount} label="Veículos" color="blue" />
        <StatisticItem value={servicesCount} label="Serviços" color="green" />
        <StatisticItem
          value={completedServicesCount}
          label="Concluídos"
          color="yellow"
        />
      </div>
    </div>
  );
};
