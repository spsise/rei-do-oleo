import {
  ArrowsPointingInIcon,
  ArrowsPointingOutIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  TagIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { type TechnicianProduct } from '../../types/technician';

interface ProductSelectionModalProps {
  isOpen: boolean;
  onClose: () => void;
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoading: boolean;
  searchTerm: string;
  onSearch: (search: string) => void;
  onAddProduct: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  selectedProductIds: number[];
}

export const ProductSelectionModal: React.FC<ProductSelectionModalProps> = ({
  isOpen,
  onClose,
  products,
  categories,
  isLoading,
  searchTerm,
  onSearch,
  onAddProduct,
  selectedProductIds,
}) => {
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [notes, setNotes] = useState('');
  const [selectedProduct, setSelectedProduct] =
    useState<TechnicianProduct | null>(null);
  const [showAddModal, setShowAddModal] = useState(false);
  const [isMaximized, setIsMaximized] = useState(false);

  // Filtra produtos por categoria e busca
  const filteredProducts = products.filter((product) => {
    const matchesSearch =
      product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      product.sku?.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory =
      !selectedCategory || product.category?.id === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  const handleAddProduct = (product: TechnicianProduct) => {
    setSelectedProduct(product);
    setQuantity(1);
    setNotes('');
    setShowAddModal(true);
  };

  const handleConfirmAdd = () => {
    if (selectedProduct) {
      onAddProduct(selectedProduct, quantity, notes.trim() || undefined);
      setShowAddModal(false);
      setSelectedProduct(null);
      setQuantity(1);
      setNotes('');
    }
  };

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

  if (!isOpen) return null;

  return (
    <>
      {/* Modal Principal */}
      <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
        <div
          className={`bg-white rounded-2xl shadow-2xl overflow-hidden animate-modalSlideInUp transition-all duration-300 ${
            isMaximized
              ? 'w-full h-full max-w-none max-h-none rounded-none'
              : 'w-full max-w-6xl max-h-[90vh]'
          }`}
        >
          {/* Header */}
          <div className="sticky top-0 bg-white rounded-t-2xl p-4 border-b border-gray-100 z-10">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg">
                  <PlusIcon className="h-5 w-5 text-white" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-gray-900">
                    Selecionar Produtos
                  </h3>
                  <p className="text-gray-600 text-xs">
                    Escolha os produtos para adicionar ao serviço
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsMaximized(!isMaximized)}
                  className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                  title={isMaximized ? 'Restaurar' : 'Maximizar'}
                >
                  {isMaximized ? (
                    <ArrowsPointingInIcon className="h-5 w-5" />
                  ) : (
                    <ArrowsPointingOutIcon className="h-5 w-5" />
                  )}
                </button>
                <button
                  onClick={onClose}
                  className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <XMarkIcon className="h-6 w-6" />
                </button>
              </div>
            </div>

            {/* Filtros */}
            <div className="space-y-3">
              {/* Busca */}
              <div className="relative">
                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => onSearch(e.target.value)}
                  placeholder="Buscar produtos por nome ou SKU..."
                  className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md text-sm"
                />
              </div>

              {/* Filtro por Categoria - Compacto */}
              <div className="space-y-2">
                <div className="flex items-center gap-2 text-xs font-medium text-gray-700">
                  <FunnelIcon className="h-3 w-3" />
                  Filtrar por categoria:
                </div>
                <div className="flex flex-wrap gap-1.5">
                  <button
                    onClick={() => setSelectedCategory(null)}
                    className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 ${
                      selectedCategory === null
                        ? 'bg-blue-100 text-blue-700 border border-blue-300'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
                    }`}
                  >
                    Todas ({products.length})
                  </button>
                  {categories.map((category) => {
                    const categoryProductCount = products.filter(
                      (product) => product.category?.id === category.id
                    ).length;
                    return (
                      <button
                        key={category.id}
                        onClick={() => setSelectedCategory(category.id)}
                        className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 ${
                          selectedCategory === category.id
                            ? 'bg-blue-100 text-blue-700 border border-blue-300'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
                        }`}
                      >
                        {category.name} ({categoryProductCount})
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>

          {/* Lista de Produtos */}
          <div
            className={`p-6 ${isMaximized ? 'h-[calc(100vh-300px)] overflow-y-auto' : 'max-h-[60vh] overflow-y-auto'}`}
          >
            {isLoading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-4 text-gray-600">Carregando produtos...</p>
              </div>
            ) : filteredProducts.length === 0 ? (
              <div className="text-center py-12">
                <div className="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                  <TagIcon className="h-8 w-8 text-gray-400" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-2">
                  Nenhum produto encontrado
                </h3>
                <p className="text-gray-500">
                  {searchTerm || selectedCategory
                    ? 'Tente ajustar os filtros de busca'
                    : 'Não há produtos disponíveis no momento'}
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {filteredProducts.map((product) => {
                  const isSelected = selectedProductIds.includes(product.id);
                  const stockStatus = getStockStatus(
                    product.stock_quantity || 0
                  );

                  return (
                    <div
                      key={product.id}
                      className={`bg-white border rounded-lg p-3 shadow-sm hover:shadow-md transition-all duration-200 ${
                        isSelected
                          ? 'border-green-300 bg-green-50/30'
                          : 'border-gray-200 hover:border-blue-300'
                      }`}
                    >
                      {/* Layout Compacto - Tudo em uma linha */}
                      <div className="flex items-center justify-between">
                        {/* Informações do Produto */}
                        <div className="flex-1 min-w-0 mr-3">
                          <div className="flex items-center gap-2 mb-1">
                            <h4 className="font-semibold text-gray-900 text-sm truncate">
                              {product.name}
                            </h4>
                            <span className="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 flex-shrink-0">
                              {product.sku || 'N/A'}
                            </span>
                          </div>

                          <div className="flex items-center gap-3 text-xs">
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
                              <span className="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0">
                                {product.category.name}
                              </span>
                            )}
                          </div>
                        </div>

                        {/* Botão de Adicionar - Compacto */}
                        <button
                          onClick={() => handleAddProduct(product)}
                          disabled={
                            isSelected || (product.stock_quantity || 0) <= 0
                          }
                          className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 flex-shrink-0 ${
                            isSelected
                              ? 'bg-green-100 text-green-700 cursor-not-allowed'
                              : (product.stock_quantity || 0) <= 0
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-sm hover:shadow-md transform hover:scale-105'
                          }`}
                        >
                          {isSelected ? (
                            '✓ Adicionado'
                          ) : (product.stock_quantity || 0) <= 0 ? (
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
                })}
              </div>
            )}
          </div>

          {/* Footer */}
          <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
            <div className="flex justify-between items-center">
              <div className="text-sm text-gray-600">
                {filteredProducts.length}{' '}
                {filteredProducts.length === 1 ? 'produto' : 'produtos'}{' '}
                encontrados
                {selectedCategory && (
                  <span className="ml-2 text-blue-600">
                    • Categoria:{' '}
                    {categories.find((c) => c.id === selectedCategory)?.name}
                  </span>
                )}
              </div>
              <div className="flex gap-3">
                <button
                  onClick={onClose}
                  className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Modal de Adicionar Produto */}
      {showAddModal && selectedProduct && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[60] animate-modalFadeIn">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md animate-modalSlideInUp">
            {/* Header */}
            <div className="p-6 border-b border-gray-100">
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-semibold text-gray-900">
                  Adicionar Produto
                </h3>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <XMarkIcon className="h-5 w-5" />
                </button>
              </div>
              <p className="text-sm text-gray-600 mt-1">
                {selectedProduct.name}
              </p>
            </div>

            {/* Conteúdo */}
            <div className="p-6 space-y-4">
              {/* Informações do Produto */}
              <div className="bg-gray-50 rounded-lg p-4 space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">SKU:</span>
                  <span className="font-mono text-sm">
                    {selectedProduct.sku || 'N/A'}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Preço:</span>
                  <span className="font-semibold text-green-600">
                    {formatPrice(selectedProduct.price || 0)}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Estoque:</span>
                  <span className="text-sm">
                    {selectedProduct.stock_quantity || 0} unidades
                  </span>
                </div>
              </div>

              {/* Quantidade */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Quantidade
                </label>
                <input
                  type="number"
                  min="1"
                  max={selectedProduct.stock_quantity || 1}
                  value={quantity}
                  onChange={(e) => setQuantity(Number(e.target.value))}
                  className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
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
            </div>

            {/* Footer */}
            <div className="p-6 border-t border-gray-100 flex gap-3">
              <button
                onClick={() => setShowAddModal(false)}
                className="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
              >
                Cancelar
              </button>
              <button
                onClick={handleConfirmAdd}
                disabled={
                  quantity < 1 ||
                  quantity > (selectedProduct.stock_quantity || 1)
                }
                className="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Adicionar
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};
