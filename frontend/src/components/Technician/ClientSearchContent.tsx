import React from 'react';
import {
  type TechnicianSearchResult,
  type TechnicianService,
} from '../../types/technician';
import { ClientInfoCard } from './ClientInfoCard';
import { RecentServicesCard } from './RecentServicesCard';
import { VehicleListCard } from './VehicleListCard';

interface ClientSearchContentProps {
  searchResult: TechnicianSearchResult;
  onServiceClick?: (service: TechnicianService) => void;
}

export const ClientSearchContent: React.FC<ClientSearchContentProps> = ({
  searchResult,
  onServiceClick,
}) => {
  return (
    <>
      {/* Grid responsivo de informações */}
      <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 mb-6">
        {searchResult.client && (
          <div className="transform transition-all duration-300 hover:scale-[1.01]">
            <ClientInfoCard client={searchResult.client} />
          </div>
        )}
        {searchResult.vehicles && (
          <div className="transform transition-all duration-300 hover:scale-[1.01]">
            <VehicleListCard vehicles={searchResult.vehicles} />
          </div>
        )}
      </div>

      {/* Serviços recentes */}
      {searchResult.recent_services && (
        <div className="transform transition-all duration-300 hover:scale-[1.01] mb-6">
          <RecentServicesCard
            services={searchResult.recent_services}
            onServiceClick={onServiceClick}
          />
        </div>
      )}
    </>
  );
};
