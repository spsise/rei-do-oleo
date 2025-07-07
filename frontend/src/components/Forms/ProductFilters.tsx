import React, { useCallback, useEffect, useState } from 'react';
import { useCategories } from '../../hooks/useCategories';
import type { ProductFilters } from '../../types/product';
import { normalizeSku } from '../../utils/sku';

interface ProductFiltersComponentProps {
  filters: ProductFilters;
  onFiltersChange: (filters: ProductFilters) => void;
  onSearch: () => void;
  loading?: boolean;
}

// Hook personalizado para debounce
const useDebounce = (value: string, delay: number) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

export const ProductFiltersComponent: React.FC<
  ProductFiltersComponentProps
> = ({ filters, onFiltersChange, onSearch, loading = false }) => {
  const { data: categoriesData } = useCategories();
  const [isExpanded, setIsExpanded] = useState(false);

  // Estados locais para campos com debounce
  const [searchInput, setSearchInput] = useState(filters.search || '');
  const [brandInput, setBrandInput] = useState(filters.brand || '');
  const [supplierInput, setSupplierInput] = useState(filters.supplier || '');

  // Debounce para os campos de texto (500ms)
  const debouncedSearch = useDebounce(searchInput, 500);
  const debouncedBrand = useDebounce(brandInput, 500);
  const debouncedSupplier = useDebounce(supplierInput, 500);

  const categories = categoriesData || [];

  // Define handleFilterChange before using it in useEffect
  const handleFilterChange = useCallback(
    (
      key: keyof ProductFilters,
      value: string | number | boolean | undefined
    ) => {
      onFiltersChange({
        ...filters,
        [key]: value,
        page: 1, // Reset to first page when filters change
      });
    },
    [filters, onFiltersChange]
  );

  // Aplicar filtros com debounce
  useEffect(() => {
    if (debouncedSearch !== filters.search) {
      handleFilterChange('search', debouncedSearch);
    }
  }, [debouncedSearch, filters.search, handleFilterChange]);

  useEffect(() => {
    if (debouncedBrand !== filters.brand) {
      handleFilterChange('brand', debouncedBrand);
    }
  }, [debouncedBrand, filters.brand, handleFilterChange]);

  useEffect(() => {
    if (debouncedSupplier !== filters.supplier) {
      handleFilterChange('supplier', debouncedSupplier);
    }
  }, [debouncedSupplier, filters.supplier, handleFilterChange]);

  // Handlers para campos com debounce
  const handleSearchChange = (value: string) => {
    setSearchInput(value);
    // Normalize SKU if it looks like a SKU (contains uppercase letters and numbers)
    if (value.match(/[A-Z]/i)) {
      const normalizedValue = normalizeSku(value);
      handleFilterChange('search', normalizedValue);
    } else {
      handleFilterChange('search', value);
    }
  };

  const handleBrandChange = (value: string) => {
    setBrandInput(value);
  };

  const handleSupplierChange = (value: string) => {
    setSupplierInput(value);
  };

  // Handler para pressionar Enter (busca imediata)
  const handleKeyPress = (
    e: React.KeyboardEvent,
    field: 'search' | 'brand' | 'supplier'
  ) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      let value = '';
      switch (field) {
        case 'search':
          value = searchInput;
          break;
        case 'brand':
          value = brandInput;
          break;
        case 'supplier':
          value = supplierInput;
          break;
      }
      handleFilterChange(field, value);
    }
  };

  const handleClearFilters = () => {
    onFiltersChange({
      search: undefined,
      category_id: undefined,
      active: undefined,
      low_stock: undefined,
      per_page: 15,
    });
    setIsExpanded(false);
    // Limpar também os estados locais
    setSearchInput('');
    setBrandInput('');
    setSupplierInput('');
  };

  const hasActiveFilters = () => {
    return !!(
      filters.search ||
      filters.category_id ||
      filters.active !== undefined ||
      filters.low_stock !== undefined
    );
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-sm border mb-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-medium text-gray-900">Filtros</h3>
        <div className="flex space-x-2">
          {hasActiveFilters() && (
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
            Buscar{' '}
            <span className="text-xs text-gray-500">
              (Nome, SKU, código de barras)
            </span>
          </label>
          <input
            type="text"
            value={searchInput}
            onChange={(e) => handleSearchChange(e.target.value)}
            onKeyPress={(e) => handleKeyPress(e, 'search')}
            placeholder="Nome, SKU, código de barras... (Enter para buscar)"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            style={{ textTransform: 'uppercase' }}
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
                value={brandInput}
                onChange={(e) => handleBrandChange(e.target.value)}
                onKeyPress={(e) => handleKeyPress(e, 'brand')}
                placeholder="Filtrar por marca... (Enter para buscar)"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Fornecedor
              </label>
              <input
                type="text"
                value={supplierInput}
                onChange={(e) => handleSupplierChange(e.target.value)}
                onKeyPress={(e) => handleKeyPress(e, 'supplier')}
                placeholder="Filtrar por fornecedor... (Enter para buscar)"
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
      {hasActiveFilters() && (
        <div className="mt-4 pt-4 border-t">
          <div className="flex flex-wrap gap-2">
            <span className="text-sm text-gray-500">Filtros ativos:</span>
            {filters.search && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Busca: {filters.search}
                <button
                  onClick={() => {
                    handleFilterChange('search', '');
                    setSearchInput('');
                  }}
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
                  onClick={() => {
                    handleFilterChange('brand', '');
                    setBrandInput('');
                  }}
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
                  onClick={() => {
                    handleFilterChange('supplier', '');
                    setSupplierInput('');
                  }}
                  className="ml-1 text-pink-600 hover:text-pink-800"
                >
                  ×
                </button>
              </span>
            )}
          </div>
        </div>
      )}

      <div className="flex justify-end">
        <button
          onClick={onSearch}
          disabled={loading}
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {loading ? 'Buscando...' : 'Aplicar Filtros'}
        </button>
      </div>
    </div>
  );
};
