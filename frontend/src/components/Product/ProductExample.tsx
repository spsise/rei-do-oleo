import React, { useState } from 'react';
import {
  ProductCard,
  ProductCatalog,
  ProductQuickView,
  type TechnicianProduct,
} from './index';

// Dados de exemplo
const mockProducts: TechnicianProduct[] = [
  {
    id: 1,
    name: 'Óleo de Motor 5W-30',
    description: 'Óleo de motor sintético de alta performance',
    sku: 'OLEO-001',
    price: 45.9,
    stock_quantity: 50,
    category: { id: 1, name: 'Automotivo' },
  },
  {
    id: 2,
    name: 'Óleo de Transmissão',
    description: 'Óleo para transmissão automática',
    sku: 'OLEO-002',
    price: 32.5,
    stock_quantity: 25,
    category: { id: 1, name: 'Automotivo' },
  },
  {
    id: 3,
    name: 'Óleo Hidráulico',
    description: 'Óleo hidráulico para máquinas industriais',
    sku: 'OLEO-003',
    price: 28.75,
    stock_quantity: 0,
    category: { id: 2, name: 'Industrial' },
  },
];

const mockCategories = [
  { id: 1, name: 'Automotivo' },
  { id: 2, name: 'Industrial' },
  { id: 3, name: 'Especial' },
];

export const ProductExample: React.FC = () => {
  const [selectedProducts, setSelectedProducts] = useState<number[]>([]);
  const [cart, setCart] = useState<
    Array<{ product: TechnicianProduct; quantity: number; notes?: string }>
  >([]);

  const handleAddToCart = (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => {
    setCart((prev) => [...prev, { product, quantity, notes }]);
    setSelectedProducts((prev) => [...prev, product.id]);
  };

  const handleRemoveFromCart = (productId: number) => {
    setCart((prev) => prev.filter((item) => item.product.id !== productId));
    setSelectedProducts((prev) => prev.filter((id) => id !== productId));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto p-6 space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            Exemplo de Componentes de Produtos
          </h1>
          <p className="text-gray-600">
            Demonstração dos componentes modulares de produtos
          </p>
        </div>

        {/* Carrinho */}
        {cart.length > 0 && (
          <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">
              Carrinho
            </h2>
            <div className="space-y-3">
              {cart.map((item) => (
                <div
                  key={item.product.id}
                  className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div>
                    <h4 className="font-medium">{item.product.name}</h4>
                    <p className="text-sm text-gray-600">
                      Qtd: {item.quantity} - R${' '}
                      {(item.product.price * item.quantity).toFixed(2)}
                    </p>
                    {item.notes && (
                      <p className="text-xs text-gray-500">Obs: {item.notes}</p>
                    )}
                  </div>
                  <button
                    onClick={() => handleRemoveFromCart(item.product.id)}
                    className="text-red-600 hover:text-red-800 text-sm font-medium"
                  >
                    Remover
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Exemplo 1: ProductCatalog */}
        <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            1. ProductCatalog
          </h2>
          <ProductCatalog
            products={mockProducts}
            categories={mockCategories}
            isLoading={false}
            onAddToCart={handleAddToCart}
            selectedProductIds={selectedProducts}
            title="Catálogo Completo"
            showFilters={true}
            compact={false}
          />
        </div>

        {/* Exemplo 2: ProductCard Individual */}
        <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            2. ProductCard Individual
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {mockProducts.map((product) => (
              <ProductCard
                key={product.id}
                product={product}
                isSelected={selectedProducts.includes(product.id)}
                onAddProduct={() => handleAddToCart(product, 1)}
                compact={false}
              />
            ))}
          </div>
        </div>

        {/* Exemplo 3: ProductCard Compacto */}
        <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            3. ProductCard Compacto
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            {mockProducts.map((product) => (
              <ProductCard
                key={product.id}
                product={product}
                isSelected={selectedProducts.includes(product.id)}
                onAddProduct={() => handleAddToCart(product, 1)}
                compact={true}
              />
            ))}
          </div>
        </div>

        {/* Exemplo 4: ProductQuickView */}
        <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            4. ProductQuickView
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {mockProducts.map((product) => (
              <ProductQuickView
                key={product.id}
                product={product}
                onAddToCart={handleAddToCart}
                isSelected={selectedProducts.includes(product.id)}
                compact={false}
              />
            ))}
          </div>
        </div>

        {/* Resumo */}
        <div className="bg-blue-50 rounded-lg p-6 border border-blue-200">
          <h3 className="text-lg font-semibold text-blue-900 mb-2">
            Resumo dos Componentes
          </h3>
          <ul className="text-blue-800 space-y-1">
            <li>
              • <strong>ProductCard</strong>: Exibe produto individual com opção
              de adicionar
            </li>
            <li>
              • <strong>ProductFilters</strong>: Filtros de busca e categoria
            </li>
            <li>
              • <strong>ProductList</strong>: Lista de produtos com loading
              states
            </li>
            <li>
              • <strong>ProductAddModal</strong>: Modal para adicionar com
              quantidade
            </li>
            <li>
              • <strong>ProductSelectionModal</strong>: Modal completo de
              seleção
            </li>
            <li>
              • <strong>ProductCatalog</strong>: Catálogo completo integrado
            </li>
            <li>
              • <strong>ProductQuickView</strong>: Visualização rápida com
              detalhes
            </li>
          </ul>
        </div>
      </div>
    </div>
  );
};
