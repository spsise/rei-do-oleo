import { useQueryClient } from '@tanstack/react-query';
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
import { QUERY_KEYS } from '../hooks/query-keys';
import { useUpdateServiceItems } from '../hooks/useServiceItems';
import { useUpdateService } from '../hooks/useServices';
import { useServiceStatus } from '../hooks/useServiceStatus';
import { useTechnician } from '../hooks/useTechnician';
import '../styles/Technician.css';
import { type Service, type UpdateServiceData } from '../types/service';
import {
  type CreateTechnicianServiceData,
  type TechnicianService,
} from '../types/technician';

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
  const queryClient = useQueryClient();
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
    resetSearch,
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
  const updateServiceMutation = useUpdateService();
  const updateServiceItemsMutation = useUpdateServiceItems();
  const [selectedServiceForEdit, setSelectedServiceForEdit] =
    useState<TechnicianService | null>(null);
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
    // Se o serviço não tem itens, carregar os dados completos do serviço
    let normalizedService: TechnicianService = service;

    if (!service.items || service.items.length === 0) {
      try {
        // Importar o serviço de serviços
        const { serviceService } = await import('../services/service.service');

        // Carregar dados completos do serviço
        const response = await serviceService.getService(service.id);
        const completeService = response.data;

        // Verificar se o serviço foi carregado corretamente
        if (!completeService) {
          throw new Error('Service not found');
        }

        // Converter para TechnicianService
        normalizedService = {
          id: completeService.id,
          service_number: completeService.service_number,
          description: completeService.description || '',
          status: completeService.status?.name || '',
          total_amount: completeService.financial?.items_total || 0,
          created_at: completeService.created_at,
          notes: completeService.internal_notes,
          observations: completeService.observations,
          items:
            completeService.items?.map((item, index) => ({
              id: `item-${completeService.id}-${item.product?.id || item.product_id}-${index}`,
              product_id:
                item.product?.id || parseInt(String(item.product_id || 0)),
              quantity: parseInt(String(item.quantity || 0)),
              unit_price: parseFloat(String(item.unit_price || 0)),
              total_price: parseFloat(String(item.total_price || 0)),
              notes: item.notes || '',
              product: item.product
                ? {
                    id: item.product.id,
                    name: item.product.name,
                    sku: item.product.sku,
                    price: 0, // Não disponível no ServiceItem
                    stock_quantity: item.product.current_stock,
                    category: item.product.category
                      ? {
                          id: parseInt(item.product.category),
                          name: item.product.category,
                        }
                      : undefined,
                  }
                : undefined,
            })) || [],
        };
      } catch (error) {
        console.error(
          'handleEditService - Error loading complete service:',
          error
        );
        // Se não conseguir carregar, usar o serviço original
      }
    } else {
      // Garantir que os dados estejam no formato correto
      normalizedService = {
        ...service,
        items:
          service.items?.map((item, index) => ({
            id:
              item.id ||
              `item-${service.id}-${item.product?.id || item.product_id}-${index}`,
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            total_price: item.total_price,
            notes: item.notes || '',
            product: item.product
              ? {
                  id: item.product.id,
                  name: item.product.name,
                  sku: item.product.sku,
                  price: item.product.price || 0,
                  stock_quantity: item.product.stock_quantity || 0,
                  category: item.product.category
                    ? {
                        id: item.product.category.id,
                        name: item.product.category.name,
                      }
                    : undefined,
                }
              : undefined,
          })) || [],
      };
    }

    setSelectedServiceForEdit(normalizedService);
    setShowEditServiceModal(true);
  };

  const handleEditServiceForDetails = (service: Service) => {
    // Converter Service para TechnicianService
    const technicianService: TechnicianService = {
      id: service.id,
      service_number: service.service_number,
      description: service.description || '',
      status: service.status?.name || '',
      total_amount: service.financial?.items_total || 0,
      created_at: service.created_at,
      notes: service.internal_notes,
      observations: service.observations,
      items: service.items?.map((item) => ({
        id: `item-${service.id}-${item.product?.id || item.product_id}`,
        product_id: item.product?.id || parseInt(String(item.product_id || 0)),
        quantity: parseInt(String(item.quantity || 0)),
        unit_price: parseFloat(String(item.unit_price || 0)),
        total_price: parseFloat(String(item.total_price || 0)),
        notes: item.notes || '',
        product: item.product
          ? {
              id: item.product.id,
              name: item.product.name,
              sku: item.product.sku,
              price: 0, // Não disponível no ServiceItem
              stock_quantity: item.product.current_stock,
              category: item.product.category
                ? {
                    id: parseInt(item.product.category),
                    name: item.product.category,
                  }
                : undefined,
            }
          : undefined,
      })),
    };

    handleEditService(technicianService);
  };

  const handleEditServiceSubmit = async (
    serviceId: number,
    data: EditServiceData
  ) => {
    try {
      // Separar dados do serviço dos itens
      const { items, ...serviceData } = data;

      // Atualizar o serviço
      await updateServiceMutation.mutateAsync({
        id: serviceId,
        data: serviceData,
      });

      // Aguardar um pouco para garantir que a primeira transação foi commitada
      await new Promise((resolve) => setTimeout(resolve, 100));

      // Atualizar os itens do serviço (sempre, mesmo que seja array vazio)
      try {
        await updateServiceItemsMutation.mutateAsync({
          serviceId,
          items: items || [],
        });
      } catch (itemsError) {
        // Se o erro for "Service not found", tentar novamente após um delay maior
        const errorMessage = (
          itemsError as { response?: { data?: { message?: string } } }
        )?.response?.data?.message;

        if (
          errorMessage?.includes('Service not found') ||
          errorMessage?.includes('Service item not found')
        ) {
          console.log('Tentando novamente após delay...');
          await new Promise((resolve) => setTimeout(resolve, 500));

          await updateServiceItemsMutation.mutateAsync({
            serviceId,
            items: items || [],
          });
        } else {
          throw itemsError; // Re-throw se não for o erro esperado
        }
      }

      setShowEditServiceModal(false);
      setSelectedServiceForEdit(null);

      // Invalidar o cache do serviço específico para atualizar a tela de detalhes
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICE, serviceId],
      });

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
      } else if (errorMessage?.includes('Service item not found')) {
        toast.error('Erro ao atualizar itens do serviço. Tente novamente.');
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
      <div className="absolute inset-0 bg-grid-pattern opacity-5"></div>

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
            <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center animate-fadeIn">
              <div className="max-w-md mx-auto">
                <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg
                    className="w-8 h-8 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                    />
                  </svg>
                </div>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">
                  Cliente não encontrado
                </h3>
                <p className="text-gray-600 mb-6">
                  Verifique se os dados estão corretos e tente novamente.
                </p>
                <button
                  onClick={resetSearch}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium"
                >
                  Nova Busca
                </button>
              </div>
            </div>
          )}

          {/* Estado inicial com instruções */}
          {!searchResult && !searchValue && (
            <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center animate-fadeIn">
              <div className="max-w-lg mx-auto">
                <div className="w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                  <svg
                    className="w-10 h-10 text-white"
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
          isLoading={
            updateServiceMutation.isPending ||
            updateServiceItemsMutation.isPending
          }
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
