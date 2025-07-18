import { TagIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianProduct } from '../../types/technician';
import { ProductCard } from './ProductCard';

interface ProductListProps {
  products: TechnicianProduct[];
  isLoading: boolean;
  searchTerm: string;
  selectedProductIds: number[];
  onAddProduct: (product: TechnicianProduct) => void;
  compact?: boolean;
  emptyMessage?: string;
  loadingMessage?: string;
}

export const ProductList: React.FC<ProductListProps> = ({
  products,
  isLoading,
  searchTerm,
  selectedProductIds,
  onAddProduct,
  compact = false,
  emptyMessage = 'Nenhum produto encontrado',
  loadingMessage = 'Carregando produtos...',
}) => {
  if (isLoading) {
    return (
      <div className="text-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p className="mt-4 text-gray-600">{loadingMessage}</p>
      </div>
    );
  }

  if (products.length === 0) {
    return (
      <div className="text-center py-12">
        <div className="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <TagIcon className="h-8 w-8 text-gray-400" />
        </div>
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          {emptyMessage}
        </h3>
        <p className="text-gray-500">
          {searchTerm
            ? 'Tente ajustar os filtros de busca'
            : 'Não há produtos disponíveis no momento'}
        </p>
      </div>
    );
  }

  return (
    <div
      className={
        compact
          ? 'grid grid-cols-1 md:grid-cols-2 gap-3'
          : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'
      }
    >
      {products.map((product) => (
        <ProductCard
          key={product.id}
          product={product}
          isSelected={selectedProductIds.includes(product.id)}
          onAddProduct={onAddProduct}
          compact={compact}
        />
      ))}
    </div>
  );
};
