import React, { useState } from 'react';
import type { ServiceFilters } from '../../types/service';

interface ServiceFiltersComponentProps {
  filters: ServiceFilters;
  onFiltersChange: (filters: ServiceFilters) => void;
  onClearFilters: () => void;
}

export const ServiceFiltersComponent: React.FC<
  ServiceFiltersComponentProps
> = ({ filters, onFiltersChange, onClearFilters }) => {
  const [isExpanded, setIsExpanded] = useState(false);

  const handleFilterChange = (
    key: keyof ServiceFilters,
    value: string | number | undefined
  ) => {
    onFiltersChange({
      ...filters,
      [key]: value,
      page: 1, // Reset to first page when filters change
    });
  };

  const handleClearAll = () => {
    onClearFilters();
    setIsExpanded(false);
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-sm border mb-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-medium text-gray-900">Filtros</h3>
        <div className="flex space-x-2">
          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="text-sm text-blue-600 hover:text-blue-700"
          >
            {isExpanded ? 'Ocultar' : 'Mostrar'} Filtros Avançados
          </button>
          <button
            onClick={handleClearAll}
            className="text-sm text-gray-600 hover:text-gray-700"
          >
            Limpar Filtros
          </button>
        </div>
      </div>

      {/* Filtros básicos sempre visíveis */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
            placeholder="Buscar por número, cliente, veículo..."
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label
            htmlFor="status"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Status
          </label>
          <select
            id="status"
            value={filters.status || ''}
            onChange={(e) =>
              handleFilterChange('status', e.target.value || undefined)
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="pendente">Pendente</option>
            <option value="em_andamento">Em Andamento</option>
            <option value="concluído">Concluído</option>
            <option value="cancelado">Cancelado</option>
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

      {/* Filtros avançados */}
      {isExpanded && (
        <div className="border-t pt-4">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label
                htmlFor="service_center_id"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Centro de Serviço
              </label>
              <select
                id="service_center_id"
                value={filters.service_center_id || ''}
                onChange={(e) =>
                  handleFilterChange(
                    'service_center_id',
                    e.target.value ? Number(e.target.value) : undefined
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os centros</option>
                {/* TODO: Carregar centros de serviço dinamicamente */}
                <option value={1}>Centro Principal</option>
                <option value={2}>Centro Zona Sul</option>
              </select>
            </div>

            <div>
              <label
                htmlFor="client_id"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Cliente
              </label>
              <select
                id="client_id"
                value={filters.client_id || ''}
                onChange={(e) =>
                  handleFilterChange(
                    'client_id',
                    e.target.value ? Number(e.target.value) : undefined
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os clientes</option>
                {/* TODO: Carregar clientes dinamicamente */}
                <option value={1}>João Silva</option>
                <option value={2}>Maria Santos</option>
              </select>
            </div>

            <div>
              <label
                htmlFor="technician_id"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Técnico
              </label>
              <select
                id="technician_id"
                value={filters.technician_id || ''}
                onChange={(e) =>
                  handleFilterChange(
                    'technician_id',
                    e.target.value ? Number(e.target.value) : undefined
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os técnicos</option>
                {/* TODO: Carregar técnicos dinamicamente */}
                <option value={1}>Carlos Técnico</option>
                <option value={2}>Ana Especialista</option>
              </select>
            </div>

            <div>
              <label
                htmlFor="date_from"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Data Inicial
              </label>
              <input
                type="date"
                id="date_from"
                value={filters.date_from || ''}
                onChange={(e) =>
                  handleFilterChange('date_from', e.target.value || undefined)
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            <div>
              <label
                htmlFor="date_to"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Data Final
              </label>
              <input
                type="date"
                id="date_to"
                value={filters.date_to || ''}
                onChange={(e) =>
                  handleFilterChange('date_to', e.target.value || undefined)
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            <div>
              <label
                htmlFor="vehicle_id"
                className="block text-sm font-medium text-gray-700 mb-1"
              >
                Veículo
              </label>
              <select
                id="vehicle_id"
                value={filters.vehicle_id || ''}
                onChange={(e) =>
                  handleFilterChange(
                    'vehicle_id',
                    e.target.value ? Number(e.target.value) : undefined
                  )
                }
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os veículos</option>
                {/* TODO: Carregar veículos dinamicamente */}
                <option value={1}>Toyota Corolla</option>
                <option value={2}>Honda Civic</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* Filtros ativos */}
      {Object.keys(filters).some(
        (key) =>
          key !== 'per_page' &&
          key !== 'page' &&
          filters[key as keyof ServiceFilters]
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
            {filters.status && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Status: {filters.status}
                <button
                  onClick={() => handleFilterChange('status', undefined)}
                  className="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500"
                >
                  ×
                </button>
              </span>
            )}
            {filters.date_from && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                De: {new Date(filters.date_from).toLocaleDateString('pt-BR')}
                <button
                  onClick={() => handleFilterChange('date_from', undefined)}
                  className="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-yellow-400 hover:bg-yellow-200 hover:text-yellow-500"
                >
                  ×
                </button>
              </span>
            )}
            {filters.date_to && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Até: {new Date(filters.date_to).toLocaleDateString('pt-BR')}
                <button
                  onClick={() => handleFilterChange('date_to', undefined)}
                  className="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-yellow-400 hover:bg-yellow-200 hover:text-yellow-500"
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
