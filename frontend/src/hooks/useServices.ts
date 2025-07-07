import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { serviceService } from '../services';
import type {
  CreateServiceData,
  SearchServiceData,
  Service,
  ServiceFilters,
  UpdateServiceData,
} from '../types/service';
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

// Listar serviços com filtros
export const useServices = (filters: ServiceFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICES, filters],
    queryFn: async (): Promise<Service[]> => {
      const response = await serviceService.getServices(filters);
      return response.data || [];
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
  });
};

// Obter serviço específico
export const useService = (id: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICE, id],
    queryFn: async (): Promise<Service> => {
      const response = await serviceService.getService(id);
      return response.data!;
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};

// Listar todos os serviços (sem filtros)
export const useAllServices = () => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICES, 'all'],
    queryFn: async (): Promise<Service[]> => {
      const response = await serviceService.getServices();
      return response.data || [];
    },
    staleTime: 5 * 60 * 1000,
  });
};

// Listar serviços por status
export const useServicesByStatus = (status: string) => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICES, 'status', status],
    queryFn: async (): Promise<Service[]> => {
      const response = await serviceService.getServices();
      return response.data || [];
    },
    staleTime: 5 * 60 * 1000,
  });
};

// Listar serviços por cliente
export const useServicesByClient = (clientId: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICES, 'client', clientId],
    queryFn: async (): Promise<Service[]> => {
      const response = await serviceService.getServices();
      return response.data || [];
    },
    enabled: !!clientId,
    staleTime: 5 * 60 * 1000,
  });
};

// Listar serviços por veículo
export const useServicesByVehicle = (vehicleId: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.SERVICES, 'vehicle', vehicleId],
    queryFn: async (): Promise<Service[]> => {
      const response = await serviceService.getServices();
      return response.data || [];
    },
    enabled: !!vehicleId,
    staleTime: 5 * 60 * 1000,
  });
};

// Criar serviço
export const useCreateService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateServiceData): Promise<Service> => {
      const response = await serviceService.createService(data);
      return response.data!;
    },
    onSuccess: () => {
      // Invalidar queries relacionadas a serviços
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao criar serviço:', error);
      throw error;
    },
  });
};

// Atualizar serviço
export const useUpdateService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateServiceData;
    }): Promise<Service> => {
      const response = await serviceService.updateService(id, data);
      return response.data!;
    },
    onSuccess: (updatedService) => {
      // Atualizar cache do serviço específico
      queryClient.setQueryData(
        [QUERY_KEYS.SERVICE, updatedService.id],
        updatedService
      );

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar serviço:', error);
      throw error;
    },
  });
};

// Deletar serviço
export const useDeleteService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number): Promise<void> => {
      await serviceService.deleteService(id);
    },
    onSuccess: (_, deletedId) => {
      // Remover serviço do cache
      queryClient.removeQueries({
        queryKey: [QUERY_KEYS.SERVICE, deletedId],
      });

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.SERVICES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao deletar serviço:', error);
      throw error;
    },
  });
};

// Buscar serviço
export const useSearchService = () => {
  return useMutation({
    mutationFn: async (data: SearchServiceData): Promise<Service> => {
      const response = await serviceService.searchService(data);
      return response.data!;
    },
    onError: (error: ApiError) => {
      console.error('Erro ao buscar serviço:', error);
      throw error;
    },
  });
};

// Obter estatísticas do dashboard
export const useDashboardStats = () => {
  return useQuery({
    queryKey: [QUERY_KEYS.DASHBOARD_STATS],
    queryFn: async () => {
      const response = await serviceService.getDashboardStats();
      return response.data;
    },
    staleTime: 2 * 60 * 1000, // 2 minutos para estatísticas
  });
};
