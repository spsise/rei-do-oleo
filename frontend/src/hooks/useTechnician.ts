import { useCallback, useState } from 'react';
import { toast } from 'react-hot-toast';
import { technicianService } from '../services/technician.service';
import {
  type CreateTechnicianServiceData,
  type TechnicianProduct,
  type TechnicianSearchResult,
  type TechnicianService,
  type TechnicianServiceItem,
} from '../types/technician';
import { useService } from './useServices';

export const useTechnician = () => {
  const [searchType, setSearchType] = useState<'license_plate' | 'document'>(
    'license_plate'
  );
  const [searchValue, setSearchValue] = useState('');
  const [isSearching, setIsSearching] = useState(false);
  const [searchResult, setSearchResult] =
    useState<TechnicianSearchResult | null>(null);
  const [showNewServiceForm, setShowNewServiceForm] = useState(false);
  const [isCreatingService, setIsCreatingService] = useState(false);
  const [newServiceData, setNewServiceData] =
    useState<CreateTechnicianServiceData>({
      client_id: 0,
      vehicle_id: 0,
      service_center_id: undefined,
      technician_id: undefined,
      attendant_id: undefined,
      service_number: undefined,
      description: '',
      estimated_duration: 60,
      scheduled_at: undefined,
      started_at: undefined,
      completed_at: undefined,
      service_status_id: undefined,
      payment_method_id: undefined,
      mileage_at_service: undefined,
      total_amount: undefined,
      discount_amount: undefined,
      final_amount: undefined,
      observations: undefined,
      notes: '',
      active: true,
      items: [],
    });

  // Estados para produtos
  const [products, setProducts] = useState<TechnicianProduct[]>([]);
  const [categories, setCategories] = useState<
    Array<{ id: number; name: string }>
  >([]);
  const [isLoadingProducts, setIsLoadingProducts] = useState(false);
  const [productSearchTerm, setProductSearchTerm] = useState('');

  const [selectedService, setSelectedService] =
    useState<TechnicianService | null>(null);
  const [showServiceDetails, setShowServiceDetails] = useState(false);

  const {
    data: serviceDetails,
    isLoading: isLoadingServiceDetails,
    isFetching: isFetchingServiceDetails,
  } = useService(selectedService?.id || 0);

  const handleSearch = async () => {
    if (!searchValue.trim()) {
      toast.error('Digite um valor para busca');
      return;
    }
    setIsSearching(true);
    try {
      const response = await technicianService.searchClient({
        search_type: searchType,
        search_value: searchValue.trim(),
      });
      if (response.status === 'success' && response.data) {
        setSearchResult(response.data);
        toast.success('Cliente encontrado!');
      } else {
        toast.error(response.message || 'Cliente não encontrado');
        setSearchResult(null);
      }
    } catch (error) {
      console.error('Erro na busca:', error);
      toast.error('Erro ao buscar cliente');
    } finally {
      setIsSearching(false);
    }
  };

  const handleVoiceResult = (value: string) => {
    // Remover todos os espaços do texto retornado pela voz
    const cleanValue = value.replace(/\s/g, '');

    // Detectar se é placa ou documento
    const hasLetters = /[A-Za-z]/.test(cleanValue);
    const hasNumbers = /\d/.test(cleanValue);

    if (hasLetters && hasNumbers) {
      setSearchType('license_plate');
    } else if (/\d{11,14}/.test(cleanValue.replace(/\D/g, ''))) {
      setSearchType('document');
    }

    setSearchValue(cleanValue);
    // Auto-executar busca após um pequeno delay
    setTimeout(() => {
      handleSearch();
    }, 500);
  };

  const handleCreateNewService = () => {
    if (searchResult?.client?.id) {
      setNewServiceData((prev: CreateTechnicianServiceData) => ({
        ...prev,
        client_id: searchResult.client.id || 0,
      }));
      setShowNewServiceForm(true);
      // Carregar produtos ativos e categorias
      loadActiveProducts();
      loadCategories();
    }
  };

  const handleSubmitService = async () => {
    if (!newServiceData.vehicle_id) {
      toast.error('Selecione um veículo');
      return;
    }
    if (!newServiceData.description.trim()) {
      toast.error('Digite uma descrição para o serviço');
      return;
    }

    setIsCreatingService(true);
    try {
      const response = await technicianService.createService(newServiceData);
      if (response.status === 'success') {
        toast.success('Serviço criado com sucesso!');
        setShowNewServiceForm(false);
        setNewServiceData({
          client_id: 0,
          vehicle_id: 0,
          service_center_id: undefined,
          technician_id: undefined,
          attendant_id: undefined,
          service_number: undefined,
          description: '',
          estimated_duration: 60,
          scheduled_at: undefined,
          started_at: undefined,
          completed_at: undefined,
          service_status_id: undefined,
          payment_method_id: undefined,
          mileage_at_service: undefined,
          total_amount: undefined,
          discount_amount: undefined,
          final_amount: undefined,
          observations: undefined,
          notes: '',
          active: true,
          items: [],
        });

        // Recarregar dados do cliente
        if (searchResult) {
          handleSearch();
        }
      } else {
        toast.error(response.message || 'Erro ao criar serviço');
      }
    } catch (error) {
      console.error('Erro ao criar serviço:', error);
      toast.error('Erro ao criar serviço');
    } finally {
      setIsCreatingService(false);
    }
  };

  const resetSearch = () => {
    setSearchValue('');
    setSearchResult(null);
    setShowNewServiceForm(false);
  };

  const handleServiceClick = async (service: TechnicianService) => {
    setSelectedService(service);
    setShowServiceDetails(true);
  };

  const handleCloseServiceDetails = () => {
    setShowServiceDetails(false);
    setSelectedService(null);
  };

  // Métodos para produtos
  const loadActiveProducts = useCallback(async () => {
    setIsLoadingProducts(true);
    try {
      const response = await technicianService.getActiveProducts();
      if (response.status === 'success' && response.data) {
        setProducts(response.data);
      }
    } catch (error) {
      console.error('Erro ao carregar produtos:', error);
      toast.error('Erro ao carregar produtos');
    } finally {
      setIsLoadingProducts(false);
    }
  }, []);

  const loadCategories = useCallback(async () => {
    try {
      const response = await technicianService.getCategories();
      if (response.status === 'success' && response.data) {
        setCategories(response.data);
      }
    } catch (error) {
      console.error('Erro ao carregar categorias:', error);
      toast.error('Erro ao carregar categorias');
    }
  }, []);

  const searchProducts = async (search: string) => {
    // Atualizar o termo de busca no estado
    setProductSearchTerm(search);

    if (!search.trim()) {
      loadActiveProducts();
      return;
    }

    setIsLoadingProducts(true);
    try {
      const response = await technicianService.searchProducts(search);
      if (response.status === 'success' && response.data) {
        setProducts(response.data);
      }
    } catch (error) {
      console.error('Erro ao buscar produtos:', error);
      toast.error('Erro ao buscar produtos');
    } finally {
      setIsLoadingProducts(false);
    }
  };

  const addProductToService = (
    product: TechnicianProduct,
    quantity: number = 1,
    notes?: string
  ) => {
    const existingItemIndex = newServiceData.items?.findIndex(
      (item) => item.product_id === product.id
    );

    if (existingItemIndex !== undefined && existingItemIndex >= 0) {
      // Atualizar quantidade do item existente
      const updatedItems = [...(newServiceData.items || [])];
      updatedItems[existingItemIndex].quantity += quantity;
      updatedItems[existingItemIndex].total_price =
        updatedItems[existingItemIndex].quantity *
        updatedItems[existingItemIndex].unit_price;

      setNewServiceData((prev) => {
        // Calcular total dos itens
        const itemsTotal = updatedItems.reduce(
          (total, item) => total + item.total_price,
          0
        );

        return {
          ...prev,
          items: updatedItems,
          total_amount: itemsTotal,
          final_amount: Math.max(0, itemsTotal - (prev.discount_amount || 0)),
        };
      });
    } else {
      // Adicionar novo item
      const newItem: TechnicianServiceItem = {
        id: `item-new-${product.id}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`, // ID único para novo item
        product_id: product.id,
        quantity,
        unit_price: product.price,
        total_price: product.price * quantity,
        notes,
        product,
      };

      setNewServiceData((prev) => {
        const updatedItems = [...(prev.items || []), newItem];

        // Calcular total dos itens
        const itemsTotal = updatedItems.reduce(
          (total, item) => total + item.total_price,
          0
        );

        return {
          ...prev,
          items: updatedItems,
          total_amount: itemsTotal,
          final_amount: Math.max(0, itemsTotal - (prev.discount_amount || 0)),
        };
      });
    }

    toast.success(`${product.name} adicionado ao serviço`);
  };

  const removeProductFromService = (itemId: string) => {
    setNewServiceData((prev) => {
      const updatedItems =
        prev.items?.filter((item) => item.id !== itemId) || [];

      // Calcular total dos itens
      const itemsTotal = updatedItems.reduce(
        (total, item) => total + item.total_price,
        0
      );

      return {
        ...prev,
        items: updatedItems,
        total_amount: itemsTotal,
        final_amount: Math.max(0, itemsTotal - (prev.discount_amount || 0)),
      };
    });
    toast.success('Produto removido do serviço');
  };

  const updateServiceItemQuantity = useCallback(
    (itemId: string, quantity: number) => {
      if (quantity <= 0) {
        // Remover o item com o itemId específico
        setNewServiceData((prev) => ({
          ...prev,
          items: prev.items?.filter((item) => item.id !== itemId) || [],
        }));
        return;
      }

      setNewServiceData((prev) => {
        const updatedItems =
          prev.items?.map((item) =>
            item.id === itemId
              ? {
                  ...item,
                  quantity,
                  total_price: item.unit_price * quantity,
                }
              : item
          ) || [];

        // Calcular total dos itens
        const itemsTotal = updatedItems.reduce(
          (total, item) => total + item.total_price,
          0
        );

        return {
          ...prev,
          items: updatedItems,
          total_amount: itemsTotal,
          final_amount: Math.max(0, itemsTotal - (prev.discount_amount || 0)),
        };
      });
    },
    []
  );

  const updateServiceItemPrice = useCallback(
    (itemId: string, unitPrice: number) => {
      setNewServiceData((prev) => {
        const updatedItems =
          prev.items?.map((item) =>
            item.id === itemId
              ? {
                  ...item,
                  unit_price: unitPrice,
                  total_price: item.quantity * unitPrice,
                }
              : item
          ) || [];

        // Calcular total dos itens
        const itemsTotal = updatedItems.reduce(
          (total, item) => total + item.total_price,
          0
        );

        return {
          ...prev,
          items: updatedItems,
          total_amount: itemsTotal,
          final_amount: Math.max(0, itemsTotal - (prev.discount_amount || 0)),
        };
      });
    },
    []
  );

  const updateServiceItemNotes = useCallback(
    (itemId: string, notes: string) => {
      setNewServiceData((prev) => ({
        ...prev,
        items:
          prev.items?.map((item) =>
            item.id === itemId ? { ...item, notes } : item
          ) || [],
      }));
    },
    []
  );

  // Calcular total dos itens
  const calculateItemsTotal = () => {
    return (
      newServiceData.items?.reduce(
        (total, item) => total + item.total_price,
        0
      ) || 0
    );
  };

  // Calcular total final (itens + desconto)
  const calculateFinalTotal = () => {
    const itemsTotal = calculateItemsTotal();
    const discount = newServiceData.discount_amount || 0;
    return Math.max(0, itemsTotal - discount);
  };

  return {
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
    setProductSearchTerm,
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
  };
};
