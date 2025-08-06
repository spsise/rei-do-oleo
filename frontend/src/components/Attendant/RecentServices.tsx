import {
  CalendarIcon,
  ClockIcon,
  TruckIcon,
  UserIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { useServiceList } from '../../hooks/useAttendantServices';
import { useServiceStatus } from '../../hooks/useServiceStatus';
import { type AttendantService } from '../../types/attendant';
import { ServiceActionsMenu } from './ServiceActionsMenu';
import { UpdateStatusModal } from './UpdateStatusModal';

export const RecentServices: React.FC = () => {
  const { services, isLoading, refetch } = useServiceList({ per_page: 5 });
  const { updateServiceStatus, isUpdatingStatus } = useServiceStatus();

  // Estados para modais
  const [selectedService, setSelectedService] =
    useState<AttendantService | null>(null);
  const [showUpdateStatusModal, setShowUpdateStatusModal] = useState(false);

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'high':
        return 'text-red-600 bg-red-100';
      case 'medium':
        return 'text-yellow-600 bg-yellow-100';
      case 'low':
        return 'text-green-600 bg-green-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  const getPriorityIcon = (priority: string) => {
    switch (priority) {
      case 'high':
        return '🔴';
      case 'medium':
        return '🟡';
      case 'low':
        return '🟢';
      default:
        return '⚪';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'completed':
      case 'concluído':
        return 'text-green-600 bg-green-100';
      case 'in_progress':
      case 'em_andamento':
        return 'text-blue-600 bg-blue-100';
      case 'pending':
      case 'pendente':
        return 'text-yellow-600 bg-yellow-100';
      case 'cancelled':
      case 'cancelado':
        return 'text-red-600 bg-red-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours > 0) {
      return `${hours}h ${mins}min`;
    }
    return `${mins}min`;
  };

  // Handlers para ações do menu
  const handleViewDetails = (_service: AttendantService) => {
    // TODO: Implementar modal de detalhes
    console.log(_service);
  };

  const handleUpdateStatus = (service: AttendantService) => {
    setSelectedService(service);
    setShowUpdateStatusModal(true);
  };

  const handleUpdateStatusSubmit = async (
    serviceId: number,
    statusId: number,
    notes?: string
  ) => {
    try {
      await updateServiceStatus(serviceId, statusId, notes);
      setShowUpdateStatusModal(false);
      setSelectedService(null);
      refetch(); // Recarrega os dados após a atualização
    } catch {
      // Erro já tratado no hook
    }
  };

  const handleCloseUpdateStatusModal = () => {
    setShowUpdateStatusModal(false);
    setSelectedService(null);
  };

  if (isLoading) {
    return (
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">
          Serviços Recentes
        </h3>
        <div className="space-y-4">
          {[...Array(3)].map((_, index) => (
            <div key={index} className="animate-pulse">
              <div className="flex items-center justify-between">
                <div className="flex-1">
                  <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                  <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
                <div className="h-8 w-16 bg-gray-200 rounded"></div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-gray-900">
          Serviços Recentes
        </h3>
        <button className="text-sm text-blue-600 hover:text-blue-700 font-medium">
          Ver todos
        </button>
      </div>

      {services?.data && services.data.length > 0 ? (
        <div className="space-y-4">
          {services.data.map((service) => (
            <div
              key={service.id}
              className="p-4 border border-gray-100 rounded-lg hover:border-gray-200 hover:shadow-sm transition-all duration-200 cursor-pointer group relative"
            >
              <div className="flex items-start justify-between">
                <div className="flex-1 min-w-0">
                  {/* Service Header */}
                  <div className="flex items-center gap-2 mb-2">
                    <span className="text-sm font-medium text-gray-900 truncate">
                      #{service.service_number}
                    </span>
                    <span
                      className={`px-2 py-1 text-xs font-medium rounded-full ${getPriorityColor(service.priority)}`}
                    >
                      {getPriorityIcon(service.priority)} {service.priority}
                    </span>
                    <span
                      className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(service.status)}`}
                    >
                      {service.status}
                    </span>
                  </div>

                  {/* Service Description */}
                  <p className="text-sm text-gray-700 mb-3 line-clamp-2">
                    {service.description}
                  </p>

                  {/* Service Details */}
                  <div className="flex items-center gap-4 text-xs text-gray-500">
                    <div className="flex items-center gap-1">
                      <UserIcon className="h-3 w-3" />
                      <span>{service.client.name}</span>
                    </div>

                    <div className="flex items-center gap-1">
                      <TruckIcon className="h-3 w-3" />
                      <span>
                        {service.vehicle.brand} {service.vehicle.model}
                      </span>
                    </div>

                    <div className="flex items-center gap-1">
                      <ClockIcon className="h-3 w-3" />
                      <span>{formatDuration(service.estimated_duration)}</span>
                    </div>

                    <div className="flex items-center gap-1">
                      <CalendarIcon className="h-3 w-3" />
                      <span>{formatDate(service.created_at)}</span>
                    </div>
                  </div>
                </div>

                {/* Menu de Ações - visível apenas em desktop */}
                <div className="hidden sm:block">
                  <ServiceActionsMenu
                    service={service}
                    onViewDetails={handleViewDetails}
                    onUpdateStatus={handleUpdateStatus}
                  />
                </div>
              </div>

              {/* Menu de Ações - posicionado no canto superior direito apenas em mobile */}
              <div className="absolute top-2 right-2 sm:hidden">
                <ServiceActionsMenu
                  service={service}
                  onViewDetails={handleViewDetails}
                  onUpdateStatus={handleUpdateStatus}
                />
              </div>

              {/* Service Notes (if any) */}
              {service.notes && (
                <div className="mt-3 pt-3 border-t border-gray-100">
                  <span className="text-xs text-gray-600 italic">
                    "{service.notes}"
                  </span>
                </div>
              )}
            </div>
          ))}
        </div>
      ) : (
        <div className="text-center py-8">
          <div className="p-4 bg-gray-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
            <ClockIcon className="h-8 w-8 text-gray-400" />
          </div>
          <h4 className="text-gray-900 font-medium mb-2">
            Nenhum serviço recente
          </h4>
          <p className="text-gray-600 text-sm">
            Os serviços criados aparecerão aqui
          </p>
        </div>
      )}

      {/* Summary */}
      {services?.data && services.data.length > 0 && (
        <div className="mt-6 pt-4 border-t border-gray-100">
          <div className="flex items-center justify-between text-sm text-gray-600">
            <span>
              Mostrando {services.data.length} de {services.total} serviços
            </span>
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                <span>
                  Concluídos:{' '}
                  {
                    services.data.filter((s) =>
                      s.status.toLowerCase().includes('concluído')
                    ).length
                  }
                </span>
              </div>
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <span>
                  Pendentes:{' '}
                  {
                    services.data.filter((s) =>
                      s.status.toLowerCase().includes('pendente')
                    ).length
                  }
                </span>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modal de Atualização de Status */}
      <UpdateStatusModal
        isOpen={showUpdateStatusModal}
        onClose={handleCloseUpdateStatusModal}
        service={selectedService}
        onUpdateStatus={handleUpdateStatusSubmit}
        isLoading={isUpdatingStatus}
      />
    </div>
  );
};
