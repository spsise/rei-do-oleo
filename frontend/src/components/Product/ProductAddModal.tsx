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
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[60] animate-modalFadeIn">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md animate-modalSlideInUp">
        {/* Header */}
        <div className="p-6 border-b border-gray-100">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-semibold text-gray-900">
              Adicionar Produto
            </h3>
            <button
              onClick={onClose}
              className="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>
          <p className="text-sm text-gray-600 mt-1">{product.name}</p>
        </div>

        {/* Conteúdo */}
        <div className="p-6 space-y-4">
          {/* Informações do Produto */}
          <div className="bg-gray-50 rounded-lg p-4 space-y-2">
            <div className="flex justify-between">
              <span className="text-sm text-gray-600">SKU:</span>
              <span className="font-mono text-sm">{product.sku || 'N/A'}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-600">Preço:</span>
              <span className="font-semibold text-green-600">
                {formatPrice(product.price || 0)}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-600">Estoque:</span>
              <span className="text-sm">
                {product.stock_quantity || 0} unidades
              </span>
            </div>
            {product.category && (
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Categoria:</span>
                <span className="text-sm font-medium">
                  {product.category.name}
                </span>
              </div>
            )}
          </div>

          {/* Quantidade */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Quantidade
            </label>
            <div className="flex items-center gap-2">
              <button
                onClick={() => handleQuantityChange(quantity - 1)}
                disabled={quantity <= 1}
                className="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                -
              </button>
              <input
                type="number"
                min="1"
                max={maxQty}
                value={quantity}
                onChange={(e) => handleQuantityChange(Number(e.target.value))}
                className="flex-1 px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
              />
              <button
                onClick={() => handleQuantityChange(quantity + 1)}
                disabled={quantity >= maxQty}
                className="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                +
              </button>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              Máximo disponível: {maxQty} unidades
            </p>
          </div>

          {/* Observações */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Observações (opcional)
            </label>
            <textarea
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              placeholder="Observações sobre este produto..."
              className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              rows={3}
            />
          </div>

          {/* Resumo */}
          <div className="bg-blue-50 rounded-lg p-4">
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-600">Total:</span>
              <span className="font-semibold text-lg text-blue-600">
                {formatPrice((product.price || 0) * quantity)}
              </span>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              {quantity} × {formatPrice(product.price || 0)}
            </p>
          </div>
        </div>

        {/* Footer */}
        <div className="p-6 border-t border-gray-100 flex gap-3">
          <button
            onClick={onClose}
            className="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
          >
            Cancelar
          </button>
          <button
            onClick={handleConfirm}
            disabled={quantity < 1 || quantity > maxQty}
            className="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Adicionar
          </button>
        </div>
      </div>
    </div>
  );
};
