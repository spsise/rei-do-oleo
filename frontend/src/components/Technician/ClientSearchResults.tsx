import { PlusIcon, UserIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianSearchResult } from '../../types/technician';
import { ClientInfoCard } from './ClientInfoCard';
import { RecentServicesCard } from './RecentServicesCard';
import { VehicleListCard } from './VehicleListCard';

interface ClientSearchResultsProps {
  searchResult: TechnicianSearchResult;
  onCreateNewService: () => void;
}

export const ClientSearchResults: React.FC<ClientSearchResultsProps> = ({
  searchResult,
  onCreateNewService,
}) => {
  return (
    <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-8 mb-8">
      <div className="flex justify-between items-start mb-8">
        <div className="flex items-center gap-3">
          <div className="p-2 bg-green-100 rounded-lg">
            <UserIcon className="h-6 w-6 text-green-600" />
          </div>
          <h2 className="text-2xl font-semibold text-gray-900">
            Cliente Encontrado
          </h2>
        </div>
        <button
          onClick={onCreateNewService}
          className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center gap-2 transition-colors font-medium"
        >
          <PlusIcon className="h-5 w-5" />
          Novo Serviço
        </button>
      </div>

      {/* Client Info - Design Melhorado */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {searchResult.client && <ClientInfoCard client={searchResult.client} />}
        {searchResult.vehicles && (
          <VehicleListCard vehicles={searchResult.vehicles} />
        )}
      </div>

      {/* Recent Services */}
      {searchResult.recent_services && (
        <RecentServicesCard services={searchResult.recent_services} />
      )}
    </div>
  );
};
