import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { apiService } from '../services/api';
import type {
  CategoryFilters,
  CreateCategoryData,
  UpdateCategoryData,
} from '../types/category';

// Chaves para cache do React Query
export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: CategoryFilters) =>
    [...categoryKeys.lists(), filters] as const,
  details: () => [...categoryKeys.all, 'detail'] as const,
  detail: (id: number) => [...categoryKeys.details(), id] as const,
};

// Hook para listar categorias
export const useCategories = (filters: CategoryFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: categoryKeys.list(filters),
    queryFn: async () => {
      const response = await apiService.getCategories(filters);
      if (response.status === 'success' && response.data) {
        // A API retorna um array direto em response.data
        // Precisamos criar a estrutura de paginação esperada pelo frontend
        const categoriesArray = Array.isArray(response.data)
          ? response.data
          : [];
        const categoriesData = {
          data: categoriesArray,
          current_page: 1,
          last_page: 1,
          per_page: categoriesArray.length,
          total: categoriesArray.length,
        };

        return categoriesData;
      }
      throw new Error(response.message || 'Erro ao carregar categorias');
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000, // 10 minutos
  });
};

// Hook para obter uma categoria específica
export const useCategory = (id: number) => {
  return useQuery({
    queryKey: categoryKeys.detail(id),
    queryFn: async () => {
      const response = await apiService.getCategory(id);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar categoria');
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  });
};

// Hook para criar categoria
export const useCreateCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateCategoryData) => {
      const response = await apiService.createCategory(data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao criar categoria');
    },
    onSuccess: (newCategory) => {
      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: categoryKeys.lists() });

      // Adicionar nova categoria ao cache
      queryClient.setQueryData(
        categoryKeys.detail(newCategory.id),
        newCategory
      );

      toast.success('Categoria criada com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao criar categoria');
    },
  });
};

// Hook para atualizar categoria
export const useUpdateCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateCategoryData;
    }) => {
      const response = await apiService.updateCategory(id, data);
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao atualizar categoria');
    },
    onSuccess: (updatedCategory) => {
      // Atualizar cache da categoria específica
      queryClient.setQueryData(
        categoryKeys.detail(updatedCategory.id),
        updatedCategory
      );

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: categoryKeys.lists() });

      toast.success('Categoria atualizada com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao atualizar categoria');
    },
  });
};

// Hook para excluir categoria
export const useDeleteCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      const response = await apiService.deleteCategory(id);
      if (response.status === 'success') {
        return id;
      }
      throw new Error(response.message || 'Erro ao excluir categoria');
    },
    onSuccess: (deletedId) => {
      // Remover categoria do cache
      queryClient.removeQueries({ queryKey: categoryKeys.detail(deletedId) });

      // Invalidar cache de listagem
      queryClient.invalidateQueries({ queryKey: categoryKeys.lists() });

      toast.success('Categoria excluída com sucesso!');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Erro ao excluir categoria');
    },
  });
};
