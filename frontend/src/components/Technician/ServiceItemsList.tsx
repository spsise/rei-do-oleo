import {
  CurrencyDollarIcon,
  MinusIcon,
  PlusIcon,
  ShoppingCartIcon,
  TrashIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import { type TechnicianServiceItem } from '../../types/technician';

interface ServiceItemsListProps {
  items: TechnicianServiceItem[];
  onRemoveItem: (itemId: string) => void;
  onUpdateQuantity: (itemId: string, quantity: number) => void;
  onUpdatePrice: (itemId: string, price: number) => void;
  onUpdateNotes: (itemId: string, notes: string) => void;
  onAddProduct: () => void;
  isLoading?: boolean;
}

// Componente de controle de quantidade otimizado para mobile
interface QuantityControlProps {
  value: number;
  onChange: (quantity: number) => void;
  min?: number;
  max?: number;
}

const QuantityControl: React.FC<QuantityControlProps> = ({
  value,
  onChange,
  min = 1,
  max = 999,
}) => {
  const [inputValue, setInputValue] = useState(value.toString());
  const [isEditing, setIsEditing] = useState(false);

  // Sincronizar o estado interno com a prop externa
  useEffect(() => {
    setInputValue(value.toString());
  }, [value]);

  const handleIncrement = () => {
    const newValue = Math.min(value + 1, max);
    onChange(newValue);
    setInputValue(newValue.toString());
  };

  const handleDecrement = () => {
    const newValue = Math.max(value - 1, min);
    onChange(newValue);
    setInputValue(newValue.toString());
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newValue = e.target.value;
    setInputValue(newValue);

    // Se o campo estiver vazio, não atualizar ainda (evita remoção do produto)
    if (newValue === '') {
      return;
    }

    const numValue = parseInt(newValue, 10);
    if (!isNaN(numValue) && numValue >= min && numValue <= max) {
      onChange(numValue);
    }
  };

  const handleInputBlur = () => {
    setIsEditing(false);
    const numValue = parseInt(inputValue, 10);

    // Se o valor for inválido ou vazio, restaurar o valor anterior
    if (isNaN(numValue) || numValue < min || inputValue === '') {
      setInputValue(value.toString());
      return;
    }

    // Aplicar limites
    const clampedValue = Math.max(min, Math.min(numValue, max));
    if (clampedValue !== value) {
      onChange(clampedValue);
      setInputValue(clampedValue.toString());
    }
  };

  const handleInputFocus = () => {
    setIsEditing(true);
  };

  return (
    <div className="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
      {/* Botão de decremento - Design moderno */}
      <button
        onClick={handleDecrement}
        disabled={value <= min}
        className="flex items-center justify-center w-10 h-10 bg-gradient-to-b from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 active:from-gray-200 active:to-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 border-r border-gray-200 touch-manipulation group"
        type="button"
        aria-label="Diminuir quantidade"
      >
        <MinusIcon className="h-4 w-4 text-gray-600 group-hover:text-gray-800 transition-colors" />
      </button>

      {/* Campo de input - Design centralizado */}
      <div className="flex-1 min-w-0">
        <input
          type="number"
          min={min}
          max={max}
          value={isEditing ? inputValue : value}
          onChange={handleInputChange}
          onBlur={handleInputBlur}
          onFocus={handleInputFocus}
          className="w-full text-center py-2.5 border-none focus:outline-none focus:ring-0 text-sm font-semibold bg-white text-gray-900 placeholder-gray-400"
          inputMode="numeric"
          pattern="[0-9]*"
          aria-label="Quantidade"
        />
      </div>

      {/* Botão de incremento - Design moderno */}
      <button
        onClick={handleIncrement}
        disabled={value >= max}
        className="flex items-center justify-center w-10 h-10 bg-gradient-to-b from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 active:from-gray-200 active:to-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 border-l border-gray-200 touch-manipulation group"
        type="button"
        aria-label="Aumentar quantidade"
      >
        <PlusIcon className="h-4 w-4 text-gray-600 group-hover:text-gray-800 transition-colors" />
      </button>
    </div>
  );
};

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
    if (isNaN(price) || !isFinite(price)) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
      }).format(0);
    }
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const calculateItemTotal = (item: TechnicianServiceItem) => {
    const total = (item.unit_price || 0) * (item.quantity || 0);
    return isNaN(total) ? 0 : total;
  };

  const calculateTotal = () => {
    const total = items.reduce(
      (total, item) => total + calculateItemTotal(item),
      0
    );
    return isNaN(total) ? 0 : total;
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
        {items.map((item, index) => (
          <div
            key={item.id || `item-${item.product_id}-${index}`}
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

            {/* Controles de Quantidade e Preço - Layout Responsivo */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
              {/* Quantidade - Versão Mobile Otimizada */}
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                  Quantidade
                </label>
                <QuantityControl
                  key={`quantity-${item.id || `item-${item.product_id}-${index}`}`}
                  value={item.quantity || 1}
                  onChange={(quantity) => {
                    onUpdateQuantity(
                      item.id || `item-${item.product_id}-${index}`,
                      quantity
                    );
                  }}
                  min={1}
                  max={999}
                />
              </div>

              {/* Preço Unitário */}
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                  Preço Unitário
                </label>
                <div className="relative group">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-gray-500 text-sm font-medium">
                      R$
                    </span>
                  </div>
                  <input
                    type="number"
                    min="0"
                    step="0.01"
                    value={item.unit_price || 0}
                    onChange={(e) =>
                      onUpdatePrice(
                        item.id || `item-${item.product_id}-${index}`,
                        Number(e.target.value)
                      )
                    }
                    className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium text-gray-900 placeholder-gray-400 shadow-sm hover:shadow-md transition-shadow group-hover:border-gray-300"
                    inputMode="decimal"
                    placeholder="0,00"
                  />
                </div>
              </div>

              {/* Total do Item */}
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                  Total do Item
                </label>
                <div className="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 shadow-sm">
                  <div className="p-1 bg-green-100 rounded-full">
                    <CurrencyDollarIcon className="h-3 w-3 text-green-600" />
                  </div>
                  <span className="font-bold text-green-700 text-sm">
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
                onChange={(e) =>
                  onUpdateNotes(
                    item.id || `item-${item.product_id}-${index}`,
                    e.target.value
                  )
                }
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
