import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { productService } from '../services';
import type {
  CreateProductData,
  Product,
  ProductFilters,
  ProductListResponse,
  UpdateProductData,
  UpdateStockData,
} from '../types/product';
import {
  transformProductData,
  transformProductsArray,
} from '../utils/product-transformers';
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

// Listar produtos com filtros
export const useProducts = (filters: ProductFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: [QUERY_KEYS.PRODUCTS, filters],
    queryFn: async (): Promise<ProductListResponse> => {
      const response = await productService.getProducts(filters);

      // A API retorna um array direto, não um objeto paginado
      const products = Array.isArray(response.data) ? response.data : [];

      // Converter campos numéricos se necessário
      const processedProducts = transformProductsArray(products);

      return {
        data: processedProducts,
        current_page: 1,
        last_page: 1,
        per_page: filters.per_page || 15,
        total: processedProducts.length,
      };
    },
    staleTime: 5 * 60 * 1000, // 5 minutos
  });
};

// Obter produto específico
export const useProduct = (id: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.PRODUCT, id],
    queryFn: async (): Promise<Product> => {
      const response = await productService.getProduct(id);
      const product = response.data!;
      return transformProductData(product);
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};

// Listar produtos ativos
export const useActiveProducts = () => {
  return useQuery({
    queryKey: [QUERY_KEYS.ACTIVE_PRODUCTS],
    queryFn: async (): Promise<Product[]> => {
      const response = await productService.getActiveProducts();
      const products = response.data || [];
      return transformProductsArray(products);
    },
    staleTime: 5 * 60 * 1000,
  });
};

// Listar produtos com estoque baixo
export const useLowStockProducts = () => {
  return useQuery({
    queryKey: [QUERY_KEYS.LOW_STOCK_PRODUCTS],
    queryFn: async (): Promise<Product[]> => {
      const response = await productService.getLowStockProducts();
      const products = response.data || [];
      return transformProductsArray(products);
    },
    staleTime: 5 * 60 * 1000,
  });
};

// Buscar produtos por nome
export const useSearchProductsByName = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (searchTerm: string): Promise<Product[]> => {
      const response = await productService.searchProduct({ name: searchTerm });
      const products = response.data || [];
      return transformProductsArray(products);
    },
    onSuccess: () => {
      // Invalidar cache de produtos para refletir a busca
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCTS],
      });
    },
  });
};

// Criar produto
export const useCreateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateProductData): Promise<Product> => {
      const response = await productService.createProduct(data);
      const product = response.data!;
      return transformProductData(product);
    },
    onSuccess: () => {
      // Invalidar queries relacionadas a produtos
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.ACTIVE_PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.LOW_STOCK_PRODUCTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao criar produto:', error);
      throw error;
    },
  });
};

// Atualizar produto
export const useUpdateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateProductData;
    }): Promise<Product> => {
      const response = await productService.updateProduct(id, data);
      const product = response.data!;
      return transformProductData(product);
    },
    onSuccess: (updatedProduct) => {
      // Atualizar cache do produto específico
      queryClient.setQueryData(
        [QUERY_KEYS.PRODUCT, updatedProduct.id],
        updatedProduct
      );

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.ACTIVE_PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.LOW_STOCK_PRODUCTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar produto:', error);
      throw error;
    },
  });
};

// Deletar produto
export const useDeleteProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number): Promise<void> => {
      await productService.deleteProduct(id);
    },
    onSuccess: (_, deletedId) => {
      // Remover produto do cache
      queryClient.removeQueries({
        queryKey: [QUERY_KEYS.PRODUCT, deletedId],
      });

      // Invalidar queries relacionadas
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.ACTIVE_PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.LOW_STOCK_PRODUCTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao deletar produto:', error);
      throw error;
    },
  });
};

// Atualizar estoque do produto
export const useUpdateProductStock = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number;
      data: UpdateStockData;
    }): Promise<void> => {
      await productService.updateProductStock(id, data);
    },
    onSuccess: (_, { id }) => {
      // Invalidar queries relacionadas ao produto
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCT, id],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.PRODUCTS],
      });
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.LOW_STOCK_PRODUCTS],
      });
    },
    onError: (error: ApiError) => {
      console.error('Erro ao atualizar estoque:', error);
      throw error;
    },
  });
};

// Obter produtos por categoria
export const useProductsByCategory = (categoryId: number) => {
  return useQuery({
    queryKey: [QUERY_KEYS.PRODUCTS_BY_CATEGORY, categoryId],
    queryFn: async (): Promise<Product[]> => {
      const response = await productService.getProductsByCategory(categoryId);
      const products = response.data || [];
      return transformProductsArray(products);
    },
    enabled: !!categoryId,
    staleTime: 5 * 60 * 1000,
  });
};
