import React, { useEffect, useMemo, useState } from 'react';

import { type Service, type UpdateServiceData } from '../../types/service';
import {
  type CreateTechnicianServiceData,
  type TechnicianProduct,
  type TechnicianService,
  type TechnicianServiceItem,
  type TechnicianVehicle,
} from '../../types/technician';
import { ModalFooter } from '../NewServiceModal/ModalFooter';
import { ModalHeader } from '../NewServiceModal/ModalHeader';
import { ServiceDetailsTab } from '../NewServiceModal/ServiceDetailsTab';
import { ServiceProductsTab } from '../NewServiceModal/ServiceProductsTab';
import { NoChangesToast } from '../ui/NoChangesToast';

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

interface EditServiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  service: TechnicianService | null;
  vehicles: TechnicianVehicle[];
  onSubmit: (serviceId: number, data: EditServiceData) => Promise<void>;
  isLoading?: boolean;
  // Props para produtos
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoadingProducts: boolean;
  productSearchTerm: string;
  onProductSearch: (search: string) => void;
  // Dados completos do serviço (opcional)
  serviceDetails?: Service | null;
}

export const EditServiceModal: React.FC<EditServiceModalProps> = ({
  isOpen,
  onClose,
  service,
  vehicles,
  onSubmit,
  isLoading = false,
  products,
  categories,
  isLoadingProducts,
  productSearchTerm,
  onProductSearch,
  serviceDetails,
}) => {
  const [activeTab, setActiveTab] = useState<'details' | 'products'>('details');
  const [isMaximized, setIsMaximized] = useState(false);
  const [editData, setEditData] = useState<CreateTechnicianServiceData | null>(
    null
  );

  // Memoizar os dados originais para evitar recriação desnecessária
  const originalServiceData = useMemo(() => {
    if (!service) return {};

    // Usar dados completos se disponíveis, senão usar dados básicos
    const completeService = serviceDetails || service;

    // Type guard para verificar se é Service completo
    const isCompleteService = (
      obj: TechnicianService | Service
    ): obj is Service => {
      return obj && typeof obj === 'object' && 'technician' in obj;
    };

    const data = {
      client_id: 0,
      vehicle_id: vehicles.length > 0 ? vehicles[0].id : 0,
      service_center_id: isCompleteService(completeService)
        ? completeService.service_center?.id || 1
        : 1,
      technician_id: isCompleteService(completeService)
        ? completeService.technician?.id || 1
        : 1,
      attendant_id: isCompleteService(completeService)
        ? completeService.attendant?.id || 1
        : 1,
      service_number: service.service_number,
      description: service.description || '',
      estimated_duration: 60,
      scheduled_at: isCompleteService(completeService)
        ? completeService.scheduled_date
        : undefined,
      started_at: isCompleteService(completeService)
        ? completeService.started_at
        : undefined,
      completed_at: isCompleteService(completeService)
        ? completeService.finished_at
        : undefined,
      service_status_id:
        isCompleteService(completeService) &&
        typeof completeService.status === 'object'
          ? completeService.status?.id || 1
          : 1,
      payment_method_id: isCompleteService(completeService)
        ? completeService.payment_method?.id || 1
        : 1,
      mileage_at_service: isCompleteService(completeService)
        ? completeService.vehicle?.mileage_at_service
        : service.mileage_at_service || 0,
      total_amount:
        isCompleteService(completeService) &&
        completeService.financial?.total_amount
          ? parseFloat(completeService.financial.total_amount)
          : service.total_amount || 0,
      discount_amount: isCompleteService(completeService)
        ? completeService.financial?.discount || 0
        : 0,
      final_amount:
        isCompleteService(completeService) &&
        completeService.financial?.total_amount
          ? parseFloat(completeService.financial.total_amount)
          : service.total_amount || 0,
      observations: isCompleteService(completeService)
        ? completeService.observations
        : service.observations || '',
      notes: isCompleteService(completeService)
        ? completeService.internal_notes
        : service.notes || '',
      active: true,
      items: service.items || [],
    };

    return data;
  }, [service, serviceDetails, vehicles]);

  // Verificação simples e direta de mudanças
  const hasAnyChanges = React.useMemo(() => {
    if (!editData || Object.keys(originalServiceData).length === 0) {
      return false;
    }

    // Comparar apenas os campos principais que podem ser editados
    const fieldsToCompare = [
      'description',
      'estimated_duration',
      'scheduled_at',
      'started_at',
      'completed_at',
      'mileage_at_service',
      'observations',
      'notes',
      'total_amount',
      'discount_amount',
      'final_amount',
    ];

    for (const field of fieldsToCompare) {
      const originalValue = (
        originalServiceData as unknown as Record<string, unknown>
      )[field];
      const currentValue = (editData as unknown as Record<string, unknown>)[
        field
      ];

      // Normalizar valores para comparação
      const normalizeValue = (value: unknown) => {
        if (typeof value === 'string') return value.trim();
        if (typeof value === 'number') return Number(value);
        return value;
      };

      if (normalizeValue(originalValue) !== normalizeValue(currentValue)) {
        return true;
      }
    }

    // Comparar itens se existirem
    const originalItems =
      ((originalServiceData as unknown as Record<string, unknown>)
        .items as unknown[]) || [];
    const currentItems =
      ((editData as unknown as Record<string, unknown>).items as unknown[]) ||
      [];

    if (originalItems.length !== currentItems.length) {
      return true;
    }

    // Comparar cada item
    for (let i = 0; i < originalItems.length; i++) {
      const originalItem = originalItems[i] as Record<string, unknown>;
      const currentItem = currentItems[i] as Record<string, unknown>;

      if (
        originalItem.product_id !== currentItem.product_id ||
        originalItem.quantity !== currentItem.quantity ||
        originalItem.unit_price !== currentItem.unit_price ||
        originalItem.notes !== currentItem.notes
      ) {
        return true;
      }
    }

    return false;
  }, [originalServiceData, editData]);

  // Estado para toast de mudanças
  const [showNoChangesToast, setShowNoChangesToast] = useState(false);

  // Inicializar dados de edição quando o serviço for carregado
  useEffect(() => {
    if (service && Object.keys(originalServiceData).length > 0) {
      // Garantir que todos os campos obrigatórios estejam presentes
      const originalData = originalServiceData as Record<string, unknown>;
      const initialData: CreateTechnicianServiceData = {
        client_id: (originalData.client_id as number) || 0,
        vehicle_id: (originalData.vehicle_id as number) || 0,
        description: (originalData.description as string) || '',
        estimated_duration: (originalData.estimated_duration as number) || 60,
        // Campos opcionais
        service_center_id: originalData.service_center_id as number | undefined,
        technician_id: originalData.technician_id as number | undefined,
        attendant_id: originalData.attendant_id as number | undefined,
        service_number: originalData.service_number as string | undefined,
        scheduled_at: originalData.scheduled_at as string | undefined,
        started_at: originalData.started_at as string | undefined,
        completed_at: originalData.completed_at as string | undefined,
        service_status_id: originalData.service_status_id as number | undefined,
        payment_method_id: originalData.payment_method_id as number | undefined,
        mileage_at_service: originalData.mileage_at_service as
          | number
          | undefined,
        total_amount: originalData.total_amount as number | undefined,
        discount_amount: originalData.discount_amount as number | undefined,
        final_amount: originalData.final_amount as number | undefined,
        observations: originalData.observations as string | undefined,
        notes: originalData.notes as string | undefined,
        active: originalData.active as boolean | undefined,
        items:
          service.items?.map((item, index) => ({
            id: `item-${service.id}-${item.product?.id || item.product_id}-${index}`, // ID único para cada item
            product_id:
              item.product_id > 0
                ? parseInt(String(item.product_id || 0))
                : item.product?.id || 0,
            product: item.product,
            quantity: parseInt(String(item.quantity || 1)),
            unit_price: parseFloat(String(item.unit_price || 0)),
            total_price:
              parseFloat(String(item.unit_price || 0)) *
              parseInt(String(item.quantity || 1)),
            notes: item.notes || '',
          })) || [],
      };

      setEditData(initialData);
    }
  }, [service, originalServiceData]);

  if (!isOpen || !service || !editData) {
    return null;
  }

  const handleServiceDataChange = (
    data: Partial<CreateTechnicianServiceData>
  ) => {
    setEditData((prev) => (prev ? { ...prev, ...data } : null));
  };

  // Funções específicas para edição de produtos
  const handleAddProduct = (
    product: TechnicianProduct,
    quantity: number = 1,
    notes?: string
  ) => {
    setEditData((prev) => {
      if (!prev) return null;

      // Verificar se o produto já existe
      const existingItem = prev.items?.find(
        (item) => item.product_id === product.id
      );

      if (existingItem) {
        // Atualizar quantidade se já existir
        return {
          ...prev,
          items:
            prev.items?.map((item) =>
              item.product_id === product.id
                ? {
                    ...item,
                    quantity: (item.quantity || 0) + quantity,
                    total_price:
                      (item.unit_price || 0) *
                      ((item.quantity || 0) + quantity),
                  }
                : item
            ) || [],
        };
      }

      // Adicionar novo produto
      const newItem: TechnicianServiceItem = {
        id: `item-${service?.id || 'new'}-${product.id}-${Date.now()}`, // ID único para novo item
        product_id: product.id,
        product: product,
        quantity,
        unit_price: product.price || 0,
        total_price: (product.price || 0) * quantity,
        notes: notes || '',
      };

      return {
        ...prev,
        items: [...(prev.items || []), newItem],
      };
    });
  };

  const handleRemoveProduct = (itemId: string) => {
    setEditData((prev) => {
      if (!prev) return null;

      // Verificar se o item existe antes de remover
      const itemToRemove = prev.items?.find((item) => item.id === itemId);
      if (!itemToRemove) {
        console.warn(`Item com ID ${itemId} não encontrado para remoção`);
        return prev;
      }

      const filteredItems =
        prev.items?.filter((item) => item.id !== itemId) || [];

      return {
        ...prev,
        items: filteredItems,
      };
    });
  };

  const handleUpdateProductQuantity = (itemId: string, quantity: number) => {
    // Garantir que a quantidade seja sempre um número válido
    const validQuantity = Math.max(1, Math.min(quantity, 999));

    setEditData((prev) => {
      if (!prev) return null;

      return {
        ...prev,
        items:
          prev.items?.map((item) =>
            item.id === itemId
              ? {
                  ...item,
                  quantity: validQuantity,
                  total_price: (item.unit_price || 0) * validQuantity,
                }
              : item
          ) || [],
      };
    });
  };

  const handleUpdateProductPrice = (itemId: string, unitPrice: number) => {
    setEditData((prev) => {
      if (!prev) return null;

      return {
        ...prev,
        items:
          prev.items?.map((item) =>
            item.id === itemId
              ? {
                  ...item,
                  unit_price: unitPrice,
                  total_price: (item.quantity || 0) * unitPrice,
                }
              : item
          ) || [],
      };
    });
  };

  const handleUpdateProductNotes = (itemId: string, notes: string) => {
    setEditData((prev) => {
      if (!prev) return null;

      return {
        ...prev,
        items:
          prev.items?.map((item) =>
            item.id === itemId ? { ...item, notes } : item
          ) || [],
      };
    });
  };

  const handleCalculateItemsTotal = () => {
    const total =
      editData?.items?.reduce((total, item) => {
        const itemTotal = (item.unit_price || 0) * (item.quantity || 0);
        return total + itemTotal;
      }, 0) || 0;
    return isNaN(total) ? 0 : total;
  };

  const handleCalculateFinalTotal = () => {
    const itemsTotal = handleCalculateItemsTotal();
    const discount = editData?.discount_amount || 0;
    const finalTotal = Math.max(0, itemsTotal - discount);
    return isNaN(finalTotal) ? 0 : finalTotal;
  };

  const handleSubmit = async () => {
    if (!editData) return;

    // Verificar se há mudanças
    if (!hasAnyChanges) {
      setShowNoChangesToast(true);
      setTimeout(() => setShowNoChangesToast(false), 3000);
      return;
    }

    // Validar e converter os dados dos itens
    const validItems = editData.items
      ?.filter((item) => {
        // Garantir que product_id seja um número válido
        const productId = parseInt(String(item.product_id || 0));
        const quantity = parseInt(String(item.quantity || 0));
        const unitPrice = parseFloat(String(item.unit_price || 0));

        const isValid = productId > 0 && quantity > 0 && unitPrice >= 0;

        if (!isValid) {
          console.error('Item inválido encontrado:', {
            original: item,
            converted: { productId, quantity, unitPrice },
          });
        }

        return isValid;
      })
      .map((item) => ({
        product_id: parseInt(String(item.product_id || 0)),
        quantity: parseInt(String(item.quantity || 0)),
        unit_price: parseFloat(String(item.unit_price || 0)),
        discount: 0,
        notes: item.notes || '',
      }));

    if (!validItems || validItems.length === 0) {
      console.error('Nenhum item válido encontrado para enviar');
      return;
    }

    // Sempre enviar os dados completos, mas apenas se há mudanças
    const submitData: EditServiceData = {
      vehicle_id: editData.vehicle_id,
      description: editData.description,
      estimated_duration: editData.estimated_duration,
      scheduled_at: editData.scheduled_at,
      mileage_at_service: editData.mileage_at_service,
      internal_notes: editData.notes,
      observations: editData.observations,
      items: validItems,
      discount: editData.discount_amount,
    };

    await onSubmit(service.id, submitData);
  };

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
      <div
        className={`bg-white rounded-2xl shadow-2xl overflow-hidden animate-modalSlideInUp transition-all duration-300 ${
          isMaximized
            ? 'w-full h-full max-w-none max-h-none rounded-none'
            : 'w-full max-w-6xl max-h-[90vh]'
        }`}
      >
        {/* Header */}
        <ModalHeader
          isMaximized={isMaximized}
          setIsMaximized={setIsMaximized}
          onClose={onClose}
          isLoading={isLoading}
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          itemsCount={editData.items?.length || 0}
          title="Editar"
          subtitle={`Serviço #${service.service_number}`}
        />

        {/* Content */}
        <div
          className={`p-6 ${isMaximized ? 'h-[calc(100vh-200px)] overflow-y-auto' : 'max-h-[60vh] overflow-y-auto'}`}
        >
          {activeTab === 'details' ? (
            <ServiceDetailsTab
              serviceData={editData}
              onServiceDataChange={handleServiceDataChange}
              vehicles={vehicles}
            />
          ) : (
            <ServiceProductsTab
              serviceData={editData}
              products={products}
              categories={categories}
              isLoadingProducts={isLoadingProducts}
              productSearchTerm={productSearchTerm}
              onProductSearch={onProductSearch}
              onAddProduct={handleAddProduct}
              onRemoveProduct={handleRemoveProduct}
              onUpdateProductQuantity={handleUpdateProductQuantity}
              onUpdateProductPrice={handleUpdateProductPrice}
              onUpdateProductNotes={handleUpdateProductNotes}
              calculateItemsTotal={handleCalculateItemsTotal}
              calculateFinalTotal={handleCalculateFinalTotal}
            />
          )}
        </div>

        {/* Footer */}
        <ModalFooter
          onClose={onClose}
          onSubmit={handleSubmit}
          isLoading={isLoading}
          activeTab={activeTab}
          serviceData={editData}
          calculateFinalTotal={handleCalculateFinalTotal}
          submitButtonText="Atualizar"
          hasChanges={hasAnyChanges}
        />

        {/* Toast para quando não há mudanças */}
        <NoChangesToast
          isVisible={showNoChangesToast}
          onClose={() => setShowNoChangesToast(false)}
        />
      </div>
    </div>
  );
};
