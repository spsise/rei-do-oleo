import {
  CalendarDaysIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  MapPinIcon,
  TruckIcon,
  UserIcon,
  WrenchScrewdriverIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type Service, type ServiceItem } from '../../types/service';
import { type TechnicianService } from '../../types/technician';

interface ServiceDetailsModalProps {
  isOpen: boolean;
  onClose: () => void;
  service: TechnicianService | null;
  clientName?: string;
  vehicleInfo?: string;
  serviceDetails?: Service | null;
  isLoadingDetails?: boolean;
  isFetchingDetails?: boolean;
  onEditService?: (service: Service) => void;
}

export const ServiceDetailsModal: React.FC<ServiceDetailsModalProps> = ({
  isOpen,
  onClose,
  service,
  clientName,
  vehicleInfo,
  serviceDetails,
  isLoadingDetails = false,
  isFetchingDetails = false,
  onEditService,
}) => {
  if (!isOpen || !service) return null;

  // Usar dados completos se disponíveis, senão usar dados básicos
  const displayService = serviceDetails || service;

  // Type guards para verificar o tipo de serviço
  const isService = (obj: Service | TechnicianService): obj is Service => {
    return 'scheduled_date' in obj;
  };

  const isTechnicianService = (
    obj: Service | TechnicianService
  ): obj is TechnicianService => {
    return 'scheduled_at' in obj;
  };

  // Função para obter a data agendada
  const getScheduledDate = (): string => {
    if (isService(displayService)) {
      return displayService.scheduled_date || '';
    }
    if (isTechnicianService(displayService)) {
      return displayService.scheduled_at || '';
    }
    return '';
  };

  // Função para obter a quilometragem
  const getMileage = (): number => {
    if (isService(displayService)) {
      return displayService.vehicle?.mileage_at_service || 0;
    }
    if (isTechnicianService(displayService)) {
      return displayService.mileage_at_service || 0;
    }
    return 0;
  };

  // Função para obter o status do serviço
  const getServiceStatus = (): string => {
    if (serviceDetails?.status?.name) {
      return serviceDetails.status.name;
    }
    if (
      'status' in displayService &&
      typeof displayService.status === 'string'
    ) {
      return displayService.status;
    }
    return 'pending';
  };

  // Função para obter o valor total
  const getTotalAmount = (): number => {
    if (serviceDetails?.financial?.total_amount) {
      return parseFloat(serviceDetails.financial.total_amount);
    }
    if (
      'total_amount' in displayService &&
      typeof displayService.total_amount === 'number'
    ) {
      return displayService.total_amount;
    }
    return 0;
  };

  // Função para obter os itens do serviço
  const getServiceItems = () => {
    if (serviceDetails?.items) {
      return serviceDetails.items;
    }
    return [];
  };

  // Função para obter as observações
  const getServiceNotes = (): string => {
    if (serviceDetails?.internal_notes) {
      return serviceDetails.internal_notes;
    }
    return '';
  };

  // Função para obter as observações detalhadas
  const getServiceObservations = (): string => {
    if (serviceDetails?.observations) {
      return serviceDetails.observations;
    }
    return '';
  };

  // Se estiver carregando detalhes, mostrar loading
  if (isLoadingDetails) {
    return (
      <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto animate-modalSlideInUp">
          <div className="p-12 text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <h3 className="text-lg font-semibold text-gray-900 mb-2">
              Carregando detalhes do serviço...
            </h3>
            <p className="text-gray-600">
              Aguarde enquanto buscamos as informações completas.
            </p>
          </div>
        </div>
      </div>
    );
  }

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

    try {
      // Tenta diferentes formatos de data
      let date: Date;

      // Se a data já está no formato brasileiro (dd/mm/yyyy)
      if (dateString.includes('/')) {
        const [day, month, year] = dateString.split('/');
        date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
      } else {
        // Tenta o formato ISO padrão
        date = new Date(dateString);
      }

      // Verifica se a data é válida
      if (isNaN(date.getTime())) {
        return 'Data inválida';
      }

      return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return 'Data inválida';
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto animate-modalSlideInUp relative">
        {/* Overlay de atualização */}
        {isFetchingDetails && (
          <div className="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-2xl z-20 flex items-center justify-center">
            <div className="text-center">
              <div className="animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent mx-auto mb-4"></div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">
                Atualizando dados do serviço...
              </h3>
              <p className="text-gray-600 text-sm">
                Aguarde enquanto sincronizamos as informações.
              </p>
            </div>
          </div>
        )}

        {/* Header */}
        <div className="sticky top-0 bg-white rounded-t-2xl px-4 pt-3 pb-2 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl shadow-lg">
                <WrenchScrewdriverIcon className="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 className="text-2xl font-bold text-gray-900">
                  Detalhes do Serviço
                </h3>
                <p className="text-gray-600 text-sm">
                  Informações completas do serviço #
                  {displayService.service_number}
                </p>
              </div>
            </div>
            <button
              onClick={onClose}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="p-4 space-y-4">
          {/* Informações Principais */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Card do Serviço */}
            <div className="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
              <h4 className="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <WrenchScrewdriverIcon className="h-5 w-5 text-yellow-600" />
                Informações do Serviço
              </h4>

              <div className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-gray-600">
                    Número do Serviço
                  </label>
                  <p className="text-lg font-mono font-bold text-gray-900">
                    #{displayService.service_number}
                  </p>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-600">
                    Descrição
                  </label>
                  <p className="text-gray-900">
                    {displayService.description || 'Sem descrição'}
                  </p>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-600">
                    Status
                  </label>
                  <div className="mt-1">
                    <span
                      className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border ${getStatusColor(getServiceStatus())}`}
                    >
                      <span>{getStatusIcon(getServiceStatus())}</span>
                      {getStatusText(getServiceStatus())}
                    </span>
                  </div>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-600">
                    Valor Total
                  </label>
                  <p className="text-2xl font-bold text-green-600">
                    {formatCurrency(getTotalAmount())}
                  </p>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-600">
                    Data de Criação
                  </label>
                  <p className="text-gray-900 flex items-center gap-2">
                    <CalendarDaysIcon className="h-4 w-4 text-gray-500" />
                    {formatDate(displayService.created_at)}
                  </p>
                </div>

                {/* Data de Agendamento */}
                {getScheduledDate() && (
                  <div>
                    <label className="text-sm font-medium text-gray-600">
                      Data de Agendamento
                    </label>
                    <p className="text-gray-900 flex items-center gap-2">
                      <CalendarDaysIcon className="h-4 w-4 text-green-500" />
                      {formatDate(getScheduledDate())}
                    </p>
                  </div>
                )}
              </div>
            </div>

            {/* Card do Cliente e Veículo */}
            <div className="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
              <h4 className="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <UserIcon className="h-5 w-5 text-blue-600" />
                Cliente e Veículo
              </h4>

              <div className="space-y-4">
                {clientName && (
                  <div>
                    <label className="text-sm font-medium text-gray-600">
                      Cliente
                    </label>
                    <p className="text-gray-900 font-medium">{clientName}</p>
                  </div>
                )}

                {vehicleInfo && (
                  <div>
                    <label className="text-sm font-medium text-gray-600">
                      Veículo
                    </label>
                    <p className="text-gray-900 font-medium flex items-center gap-2">
                      <MapPinIcon className="h-4 w-4 text-gray-500" />
                      {vehicleInfo}
                    </p>
                  </div>
                )}

                {/* Quilometragem */}
                {getMileage() > 0 && (
                  <div>
                    <label className="text-sm font-medium text-gray-600">
                      Quilometragem no Serviço
                    </label>
                    <p className="text-gray-900 font-medium flex items-center gap-2">
                      <TruckIcon className="h-4 w-4 text-gray-500" />
                      {getMileage().toLocaleString('pt-BR')} km
                    </p>
                  </div>
                )}

                <div className="bg-blue-100 rounded-lg p-3">
                  <p className="text-sm text-blue-800">
                    <strong>Dica:</strong> Para mais informações sobre o cliente
                    e veículo, consulte o histórico completo na seção de
                    clientes.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Seção de Itens do Serviço */}
          <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h4 className="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
              <DocumentTextIcon className="h-5 w-5 text-gray-600" />
              Itens do Serviço
            </h4>

            {/* Assuming serviceDetails?.items is an array of items */}
            {getServiceItems().length > 0 ? (
              <div className="space-y-3">
                {getServiceItems().map((item: ServiceItem, index: number) => (
                  <div
                    key={index}
                    className="bg-gray-50 rounded-lg p-4 border border-gray-100"
                  >
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex-1">
                        <h5 className="font-semibold text-gray-900">
                          {item.product?.name || `Produto #${item.product_id}`}
                        </h5>
                        {item.product?.sku && (
                          <p className="text-sm text-gray-600">
                            SKU: {item.product.sku}
                          </p>
                        )}
                      </div>
                      <div className="text-right">
                        <div className="font-bold text-green-600">
                          {formatCurrency(parseFloat(String(item.total_price)))}
                        </div>
                        <div className="text-sm text-gray-500">
                          {item.quantity}x{' '}
                          {formatCurrency(parseFloat(String(item.unit_price)))}
                        </div>
                      </div>
                    </div>
                    {item.notes && (
                      <div className="mt-2 pt-2 border-t border-gray-200">
                        <p className="text-sm text-gray-700 italic">
                          "{item.notes}"
                        </p>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <div className="bg-gray-50 rounded-lg p-4 text-center">
                <div className="text-gray-400 text-4xl mb-3">📋</div>
                <h5 className="text-lg font-semibold text-gray-900 mb-2">
                  Nenhum item registrado
                </h5>
                <p className="text-gray-600 text-sm">
                  Este serviço não possui itens de produto registrados.
                </p>
              </div>
            )}
          </div>

          {/* Seção de Observações */}
          <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h4 className="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
              <DocumentTextIcon className="h-5 w-5 text-gray-600" />
              Observações e Notas
            </h4>

            {/* Assuming serviceDetails?.internal_notes and serviceDetails?.observations are strings */}
            {getServiceNotes() || getServiceObservations() ? (
              <div className="space-y-4">
                {getServiceNotes() && (
                  <div>
                    <h5 className="font-semibold text-gray-900 mb-2">
                      Observações Gerais
                    </h5>
                    <div className="bg-blue-50 rounded-lg p-4 border border-blue-200">
                      <p className="text-gray-800 whitespace-pre-wrap">
                        {getServiceNotes()}
                      </p>
                    </div>
                  </div>
                )}

                {getServiceObservations() && (
                  <div>
                    <h5 className="font-semibold text-gray-900 mb-2">
                      Observações Detalhadas
                    </h5>
                    <div className="bg-purple-50 rounded-lg p-4 border border-purple-200">
                      <p className="text-gray-800 whitespace-pre-wrap">
                        {getServiceObservations()}
                      </p>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="bg-gray-50 rounded-lg p-4 text-center">
                <div className="text-gray-400 text-4xl mb-3">📝</div>
                <h5 className="text-lg font-semibold text-gray-900 mb-2">
                  Nenhuma observação
                </h5>
                <p className="text-gray-600 text-sm">
                  Este serviço não possui observações ou notas registradas.
                </p>
              </div>
            )}
          </div>

          {/* Resumo Financeiro */}
          <div className="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
            <h4 className="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
              <CurrencyDollarIcon className="h-5 w-5 text-green-600" />
              Resumo Financeiro
            </h4>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">
                  {formatCurrency(getTotalAmount())}
                </div>
                <div className="text-sm text-gray-600">Valor Total</div>
              </div>

              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">
                  {getStatusText(getServiceStatus())}
                </div>
                <div className="text-sm text-gray-600">Status Atual</div>
              </div>

              <div className="text-center">
                <div className="text-2xl font-bold text-purple-600">
                  {formatDate(displayService.created_at)}
                </div>
                <div className="text-sm text-gray-600">Data de Criação</div>
              </div>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-white rounded-b-2xl px-4 py-2 border-t border-gray-100">
          <div className="flex justify-end gap-3">
            <button
              onClick={onClose}
              className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
            >
              Fechar
            </button>
            <button
              onClick={() => {
                if (onEditService) {
                  if (serviceDetails) {
                    onEditService(serviceDetails);
                  } else {
                    // Converter TechnicianService para Service se necessário
                    const serviceAsService: Service = {
                      id: service.id,
                      service_number: service.service_number,
                      description: service.description,
                      status: {
                        name: service.status,
                        id: null,
                        label: null,
                        color: null,
                      },
                      financial: {
                        total_amount: service.total_amount.toString(),
                        items_total: 0,
                        items_total_formatted: 'R$ 0,00',
                        total_amount_formatted: `R$ ${service.total_amount.toFixed(2)}`,
                      },
                      created_at: service.created_at,
                      updated_at: service.created_at,
                      observations: service.observations,
                      internal_notes: service.notes,
                      items:
                        service.items?.map((item) => ({
                          id: 0,
                          service_id: service.id,
                          product_id: item.product_id,
                          quantity: item.quantity,
                          unit_price: item.unit_price,
                          total_price: item.total_price,
                          notes: item.notes,
                          created_at: service.created_at,
                          updated_at: service.created_at,
                          product: item.product
                            ? {
                                id: item.product.id,
                                name: item.product.name,
                                sku: item.product.sku,
                                category: item.product.category?.name || '',
                                unit: 'un',
                                current_stock: item.product.stock_quantity,
                              }
                            : undefined,
                        })) || [],
                    };
                    onEditService(serviceAsService);
                  }
                } else {
                  // Editar serviço
                }
              }}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              Editar Serviço
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
