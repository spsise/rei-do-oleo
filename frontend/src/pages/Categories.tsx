import React, { useState } from 'react';
import { CategoryFiltersComponent } from '../components/Category/CategoryFilters';
import { CategoryForm } from '../components/Category/CategoryForm';
import { CategoryTable } from '../components/Category/CategoryTable';
import { Pagination } from '../components/Common/Pagination';
import {
  useCategories,
  useCreateCategory,
  useDeleteCategory,
  useUpdateCategory,
} from '../hooks/useCategories';
import type {
  Category,
  CategoryFilters,
  CreateCategoryData,
  UpdateCategoryData,
} from '../types/category';

interface ModalState {
  isOpen: boolean;
  type: 'create' | 'edit';
  category?: Category;
}

export const CategoriesPage: React.FC = () => {
  const [modal, setModal] = useState<ModalState>({
    isOpen: false,
    type: 'create',
  });
  const [filters, setFilters] = useState<CategoryFilters>({ per_page: 15 });

  // Hooks do React Query
  const { data: categoriesData, isLoading } = useCategories(filters);
  const createCategoryMutation = useCreateCategory();
  const updateCategoryMutation = useUpdateCategory();
  const deleteCategoryMutation = useDeleteCategory();

  // Abrir modal
  const openModal = (type: ModalState['type'], category?: Category) => {
    setModal({ isOpen: true, type, category });
  };

  // Fechar modal
  const closeModal = () => {
    setModal({ isOpen: false, type: 'create' });
  };

  // Criar categoria
  const handleCreateCategory = async (data: CreateCategoryData) => {
    await createCategoryMutation.mutateAsync(data);
    closeModal();
  };

  // Atualizar categoria
  const handleUpdateCategory = async (data: UpdateCategoryData) => {
    if (!modal.category) return;
    await updateCategoryMutation.mutateAsync({ id: modal.category.id, data });
    closeModal();
  };

  // Excluir categoria
  const handleDeleteCategory = async (category: Category) => {
    await deleteCategoryMutation.mutateAsync(category.id);
  };

  // Aplicar filtros
  const handleFiltersChange = (newFilters: CategoryFilters) => {
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

  const categories = categoriesData?.data || [];
  const pagination = {
    current_page: categoriesData?.current_page || 1,
    last_page: categoriesData?.last_page || 1,
    per_page: categoriesData?.per_page || 15,
    total: categoriesData?.total || 0,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-10xl mx-auto px-0 sm:px-0 lg:px-0 py-0">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Gestão de Categorias
              </h1>
              <p className="mt-2 text-gray-600">
                Gerencie as categorias de produtos e serviços do sistema.
              </p>
            </div>
            <div className="flex space-x-3">
              <button
                onClick={() => openModal('create')}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Nova Categoria
              </button>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <CategoryFiltersComponent
          filters={filters}
          onFiltersChange={handleFiltersChange}
          onClearFilters={handleClearFilters}
        />
      </div>

      {/* Tabela de Categorias - FORA do container de página */}
      <div className="w-full overflow-x-auto">
        <div className="max-w-7xl mx-auto">
          <CategoryTable
            categories={categories}
            onEdit={(category) => openModal('edit', category)}
            onDelete={handleDeleteCategory}
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
                  {modal.type === 'create' && 'Nova Categoria'}
                  {modal.type === 'edit' && 'Editar Categoria'}
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

              <CategoryForm
                category={modal.category}
                onSubmit={(data) => {
                  if (modal.type === 'create') {
                    handleCreateCategory(data as CreateCategoryData);
                  } else {
                    handleUpdateCategory(data as UpdateCategoryData);
                  }
                }}
                onCancel={closeModal}
                loading={
                  createCategoryMutation.isPending ||
                  updateCategoryMutation.isPending
                }
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
