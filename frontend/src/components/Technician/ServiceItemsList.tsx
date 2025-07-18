import { PencilIcon, TrashIcon, XMarkIcon } from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import type { TechnicianServiceItem } from '../../types/technician';

interface ServiceItemsListProps {
  items: TechnicianServiceItem[];
  onRemoveItem: (productId: number) => void;
  onUpdateQuantity: (productId: number, quantity: number) => void;
  onUpdatePrice: (productId: number, price: number) => void;
  onUpdateNotes: (productId: number, notes: string) => void;
}

export const ServiceItemsList: React.FC<ServiceItemsListProps> = ({
  items,
  onRemoveItem,
  onUpdateQuantity,
  onUpdatePrice,
  onUpdateNotes,
}) => {
  const [editingItem, setEditingItem] = useState<number | null>(null);
  const [editQuantity, setEditQuantity] = useState(1);
  const [editPrice, setEditPrice] = useState(0);
  const [editNotes, setEditNotes] = useState('');

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const handleEdit = (item: TechnicianServiceItem) => {
    setEditingItem(item.product_id);
    setEditQuantity(item.quantity);
    setEditPrice(item.unit_price);
    setEditNotes(item.notes || '');
  };

  const handleSave = (productId: number) => {
    onUpdateQuantity(productId, editQuantity);
    onUpdatePrice(productId, editPrice);
    onUpdateNotes(productId, editNotes);
    setEditingItem(null);
  };

  const handleCancel = () => {
    setEditingItem(null);
  };

  const calculateTotal = () => {
    return items.reduce((total, item) => total + item.total_price, 0);
  };

  if (items.length === 0) {
    return (
      <div className="text-center py-8 text-gray-500">
        <p>Nenhum produto adicionado</p>
        <p className="text-sm">Adicione produtos ao serviço</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h4 className="text-sm font-semibold text-gray-700">
          Produtos do Serviço ({items.length})
        </h4>
        <div className="text-sm text-gray-600">
          Total:{' '}
          <span className="font-semibold text-green-600">
            {formatPrice(calculateTotal())}
          </span>
        </div>
      </div>

      <div className="space-y-3">
        {items.map((item) => (
          <div
            key={item.product_id}
            className="p-4 border border-gray-200 rounded-xl bg-white"
          >
            {editingItem === item.product_id ? (
              // Modo de edição
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <h5 className="font-medium text-gray-900">
                    {item.product?.name || `Produto ${item.product_id}`}
                  </h5>
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => handleSave(item.product_id)}
                      className="p-1 text-green-600 hover:text-green-700"
                    >
                      <svg
                        className="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </button>
                    <button
                      onClick={handleCancel}
                      className="p-1 text-gray-400 hover:text-gray-600"
                    >
                      <XMarkIcon className="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Quantidade
                    </label>
                    <input
                      type="number"
                      value={editQuantity}
                      onChange={(e) =>
                        setEditQuantity(
                          Math.max(1, parseInt(e.target.value) || 1)
                        )
                      }
                      min="1"
                      className="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Preço Unit.
                    </label>
                    <input
                      type="number"
                      value={editPrice}
                      onChange={(e) =>
                        setEditPrice(
                          Math.max(0, parseFloat(e.target.value) || 0)
                        )
                      }
                      min="0"
                      step="0.01"
                      className="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Observações
                  </label>
                  <textarea
                    value={editNotes}
                    onChange={(e) => setEditNotes(e.target.value)}
                    rows={2}
                    className="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                  />
                </div>

                <div className="text-right text-sm">
                  <span className="font-semibold text-green-600">
                    Total: {formatPrice(editPrice * editQuantity)}
                  </span>
                </div>
              </div>
            ) : (
              // Modo de visualização
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <h5 className="font-medium text-gray-900">
                      {item.product?.name || `Produto ${item.product_id}`}
                    </h5>
                    <p className="text-sm text-gray-600">
                      SKU: {item.product?.sku || 'N/A'}
                      {item.product?.category &&
                        ` • ${item.product.category.name}`}
                    </p>
                    {item.notes && (
                      <p className="text-xs text-gray-500 mt-1">{item.notes}</p>
                    )}
                  </div>
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => handleEdit(item)}
                      className="p-1 text-blue-600 hover:text-blue-700"
                    >
                      <PencilIcon className="h-4 w-4" />
                    </button>
                    <button
                      onClick={() => onRemoveItem(item.product_id)}
                      className="p-1 text-red-600 hover:text-red-700"
                    >
                      <TrashIcon className="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div className="flex items-center justify-between text-sm">
                  <div className="flex items-center gap-4">
                    <span className="text-gray-600">
                      Qtd: <span className="font-medium">{item.quantity}</span>
                    </span>
                    <span className="text-gray-600">
                      Preço:{' '}
                      <span className="font-medium">
                        {formatPrice(item.unit_price)}
                      </span>
                    </span>
                  </div>
                  <span className="font-semibold text-green-600">
                    {formatPrice(item.total_price)}
                  </span>
                </div>
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Resumo */}
      <div className="p-4 bg-gray-50 rounded-xl">
        <div className="flex items-center justify-between">
          <span className="font-medium text-gray-700">Total dos Produtos:</span>
          <span className="font-semibold text-lg text-green-600">
            {formatPrice(calculateTotal())}
          </span>
        </div>
      </div>
    </div>
  );
};
