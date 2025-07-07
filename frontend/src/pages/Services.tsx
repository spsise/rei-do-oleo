import React, { useState } from 'react';
import { Pagination } from '../components/Common/Pagination';
import { ServiceFiltersComponent } from '../components/Service/ServiceFilters';
import { ServiceForm } from '../components/Service/ServiceForm';
import { ServiceSearchForm } from '../components/Service/ServiceSearchForm';
import { ServiceTable } from '../components/Service/ServiceTable';
import {
  useCreateService,
  useDeleteService,
  useSearchServiceByNumber,
  useServices,
  useUpdateService,
} from '../hooks/useServices';
import type {
  CreateServiceData,
  Service,
  ServiceFilters,
  UpdateServiceData,
} from '../types/service';

interface ModalState {
  isOpen: boolean;
  type: 'create' | 'edit' | 'search';
  service?: Service;
}

export const ServicesPage: React.FC = () => {
  const [modal, setModal] = useState<ModalState>({
    isOpen: false,
    type: 'create',
  });
  const [filters, setFilters] = useState<ServiceFilters>({ per_page: 15 });
  const [searchResult, setSearchResult] = useState<Service | null>(null);

  // Hooks do React Query
  const { data: servicesData, isLoading } = useServices(filters);
  const createServiceMutation = useCreateService();
  const updateServiceMutation = useUpdateService();
  const deleteServiceMutation = useDeleteService();
  const searchByNumberMutation = useSearchServiceByNumber();

  // Abrir modal
  const openModal = (type: ModalState['type'], service?: Service) => {
    setModal({ isOpen: true, type, service });
    setSearchResult(null);
  };

  // Fechar modal
  const closeModal = () => {
    setModal({ isOpen: false, type: 'create' });
  };

  // Criar serviço
  const handleCreateService = async (data: CreateServiceData) => {
    await createServiceMutation.mutateAsync(data);
    closeModal();
  };

  // Atualizar serviço
  const handleUpdateService = async (data: UpdateServiceData) => {
    if (!modal.service) return;
    await updateServiceMutation.mutateAsync({ id: modal.service.id, data });
    closeModal();
  };

  // Excluir serviço
  const handleDeleteService = async (service: Service) => {
    await deleteServiceMutation.mutateAsync(service.id);
  };

  // Buscar por número
  const handleSearchByNumber = async (serviceNumber: string) => {
    try {
      const result = await searchByNumberMutation.mutateAsync({
        service_number: serviceNumber,
      });
      setSearchResult(result);
      setModal({ isOpen: false, type: 'create' });
    } catch {
      setSearchResult(null);
    }
  };

  // Aplicar filtros
  const handleFiltersChange = (newFilters: ServiceFilters) => {
    setFilters(newFilters);
  };

  // Limpar filtros
  const handleClearFilters = () => {
    setFilters({ per_page: 15 });
  };

  // Mudar página
  const handlePageChange = (page: number) => {
    setFilters((prev) => ({ ...prev, page }));
  };

  const services = servicesData?.data || [];
  const pagination = {
    current_page: servicesData?.current_page || 1,
    last_page: servicesData?.last_page || 1,
    per_page: servicesData?.per_page || 15,
    total: servicesData?.total || 0,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-10xl mx-auto px-0 sm:px-0 lg:px-0 py-0">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Gestão de Serviços
              </h1>
              <p className="mt-2 text-gray-600">
                Gerencie os serviços, acompanhe o status e mantenha o controle
                dos atendimentos.
              </p>
            </div>
            <div className="flex space-x-3">
              <button
                onClick={() => openModal('search')}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Buscar Serviço
              </button>
              <button
                onClick={() => openModal('create')}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Novo Serviço
              </button>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <ServiceFiltersComponent
          filters={filters}
          onFiltersChange={handleFiltersChange}
          onClearFilters={handleClearFilters}
        />

        {/* Resultado da busca */}
        {searchResult && (
          <div className="mb-6 bg-white p-6 rounded-lg shadow-sm border">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              Resultado da Busca
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Número:
                </span>
                <p className="text-sm text-gray-900">
                  {searchResult.service_number || 'N/A'}
                </p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Cliente:
                </span>
                <p className="text-sm text-gray-900">
                  {searchResult.client?.name || 'N/A'}
                </p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Veículo:
                </span>
                <p className="text-sm text-gray-900">
                  {searchResult.vehicle
                    ? `${searchResult.vehicle.brand} ${searchResult.vehicle.model}`
                    : 'N/A'}
                </p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Status:
                </span>
                <p className="text-sm text-gray-900">
                  {searchResult.status?.name || 'N/A'}
                </p>
              </div>
            </div>
            <div className="mt-4 flex space-x-3">
              <button
                onClick={() => openModal('edit', searchResult)}
                className="px-3 py-1 text-sm text-blue-600 hover:text-blue-700"
              >
                Editar
              </button>
              <button
                onClick={() => setSearchResult(null)}
                className="px-3 py-1 text-sm text-gray-600 hover:text-gray-700"
              >
                Fechar
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Tabela de Serviços - FORA do container de página */}
      <div className="w-full overflow-x-auto">
        <div className="max-w-7xl mx-auto">
          <ServiceTable
            services={services}
            onEdit={(service) => openModal('edit', service)}
            onDelete={handleDeleteService}
            loading={isLoading}
          />
        </div>
      </div>

      {/* Paginação */}
      {pagination.last_page > 1 && (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <Pagination
            currentPage={pagination.current_page}
            lastPage={pagination.last_page}
            total={pagination.total}
            perPage={pagination.per_page}
            onPageChange={handlePageChange}
          />
        </div>
      )}

      {/* Modal */}
      {modal.isOpen && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-medium text-gray-900">
                  {modal.type === 'create' && 'Novo Serviço'}
                  {modal.type === 'edit' && 'Editar Serviço'}
                  {modal.type === 'search' && 'Buscar Serviço'}
                </h3>
                <button
                  onClick={closeModal}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              {modal.type === 'search' ? (
                <ServiceSearchForm
                  onSearchByNumber={handleSearchByNumber}
                  loading={searchByNumberMutation.isPending}
                />
              ) : (
                <ServiceForm
                  service={modal.service}
                  onSubmit={(data) => {
                    if (modal.type === 'create') {
                      handleCreateService(data as CreateServiceData);
                    } else {
                      handleUpdateService(data as UpdateServiceData);
                    }
                  }}
                  onCancel={closeModal}
                  loading={
                    createServiceMutation.isPending ||
                    updateServiceMutation.isPending
                  }
                />
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
