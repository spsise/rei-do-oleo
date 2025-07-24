import { CheckIcon, PlusIcon, TagIcon } from '@heroicons/react/24/outline';
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

  // Componente de botão reutilizável com design melhorado
  const ActionButton = ({ isCompact = false }: { isCompact?: boolean }) => {
    const baseClasses = `
      relative w-full sm:w-auto
      px-4 py-3 sm:py-2.5 md:py-2
      rounded-xl sm:rounded-lg
      text-sm font-semibold
      transition-all duration-300 ease-out
      flex items-center justify-center gap-2
      focus:outline-none focus:ring-4 focus:ring-offset-2
      disabled:cursor-not-allowed disabled:opacity-60
      touch-manipulation select-none
      min-h-[30px] sm:min-h-[44px] md:min-h-[40px]
      ${isCompact ? 'text-xs' : 'text-sm'}
    `;

    const getButtonClasses = () => {
      if (isSelected) {
        return `${baseClasses}
          bg-gradient-to-r from-green-500 to-emerald-500
          text-white shadow-lg shadow-green-500/25
          hover:from-green-600 hover:to-emerald-600
          focus:ring-green-500/50
          transform hover:scale-[1.02] active:scale-[0.98]
        `;
      }

      if (isOutOfStock) {
        return `${baseClasses}
          bg-gray-100 text-gray-500
          border border-gray-200
          focus:ring-gray-400/50
        `;
      }

      return `${baseClasses}
        bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600
        text-white shadow-lg shadow-blue-500/25
        hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700
        hover:shadow-xl hover:shadow-blue-500/30
        focus:ring-blue-500/50
        transform hover:scale-[1.02] active:scale-[0.98]
        hover:-translate-y-0.5
      `;
    };

    const getButtonContent = () => {
      if (isSelected) {
        return (
          <>
            <CheckIcon className="h-4 w-4 sm:h-3.5 sm:w-3.5 md:h-3 md:w-3" />
            <span className="hidden sm:inline">
              {isCompact ? 'Adicionado' : 'Produto Adicionado'}
            </span>
            <span className="sm:hidden">Adicionado</span>
          </>
        );
      }

      if (isOutOfStock) {
        return (
          <>
            <span className="hidden sm:inline">
              {isCompact ? 'Sem estoque' : 'Sem Estoque'}
            </span>
            <span className="sm:hidden">Sem estoque</span>
          </>
        );
      }

      return (
        <>
          <PlusIcon className="h-4 w-4 sm:h-3.5 sm:w-3.5 md:h-3 md:w-3" />
          <span className="hidden sm:inline">
            {isCompact ? 'Adicionar' : 'Adicionar Produto'}
          </span>
          <span className="sm:hidden">Adicionar</span>
        </>
      );
    };

    return (
      <button
        onClick={() => onAddProduct(product)}
        disabled={isSelected || isOutOfStock || disabled}
        className={getButtonClasses()}
        aria-label={
          isSelected
            ? 'Produto já adicionado'
            : isOutOfStock
              ? 'Produto sem estoque'
              : `Adicionar ${product.name}`
        }
      >
        {getButtonContent()}

        {/* Efeito de brilho no hover */}
        <div className="absolute inset-0 rounded-xl sm:rounded-lg bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300 pointer-events-none" />
      </button>
    );
  };

  if (compact) {
    return (
      <div
        className={`bg-white border rounded-xl sm:rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 ${
          isSelected
            ? 'border-green-300 bg-gradient-to-br from-green-50/50 to-emerald-50/30 shadow-green-100'
            : 'border-gray-200 hover:border-blue-300 hover:shadow-blue-100'
        } p-3 sm:p-4`}
      >
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
          <div className="flex-1 min-w-0 sm:mr-3">
            <div className="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-2">
              <h4 className="font-semibold text-gray-900 text-sm truncate">
                {product.name}
              </h4>
              <span className="font-mono text-xs bg-gray-100 px-2 py-1 rounded-lg text-gray-600 flex-shrink-0 self-start sm:self-auto">
                {product.sku || 'N/A'}
              </span>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 text-xs">
              <div className="flex items-center gap-1">
                <span className="text-gray-500">Preço:</span>
                <span className="font-semibold text-green-600">
                  {formatPrice(product.price || 0)}
                </span>
              </div>

              <div className="flex items-center gap-1">
                <span className="text-gray-500">Estoque:</span>
                <span
                  className={`px-2 py-1 rounded-full font-medium ${stockStatus.color}`}
                >
                  {stockStatus.text}
                </span>
              </div>

              {product.category && (
                <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium flex-shrink-0 self-start sm:self-auto">
                  {product.category.name}
                </span>
              )}
            </div>
          </div>

          <ActionButton isCompact={true} />
        </div>
      </div>
    );
  }

  // Layout padrão (não compacto)
  return (
    <div
      className={`bg-white border rounded-xl sm:rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 ${
        isSelected
          ? 'border-green-300 bg-gradient-to-br from-green-50/50 to-emerald-50/30 shadow-green-100'
          : 'border-gray-200 hover:border-blue-300 hover:shadow-blue-100'
      } p-4 sm:p-5`}
    >
      <div className="space-y-3 sm:space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 sm:gap-4">
          <div className="flex-1 min-w-0">
            <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-3">
              <h4 className="font-semibold text-gray-900 truncate text-base sm:text-lg">
                {product.name}
              </h4>
              <span className="font-mono text-xs bg-gray-100 px-3 py-1.5 rounded-lg text-gray-600 flex-shrink-0 self-start sm:self-auto">
                {product.sku || 'N/A'}
              </span>
            </div>

            {product.description && (
              <p className="text-sm text-gray-600 line-clamp-2 mb-3 sm:mb-4">
                {product.description}
              </p>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
          <div className="flex items-center gap-2">
            <span className="text-gray-500 text-xs sm:text-sm">Preço:</span>
            <span className="font-semibold text-green-600 text-sm sm:text-base">
              {formatPrice(product.price || 0)}
            </span>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-gray-500 text-xs sm:text-sm">Estoque:</span>
            <span
              className={`px-3 py-1.5 rounded-full font-medium text-xs ${stockStatus.color}`}
            >
              {stockStatus.text}
            </span>
          </div>
        </div>

        {product.category && (
          <div className="flex items-center gap-2">
            <TagIcon className="h-4 w-4 text-gray-400 flex-shrink-0" />
            <span className="bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-medium">
              {product.category.name}
            </span>
          </div>
        )}

        <ActionButton />
      </div>
    </div>
  );
};
