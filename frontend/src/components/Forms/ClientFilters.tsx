import React, { useCallback, useEffect, useState } from 'react';
import type { ClientFilters } from '../../types/client';

interface ClientFiltersProps {
  filters: ClientFilters;
  onFiltersChange: (filters: ClientFilters) => void;
  onClearFilters: () => void;
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

export const ClientFiltersComponent: React.FC<ClientFiltersProps> = ({
  filters,
  onFiltersChange,
  onClearFilters,
}) => {
  const [localFilters, setLocalFilters] = useState<ClientFilters>(filters);
  const [isExpanded, setIsExpanded] = useState(false);

  // Estado local para campo de busca com debounce
  const [searchInput, setSearchInput] = useState(filters.search || '');

  // Debounce para o campo de busca (500ms)
  const debouncedSearch = useDebounce(searchInput, 500);

  useEffect(() => {
    setLocalFilters(filters);
  }, [filters]);

  // Aplicar filtro de busca com debounce
  useEffect(() => {
    if (debouncedSearch !== filters.search) {
      const newFilters = {
        ...localFilters,
        search: debouncedSearch,
        page: 1, // Reset to first page when search changes
      };
      onFiltersChange(newFilters);
    }
  }, [debouncedSearch]);

  const handleFilterChange = useCallback(
    (
      key: keyof ClientFilters,
      value: string | boolean | number | undefined
    ) => {
      const newFilters = {
        ...localFilters,
        [key]: value,
        page: 1, // Reset to first page when filters change
      };
      setLocalFilters(newFilters);
      onFiltersChange(newFilters);
    },
    [localFilters, onFiltersChange]
  );

  // Handler para campo de busca com debounce
  const handleSearchChange = (value: string) => {
    setSearchInput(value);
  };

  // Handler para pressionar Enter (busca imediata)
  const handleSearchKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const newFilters = {
        ...localFilters,
        search: searchInput,
        page: 1,
      };
      onFiltersChange(newFilters);
    }
  };

  const handleClearFilters = () => {
    const emptyFilters: ClientFilters = {};
    setLocalFilters(emptyFilters);
    setSearchInput('');
    onClearFilters();
  };

  const hasActiveFilters = Object.values(filters).some(
    (value) => value !== undefined && value !== ''
  );

  return (
    <div className="bg-white p-4 rounded-lg shadow-sm border mb-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-medium text-gray-900">Filtros</h3>
        <button
          type="button"
          onClick={() => setIsExpanded(!isExpanded)}
          className="text-sm text-blue-600 hover:text-blue-700 focus:outline-none"
        >
          {isExpanded ? 'Ocultar' : 'Mostrar'} filtros
        </button>
      </div>

      {isExpanded && (
        <div className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {/* Busca */}
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
                value={searchInput}
                onChange={(e) => handleSearchChange(e.target.value)}
                onKeyPress={handleSearchKeyPress}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
                placeholder="Nome, email... (Enter para buscar)"
              />
            </div>

            {/* Tipo */}
            <div>
              <label
                htmlFor="type"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Tipo
              </label>
              <select
                id="type"
                value={localFilters.type || ''}
                onChange={(e) =>
                  handleFilterChange('type', e.target.value || undefined)
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
              >
                <option value="">Todos</option>
                <option value="pessoa_fisica">Pessoa Física</option>
                <option value="pessoa_juridica">Pessoa Jurídica</option>
              </select>
            </div>

            {/* Status */}
            <div>
              <label
                htmlFor="active"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Status
              </label>
              <select
                id="active"
                value={localFilters.active?.toString() || ''}
                onChange={(e) => {
                  const value = e.target.value;
                  handleFilterChange(
                    'active',
                    value === '' ? undefined : value === 'true'
                  );
                }}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
              >
                <option value="">Todos</option>
                <option value="true">Ativo</option>
                <option value="false">Inativo</option>
              </select>
            </div>

            {/* Itens por página */}
            <div>
              <label
                htmlFor="per_page"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Por página
              </label>
              <select
                id="per_page"
                value={localFilters.per_page?.toString() || '15'}
                onChange={(e) =>
                  handleFilterChange('per_page', parseInt(e.target.value) || 15)
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
              >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>

          {/* Botões */}
          <div className="flex justify-between items-center pt-4 border-t">
            <div className="flex items-center space-x-2">
              {hasActiveFilters && (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  Filtros ativos
                </span>
              )}
            </div>

            <div className="flex space-x-3">
              <button
                type="button"
                onClick={handleClearFilters}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Limpar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
