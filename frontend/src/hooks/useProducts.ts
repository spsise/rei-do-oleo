import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiService } from '../services/api';
import type {
  CreateProductData,
  Product,
  ProductFilters,
  ProductListResponse,
  UpdateProductData,
  UpdateStockData,
} from '../types/product';

// Interface para erro da API
interface ApiError extends Error {
  response?: {
    data?: {
      message?: string;
      errors?: Record<string, string[]>;
    };
  };
}

// Query Keys
const PRODUCTS_QUERY_KEY = 'products';
const PRODUCT_QUERY_KEY = 'product';
const ACTIVE_PRODUCTS_QUERY_KEY = 'active-products';
const LOW_STOCK_PRODUCTS_QUERY_KEY = 'low-stock-products';

// Listar produtos com filtros
export const useProducts = (filters: ProductFilters = { per_page: 15 }) => {
  return useQuery({
    queryKey: [PRODUCTS_QUERY_KEY, filters],
    queryFn: async (): Promise<ProductListResponse> => {
      const response = await apiService.getProducts(filters);

      // A API retorna um array direto, não um objeto paginado
      const products = Array.isArray(response.data) ? response.data : [];

      // Converter campos numéricos se necessário
      const processedProducts = products.map((product) => ({
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      }));

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
    queryKey: [PRODUCT_QUERY_KEY, id],
    queryFn: async (): Promise<Product> => {
      const response = await apiService.getProduct(id);
      const product = response.data!;

      // Converter campos numéricos se necessário
      return {
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      };
    },
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};

// Listar produtos ativos
export const useActiveProducts = () => {
  return useQuery({
    queryKey: [ACTIVE_PRODUCTS_QUERY_KEY],
    queryFn: async (): Promise<Product[]> => {
      const response = await apiService.getActiveProducts();
      const products = response.data || [];

      // Converter campos numéricos se necessário
      return products.map((product) => ({
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      }));
    },
    staleTime: 5 * 60 * 1000,
  });
};

// Listar produtos com estoque baixo
export const useLowStockProducts = () => {
  return useQuery({
    queryKey: [LOW_STOCK_PRODUCTS_QUERY_KEY],
    queryFn: async (): Promise<Product[]> => {
      const response = await apiService.getLowStockProducts();
      const products = response.data || [];

      // Converter campos numéricos se necessário
      return products.map((product) => ({
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      }));
    },
    staleTime: 2 * 60 * 1000, // 2 minutos para estoque
  });
};

// Buscar produtos por nome
export const useSearchProductsByName = () => {
  return useMutation({
    mutationFn: async (name: string): Promise<Product[]> => {
      const response = await apiService.searchProduct({ name });
      const products = response.data || [];

      // Converter campos numéricos se necessário
      return products.map((product) => ({
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      }));
    },
  });
};

// Criar produto
export const useCreateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateProductData): Promise<Product> => {
      const response = await apiService.createProduct(data);

      // Verificar se a resposta indica erro ou se não tem dados
      if (response.status === 'error' || !response.data) {
        const error = new Error(
          response.message || 'Erro ao criar produto'
        ) as ApiError;
        error.response = { data: response };
        throw error;
      }

      const product = response.data;

      // Converter campos numéricos se necessário
      return {
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({ queryKey: [ACTIVE_PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({
        queryKey: [LOW_STOCK_PRODUCTS_QUERY_KEY],
      });
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
      const response = await apiService.updateProduct(id, data);

      // Verificar se a resposta indica erro ou se não tem dados
      if (response.status === 'error' || !response.data) {
        const error = new Error(
          response.message || 'Erro ao atualizar produto'
        ) as ApiError;
        error.response = { data: response };
        throw error;
      }

      const product = response.data;

      // Converter campos numéricos se necessário
      return {
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      };
    },
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: [PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({ queryKey: [PRODUCT_QUERY_KEY, id] });
      queryClient.invalidateQueries({ queryKey: [ACTIVE_PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({
        queryKey: [LOW_STOCK_PRODUCTS_QUERY_KEY],
      });
    },
  });
};

// Excluir produto
export const useDeleteProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number): Promise<void> => {
      await apiService.deleteProduct(id);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({ queryKey: [ACTIVE_PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({
        queryKey: [LOW_STOCK_PRODUCTS_QUERY_KEY],
      });
    },
  });
};

// Atualizar estoque
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
      await apiService.updateProductStock(id, data);
    },
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: [PRODUCTS_QUERY_KEY] });
      queryClient.invalidateQueries({ queryKey: [PRODUCT_QUERY_KEY, id] });
      queryClient.invalidateQueries({
        queryKey: [LOW_STOCK_PRODUCTS_QUERY_KEY],
      });
    },
  });
};

// Obter produtos por categoria
export const useProductsByCategory = (categoryId: number) => {
  return useQuery({
    queryKey: [PRODUCTS_QUERY_KEY, 'category', categoryId],
    queryFn: async (): Promise<Product[]> => {
      const response = await apiService.getProductsByCategory(categoryId);
      const products = response.data || [];

      // Converter campos numéricos se necessário
      return products.map((product) => ({
        ...product,
        price:
          typeof product.price === 'string'
            ? parseFloat(product.price)
            : product.price,
        cost_price: product.cost_price
          ? typeof product.cost_price === 'string'
            ? parseFloat(product.cost_price)
            : product.cost_price
          : undefined,
        stock_quantity:
          typeof product.stock_quantity === 'string'
            ? parseInt(product.stock_quantity)
            : product.stock_quantity,
        min_stock:
          typeof product.min_stock === 'string'
            ? parseInt(product.min_stock)
            : product.min_stock,
        weight: product.weight
          ? typeof product.weight === 'string'
            ? parseFloat(product.weight)
            : product.weight
          : undefined,
        warranty_months: product.warranty_months
          ? typeof product.warranty_months === 'string'
            ? parseInt(product.warranty_months)
            : product.warranty_months
          : undefined,
      }));
    },
    enabled: !!categoryId,
    staleTime: 5 * 60 * 1000,
  });
};
