import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { apiService } from '../services/api';
import type {
  CreateServiceData,
  SearchServiceByNumberData,
  SearchServiceData,
  Service,
  ServiceFilters,
  UpdateServiceData,
  UpdateServiceStatusData,
} from '../types/service';

// Keys para React Query
const serviceKeys = {
  all: ['services'] as const,
  lists: () => [...serviceKeys.all, 'list'] as const,
  list: (filters: ServiceFilters) => [...serviceKeys.lists(), filters] as const,
  details: () => [...serviceKeys.all, 'detail'] as const,
  detail: (id: number) => [...serviceKeys.details(), id] as const,
};

// Listar serviços
export const useServices = (filters: ServiceFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: serviceKeys.list(filters),
    queryFn: async () => {
      const response = await apiService.getServices(filters);
      if (response.status === 'success' && response.data) {
        // A API retorna um array direto em response.data
        // Precisamos criar a estrutura de paginação esperada pelo frontend
        const servicesArray = Array.isArray(response.data) ? response.data : [];
        const servicesData = {
          data: servicesArray,
          current_page: 1,
          last_page: 1,
          per_page: servicesArray.length,
          total: servicesArray.length,
        };

        return servicesData;
      }
      throw new Error(response.message || 'Erro ao carregar serviços');
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000, // 10 minutos
  });
};

// Buscar serviço por ID
export const useService = (id: number) => {
  return useQuery({
    queryKey: serviceKeys.detail(id),
    queryFn: async () => {
      const response = await apiService.getService(id);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar serviço');
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  });
};

// Buscar serviço por número
export const useSearchServiceByNumber = () => {
  return useMutation({
    mutationFn: async (data: SearchServiceByNumberData) => {
      // TODO: Implementar método específico para busca por número
      const response = await apiService.getServices();
      return response.data?.find(
        (service: Service) => service.service_number === data.service_number
      );
    },
  });
};

// Listar serviços por centro de serviço
export const useServicesByServiceCenter = (serviceCenterId: number) => {
  return useQuery({
    queryKey: [...serviceKeys.lists(), 'service-center', serviceCenterId],
    queryFn: async () => {
      const response = await apiService.getServices();
      return {
        ...response,
        data: response.data?.filter(
          (service: Service) => service.service_center?.id === serviceCenterId
        ),
      };
    },
    enabled: !!serviceCenterId,
  });
};

// Listar serviços por cliente
export const useServicesByClient = (clientId: number) => {
  return useQuery({
    queryKey: [...serviceKeys.lists(), 'client', clientId],
    queryFn: async () => {
      const response = await apiService.getServices();
      return {
        ...response,
        data: response.data?.filter(
          (service: Service) => service.client?.id === clientId
        ),
      };
    },
    enabled: !!clientId,
  });
};

// Listar serviços por veículo
export const useServicesByVehicle = (vehicleId: number) => {
  return useQuery({
    queryKey: [...serviceKeys.lists(), 'vehicle', vehicleId],
    queryFn: async () => {
      const response = await apiService.getServices();
      return {
        ...response,
        data: response.data?.filter(
          (service: Service) => service.vehicle?.id === vehicleId
        ),
      };
    },
    enabled: !!vehicleId,
  });
};

// Criar serviço
export const useCreateService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateServiceData) => {
      const response = await apiService.createService(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao criar serviço');
    },
    onSuccess: (newService) => {
      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: serviceKeys.lists() });

      // Adicionar novo serviço ao cache
      queryClient.setQueryData(serviceKeys.detail(newService.id), newService);

      toast.success('Serviço criado com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao criar serviço');
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
    }) => {
      const response = await apiService.updateService(id, data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao atualizar serviço');
    },
    onSuccess: (updatedService) => {
      // Atualizar cache do serviço específico
      queryClient.setQueryData(
        serviceKeys.detail(updatedService.id),
        updatedService
      );

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: serviceKeys.lists() });

      toast.success('Serviço atualizado com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao atualizar serviço');
    },
  });
};

// Excluir serviço
export const useDeleteService = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      const response = await apiService.deleteService(id);
      if (response.status === 'success') {
        return id;
      }
      throw new Error(response.message || 'Erro ao excluir serviço');
    },
    onSuccess: (deletedId) => {
      // Remover serviço do cache
      queryClient.removeQueries({ queryKey: serviceKeys.detail(deletedId) });

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: serviceKeys.lists() });

      toast.success('Serviço excluído com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao excluir serviço');
    },
  });
};

// Atualizar status do serviço
export const useUpdateServiceStatus = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateServiceStatusData;
    }) => {
      // TODO: Implementar método específico para atualizar status
      console.log('Updating service status:', id, data);
      return { success: true };
    },
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: serviceKeys.lists() });
      queryClient.invalidateQueries({ queryKey: serviceKeys.detail(id) });
    },
  });
};

// Estatísticas do dashboard
export const useServiceDashboardStats = (serviceCenterId?: number) => {
  return useQuery({
    queryKey: [...serviceKeys.all, 'stats', serviceCenterId],
    queryFn: async () => {
      const response = await apiService.getDashboardStats();
      return response.data;
    },
  });
};

// Hook para buscar serviço
export const useSearchService = () => {
  return useMutation({
    mutationFn: async (data: SearchServiceData) => {
      const response = await apiService.searchService(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Serviço não encontrado');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Serviço não encontrado');
    },
  });
};
