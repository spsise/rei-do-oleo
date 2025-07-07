import { ClockIcon } from '@heroicons/react/24/outline';
import React from 'react';

interface RecentService {
  id: number;
  service_number: string;
  client_name: string;
  vehicle_plate: string;
  status: string;
  total: number;
  created_at: string;
}

interface RecentServicesProps {
  services: RecentService[];
}

const getStatusBadge = (status: string) => {
  const statusConfig = {
    completed: {
      label: 'Concluído',
      className: 'bg-green-100 text-green-800',
    },
    in_progress: {
      label: 'Em Andamento',
      className: 'bg-yellow-100 text-yellow-800',
    },
    pending: {
      label: 'Pendente',
      className: 'bg-gray-100 text-gray-800',
    },
    cancelled: {
      label: 'Cancelado',
      className: 'bg-red-100 text-red-800',
    },
  };

  const config =
    statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;

  return (
    <span
      className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${config.className}`}
    >
      {config.label}
    </span>
  );
};

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

export const RecentServices: React.FC<RecentServicesProps> = ({ services }) => {
  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h3 className="text-lg font-medium text-gray-900">
            Serviços Recentes
          </h3>
          <ClockIcon className="h-5 w-5 text-gray-400" />
        </div>
      </div>

      <div className="divide-y divide-gray-200">
        {services.length === 0 ? (
          <div className="px-6 py-8 text-center">
            <p className="text-gray-500">Nenhum serviço recente encontrado</p>
          </div>
        ) : (
          services.map((service) => (
            <div
              key={service.id}
              className="px-6 py-4 hover:bg-gray-50 transition-colors"
            >
              <div className="flex items-center justify-between">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center space-x-3">
                    <p className="text-sm font-medium text-gray-900 truncate">
                      #{service.service_number}
                    </p>
                    {getStatusBadge(service.status)}
                  </div>

                  <div className="mt-1">
                    <p className="text-sm text-gray-600">
                      {service.client_name}
                    </p>
                    <p className="text-xs text-gray-500">
                      {service.vehicle_plate}
                    </p>
                    <p className="text-xs text-gray-400 mt-1">
                      {formatDate(service.created_at)}
                    </p>
                  </div>
                </div>

                <div className="text-right ml-4">
                  <p className="text-sm font-medium text-gray-900">
                    {formatCurrency(service.total)}
                  </p>
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {services.length > 0 && (
        <div className="px-6 py-3 bg-gray-50 border-t border-gray-200">
          <button className="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Ver todos os serviços →
          </button>
        </div>
      )}
    </div>
  );
};
