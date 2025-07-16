import { ChartBarIcon, CubeIcon } from '@heroicons/react/24/outline';
import React from 'react';

interface TopProduct {
  id: number;
  name: string;
  sales_count: number;
  revenue: number;
  quantity_sold: number;
  category: string;
}

interface TopProductsProps {
  products: TopProduct[];
  loading?: boolean;
}

export const TopProducts: React.FC<TopProductsProps> = ({
  products,
  loading = false,
}) => {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  if (loading) {
    return (
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between mb-6">
          <h3 className="text-lg font-semibold text-gray-900">
            Produtos Mais Vendidos
          </h3>
          <CubeIcon className="h-6 w-6 text-gray-400" />
        </div>
        <div className="space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="animate-pulse">
              <div className="flex items-center justify-between">
                <div className="space-y-2 flex-1">
                  <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                  <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
                <div className="h-4 bg-gray-200 rounded w-20"></div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (!products || products.length === 0) {
    return (
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between mb-6">
          <h3 className="text-lg font-semibold text-gray-900">
            Produtos Mais Vendidos
          </h3>
          <CubeIcon className="h-6 w-6 text-gray-400" />
        </div>
        <div className="text-center py-8">
          <CubeIcon className="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <p className="text-gray-500">Nenhum produto vendido ainda</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-gray-900">
          Produtos Mais Vendidos
        </h3>
        <ChartBarIcon className="h-6 w-6 text-green-600" />
      </div>

      <div className="space-y-4">
        {products.map((product, index) => (
          <div
            key={product.id}
            className="flex items-center justify-between p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors"
          >
            <div className="flex items-center space-x-4 flex-1">
              <div className="flex-shrink-0">
                <div
                  className={`w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold ${
                    index === 0
                      ? 'bg-yellow-500'
                      : index === 1
                        ? 'bg-gray-400'
                        : index === 2
                          ? 'bg-orange-500'
                          : 'bg-blue-500'
                  }`}
                >
                  {index + 1}
                </div>
              </div>

              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {product.name}
                </p>
                <div className="flex items-center space-x-4 mt-1">
                  <span className="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded-full">
                    {product.category}
                  </span>
                  <span className="text-xs text-gray-500">
                    {product.quantity_sold} unidades
                  </span>
                </div>
              </div>
            </div>

            <div className="text-right">
              <p className="text-sm font-semibold text-gray-900">
                {formatCurrency(product.revenue)}
              </p>
              <p className="text-xs text-gray-500">
                {product.sales_count} vendas
              </p>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-6 pt-4 border-t border-gray-200">
        <div className="flex items-center justify-between text-sm">
          <span className="text-gray-600">Total de vendas:</span>
          <span className="font-semibold text-gray-900">
            {products.reduce((sum, product) => sum + product.sales_count, 0)}{' '}
            vendas
          </span>
        </div>
        <div className="flex items-center justify-between text-sm mt-1">
          <span className="text-gray-600">Receita total:</span>
          <span className="font-semibold text-green-600">
            {formatCurrency(
              products.reduce((sum, product) => sum + product.revenue, 0)
            )}
          </span>
        </div>
      </div>
    </div>
  );
};
