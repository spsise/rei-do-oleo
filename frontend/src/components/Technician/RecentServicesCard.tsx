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
import { ServiceActionsMenu } from './ServiceActionsMenu';

interface RecentServicesCardProps {
  services: TechnicianService[];
  onServiceClick?: (service: TechnicianService) => void;
  onUpdateStatus?: (service: TechnicianService) => void;
  onEditService?: (service: TechnicianService) => void;
}

export const RecentServicesCard: React.FC<RecentServicesCardProps> = ({
  services,
  onServiceClick,
  onUpdateStatus,
  onEditService,
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
      case 'scheduled':
        return 'bg-purple-100 text-purple-700 border-purple-200';
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
      case 'scheduled':
        return 'Agendado';
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
      case 'scheduled':
        return '📅';
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

  const handleServiceClick = (service: TechnicianService) => {
    onServiceClick?.(service);
  };

  // Handlers para ações do menu
  const handleViewDetails = (service: TechnicianService) => {
    onServiceClick?.(service);
  };

  const handleUpdateStatus = (service: TechnicianService) => {
    onUpdateStatus?.(service);
  };

  return (
    <div className="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-3 sm:p-4 border border-yellow-200 shadow-lg overflow-visible">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div className="flex items-center gap-2 sm:gap-3">
          <div className="p-1.5 sm:p-2 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-lg">
            <WrenchScrewdriverIcon className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
          </div>
          <div>
            <h3 className="font-bold text-gray-900 text-sm sm:text-base">
              Serviços Recentes
            </h3>
            <p className="text-yellow-600 text-xs sm:text-sm">
              {services?.length || 0} serviço{services?.length !== 1 ? 's' : ''}
            </p>
          </div>
        </div>

        {/* Botão para mostrar/ocultar todos os valores */}
        {services?.length > 0 && (
          <button
            onClick={toggleAllValues}
            className="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 bg-white/80 hover:bg-white rounded-lg border border-yellow-200 text-xs sm:text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200 w-full sm:w-auto"
          >
            {showAllValues ? (
              <>
                <EyeSlashIcon className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                <span className="hidden sm:inline">Ocultar Valores</span>
                <span className="sm:hidden">Ocultar</span>
              </>
            ) : (
              <>
                <EyeIcon className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                <span className="hidden sm:inline">Mostrar Valores</span>
                <span className="sm:hidden">Mostrar</span>
              </>
            )}
          </button>
        )}
      </div>

      {/* Lista de serviços */}
      <div className="space-y-2.5 sm:space-y-3 relative overflow-visible">
        {services?.length ? (
          services.map((service, index) => (
            <div
              key={service.id || index}
              className="bg-white/80 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-yellow-100 hover:border-yellow-200 transition-all duration-200 cursor-pointer hover:shadow-md"
              onClick={() => handleServiceClick(service)}
            >
              {/* Header do serviço */}
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 mb-2.5 sm:mb-3">
                <div className="flex items-center gap-2 sm:gap-3">
                  <div className="p-1 sm:p-1.5 bg-gradient-to-r from-yellow-100 to-orange-100 rounded-md">
                    <WrenchScrewdriverIcon className="h-3 w-3 sm:h-3.5 sm:w-3.5 text-yellow-600" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="font-bold text-gray-900 text-xs sm:text-sm font-mono">
                      #{service.service_number}
                    </div>
                    <div className="text-xs text-gray-600 truncate">
                      {service.description}
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <div
                    className={`text-xs px-2 py-1 rounded-full font-medium border ${getStatusColor(service.status)} self-start sm:self-auto`}
                  >
                    <span className="hidden sm:inline">
                      {getStatusIcon(service.status)}{' '}
                    </span>
                    {getStatusText(service.status)}
                  </div>
                  <ServiceActionsMenu
                    service={service}
                    onViewDetails={handleViewDetails}
                    onUpdateStatus={handleUpdateStatus}
                    onEditService={onEditService}
                  />
                </div>
              </div>

              {/* Detalhes compactos */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
                {/* Data */}
                <div className="flex items-center gap-1.5 sm:gap-2">
                  <CalendarDaysIcon className="h-3 w-3 sm:h-3.5 sm:w-3.5 text-gray-500 flex-shrink-0" />
                  <span className="text-xs text-gray-700">
                    {formatDate(service.created_at || '')}
                  </span>
                </div>

                {/* Status */}
                <div className="flex items-center gap-1.5 sm:gap-2">
                  <ClockIcon className="h-3 w-3 sm:h-3.5 sm:w-3.5 text-gray-500 flex-shrink-0" />
                  <span className="text-xs text-gray-700 capitalize">
                    {getStatusText(service.status)}
                  </span>
                </div>

                {/* Valor com toggle */}
                <div className="flex items-center justify-between">
                  <CurrencyDollarIcon className="h-3 w-3 sm:h-3.5 sm:w-3.5 text-gray-500 flex-shrink-0" />
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
                        <EyeSlashIcon className="h-2.5 w-2.5 sm:h-3 sm:w-3 text-gray-500" />
                      ) : (
                        <EyeIcon className="h-2.5 w-2.5 sm:h-3 sm:w-3 text-gray-500" />
                      )}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="bg-white/80 backdrop-blur-sm rounded-lg p-4 sm:p-6 text-center border border-yellow-100">
            <div className="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-3">
              <WrenchScrewdriverIcon className="h-4 w-4 sm:h-5 sm:w-5 text-yellow-500" />
            </div>
            <h4 className="text-gray-900 font-semibold text-xs sm:text-sm mb-1">
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
        <div className="mt-3 sm:mt-4 p-2.5 sm:p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
          <div className="grid grid-cols-4 gap-2 sm:gap-3 text-center">
            <div>
              <div className="text-base sm:text-lg font-bold text-yellow-600">
                {
                  services.filter(
                    (s) => s.status?.toLowerCase() === 'completed'
                  ).length
                }
              </div>
              <div className="text-xs text-gray-600">Concluídos</div>
            </div>
            <div>
              <div className="text-base sm:text-lg font-bold text-blue-600">
                {
                  services.filter(
                    (s) => s.status?.toLowerCase() === 'in_progress'
                  ).length
                }
              </div>
              <div className="text-xs text-gray-600">Em Andamento</div>
            </div>
            <div>
              <div className="text-base sm:text-lg font-bold text-purple-600">
                {
                  services.filter(
                    (s) => s.status?.toLowerCase() === 'scheduled'
                  ).length
                }
              </div>
              <div className="text-xs text-gray-600">Agendados</div>
            </div>
            <div>
              <div className="text-base sm:text-lg font-bold text-green-600">
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
