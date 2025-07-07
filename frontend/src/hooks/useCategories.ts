import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { categoryService } from '../services';
import type {
  Category,
  CategoryFilters,
  CreateCategoryData,
  UpdateCategoryData,
} from '../types/category';
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

// Listar categorias com filtros
export const useCategories = (filters: CategoryFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: [QUERY_KEYS.CATEGORIES, filters],
    queryFn: async (): Promise<Category[]> => {
      const response = await categoryService.getCategories(filters);
      return response.data || [];
    },
    staleTime: 10 * 60 * 1000, // 10 minutos (categorias mudam menos)
  });
};

// Obter categoria específica
export const useCategory = (id: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.CATEGORY, id],
    queryFn: async (): Promise<Category> => {
      const response = await categoryService.getCategory(id);
      return response.data!;
    },
    enabled: !!id,
    staleTime: 10 * 60 * 1000,
  });
};

// Criar categoria
export const useCreateCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateCategoryData): Promise<Category> => {
      const response = await categoryService.createCategory(data);
      return response.data!;
    },
    onSuccess: () => {
      // Invalidar queries relacionadas a categorias
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CATEGORIES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao criar categoria:', error);
      throw error;
    },
  });
};

// Atualizar categoria
export const useUpdateCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateCategoryData;
    }): Promise<Category> => {
      const response = await categoryService.updateCategory(id, data);
      return response.data!;
    },
    onSuccess: (updatedCategory) => {
      // Atualizar cache da categoria específica
      queryClient.setQueryData(
        [QUERY_KEYS.CATEGORY, updatedCategory.id],
        updatedCategory
      );

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CATEGORIES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar categoria:', error);
      throw error;
    },
  });
};

// Deletar categoria
export const useDeleteCategory = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number): Promise<void> => {
      await categoryService.deleteCategory(id);
    },
    onSuccess: (_, deletedId) => {
      // Remover categoria do cache
      queryClient.removeQueries({
        queryKey: [QUERY_KEYS.CATEGORY, deletedId],
      });

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.CATEGORIES],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao deletar categoria:', error);
      throw error;
    },
  });
};
