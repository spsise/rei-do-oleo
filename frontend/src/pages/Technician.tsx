import React, { useEffect, useState } from 'react';
import { toast } from 'react-hot-toast';
import {
  ClientSearchForm,
  ClientSearchResults,
  EditServiceModal,
  NewServiceModal,
  ServiceDetailsModal,
  TechnicianHeader,
  UpdateStatusModal,
} from '../components/Technician';

import { useUpdateServiceWithItems } from '../hooks/useServices';
import { useServiceStatus } from '../hooks/useServiceStatus';
import { useTechnician } from '../hooks/useTechnician';
import { serviceService } from '../services';
import '../styles/Technician.css';
import { type Service, type UpdateServiceData } from '../types/service';
import {
  type CreateTechnicianServiceData,
  type TechnicianService,
} from '../types/technician';
import {
  ServiceAdapter,
  type UnifiedServiceData,
} from '../utils/serviceAdapter';

// Tipo específico para edição de serviço que inclui itens
interface EditServiceData extends UpdateServiceData {
  items?: Array<{
    product_id: number;
    quantity: number;
    unit_price: number;
    discount?: number;
    notes?: string;
  }>;
}

export const TechnicianPage: React.FC = () => {
  const {
    // Estado
    searchType,
    searchValue,
    isSearching,
    searchResult,
    showNewServiceForm,
    isCreatingService,
    newServiceData,
    products,
    categories,
    isLoadingProducts,
    productSearchTerm,
    selectedService,
    showServiceDetails,
    serviceDetails,
    isLoadingServiceDetails,
    isFetchingServiceDetails,

    // Ações
    setSearchType,
    setSearchValue,
    setNewServiceData,
    setShowNewServiceForm,
    handleSearch,
    handleVoiceResult,
    handleCreateNewService,
    handleSubmitService,
    handleServiceClick,
    handleCloseServiceDetails,

    // Métodos para produtos
    loadActiveProducts,
    loadCategories,
    searchProducts,
    addProductToService,
    removeProductFromService,
    updateServiceItemQuantity,
    updateServiceItemPrice,
    updateServiceItemNotes,
    calculateItemsTotal,
    calculateFinalTotal,
  } = useTechnician();

  // Carregar produtos e categorias quando a página for montada
  useEffect(() => {
    loadActiveProducts();
    loadCategories();
  }, [loadActiveProducts, loadCategories]);

  // Estados para modal de atualização de status
  const { updateServiceStatus, isUpdatingStatus } = useServiceStatus();
  const [selectedServiceForUpdate, setSelectedServiceForUpdate] =
    useState<TechnicianService | null>(null);
  const [showUpdateStatusModal, setShowUpdateStatusModal] = useState(false);

  // Estados para modal de edição de serviço
  const updateServiceWithItemsMutation = useUpdateServiceWithItems();
  const [selectedServiceForEdit, setSelectedServiceForEdit] =
    useState<UnifiedServiceData | null>(null);
  const [showEditServiceModal, setShowEditServiceModal] = useState(false);

  const handleUpdateStatus = (service: TechnicianService) => {
    setSelectedServiceForUpdate(service);
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
      setSelectedServiceForUpdate(null);

      if (searchResult) {
        handleSearch();
      }
    } catch {
      // Erro já tratado no hook
    }
  };

  const handleCloseUpdateStatusModal = () => {
    setShowUpdateStatusModal(false);
    setSelectedServiceForUpdate(null);
  };

  // Funções para edição de serviço
  const handleEditService = async (service: TechnicianService) => {
    try {
      // SEMPRE carregar dados completos do serviço para edição
      const response = await serviceService.getService(service.id);
      const completeService = response.data;

      // Verificar se o serviço foi carregado corretamente
      if (!completeService) {
        throw new Error('Service not found');
      }

      // Converter para UnifiedServiceData
      const unifiedService = ServiceAdapter.fromService(completeService);
      setSelectedServiceForEdit(unifiedService);
      setShowEditServiceModal(true);
    } catch (error) {
      console.error(
        'handleEditService - Error loading complete service:',
        error
      );
      toast.error('Erro ao carregar dados do serviço. Tente novamente.');
    }
  };

  const handleEditServiceForDetails = (service: Service) => {
    // Converter Service para UnifiedServiceData
    const unifiedService = ServiceAdapter.fromService(service);
    setSelectedServiceForEdit(unifiedService);
    setShowEditServiceModal(true);
  };

  const handleEditServiceSubmit = async (
    serviceId: number,
    data: EditServiceData
  ) => {
    try {
      // Preparar dados para a nova estrutura unificada
      const { items, ...serviceData } = data;

      const unifiedData = {
        service: serviceData,
        items: {
          operation: 'update' as const,
          remove_unsent: true, // Remove itens não enviados
          data: items || [],
        },
      };

      // Atualizar serviço e itens em uma única requisição
      await updateServiceWithItemsMutation.mutateAsync({
        id: serviceId,
        data: unifiedData,
      });

      setShowEditServiceModal(false);
      setSelectedServiceForEdit(null);

      // Recarregar dados se necessário
      if (searchResult) {
        handleSearch();
      }

      toast.success('Serviço atualizado com sucesso!');
    } catch (error) {
      console.error('Erro ao editar serviço:', error);

      // Mensagem de erro mais específica
      const errorMessage = (
        error as { response?: { data?: { message?: string } } }
      )?.response?.data?.message;

      if (errorMessage?.includes('Service not found')) {
        toast.error('Serviço não encontrado. Tente recarregar a página.');
      } else {
        toast.error('Erro ao salvar alterações do serviço');
      }
    }
  };

  const handleCloseEditServiceModal = () => {
    setShowEditServiceModal(false);
    setSelectedServiceForEdit(null);
  };

  const handleServiceDataChange = (
    data: Partial<CreateTechnicianServiceData>
  ) => {
    setNewServiceData((prev) => ({ ...prev, ...data }));
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Background Pattern */}
      <div className="absolute inset-0 bg-grid-pattern opacity-5 z-0"></div>

      <div className="relative max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        {/* Header com design melhorado */}
        <div className="mb-2">
          <TechnicianHeader />
        </div>

        {/* Container principal com animações */}
        <div className="space-y-6 animate-fadeIn">
          {/* Search Form com design aprimorado */}
          <div className="transform transition-all duration-300 hover:scale-[1.01]">
            <ClientSearchForm
              searchType={searchType}
              searchValue={searchValue}
              isSearching={isSearching}
              onSearchTypeChange={setSearchType}
              onSearchValueChange={setSearchValue}
              onSearch={handleSearch}
              onVoiceResult={handleVoiceResult}
            />
          </div>

          {/* Search Results com animação de entrada */}
          {searchResult && (
            <div className="animate-slideInUp">
              <ClientSearchResults
                searchResult={searchResult}
                onCreateNewService={handleCreateNewService}
                onServiceClick={handleServiceClick}
                onUpdateStatus={handleUpdateStatus}
                onEditService={handleEditService}
              />
            </div>
          )}

          {/* Estado vazio melhorado */}
          {!searchResult && !isSearching && searchValue && (
            <div className="text-center py-12">
              <div className="max-w-md mx-auto">
                <div className="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                  <svg
                    className="w-12 h-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-4">
                  Busque um Cliente
                </h3>
                <p className="text-gray-600 mb-8 leading-relaxed">
                  Digite a placa do veículo ou documento do cliente para começar
                  a registrar serviços. Você também pode usar o microfone para
                  busca por voz.
                </p>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span>Placa do veículo (ex: ABC1234)</span>
                  </div>
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-2 h-2 bg-indigo-500 rounded-full"></div>
                    <span>CPF/CNPJ do cliente</span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* New Service Modal */}
        <NewServiceModal
          isOpen={showNewServiceForm}
          onClose={() => setShowNewServiceForm(false)}
          serviceData={newServiceData}
          onServiceDataChange={handleServiceDataChange}
          vehicles={searchResult?.vehicles || []}
          onSubmit={handleSubmitService}
          isLoading={isCreatingService}
          isReadOnly={false}
          // Props para produtos
          products={products}
          categories={categories}
          isLoadingProducts={isLoadingProducts}
          productSearchTerm={productSearchTerm}
          onProductSearch={searchProducts}
          onAddProduct={addProductToService}
          onRemoveProduct={removeProductFromService}
          onUpdateProductQuantity={updateServiceItemQuantity}
          onUpdateProductPrice={updateServiceItemPrice}
          onUpdateProductNotes={updateServiceItemNotes}
          calculateItemsTotal={calculateItemsTotal}
          calculateFinalTotal={calculateFinalTotal}
        />

        {/* Service Details Modal */}
        <ServiceDetailsModal
          isOpen={showServiceDetails}
          onClose={handleCloseServiceDetails}
          service={selectedService}
          clientName={searchResult?.client?.name}
          vehicleInfo={
            searchResult?.vehicles?.[0]
              ? `${searchResult.vehicles[0].brand} ${searchResult.vehicles[0].model} - ${searchResult.vehicles[0].license_plate}`
              : undefined
          }
          serviceDetails={serviceDetails}
          isLoadingDetails={isLoadingServiceDetails}
          isFetchingDetails={isFetchingServiceDetails}
          onEditService={handleEditServiceForDetails}
        />

        {/* Edit Service Modal */}
        <EditServiceModal
          isOpen={showEditServiceModal}
          onClose={handleCloseEditServiceModal}
          service={selectedServiceForEdit}
          vehicles={searchResult?.vehicles || []}
          onSubmit={handleEditServiceSubmit}
          isLoading={updateServiceWithItemsMutation.isPending}
          // Props para produtos
          products={products}
          categories={categories}
          isLoadingProducts={isLoadingProducts}
          productSearchTerm={productSearchTerm}
          onProductSearch={searchProducts}
        />

        {/* Update Status Modal */}
        <UpdateStatusModal
          isOpen={showUpdateStatusModal}
          onClose={handleCloseUpdateStatusModal}
          service={selectedServiceForUpdate}
          onUpdateStatus={handleUpdateStatusSubmit}
          isLoading={isUpdatingStatus}
        />
      </div>
    </div>
  );
};
