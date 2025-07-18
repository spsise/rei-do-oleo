import { EyeIcon, PlusIcon, XMarkIcon } from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { type TechnicianProduct } from '../../types/technician';
import { ProductAddModal } from './ProductAddModal';

interface ProductQuickViewProps {
  product: TechnicianProduct;
  onAddToCart: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  isSelected?: boolean;
  compact?: boolean;
}

export const ProductQuickView: React.FC<ProductQuickViewProps> = ({
  product,
  onAddToCart,
  isSelected = false,
  compact = false,
}) => {
  const [showDetails, setShowDetails] = useState(false);
  const [showAddModal, setShowAddModal] = useState(false);

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

  const handleAddToCart = (quantity: number, notes?: string) => {
    onAddToCart(product, quantity, notes);
    setShowAddModal(false);
  };

  if (compact) {
    return (
      <>
        <div className="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-md transition-all duration-200">
          <div className="flex items-center justify-between">
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold text-gray-900 text-sm truncate">
                {product.name}
              </h4>
              <div className="flex items-center gap-2 mt-1">
                <span className="font-semibold text-green-600 text-sm">
                  {formatPrice(product.price || 0)}
                </span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-xs font-medium ${stockStatus.color}`}
                >
                  {stockStatus.text}
                </span>
              </div>
            </div>
            <div className="flex items-center gap-1 ml-3">
              <button
                onClick={() => setShowDetails(true)}
                className="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                title="Ver detalhes"
              >
                <EyeIcon className="h-4 w-4" />
              </button>
              <button
                onClick={() => setShowAddModal(true)}
                disabled={isSelected || isOutOfStock}
                className={`p-1.5 rounded-lg transition-all duration-200 ${
                  isSelected
                    ? 'bg-green-100 text-green-700 cursor-not-allowed'
                    : isOutOfStock
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-blue-100 text-blue-600 hover:bg-blue-200'
                }`}
                title={
                  isSelected
                    ? 'Já adicionado'
                    : isOutOfStock
                      ? 'Sem estoque'
                      : 'Adicionar ao carrinho'
                }
              >
                <PlusIcon className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        {/* Modal de Detalhes */}
        {showDetails && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
              <div className="p-6 border-b border-gray-100">
                <div className="flex items-center justify-between">
                  <h3 className="text-lg font-semibold text-gray-900">
                    Detalhes do Produto
                  </h3>
                  <button
                    onClick={() => setShowDetails(false)}
                    className="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                  >
                    <XMarkIcon className="h-5 w-5" />
                  </button>
                </div>
              </div>

              <div className="p-6 space-y-4">
                <div className="space-y-3">
                  <div>
                    <h4 className="font-semibold text-gray-900">
                      {product.name}
                    </h4>
                    <p className="text-sm text-gray-600 mt-1">
                      {product.sku || 'N/A'}
                    </p>
                  </div>

                  {product.description && (
                    <p className="text-gray-700">{product.description}</p>
                  )}

                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span className="text-gray-500">Preço:</span>
                      <p className="font-semibold text-green-600">
                        {formatPrice(product.price || 0)}
                      </p>
                    </div>
                    <div>
                      <span className="text-gray-500">Estoque:</span>
                      <p className="font-medium">
                        {product.stock_quantity || 0} unidades
                      </p>
                    </div>
                  </div>

                  {product.category && (
                    <div>
                      <span className="text-gray-500 text-sm">Categoria:</span>
                      <p className="font-medium">{product.category.name}</p>
                    </div>
                  )}
                </div>
              </div>

              <div className="p-6 border-t border-gray-100">
                <button
                  onClick={() => setShowDetails(false)}
                  className="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Modal de Adicionar */}
        <ProductAddModal
          isOpen={showAddModal}
          onClose={() => setShowAddModal(false)}
          product={product}
          onConfirm={handleAddToCart}
        />
      </>
    );
  }

  // Layout padrão
  return (
    <>
      <div className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200">
        <div className="space-y-3">
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <h4 className="font-semibold text-gray-900">{product.name}</h4>
              <p className="text-sm text-gray-600 mt-1">
                {product.sku || 'N/A'}
              </p>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setShowDetails(true)}
                className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                title="Ver detalhes"
              >
                <EyeIcon className="h-4 w-4" />
              </button>
              <button
                onClick={() => setShowAddModal(true)}
                disabled={isSelected || isOutOfStock}
                className={`px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                  isSelected
                    ? 'bg-green-100 text-green-700 cursor-not-allowed'
                    : isOutOfStock
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-blue-600 text-white hover:bg-blue-700'
                }`}
              >
                {isSelected
                  ? 'Adicionado'
                  : isOutOfStock
                    ? 'Sem estoque'
                    : 'Adicionar'}
              </button>
            </div>
          </div>

          {product.description && (
            <p className="text-gray-700 text-sm line-clamp-2">
              {product.description}
            </p>
          )}

          <div className="flex items-center justify-between">
            <span className="font-semibold text-green-600">
              {formatPrice(product.price || 0)}
            </span>
            <span
              className={`px-2 py-1 rounded-full text-xs font-medium ${stockStatus.color}`}
            >
              {stockStatus.text}
            </span>
          </div>
        </div>
      </div>

      {/* Modais (mesmos do layout compacto) */}
      {showDetails && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6 border-b border-gray-100">
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-semibold text-gray-900">
                  Detalhes do Produto
                </h3>
                <button
                  onClick={() => setShowDetails(false)}
                  className="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <XMarkIcon className="h-5 w-5" />
                </button>
              </div>
            </div>

            <div className="p-6 space-y-4">
              <div className="space-y-3">
                <div>
                  <h4 className="font-semibold text-gray-900">
                    {product.name}
                  </h4>
                  <p className="text-sm text-gray-600 mt-1">
                    {product.sku || 'N/A'}
                  </p>
                </div>

                {product.description && (
                  <p className="text-gray-700">{product.description}</p>
                )}

                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-gray-500">Preço:</span>
                    <p className="font-semibold text-green-600">
                      {formatPrice(product.price || 0)}
                    </p>
                  </div>
                  <div>
                    <span className="text-gray-500">Estoque:</span>
                    <p className="font-medium">
                      {product.stock_quantity || 0} unidades
                    </p>
                  </div>
                </div>

                {product.category && (
                  <div>
                    <span className="text-gray-500 text-sm">Categoria:</span>
                    <p className="font-medium">{product.category.name}</p>
                  </div>
                )}
              </div>
            </div>

            <div className="p-6 border-t border-gray-100">
              <button
                onClick={() => setShowDetails(false)}
                className="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
              >
                Fechar
              </button>
            </div>
          </div>
        </div>
      )}

      <ProductAddModal
        isOpen={showAddModal}
        onClose={() => setShowAddModal(false)}
        product={product}
        onConfirm={handleAddToCart}
      />
    </>
  );
};
