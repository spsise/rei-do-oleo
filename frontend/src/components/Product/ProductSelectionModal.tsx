import {
  ArrowsPointingInIcon,
  ArrowsPointingOutIcon,
  PlusIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { type TechnicianProduct } from '../../types/technician';
import { ProductAddModal } from './ProductAddModal';
import { ProductFilters } from './ProductFilters';
import { ProductList } from './ProductList';

interface ProductSelectionModalProps {
  isOpen: boolean;
  onClose: () => void;
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoading: boolean;
  searchTerm: string;
  onSearch: (search: string) => void;
  onAddProduct: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  selectedProductIds: number[];
  title?: string;
  subtitle?: string;
  compact?: boolean;
}

export const ProductSelectionModal: React.FC<ProductSelectionModalProps> = ({
  isOpen,
  onClose,
  products,
  categories,
  isLoading,
  searchTerm,
  onSearch,
  onAddProduct,
  selectedProductIds,
  title = 'Selecionar Produtos',
  subtitle = 'Escolha os produtos para adicionar ao serviço',
  compact = false,
}) => {
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedProduct, setSelectedProduct] =
    useState<TechnicianProduct | null>(null);
  const [showAddModal, setShowAddModal] = useState(false);
  const [isMaximized, setIsMaximized] = useState(false);

  // Filtra produtos por categoria e busca
  const filteredProducts = products.filter((product) => {
    const matchesSearch =
      product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      product.sku?.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory =
      !selectedCategory || product.category?.id === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  const handleAddProduct = (product: TechnicianProduct) => {
    setSelectedProduct(product);
    setShowAddModal(true);
  };

  const handleConfirmAdd = (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => {
    onAddProduct(product, quantity, notes);
    setShowAddModal(false);
    setSelectedProduct(null);
  };

  if (!isOpen) return null;

  return (
    <>
      {/* Modal Principal */}
      <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
        <div
          className={`bg-white rounded-2xl shadow-2xl overflow-hidden animate-modalSlideInUp transition-all duration-300 ${
            isMaximized
              ? 'w-full h-full max-w-none max-h-none rounded-none'
              : 'w-full max-w-6xl max-h-[90vh]'
          }`}
        >
          {/* Header */}
          <div className="sticky top-0 bg-white rounded-t-2xl p-4 border-b border-gray-100 z-10">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg">
                  <PlusIcon className="h-5 w-5 text-white" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-gray-900">{title}</h3>
                  <p className="text-gray-600 text-xs">{subtitle}</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsMaximized(!isMaximized)}
                  className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                  title={isMaximized ? 'Restaurar' : 'Maximizar'}
                >
                  {isMaximized ? (
                    <ArrowsPointingInIcon className="h-5 w-5" />
                  ) : (
                    <ArrowsPointingOutIcon className="h-5 w-5" />
                  )}
                </button>
                <button
                  onClick={onClose}
                  className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <XMarkIcon className="h-6 w-6" />
                </button>
              </div>
            </div>

            {/* Filtros */}
            <ProductFilters
              searchTerm={searchTerm}
              onSearch={onSearch}
              selectedCategory={selectedCategory}
              onCategoryChange={setSelectedCategory}
              categories={categories}
              products={products}
              compact={compact}
            />
          </div>

          {/* Lista de Produtos */}
          <div
            className={`p-6 ${isMaximized ? 'h-[calc(100vh-300px)] overflow-y-auto' : 'max-h-[60vh] overflow-y-auto'}`}
          >
            <ProductList
              products={filteredProducts}
              isLoading={isLoading}
              searchTerm={searchTerm}
              selectedProductIds={selectedProductIds}
              onAddProduct={handleAddProduct}
              compact={compact}
            />
          </div>

          {/* Footer */}
          <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
            <div className="flex justify-between items-center">
              <div className="text-sm text-gray-600">
                {filteredProducts.length}{' '}
                {filteredProducts.length === 1 ? 'produto' : 'produtos'}{' '}
                encontrados
                {selectedCategory && (
                  <span className="ml-2 text-blue-600">
                    • Categoria:{' '}
                    {categories.find((c) => c.id === selectedCategory)?.name}
                  </span>
                )}
              </div>
              <div className="flex gap-3">
                <button
                  onClick={onClose}
                  className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Modal de Adicionar Produto */}
      <ProductAddModal
        isOpen={showAddModal}
        onClose={() => setShowAddModal(false)}
        product={selectedProduct}
        onConfirm={handleConfirmAdd}
      />
    </>
  );
};
