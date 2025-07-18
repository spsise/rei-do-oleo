import {
  MagnifyingGlassIcon,
  PlusIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import type { TechnicianProduct } from '../../types/technician';

interface ProductSelectorProps {
  products: TechnicianProduct[];
  isLoading: boolean;
  onSearch: (search: string) => void;
  onAddProduct: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  searchTerm: string;
  onSearchTermChange: (term: string) => void;
}

export const ProductSelector: React.FC<ProductSelectorProps> = ({
  products,
  isLoading,
  onSearch,
  onAddProduct,
  searchTerm,
  onSearchTermChange,
}) => {
  const [selectedProduct, setSelectedProduct] =
    useState<TechnicianProduct | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [notes, setNotes] = useState('');
  const [showAddForm, setShowAddForm] = useState(false);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (searchTerm.trim()) {
        onSearch(searchTerm);
      }
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [searchTerm, onSearch]);

  const handleProductSelect = (product: TechnicianProduct) => {
    setSelectedProduct(product);
    setQuantity(1);
    setNotes('');
    setShowAddForm(true);
  };

  const handleAddProduct = () => {
    if (selectedProduct) {
      onAddProduct(selectedProduct, quantity, notes.trim() || undefined);
      setSelectedProduct(null);
      setQuantity(1);
      setNotes('');
      setShowAddForm(false);
    }
  };

  const handleCancel = () => {
    setSelectedProduct(null);
    setQuantity(1);
    setNotes('');
    setShowAddForm(false);
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  return (
    <div className="space-y-4">
      {/* Busca de Produtos */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700">
          Buscar Produtos
        </label>
        <div className="relative">
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => onSearchTermChange(e.target.value)}
            placeholder="Digite o nome ou SKU do produto..."
            className="w-full px-4 py-3.5 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
          />
          <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
          </div>
        </div>
      </div>

      {/* Lista de Produtos */}
      {!showAddForm && (
        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">
              Produtos Disponíveis
            </h4>
            {isLoading && (
              <div className="flex items-center gap-2 text-sm text-gray-500">
                <div className="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent"></div>
                Carregando...
              </div>
            )}
          </div>

          <div className="max-h-60 overflow-y-auto space-y-2">
            {products.length === 0 && !isLoading ? (
              <div className="text-center py-8 text-gray-500">
                <p>Nenhum produto encontrado</p>
                <p className="text-sm">Tente buscar por outro termo</p>
              </div>
            ) : (
              products.map((product) => (
                <div
                  key={product.id}
                  onClick={() => handleProductSelect(product)}
                  className="p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 cursor-pointer transition-all duration-200"
                >
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <h5 className="font-medium text-gray-900">
                        {product.name}
                      </h5>
                      <p className="text-sm text-gray-600">
                        SKU: {product.sku}
                        {product.category && ` • ${product.category.name}`}
                      </p>
                      {product.description && (
                        <p className="text-xs text-gray-500 mt-1 line-clamp-2">
                          {product.description}
                        </p>
                      )}
                    </div>
                    <div className="text-right ml-4">
                      <p className="font-semibold text-green-600">
                        {formatPrice(product.price)}
                      </p>
                      <p className="text-xs text-gray-500">
                        Estoque: {product.stock_quantity}
                      </p>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      )}

      {/* Formulário de Adição */}
      {showAddForm && selectedProduct && (
        <div className="p-4 border border-blue-200 rounded-xl bg-blue-50/30">
          <div className="flex items-center justify-between mb-4">
            <h4 className="font-semibold text-gray-900">
              Adicionar {selectedProduct.name}
            </h4>
            <button
              onClick={handleCancel}
              className="p-1 text-gray-400 hover:text-gray-600 rounded"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <div className="space-y-4">
            {/* Informações do Produto */}
            <div className="flex items-center justify-between p-3 bg-white rounded-lg">
              <div>
                <p className="font-medium text-gray-900">
                  {selectedProduct.name}
                </p>
                <p className="text-sm text-gray-600">
                  SKU: {selectedProduct.sku}
                </p>
              </div>
              <div className="text-right">
                <p className="font-semibold text-green-600">
                  {formatPrice(selectedProduct.price)}
                </p>
                <p className="text-xs text-gray-500">
                  Estoque: {selectedProduct.stock_quantity}
                </p>
              </div>
            </div>

            {/* Quantidade */}
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">
                Quantidade
              </label>
              <input
                type="number"
                value={quantity}
                onChange={(e) =>
                  setQuantity(Math.max(1, parseInt(e.target.value) || 1))
                }
                min="1"
                max={selectedProduct.stock_quantity}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <p className="text-xs text-gray-500">
                Máximo disponível: {selectedProduct.stock_quantity}
              </p>
            </div>

            {/* Observações */}
            <div className="space-y-2">
              <label className="block text-sm font-medium text-gray-700">
                Observações (opcional)
              </label>
              <textarea
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                placeholder="Observações sobre este produto..."
                rows={2}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              />
            </div>

            {/* Total */}
            <div className="flex items-center justify-between p-3 bg-white rounded-lg">
              <span className="font-medium text-gray-700">Total:</span>
              <span className="font-semibold text-lg text-green-600">
                {formatPrice(selectedProduct.price * quantity)}
              </span>
            </div>

            {/* Botões */}
            <div className="flex gap-3">
              <button
                onClick={handleCancel}
                className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                Cancelar
              </button>
              <button
                onClick={handleAddProduct}
                className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors flex items-center justify-center gap-2"
              >
                <PlusIcon className="h-4 w-4" />
                Adicionar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
