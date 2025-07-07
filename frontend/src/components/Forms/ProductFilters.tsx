import React, { useState } from 'react';
import { useCategories } from '../../hooks/useCategories';
import type { ProductFilters } from '../../types/product';

interface ProductFiltersComponentProps {
  filters: ProductFilters;
  onFiltersChange: (filters: ProductFilters) => void;
  onClearFilters: () => void;
}

export const ProductFiltersComponent: React.FC<
  ProductFiltersComponentProps
> = ({ filters, onFiltersChange, onClearFilters }) => {
  const { data: categoriesData } = useCategories();
  const [isExpanded, setIsExpanded] = useState(false);

  const categories = categoriesData?.data || [];

  const handleFilterChange = (key: keyof ProductFilters, value: any) => {
    onFiltersChange({
      ...filters,
      [key]: value,
      page: 1, // Reset to first page when filters change
    });
  };

  const handleClearFilters = () => {
    onClearFilters();
    setIsExpanded(false);
  };

  const hasActiveFilters = Object.keys(filters).some(
    (key) =>
      key !== 'per_page' &&
      key !== 'page' &&
      filters[key as keyof ProductFilters]
  );

  return (
    <div className="bg-white p-6 rounded-lg shadow-sm border mb-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-medium text-gray-900">Filtros</h3>
        <div className="flex space-x-2">
          {hasActiveFilters && (
            <button
              onClick={handleClearFilters}
              className="px-3 py-1 text-sm text-gray-600 hover:text-gray-800"
            >
              Limpar Filtros
            </button>
          )}
          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="px-3 py-1 text-sm text-blue-600 hover:text-blue-700"
          >
            {isExpanded ? 'Ocultar' : 'Mostrar'} Filtros
          </button>
        </div>
      </div>

      {/* Filtros básicos sempre visíveis */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Buscar
          </label>
          <input
            type="text"
            value={filters.search || ''}
            onChange={(e) => handleFilterChange('search', e.target.value)}
            placeholder="Nome, SKU, código de barras..."
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Categoria
          </label>
          <select
            value={filters.category_id || ''}
            onChange={(e) =>
              handleFilterChange(
                'category_id',
                e.target.value ? Number(e.target.value) : undefined
              )
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todas as categorias</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Status
          </label>
          <select
            value={
              filters.active === undefined ? '' : filters.active.toString()
            }
            onChange={(e) =>
              handleFilterChange(
                'active',
                e.target.value === '' ? undefined : e.target.value === 'true'
              )
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos os status</option>
            <option value="true">Ativo</option>
            <option value="false">Inativo</option>
          </select>
        </div>
      </div>

      {/* Filtros expandidos */}
      {isExpanded && (
        <div className="border-t pt-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Estoque Baixo
              </label>
              <select
                value={
                  filters.low_stock === undefined
                    ? ''
                    : filters.low_stock.toString()
                }
                onChange={(e) =>
                  handleFilterChange(
                    'low_stock',
                    e.target.value === ''
                      ? undefined
                      : e.target.value === 'true'
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option value="true">Apenas estoque baixo</option>
                <option value="false">Sem estoque baixo</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Destaque
              </label>
              <select
                value={
                  filters.featured === undefined
                    ? ''
                    : filters.featured.toString()
                }
                onChange={(e) =>
                  handleFilterChange(
                    'featured',
                    e.target.value === ''
                      ? undefined
                      : e.target.value === 'true'
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option value="true">Apenas destacados</option>
                <option value="false">Sem destaque</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Marca
              </label>
              <input
                type="text"
                value={filters.brand || ''}
                onChange={(e) => handleFilterChange('brand', e.target.value)}
                placeholder="Filtrar por marca..."
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Fornecedor
              </label>
              <input
                type="text"
                value={filters.supplier || ''}
                onChange={(e) => handleFilterChange('supplier', e.target.value)}
                placeholder="Filtrar por fornecedor..."
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <div className="mt-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Itens por página
            </label>
            <select
              value={filters.per_page || 15}
              onChange={(e) =>
                handleFilterChange('per_page', Number(e.target.value))
              }
              className="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value={10}>10</option>
              <option value={15}>15</option>
              <option value={25}>25</option>
              <option value={50}>50</option>
              <option value={100}>100</option>
            </select>
          </div>
        </div>
      )}

      {/* Indicador de filtros ativos */}
      {hasActiveFilters && (
        <div className="mt-4 pt-4 border-t">
          <div className="flex flex-wrap gap-2">
            <span className="text-sm text-gray-500">Filtros ativos:</span>
            {filters.search && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Busca: {filters.search}
                <button
                  onClick={() => handleFilterChange('search', '')}
                  className="ml-1 text-blue-600 hover:text-blue-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.category_id && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Categoria:{' '}
                {categories.find((c) => c.id === filters.category_id)?.name}
                <button
                  onClick={() => handleFilterChange('category_id', undefined)}
                  className="ml-1 text-green-600 hover:text-green-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.active !== undefined && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Status: {filters.active ? 'Ativo' : 'Inativo'}
                <button
                  onClick={() => handleFilterChange('active', undefined)}
                  className="ml-1 text-yellow-600 hover:text-yellow-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.low_stock !== undefined && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Estoque: {filters.low_stock ? 'Baixo' : 'Normal'}
                <button
                  onClick={() => handleFilterChange('low_stock', undefined)}
                  className="ml-1 text-red-600 hover:text-red-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.featured !== undefined && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                Destaque: {filters.featured ? 'Sim' : 'Não'}
                <button
                  onClick={() => handleFilterChange('featured', undefined)}
                  className="ml-1 text-purple-600 hover:text-purple-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.brand && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                Marca: {filters.brand}
                <button
                  onClick={() => handleFilterChange('brand', '')}
                  className="ml-1 text-indigo-600 hover:text-indigo-800"
                >
                  ×
                </button>
              </span>
            )}
            {filters.supplier && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                Fornecedor: {filters.supplier}
                <button
                  onClick={() => handleFilterChange('supplier', '')}
                  className="ml-1 text-pink-600 hover:text-pink-800"
                >
                  ×
                </button>
              </span>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
