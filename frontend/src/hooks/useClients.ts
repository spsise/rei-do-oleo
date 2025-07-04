import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { apiService } from '../services/api';
import type {
  ClientFilters,
  CreateClientData,
  SearchByDocumentData,
  SearchByPhoneData,
  UpdateClientData,
} from '../types/client';

// Chaves para cache do React Query
export const clientKeys = {
  all: ['clients'] as const,
  lists: () => [...clientKeys.all, 'list'] as const,
  list: (filters: ClientFilters) => [...clientKeys.lists(), filters] as const,
  details: () => [...clientKeys.all, 'detail'] as const,
  detail: (id: number) => [...clientKeys.details(), id] as const,
};

// Hook para listar clientes
export const useClients = (filters: ClientFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: clientKeys.list(filters),
    queryFn: async () => {
      const response = await apiService.getClients(filters);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar clientes');
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000, // 10 minutos
  });
};

// Hook para obter um cliente específico
export const useClient = (id: number) => {
  return useQuery({
    queryKey: clientKeys.detail(id),
    queryFn: async () => {
      const response = await apiService.getClient(id);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar cliente');
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  });
};

// Hook para criar cliente
export const useCreateClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateClientData) => {
      const response = await apiService.createClient(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao criar cliente');
    },
    onSuccess: (newClient) => {
      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });

      // Adicionar novo cliente ao cache
      queryClient.setQueryData(clientKeys.detail(newClient.id), newClient);

      toast.success('Cliente criado com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao criar cliente');
    },
  });
};

// Hook para atualizar cliente
export const useUpdateClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateClientData;
    }) => {
      const response = await apiService.updateClient(id, data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao atualizar cliente');
    },
    onSuccess: (updatedClient) => {
      // Atualizar cache do cliente específico
      queryClient.setQueryData(
        clientKeys.detail(updatedClient.id),
        updatedClient
      );

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });

      toast.success('Cliente atualizado com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao atualizar cliente');
    },
  });
};

// Hook para excluir cliente
export const useDeleteClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      const response = await apiService.deleteClient(id);
      if (response.status === 'success') {
        return id;
      }
      throw new Error(response.message || 'Erro ao excluir cliente');
    },
    onSuccess: (deletedId) => {
      // Remover cliente do cache
      queryClient.removeQueries({ queryKey: clientKeys.detail(deletedId) });

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });

      toast.success('Cliente excluído com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao excluir cliente');
    },
  });
};

// Hook para buscar cliente por documento
export const useSearchClientByDocument = () => {
  return useMutation({
    mutationFn: async (data: SearchByDocumentData) => {
      const response = await apiService.searchClientByDocument(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Cliente não encontrado');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Cliente não encontrado');
    },
  });
};

// Hook para buscar cliente por telefone
export const useSearchClientByPhone = () => {
  return useMutation({
    mutationFn: async (data: SearchByPhoneData) => {
      const response = await apiService.searchClientByPhone(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Cliente não encontrado');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Cliente não encontrado');
    },
  });
};
