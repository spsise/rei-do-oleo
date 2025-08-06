import { XMarkIcon } from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import { type TechnicianProduct } from '../../types/technician';

interface ProductAddModalProps {
  isOpen: boolean;
  onClose: () => void;
  product: TechnicianProduct | null;
  onConfirm: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  maxQuantity?: number;
}

export const ProductAddModal: React.FC<ProductAddModalProps> = ({
  isOpen,
  onClose,
  product,
  onConfirm,
  maxQuantity,
}) => {
  const [quantity, setQuantity] = useState(1);
  const [notes, setNotes] = useState('');

  useEffect(() => {
    if (product) {
      setQuantity(1);
      setNotes('');
    }
  }, [product]);

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const handleConfirm = () => {
    if (product) {
      onConfirm(product, quantity, notes.trim() || undefined);
      onClose();
    }
  };

  const handleQuantityChange = (value: number) => {
    const max = maxQuantity || product?.stock_quantity || 1;
    const newQuantity = Math.max(1, Math.min(value, max));
    setQuantity(newQuantity);
  };

  if (!isOpen || !product) return null;

  const maxQty = maxQuantity || product.stock_quantity || 1;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4 z-[60] animate-modalFadeIn">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-sm sm:max-w-md max-h-[90vh] overflow-y-auto animate-modalSlideInUp">
        {/* Header Compacto */}
        <div className="sticky top-0 bg-white rounded-t-xl p-4 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div className="flex-1 min-w-0">
              <h3 className="text-base font-semibold text-gray-900 truncate">
                Adicionar Produto
              </h3>
              <p className="text-xs text-gray-600 mt-0.5 truncate">
                {product.name}
              </p>
            </div>
            <button
              onClick={onClose}
              className="ml-2 p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
            >
              <XMarkIcon className="h-4 w-4" />
            </button>
          </div>
        </div>

        {/* Conteúdo Compacto */}
        <div className="p-4 space-y-3">
          {/* Informações do Produto - Layout Compacto */}
          <div className="bg-gray-50 rounded-lg p-3">
            <div className="grid grid-cols-2 gap-2 text-xs">
              <div className="flex justify-between">
                <span className="text-gray-600">SKU:</span>
                <span className="font-mono font-medium">
                  {product.sku || 'N/A'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Preço:</span>
                <span className="font-semibold text-green-600">
                  {formatPrice(product.price || 0)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Estoque:</span>
                <span className="font-medium">
                  {product.stock_quantity || 0} un.
                </span>
              </div>
              {product.category && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Categoria:</span>
                  <span className="font-medium truncate">
                    {product.category.name}
                  </span>
                </div>
              )}
            </div>
          </div>

          {/* Quantidade - Layout Compacto */}
          <div>
            <label className="block text-xs font-medium text-gray-700 mb-2">
              Quantidade
            </label>
            <div className="flex items-center gap-3">
              <button
                onClick={() => handleQuantityChange(quantity - 1)}
                disabled={quantity <= 1}
                className="w-10 h-10 flex items-center justify-center bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-200 transition-all duration-200 shadow-sm hover:shadow-md"
                title="Diminuir quantidade"
              >
                <span className="text-lg font-bold text-gray-600">−</span>
              </button>
              <div className="flex-1 relative">
                <input
                  type="number"
                  min="1"
                  max={maxQty}
                  value={quantity}
                  onChange={(e) => handleQuantityChange(Number(e.target.value))}
                  className="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-semibold bg-white shadow-sm"
                />
                <div className="absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-gray-50 to-transparent pointer-events-none rounded-l-xl"></div>
                <div className="absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-gray-50 to-transparent pointer-events-none rounded-r-xl"></div>
              </div>
              <button
                onClick={() => handleQuantityChange(quantity + 1)}
                disabled={quantity >= maxQty}
                className="w-10 h-10 flex items-center justify-center bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-200 transition-all duration-200 shadow-sm hover:shadow-md"
                title="Aumentar quantidade"
              >
                <span className="text-lg font-bold text-gray-600">+</span>
              </button>
            </div>
            <div className="flex justify-between items-center mt-2">
              <p className="text-xs text-gray-500">Máx: {maxQty} unidades</p>
              <p className="text-xs text-blue-600 font-medium">
                Total: {formatPrice((product.price || 0) * quantity)}
              </p>
            </div>
          </div>

          {/* Observações - Compacto */}
          <div>
            <label className="block text-xs font-medium text-gray-700 mb-1.5">
              Observações (opcional)
            </label>
            <textarea
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              placeholder="Observações sobre este produto..."
              className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
              rows={2}
            />
          </div>

          {/* Resumo - Compacto */}
          <div className="bg-blue-50 rounded-lg p-3">
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-600">Total:</span>
              <span className="font-semibold text-base text-blue-600">
                {formatPrice((product.price || 0) * quantity)}
              </span>
            </div>
            <p className="text-xs text-gray-500 mt-0.5">
              {quantity} × {formatPrice(product.price || 0)}
            </p>
          </div>
        </div>

        {/* Footer Compacto */}
        <div className="sticky bottom-0 bg-white rounded-b-xl p-4 border-t border-gray-100 flex gap-2">
          <button
            onClick={onClose}
            className="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium text-sm"
          >
            Cancelar
          </button>
          <button
            onClick={handleConfirm}
            disabled={quantity < 1 || quantity > maxQty}
            className="flex-1 px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed text-sm"
          >
            Adicionar
          </button>
        </div>
      </div>
    </div>
  );
};
