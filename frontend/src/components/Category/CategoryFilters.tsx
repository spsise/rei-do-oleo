import React from 'react';
import type { CategoryFilters } from '../../types/category';

interface CategoryFiltersComponentProps {
  filters: CategoryFilters;
  onFiltersChange: (filters: CategoryFilters) => void;
  onClearFilters: () => void;
}

export const CategoryFiltersComponent: React.FC<
  CategoryFiltersComponentProps
> = ({ filters, onFiltersChange, onClearFilters }) => {
  const handleFilterChange = (
    key: keyof CategoryFilters,
    value: string | number | boolean | undefined
  ) => {
    onFiltersChange({
      ...filters,
      [key]: value,
      page: 1, // Reset to first page when filters change
    });
  };

  const handleClearAll = () => {
    onClearFilters();
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-sm border mb-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-medium text-gray-900">Filtros</h3>
        <button
          onClick={handleClearAll}
          className="text-sm text-gray-600 hover:text-gray-700"
        >
          Limpar Filtros
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label
            htmlFor="search"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Buscar
          </label>
          <input
            type="text"
            id="search"
            value={filters.search || ''}
            onChange={(e) =>
              handleFilterChange('search', e.target.value || undefined)
            }
            placeholder="Buscar por nome..."
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label
            htmlFor="active"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Status
          </label>
          <select
            id="active"
            value={
              filters.active === undefined ? '' : filters.active.toString()
            }
            onChange={(e) => {
              const value = e.target.value;
              handleFilterChange(
                'active',
                value === '' ? undefined : value === 'true'
              );
            }}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="true">Ativas</option>
            <option value="false">Inativas</option>
          </select>
        </div>

        <div>
          <label
            htmlFor="per_page"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Itens por página
          </label>
          <select
            id="per_page"
            value={filters.per_page || 15}
            onChange={(e) =>
              handleFilterChange('per_page', Number(e.target.value))
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value={10}>10</option>
            <option value={15}>15</option>
            <option value={25}>25</option>
            <option value={50}>50</option>
          </select>
        </div>
      </div>

      {/* Filtros ativos */}
      {Object.keys(filters).some(
        (key) =>
          key !== 'per_page' &&
          key !== 'page' &&
          filters[key as keyof CategoryFilters]
      ) && (
        <div className="border-t pt-4 mt-4">
          <div className="flex flex-wrap gap-2">
            {filters.search && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Busca: {filters.search}
                <button
                  onClick={() => handleFilterChange('search', undefined)}
                  className="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-500"
                >
                  ×
                </button>
              </span>
            )}
            {filters.active !== undefined && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Status: {filters.active ? 'Ativas' : 'Inativas'}
                <button
                  onClick={() => handleFilterChange('active', undefined)}
                  className="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500"
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
