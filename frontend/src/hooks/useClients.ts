import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { clientService } from '../services';
import type {
  Client,
  ClientFilters,
  ClientListResponse,
  CreateClientData,
  SearchByDocumentData,
  SearchByPhoneData,
  UpdateClientData,
} from '../types/client';
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

// Listar clientes com filtros
export const useClients = (filters: ClientFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: [QUERY_KEYS.CLIENTS, filters],
    queryFn: async (): Promise<ClientListResponse> => {
      const response = await clientService.getClients(filters);
      return response.data!;
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
  });
};

// Obter cliente específico
export const useClient = (id: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.CLIENT, id],
    queryFn: async (): Promise<Client> => {
      const response = await clientService.getClient(id);
      return response.data!;
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};

// Criar cliente
export const useCreateClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateClientData): Promise<Client> => {
      const response = await clientService.createClient(data);
      return response.data!;
    },
    onSuccess: () => {
      // Invalidar queries relacionadas a clientes
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CLIENTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao criar cliente:', error);
      throw error;
    },
  });
};

// Atualizar cliente
export const useUpdateClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateClientData;
    }): Promise<Client> => {
      const response = await clientService.updateClient(id, data);
      return response.data!;
    },
    onSuccess: (updatedClient) => {
      // Atualizar cache do cliente específico
      queryClient.setQueryData(
        [QUERY_KEYS.CLIENT, updatedClient.id],
        updatedClient
      );

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CLIENTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar cliente:', error);
      throw error;
    },
  });
};

// Deletar cliente
export const useDeleteClient = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number): Promise<void> => {
      await clientService.deleteClient(id);
    },
    onSuccess: (_, deletedId) => {
      // Remover cliente do cache
      queryClient.removeQueries({
        queryKey: [QUERY_KEYS.CLIENT, deletedId],
      });

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CLIENTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao deletar cliente:', error);
      throw error;
    },
  });
};

// Buscar cliente por documento
export const useSearchClientByDocument = () => {
  return useMutation({
    mutationFn: async (data: SearchByDocumentData): Promise<Client> => {
      const response = await clientService.searchClientByDocument(data);
      return response.data!;
    },
    onError: (error: ApiError) => {
      console.error('Erro ao buscar cliente por documento:', error);
      throw error;
    },
  });
};

// Buscar cliente por telefone
export const useSearchClientByPhone = () => {
  return useMutation({
    mutationFn: async (data: SearchByPhoneData): Promise<Client> => {
      const response = await clientService.searchClientByPhone(data);
      return response.data!;
    },
    onError: (error: ApiError) => {
      console.error('Erro ao buscar cliente por telefone:', error);
      throw error;
    },
  });
};
