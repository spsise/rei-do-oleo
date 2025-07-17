import {
  CalendarDaysIcon,
  ClockIcon,
  CurrencyDollarIcon,
  EyeIcon,
  EyeSlashIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { type TechnicianService } from '../../types/technician';

interface RecentServicesCardProps {
  services: TechnicianService[];
}

export const RecentServicesCard: React.FC<RecentServicesCardProps> = ({
  services,
}) => {
  const [showAllValues, setShowAllValues] = useState(false);
  const [visibleValues, setVisibleValues] = useState<Set<number>>(new Set());

  const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'completed':
        return 'bg-green-100 text-green-700 border-green-200';
      case 'in_progress':
        return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'pending':
        return 'bg-yellow-100 text-yellow-700 border-yellow-200';
      case 'cancelled':
        return 'bg-red-100 text-red-700 border-red-200';
      default:
        return 'bg-gray-100 text-gray-700 border-gray-200';
    }
  };

  const getStatusText = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'completed':
        return 'Concluído';
      case 'in_progress':
        return 'Em Andamento';
      case 'pending':
        return 'Pendente';
      case 'cancelled':
        return 'Cancelado';
      default:
        return 'N/A';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'completed':
        return '✅';
      case 'in_progress':
        return '🔄';
      case 'pending':
        return '⏳';
      case 'cancelled':
        return '❌';
      default:
        return '📋';
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const toggleValueVisibility = (serviceId: number) => {
    const newVisibleValues = new Set(visibleValues);
    if (newVisibleValues.has(serviceId)) {
      newVisibleValues.delete(serviceId);
    } else {
      newVisibleValues.add(serviceId);
    }
    setVisibleValues(newVisibleValues);
  };

  const toggleAllValues = () => {
    if (showAllValues) {
      setVisibleValues(new Set());
      setShowAllValues(false);
    } else {
      setVisibleValues(new Set(services.map((s) => s.id)));
      setShowAllValues(true);
    }
  };

  const isValueVisible = (serviceId: number) => {
    return showAllValues || visibleValues.has(serviceId);
  };

  return (
    <div className="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-200 shadow-lg">
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-3">
          <div className="p-2 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-lg">
            <WrenchScrewdriverIcon className="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 className="font-bold text-gray-900">Serviços Recentes</h3>
            <p className="text-yellow-600 text-sm">
              {services?.length || 0} serviço{services?.length !== 1 ? 's' : ''}
            </p>
          </div>
        </div>

        {/* Botão para mostrar/ocultar todos os valores */}
        {services?.length > 0 && (
          <button
            onClick={toggleAllValues}
            className="flex items-center gap-2 px-3 py-1.5 bg-white/80 hover:bg-white rounded-lg border border-yellow-200 text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
          >
            {showAllValues ? (
              <>
                <EyeSlashIcon className="h-4 w-4" />
                Ocultar Valores
              </>
            ) : (
              <>
                <EyeIcon className="h-4 w-4" />
                Mostrar Valores
              </>
            )}
          </button>
        )}
      </div>

      {/* Lista de serviços */}
      <div className="space-y-3">
        {services?.length ? (
          services.map((service, index) => (
            <div
              key={service.id || index}
              className="bg-white/80 backdrop-blur-sm rounded-lg p-4 border border-yellow-100 hover:border-yellow-200 transition-all duration-200"
            >
              {/* Header do serviço */}
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-3">
                  <div className="p-1.5 bg-gradient-to-r from-yellow-100 to-orange-100 rounded-md">
                    <WrenchScrewdriverIcon className="h-3.5 w-3.5 text-yellow-600" />
                  </div>
                  <div>
                    <div className="font-bold text-gray-900 text-sm font-mono">
                      #{service.service_number}
                    </div>
                    <div className="text-xs text-gray-600 truncate max-w-32">
                      {service.description}
                    </div>
                  </div>
                </div>
                <div
                  className={`text-xs px-2 py-1 rounded-full font-medium border ${getStatusColor(service.status)}`}
                >
                  {getStatusIcon(service.status)}{' '}
                  {getStatusText(service.status)}
                </div>
              </div>

              {/* Detalhes compactos */}
              <div className="grid grid-cols-3 gap-3">
                {/* Data */}
                <div className="flex items-center gap-2">
                  <CalendarDaysIcon className="h-3.5 w-3.5 text-gray-500" />
                  <span className="text-xs text-gray-700">
                    {formatDate(service.created_at || '')}
                  </span>
                </div>

                {/* Status */}
                <div className="flex items-center gap-2">
                  <ClockIcon className="h-3.5 w-3.5 text-gray-500" />
                  <span className="text-xs text-gray-700 capitalize">
                    {getStatusText(service.status)}
                  </span>
                </div>

                {/* Valor com toggle */}
                <div className="flex items-center justify-between">
                  <CurrencyDollarIcon className="h-3.5 w-3.5 text-gray-500" />
                  <div className="flex items-center gap-1">
                    {isValueVisible(service.id) ? (
                      <span className="text-xs font-bold text-gray-900">
                        R$ {service.total_amount?.toFixed(2) || '0,00'}
                      </span>
                    ) : (
                      <span className="text-xs text-gray-500">••••</span>
                    )}
                    <button
                      onClick={() => toggleValueVisibility(service.id)}
                      className="p-0.5 hover:bg-gray-100 rounded transition-colors"
                    >
                      {isValueVisible(service.id) ? (
                        <EyeSlashIcon className="h-3 w-3 text-gray-500" />
                      ) : (
                        <EyeIcon className="h-3 w-3 text-gray-500" />
                      )}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="bg-white/80 backdrop-blur-sm rounded-lg p-6 text-center border border-yellow-100">
            <div className="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <WrenchScrewdriverIcon className="h-5 w-5 text-yellow-500" />
            </div>
            <h4 className="text-gray-900 font-semibold text-sm mb-1">
              Nenhum serviço encontrado
            </h4>
            <p className="text-gray-600 text-xs">
              Este cliente ainda não possui serviços registrados.
            </p>
          </div>
        )}
      </div>

      {/* Resumo compacto */}
      {services?.length > 0 && (
        <div className="mt-4 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
          <div className="grid grid-cols-3 gap-3 text-center">
            <div>
              <div className="text-lg font-bold text-yellow-600">
                {
                  services.filter(
                    (s) => s.status?.toLowerCase() === 'completed'
                  ).length
                }
              </div>
              <div className="text-xs text-gray-600">Concluídos</div>
            </div>
            <div>
              <div className="text-lg font-bold text-blue-600">
                {
                  services.filter(
                    (s) => s.status?.toLowerCase() === 'in_progress'
                  ).length
                }
              </div>
              <div className="text-xs text-gray-600">Em Andamento</div>
            </div>
            <div>
              <div className="text-lg font-bold text-green-600">
                {showAllValues ? (
                  `R$ ${services
                    .reduce((sum, s) => sum + (s.total_amount || 0), 0)
                    .toFixed(0)}`
                ) : (
                  <span className="text-gray-500">••••</span>
                )}
              </div>
              <div className="text-xs text-gray-600">Total</div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
