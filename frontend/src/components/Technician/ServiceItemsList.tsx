import {
  CurrencyDollarIcon,
  PlusIcon,
  ShoppingCartIcon,
  TrashIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianServiceItem } from '../../types/technician';

interface ServiceItemsListProps {
  items: TechnicianServiceItem[];
  onRemoveItem: (itemId: string) => void;
  onUpdateQuantity: (productId: number, quantity: number) => void;
  onUpdatePrice: (productId: number, price: number) => void;
  onUpdateNotes: (productId: number, notes: string) => void;
  onAddProduct: () => void;
  isLoading?: boolean;
}

export const ServiceItemsList: React.FC<ServiceItemsListProps> = ({
  items,
  onRemoveItem,
  onUpdateQuantity,
  onUpdatePrice,
  onUpdateNotes,
  onAddProduct,
  isLoading = false,
}) => {
  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const calculateItemTotal = (item: TechnicianServiceItem) => {
    return (item.unit_price || 0) * (item.quantity || 0);
  };

  const calculateTotal = () => {
    return items.reduce((total, item) => total + calculateItemTotal(item), 0);
  };

  if (items.length === 0) {
    return (
      <div className="text-center py-12">
        <div className="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <ShoppingCartIcon className="h-8 w-8 text-gray-400" />
        </div>
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          Nenhum produto adicionado
        </h3>
        <p className="text-gray-500 mb-6">
          Adicione produtos ao serviço para começar
        </p>
        <button
          onClick={onAddProduct}
          disabled={isLoading}
          className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none disabled:opacity-50"
        >
          <PlusIcon className="h-5 w-5" />
          Adicionar Produto
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {/* Header do Carrinho */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="p-2 bg-blue-100 rounded-lg">
            <ShoppingCartIcon className="h-5 w-5 text-blue-600" />
          </div>
          <div>
            <h3 className="text-lg font-semibold text-gray-900">
              Produtos do Serviço
            </h3>
            <p className="text-sm text-gray-500">
              {items.length} {items.length === 1 ? 'produto' : 'produtos'} no
              carrinho
            </p>
          </div>
        </div>
        <button
          onClick={onAddProduct}
          disabled={isLoading}
          className="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-medium text-sm shadow-md hover:shadow-lg transform hover:scale-105 disabled:transform-none disabled:opacity-50"
        >
          <PlusIcon className="h-4 w-4" />
          Adicionar
        </button>
      </div>

      {/* Lista de Itens - Mais Concisos */}
      <div className="space-y-3">
        {items.map((item) => (
          <div
            key={item.product_id}
            className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
          >
            {/* Header do Item */}
            <div className="flex items-start justify-between mb-3">
              <div className="flex-1 min-w-0">
                <h4 className="font-semibold text-gray-900 text-sm truncate">
                  {item.product?.name || 'Produto não encontrado'}
                </h4>
                <div className="flex items-center gap-2 mt-1">
                  <span className="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-600">
                    SKU: {item.product?.sku || 'N/A'}
                  </span>
                  {item.product?.category && (
                    <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                      {item.product.category.name}
                    </span>
                  )}
                </div>
              </div>
              <button
                onClick={() =>
                  onRemoveItem(item.id || `item-${item.product_id}`)
                }
                className="ml-2 p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Remover produto"
              >
                <TrashIcon className="h-4 w-4" />
              </button>
            </div>

            {/* Controles de Quantidade e Preço - Layout Compacto */}
            <div className="grid grid-cols-3 gap-3 mb-3">
              {/* Quantidade */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  Qtd
                </label>
                <input
                  type="number"
                  min="1"
                  value={item.quantity || 1}
                  onChange={(e) =>
                    onUpdateQuantity(item.product_id, Number(e.target.value))
                  }
                  className="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
              </div>

              {/* Preço Unitário */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  Preço Unit.
                </label>
                <div className="relative">
                  <span className="absolute left-2 top-1.5 text-gray-400 text-xs">
                    R$
                  </span>
                  <input
                    type="number"
                    min="0"
                    step="0.01"
                    value={item.unit_price || 0}
                    onChange={(e) =>
                      onUpdatePrice(item.product_id, Number(e.target.value))
                    }
                    className="w-full pl-6 pr-2 py-1.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  />
                </div>
              </div>

              {/* Total do Item */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  Total
                </label>
                <div className="flex items-center gap-1 px-2 py-1.5 bg-gray-50 rounded-lg">
                  <CurrencyDollarIcon className="h-3 w-3 text-green-600" />
                  <span className="font-semibold text-green-600 text-sm">
                    {formatPrice(calculateItemTotal(item))}
                  </span>
                </div>
              </div>
            </div>

            {/* Observações - Compacta */}
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">
                Observações
              </label>
              <textarea
                value={item.notes || ''}
                onChange={(e) => onUpdateNotes(item.product_id, e.target.value)}
                placeholder="Observações sobre este produto..."
                className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"
                rows={1}
              />
            </div>
          </div>
        ))}
      </div>

      {/* Resumo do Carrinho */}
      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <CurrencyDollarIcon className="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <h4 className="font-semibold text-gray-900">
                Total dos Produtos
              </h4>
              <p className="text-sm text-gray-600">
                {items.length} {items.length === 1 ? 'item' : 'itens'}
              </p>
            </div>
          </div>
          <div className="text-right">
            <div className="text-2xl font-bold text-blue-600">
              {formatPrice(calculateTotal())}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
