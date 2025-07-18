import { PlusIcon, TagIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianProduct } from '../../types/technician';

interface ProductCardProps {
  product: TechnicianProduct;
  isSelected?: boolean;
  onAddProduct: (product: TechnicianProduct) => void;
  disabled?: boolean;
  compact?: boolean;
}

export const ProductCard: React.FC<ProductCardProps> = ({
  product,
  isSelected = false,
  onAddProduct,
  disabled = false,
  compact = false,
}) => {
  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const getStockStatus = (stock: number) => {
    if (stock <= 0)
      return { text: 'Sem estoque', color: 'text-red-600 bg-red-50' };
    if (stock < 10)
      return { text: 'Estoque baixo', color: 'text-orange-600 bg-orange-50' };
    return { text: 'Em estoque', color: 'text-green-600 bg-green-50' };
  };

  const stockStatus = getStockStatus(product.stock_quantity || 0);
  const isOutOfStock = (product.stock_quantity || 0) <= 0;

  if (compact) {
    return (
      <div
        className={`bg-white border rounded-lg shadow-sm hover:shadow-md transition-all duration-200 ${
          isSelected
            ? 'border-green-300 bg-green-50/30'
            : 'border-gray-200 hover:border-blue-300'
        } p-2 sm:p-3`}
      >
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
          <div className="flex-1 min-w-0 sm:mr-3">
            <div className="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">
              <h4 className="font-semibold text-gray-900 text-sm truncate">
                {product.name}
              </h4>
              <span className="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 flex-shrink-0 self-start sm:self-auto">
                {product.sku || 'N/A'}
              </span>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-xs">
              <div className="flex items-center gap-1">
                <span className="text-gray-500">Preço:</span>
                <span className="font-semibold text-green-600">
                  {formatPrice(product.price || 0)}
                </span>
              </div>

              <div className="flex items-center gap-1">
                <span className="text-gray-500">Estoque:</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full font-medium ${stockStatus.color}`}
                >
                  {stockStatus.text}
                </span>
              </div>

              {product.category && (
                <span className="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0 self-start sm:self-auto">
                  {product.category.name}
                </span>
              )}
            </div>
          </div>

          <button
            onClick={() => onAddProduct(product)}
            disabled={isSelected || isOutOfStock || disabled}
            className={`w-full sm:w-auto px-3 py-2.5 sm:py-1.5 rounded-md text-xs font-medium transition-all duration-200 flex-shrink-0 touch-manipulation min-h-[44px] sm:min-h-0 ${
              isSelected
                ? 'bg-green-100 text-green-700 cursor-not-allowed'
                : isOutOfStock
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-sm hover:shadow-md transform hover:scale-105 active:scale-95'
            }`}
          >
            {isSelected ? (
              '✓ Adicionado'
            ) : isOutOfStock ? (
              'Sem estoque'
            ) : (
              <>
                <PlusIcon className="h-3 w-3 mr-1" />
                Adicionar
              </>
            )}
          </button>
        </div>
      </div>
    );
  }

  // Layout padrão (não compacto)
  return (
    <div
      className={`bg-white border rounded-lg shadow-sm hover:shadow-md transition-all duration-200 ${
        isSelected
          ? 'border-green-300 bg-green-50/30'
          : 'border-gray-200 hover:border-blue-300'
      } p-3 sm:p-4`}
    >
      <div className="space-y-2 sm:space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-0">
          <div className="flex-1 min-w-0">
            <div className="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-2">
              <h4 className="font-semibold text-gray-900 truncate text-base sm:text-lg">
                {product.name}
              </h4>
              <span className="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 flex-shrink-0 self-start sm:self-auto">
                {product.sku || 'N/A'}
              </span>
            </div>

            {product.description && (
              <p className="text-sm text-gray-600 line-clamp-2 mb-2 sm:mb-3">
                {product.description}
              </p>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-sm">
          <div className="flex items-center gap-2">
            <span className="text-gray-500 text-xs sm:text-sm">Preço:</span>
            <span className="font-semibold text-green-600 text-sm sm:text-base">
              {formatPrice(product.price || 0)}
            </span>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-gray-500 text-xs sm:text-sm">Estoque:</span>
            <span
              className={`px-2 py-1 rounded-full font-medium text-xs ${stockStatus.color}`}
            >
              {stockStatus.text}
            </span>
          </div>
        </div>

        {product.category && (
          <div className="flex items-center gap-2">
            <TagIcon className="h-4 w-4 text-gray-400 flex-shrink-0" />
            <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
              {product.category.name}
            </span>
          </div>
        )}

        <button
          onClick={() => onAddProduct(product)}
          disabled={isSelected || isOutOfStock || disabled}
          className={`w-full px-4 py-3 sm:py-2 rounded-lg text-sm font-medium transition-all duration-200 touch-manipulation min-h-[48px] sm:min-h-0 ${
            isSelected
              ? 'bg-green-100 text-green-700 cursor-not-allowed'
              : isOutOfStock
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-sm hover:shadow-md transform hover:scale-105 active:scale-95'
          }`}
        >
          {isSelected ? (
            '✓ Produto Adicionado'
          ) : isOutOfStock ? (
            'Sem Estoque'
          ) : (
            <>
              <PlusIcon className="h-4 w-4 mr-2 inline" />
              Adicionar Produto
            </>
          )}
        </button>
      </div>
    </div>
  );
};
