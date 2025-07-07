import React, { useState } from 'react';
import toast from 'react-hot-toast';
import { Pagination } from '../components/Common/Pagination';
import { ProductFiltersComponent } from '../components/Forms/ProductFilters';
import { ProductForm } from '../components/Forms/ProductForm';
import { ProductSearchForm } from '../components/Forms/ProductSearchForm';
import { StockUpdateForm } from '../components/Forms/StockUpdateForm';
import { ProductTable } from '../components/Product/ProductTable';
import {
  useCreateProduct,
  useDeleteProduct,
  useProducts,
  useSearchProductsByName,
  useUpdateProduct,
  useUpdateProductStock,
} from '../hooks/useProducts';
import type {
  CreateProductData,
  Product,
  ProductFilters,
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

// Função para traduzir nomes de campos para português
const translateFieldName = (fieldName: string): string => {
  const fieldTranslations: Record<string, string> = {
    name: 'Nome',
    sku: 'SKU',
    price: 'Preço',
    stock_quantity: 'Quantidade em Estoque',
    min_stock: 'Estoque Mínimo',
    unit: 'Unidade',
    category_id: 'Categoria',
    brand: 'Marca',
    supplier: 'Fornecedor',
    location: 'Localização',
    weight: 'Peso',
    dimensions: 'Dimensões',
    warranty_months: 'Garantia',
    active: 'Status',
    featured: 'Destaque',
    observations: 'Observações',
    description: 'Descrição',
    barcode: 'Código de Barras',
  };

  return fieldTranslations[fieldName] || fieldName;
};

interface ModalState {
  isOpen: boolean;
  type: 'create' | 'edit' | 'search' | 'stock';
  product?: Product;
}

export const ProductsPage: React.FC = () => {
  const [modal, setModal] = useState<ModalState>({
    isOpen: false,
    type: 'create',
  });
  const [filters, setFilters] = useState<ProductFilters>({ per_page: 15 });
  const [searchResults, setSearchResults] = useState<Product[]>([]);
  const [backendErrors, setBackendErrors] = useState<Record<string, string[]>>(
    {}
  );

  // Hooks do React Query
  const { data: productsData, isLoading } = useProducts(filters);
  const createProductMutation = useCreateProduct();
  const updateProductMutation = useUpdateProduct();
  const deleteProductMutation = useDeleteProduct();
  const searchByNameMutation = useSearchProductsByName();
  const updateStockMutation = useUpdateProductStock();

  // Abrir modal
  const openModal = (type: ModalState['type'], product?: Product) => {
    setModal({ isOpen: true, type, product });
    setSearchResults([]);
    setBackendErrors({}); // Limpar erros ao abrir modal
  };

  // Fechar modal
  const closeModal = () => {
    setModal({ isOpen: false, type: 'create' });
    setBackendErrors({}); // Limpar erros ao fechar modal
  };

  // Criar produto
  const handleCreateProduct = async (data: CreateProductData) => {
    try {
      await createProductMutation.mutateAsync(data);
      toast.success('Produto criado com sucesso!');
      closeModal();
    } catch (error: unknown) {
      // Tratar erros de validação do backend
      if (error && typeof error === 'object' && 'response' in error) {
        const apiError = error as ApiError;

        if (apiError.response?.data?.errors) {
          setBackendErrors(apiError.response.data.errors);

          // Mostrar toast informando que há erros de validação
          const errorFields = Object.keys(apiError.response.data.errors);
          if (errorFields.length > 0) {
            const translatedFields = errorFields.map(translateFieldName);
            toast.error(
              `Erro de validação: Verifique os campos ${translatedFields.join(', ')}`
            );
          }
        } else if (apiError.response?.data?.message) {
          // Exibir mensagem de erro geral se não houver erros de validação específicos
          toast.error(apiError.response.data.message);
        } else {
          toast.error('Erro ao criar produto. Tente novamente.');
        }
      } else {
        toast.error('Erro ao criar produto. Tente novamente.');
      }
    }
  };

  // Atualizar produto
  const handleUpdateProduct = async (data: UpdateProductData) => {
    if (!modal.product) return;
    try {
      await updateProductMutation.mutateAsync({ id: modal.product.id, data });
      toast.success('Produto atualizado com sucesso!');
      closeModal();
    } catch (error: unknown) {
      // Tratar erros de validação do backend
      if (error && typeof error === 'object' && 'response' in error) {
        const apiError = error as ApiError;
        if (apiError.response?.data?.errors) {
          setBackendErrors(apiError.response.data.errors);

          // Mostrar toast informando que há erros de validação
          const errorFields = Object.keys(apiError.response.data.errors);
          if (errorFields.length > 0) {
            const translatedFields = errorFields.map(translateFieldName);
            toast.error(
              `Erro de validação: Verifique os campos ${translatedFields.join(', ')}`
            );
          }
        } else if (apiError.response?.data?.message) {
          // Exibir mensagem de erro geral se não houver erros de validação específicos
          toast.error(apiError.response.data.message);
        } else {
          toast.error('Erro ao atualizar produto. Tente novamente.');
        }
      } else {
        toast.error('Erro ao atualizar produto. Tente novamente.');
      }
    }
  };

  // Excluir produto
  const handleDeleteProduct = async (product: Product) => {
    try {
      await deleteProductMutation.mutateAsync(product.id);
      toast.success('Produto excluído com sucesso!');
    } catch {
      toast.error('Erro ao excluir produto. Tente novamente.');
    }
  };

  // Buscar por nome
  const handleSearchByName = async (name: string) => {
    try {
      const results = await searchByNameMutation.mutateAsync(name);
      setSearchResults(results);
      setModal({ isOpen: false, type: 'create' });
      if (results.length === 0) {
        toast.error('Nenhum produto encontrado com esse nome.');
      }
    } catch {
      setSearchResults([]);
      toast.error('Erro ao buscar produtos. Tente novamente.');
    }
  };

  // Buscar por SKU
  const handleSearchBySku = async (sku: string) => {
    // Implementar busca por SKU quando a API estiver disponível
    console.log('Busca por SKU:', sku);
  };

  // Buscar por código de barras
  const handleSearchByBarcode = async (barcode: string) => {
    // Implementar busca por código de barras quando a API estiver disponível
    console.log('Busca por código de barras:', barcode);
  };

  // Buscar por categoria
  const handleSearchByCategory = async (categoryId: number) => {
    // Implementar busca por categoria quando a API estiver disponível
    console.log('Busca por categoria:', categoryId);
  };

  // Atualizar estoque
  const handleUpdateStock = async (data: UpdateStockData) => {
    if (!modal.product) return;
    try {
      await updateStockMutation.mutateAsync({ id: modal.product.id, data });
      toast.success('Estoque atualizado com sucesso!');
      closeModal();
    } catch {
      toast.error('Erro ao atualizar estoque. Tente novamente.');
    }
  };

  // Aplicar filtros
  const handleFiltersChange = (newFilters: ProductFilters) => {
    setFilters(newFilters);
  };

  // Limpar filtros
  const handleClearFilters = () => {
    setFilters({ per_page: 15 });
  };

  // Mudar página
  const handlePageChange = (page: number) => {
    setFilters((prev) => ({ ...prev, page }));
  };

  const products = productsData?.data || [];
  const pagination = {
    current_page: productsData?.current_page || 1,
    last_page: productsData?.last_page || 1,
    per_page: productsData?.per_page || 15,
    total: productsData?.total || 0,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-10xl mx-auto px-0 sm:px-0 lg:px-0 py-0">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Gestão de Produtos
              </h1>
              <p className="mt-2 text-gray-600">
                Gerencie seus produtos, controle estoque e mantenha os dados
                atualizados.
              </p>
            </div>
            <div className="flex space-x-3">
              <button
                onClick={() => openModal('search')}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Buscar Produto
              </button>
              <button
                onClick={() => openModal('create')}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Novo Produto
              </button>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <ProductFiltersComponent
          filters={filters}
          onFiltersChange={handleFiltersChange}
          onClearFilters={handleClearFilters}
        />

        {/* Resultado da busca */}
        {searchResults.length > 0 && (
          <div className="mb-6 bg-white p-6 rounded-lg shadow-sm border">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              Resultado da Busca ({searchResults.length} produtos encontrados)
            </h3>
            <ProductTable
              products={searchResults}
              onEdit={(product) => openModal('edit', product)}
              onDelete={handleDeleteProduct}
              onUpdateStock={(product) => openModal('stock', product)}
            />
            <div className="mt-4">
              <button
                onClick={() => setSearchResults([])}
                className="px-3 py-1 text-sm text-gray-600 hover:text-gray-700"
              >
                Fechar Resultados
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Tabela de Produtos - FORA do container de página */}
      <div className="w-full overflow-x-auto">
        <div className="max-w-7xl mx-auto">
          <ProductTable
            products={products}
            onEdit={(product) => openModal('edit', product)}
            onDelete={handleDeleteProduct}
            onUpdateStock={(product) => openModal('stock', product)}
            loading={isLoading}
          />
        </div>
      </div>

      {/* Paginação */}
      {pagination.last_page > 1 && (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <Pagination
            currentPage={pagination.current_page}
            lastPage={pagination.last_page}
            total={pagination.total}
            perPage={pagination.per_page}
            onPageChange={handlePageChange}
          />
        </div>
      )}

      {/* Modal */}
      {modal.isOpen && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-medium text-gray-900">
                  {modal.type === 'create' && 'Novo Produto'}
                  {modal.type === 'edit' && 'Editar Produto'}
                  {modal.type === 'search' && 'Buscar Produto'}
                  {modal.type === 'stock' && 'Ajustar Estoque'}
                </h3>
                <button
                  onClick={closeModal}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              {modal.type === 'search' ? (
                <ProductSearchForm
                  onSearchByName={handleSearchByName}
                  onSearchBySku={handleSearchBySku}
                  onSearchByBarcode={handleSearchByBarcode}
                  onSearchByCategory={handleSearchByCategory}
                  loading={searchByNameMutation.isPending}
                />
              ) : modal.type === 'stock' ? (
                <StockUpdateForm
                  product={modal.product!}
                  onSubmit={handleUpdateStock}
                  onCancel={closeModal}
                  loading={updateStockMutation.isPending}
                />
              ) : (
                <ProductForm
                  product={modal.product}
                  onSubmit={(data) => {
                    if (modal.type === 'create') {
                      handleCreateProduct(data as CreateProductData);
                    } else {
                      handleUpdateProduct(data as UpdateProductData);
                    }
                  }}
                  onCancel={closeModal}
                  loading={
                    createProductMutation.isPending ||
                    updateProductMutation.isPending
                  }
                  backendErrors={backendErrors}
                />
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
