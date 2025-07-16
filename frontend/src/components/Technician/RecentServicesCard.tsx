import { WrenchScrewdriverIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianService } from '../../types/technician';

interface RecentServicesCardProps {
  services: TechnicianService[];
}

export const RecentServicesCard: React.FC<RecentServicesCardProps> = ({
  services,
}) => {
  return (
    <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 bg-yellow-100 rounded-lg">
          <WrenchScrewdriverIcon className="h-5 w-5 text-yellow-600" />
        </div>
        <h3 className="font-semibold text-gray-900 text-lg">
          Serviços Recentes
        </h3>
      </div>
      <div className="space-y-3">
        {services?.length ? (
          services.map((service) => (
            <div
              key={service.id}
              className="bg-white rounded-lg p-4 border border-gray-200"
            >
              <div className="grid grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
                <div>
                  <span className="font-medium text-gray-700">Nº Serviço:</span>
                  <p className="text-gray-900 font-mono">
                    {service.service_number}
                  </p>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Descrição:</span>
                  <p className="text-gray-900">{service.description}</p>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Status:</span>
                  <p className="text-gray-900">{service.status}</p>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Valor:</span>
                  <p className="text-gray-900 font-mono">
                    R$ {service.total_amount?.toFixed(2)}
                  </p>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Data:</span>
                  <p className="text-gray-900">{service.created_at}</p>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="text-center py-8">
            <WrenchScrewdriverIcon className="h-12 w-12 text-gray-300 mx-auto mb-3" />
            <p className="text-gray-500">Nenhum serviço recente encontrado.</p>
          </div>
        )}
      </div>
    </div>
  );
};
