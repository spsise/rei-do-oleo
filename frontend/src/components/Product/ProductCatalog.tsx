import React, { useState } from 'react';
import { type TechnicianProduct } from '../../types/technician';
import { ProductAddModal } from './ProductAddModal';
import { ProductFilters } from './ProductFilters';
import { ProductList } from './ProductList';

interface ProductCatalogProps {
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoading: boolean;
  onAddToCart: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  selectedProductIds: number[];
  title?: string;
  showFilters?: boolean;
  compact?: boolean;
}

export const ProductCatalog: React.FC<ProductCatalogProps> = ({
  products,
  categories,
  isLoading,
  onAddToCart,
  selectedProductIds,
  title = 'Catálogo de Produtos',
  showFilters = true,
  compact = false,
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedProduct, setSelectedProduct] =
    useState<TechnicianProduct | null>(null);
  const [showAddModal, setShowAddModal] = useState(false);

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
    onAddToCart(product, quantity, notes);
    setShowAddModal(false);
    setSelectedProduct(null);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">{title}</h2>
          <p className="text-gray-600 mt-1">
            {filteredProducts.length} produtos encontrados
          </p>
        </div>
      </div>

      {/* Filtros */}
      {showFilters && (
        <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <ProductFilters
            searchTerm={searchTerm}
            onSearch={setSearchTerm}
            selectedCategory={selectedCategory}
            onCategoryChange={setSelectedCategory}
            categories={categories}
            products={products}
            compact={compact}
          />
        </div>
      )}

      {/* Lista de Produtos */}
      <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <ProductList
          products={filteredProducts}
          isLoading={isLoading}
          searchTerm={searchTerm}
          selectedProductIds={selectedProductIds}
          onAddProduct={handleAddProduct}
          compact={compact}
        />
      </div>

      {/* Modal de Adicionar Produto */}
      <ProductAddModal
        isOpen={showAddModal}
        onClose={() => setShowAddModal(false)}
        product={selectedProduct}
        onConfirm={handleConfirmAdd}
      />
    </div>
  );
};
