import { useMutation, useQueryClient } from '@tanstack/react-query';
import { serviceItemService } from '../services';
import { QUERY_KEYS } from './query-keys';

// Interface para erro da API
interface ApiError extends Error {
  response?: {
    data?: {
      message?: string;
      errors?: Record<string, string[]>;
    };
  };
}

interface ServiceItemResponse {
  id: number;
  service_id: number;
  product_id: number;
  quantity: number;
  unit_price: number;
  discount?: number;
  total_price: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  product?: {
    id: number;
    name: string;
    sku: string;
    brand?: string;
    category: string;
    unit: string;
    current_stock: number;
  };
}

// Atualizar itens de serviço
export const useUpdateServiceItems = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      serviceId,
      items,
    }: {
      serviceId: number;
      items: Array<{
        id?: number;
        product_id: number;
        quantity: number;
        unit_price: number;
        notes?: string;
      }>;
    }): Promise<ServiceItemResponse[]> => {
      const response = await serviceItemService.updateServiceItems(
        serviceId,
        items
      );
      return response.data || [];
    },
    onSuccess: (_, { serviceId }) => {
      // Atualizar cache do serviço específico
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICE, serviceId],
      });

      // Invalidar queries relacionadas a serviços
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });

      // Invalidar queries específicas de itens do serviço
      queryClient.invalidateQueries({
        queryKey: ['serviceItems', serviceId],
      });

      // Invalidar queries de busca do técnico
      queryClient.invalidateQueries({
        queryKey: ['technician', 'search'],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar itens do serviço:', error);
      throw error;
    },
  });
};

// Criar itens de serviço em lote
export const useBulkCreateServiceItems = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      serviceId,
      items,
    }: {
      serviceId: number;
      items: Array<{
        product_id: number;
        quantity: number;
        unit_price: number;
        notes?: string;
      }>;
    }): Promise<ServiceItemResponse[]> => {
      const response = await serviceItemService.bulkCreateServiceItems(
        serviceId,
        items
      );
      return response.data || [];
    },
    onSuccess: (_, { serviceId }) => {
      // Atualizar cache do serviço específico
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICE, serviceId],
      });

      // Invalidar queries relacionadas a serviços
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao criar itens do serviço:', error);
      throw error;
    },
  });
};
