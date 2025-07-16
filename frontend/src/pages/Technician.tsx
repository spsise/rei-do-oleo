import {
  MagnifyingGlassIcon,
  PlusIcon,
  TruckIcon,
  UserIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { toast } from 'react-hot-toast';
import { LoadingSpinner } from '../components/ui/LoadingSpinner';
import { useAuth } from '../hooks/useAuth';
import {
  technicianService,
  type CreateServiceData,
  type TechnicianSearchResult,
} from '../services';

export const TechnicianPage: React.FC = () => {
  const { user } = useAuth();
  const [searchType, setSearchType] = useState<'license_plate' | 'document'>(
    'license_plate'
  );
  const [searchValue, setSearchValue] = useState('');
  const [isSearching, setIsSearching] = useState(false);
  const [searchResult, setSearchResult] =
    useState<TechnicianSearchResult | null>(null);
  const [showNewServiceForm, setShowNewServiceForm] = useState(false);
  const [newServiceData, setNewServiceData] = useState<CreateServiceData>({
    client_id: 0,
    vehicle_id: 0,
    description: '',
    estimated_duration: 60,
    priority: 'medium',
    notes: '',
  });

  const handleSearch = async () => {
    if (!searchValue.trim()) {
      toast.error('Digite um valor para busca');
      return;
    }

    setIsSearching(true);
    try {
      const response = await technicianService.searchClient({
        search_type: searchType,
        search_value: searchValue.trim(),
      });

      if (response.status === 'success' && response.data) {
        setSearchResult(response.data);
        toast.success('Cliente encontrado!');
      } else {
        toast.error(response.message || 'Cliente não encontrado');
        setSearchResult(null);
      }
    } catch (error) {
      console.error('Erro na busca:', error);
      toast.error('Erro ao buscar cliente');
    } finally {
      setIsSearching(false);
    }
  };

  const handleCreateService = async () => {
    if (!newServiceData.description.trim()) {
      toast.error('Digite uma descrição para o serviço');
      return;
    }

    try {
      const response = await technicianService.createService(newServiceData);

      if (response.status === 'success') {
        toast.success('Serviço criado com sucesso!');
        setShowNewServiceForm(false);
        setNewServiceData({
          client_id: 0,
          vehicle_id: 0,
          description: '',
          estimated_duration: 60,
          priority: 'medium',
          notes: '',
        });
        // Recarregar dados do cliente
        if (searchResult) {
          handleSearch();
        }
      } else {
        toast.error(response.message || 'Erro ao criar serviço');
      }
    } catch (error) {
      console.error('Erro ao criar serviço:', error);
      toast.error('Erro ao criar serviço');
    }
  };

  const formatDocument = (document: string) => {
    if (!document) return 'N/A';

    if (document.length === 11) {
      return document.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (document.length === 14) {
      return document.replace(
        /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
        '$1.$2.$3/$4-$5'
      );
    }
    return document;
  };

  const formatLicensePlate = (plate: string) => {
    if (!plate) return 'N/A';
    return plate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  };

  return (
    <div className="max-w-6xl mx-auto p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          Área de Serviços
        </h1>
        <p className="text-gray-600">
          Bem-vindo, {user?.name || 'Técnico'}! Busque clientes por placa ou
          documento para registrar serviços.
        </p>
      </div>

      {/* Search Section */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <MagnifyingGlassIcon className="h-6 w-6 mr-2 text-blue-600" />
          Buscar Cliente
        </h2>

        <div className="flex flex-col sm:flex-row gap-4 mb-4">
          <div className="flex-1">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Tipo de Busca
            </label>
            <select
              value={searchType}
              onChange={(e) =>
                setSearchType(e.target.value as 'license_plate' | 'document')
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="license_plate">Placa do Veículo</option>
              <option value="document">CPF/CNPJ</option>
            </select>
          </div>

          <div className="flex-1">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {searchType === 'license_plate' ? 'Placa' : 'CPF/CNPJ'}
            </label>
            <input
              type="text"
              value={searchValue}
              onChange={(e) => setSearchValue(e.target.value)}
              placeholder={
                searchType === 'license_plate' ? 'ABC1234' : '123.456.789-00'
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div className="flex items-end">
            <button
              onClick={handleSearch}
              disabled={isSearching}
              className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 flex items-center"
            >
              {isSearching ? (
                <LoadingSpinner size="sm" />
              ) : (
                <MagnifyingGlassIcon className="h-5 w-5 mr-2" />
              )}
              Buscar
            </button>
          </div>
        </div>
      </div>

      {/* Search Results */}
      {searchResult && (
        <div className="bg-white rounded-lg shadow-md p-6 mb-8">
          <div className="flex justify-between items-start mb-6">
            <h2 className="text-xl font-semibold text-gray-900 flex items-center">
              <UserIcon className="h-6 w-6 mr-2 text-green-600" />
              Cliente Encontrado
            </h2>
            <button
              onClick={() => {
                setNewServiceData((prev) => ({
                  ...prev,
                  client_id: searchResult.client?.id || 0,
                }));
                setShowNewServiceForm(true);
              }}
              className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center"
            >
              <PlusIcon className="h-5 w-5 mr-2" />
              Novo Serviço
            </button>
          </div>

          {/* Client Info */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div className="bg-gray-50 rounded-lg p-4">
              <h3 className="font-semibold text-gray-900 mb-3">
                Dados do Cliente
              </h3>
              <div className="space-y-2">
                <p>
                  <span className="font-medium">Nome:</span>{' '}
                  {searchResult.client?.name || 'N/A'}
                </p>
                <p>
                  <span className="font-medium">Email:</span>{' '}
                  {searchResult.client?.email || 'N/A'}
                </p>
                <p>
                  <span className="font-medium">Telefone:</span>{' '}
                  {searchResult.client?.phone || 'N/A'}
                </p>
                <p>
                  <span className="font-medium">Documento:</span>{' '}
                  {formatDocument(searchResult.client?.document || '')}
                </p>
              </div>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <h3 className="font-semibold text-gray-900 mb-3 flex items-center">
                <TruckIcon className="h-5 w-5 mr-2 text-blue-600" />
                Veículos ({searchResult.vehicles?.length || 0})
              </h3>
              <div className="space-y-3">
                {searchResult.vehicles?.map((vehicle) => (
                  <div
                    key={vehicle.id || `vehicle-${Math.random()}`}
                    className="border-l-4 border-blue-500 pl-3"
                  >
                    <p className="font-medium">
                      {vehicle.brand || 'N/A'} {vehicle.model || 'N/A'} (
                      {vehicle.year || 'N/A'})
                    </p>
                    <p className="text-sm text-gray-600">
                      Placa: {formatLicensePlate(vehicle.license_plate || '')} |
                      Cor: {vehicle.color || 'N/A'} | KM:{' '}
                      {(vehicle.mileage || 0).toLocaleString()}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Recent Services */}
          {searchResult.recent_services?.length > 0 && (
            <div className="bg-gray-50 rounded-lg p-4">
              <h3 className="font-semibold text-gray-900 mb-3 flex items-center">
                <WrenchScrewdriverIcon className="h-5 w-5 mr-2 text-orange-600" />
                Serviços Recentes
              </h3>
              <div className="space-y-3">
                {searchResult.recent_services?.map((service) => (
                  <div
                    key={service.id || `service-${Math.random()}`}
                    className="border-l-4 border-orange-500 pl-3"
                  >
                    <p className="font-medium">
                      {service.service_number}
                      {service.description ? ` - ${service.description}` : ''}
                    </p>
                    <p className="text-sm text-gray-600">
                      Status:{' '}
                      <span
                        className={`px-2 py-1 rounded-full text-xs ${
                          service.status === 'completed'
                            ? 'bg-green-100 text-green-800'
                            : service.status === 'in_progress'
                              ? 'bg-blue-100 text-blue-800'
                              : service.status === 'scheduled'
                                ? 'bg-yellow-100 text-yellow-800'
                                : service.status === 'cancelled'
                                  ? 'bg-red-100 text-red-800'
                                  : 'bg-gray-100 text-gray-800'
                        }`}
                      >
                        {service.status === 'completed'
                          ? 'Concluído'
                          : service.status === 'in_progress'
                            ? 'Em Andamento'
                            : service.status === 'scheduled'
                              ? 'Agendado'
                              : service.status === 'cancelled'
                                ? 'Cancelado'
                                : 'Pendente'}
                      </span>
                      | Valor: R${' '}
                      {(Number(service.total_amount) || 0).toFixed(2)} | Data:{' '}
                      {service.created_at
                        ? new Date(service.created_at).toLocaleDateString()
                        : ''}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {/* New Service Form Modal */}
      {showNewServiceForm && searchResult && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h3 className="text-xl font-semibold text-gray-900 mb-4">
              Novo Serviço
            </h3>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Veículo
                </label>
                <select
                  value={newServiceData.vehicle_id}
                  onChange={(e) =>
                    setNewServiceData((prev) => ({
                      ...prev,
                      vehicle_id: Number(e.target.value),
                    }))
                  }
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value={0}>Selecione um veículo</option>
                  {searchResult.vehicles?.map((vehicle) => (
                    <option
                      key={vehicle.id || `vehicle-${Math.random()}`}
                      value={vehicle.id || 0}
                    >
                      {vehicle.brand || 'N/A'} {vehicle.model || 'N/A'} -{' '}
                      {formatLicensePlate(vehicle.license_plate || '')}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Descrição do Serviço
                </label>
                <textarea
                  value={newServiceData.description}
                  onChange={(e) =>
                    setNewServiceData((prev) => ({
                      ...prev,
                      description: e.target.value,
                    }))
                  }
                  placeholder="Descreva o serviço a ser realizado..."
                  rows={3}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Duração Estimada (minutos)
                  </label>
                  <input
                    type="number"
                    value={newServiceData.estimated_duration}
                    onChange={(e) =>
                      setNewServiceData((prev) => ({
                        ...prev,
                        estimated_duration: Number(e.target.value),
                      }))
                    }
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Prioridade
                  </label>
                  <select
                    value={newServiceData.priority}
                    onChange={(e) =>
                      setNewServiceData((prev) => ({
                        ...prev,
                        priority: e.target.value as 'low' | 'medium' | 'high',
                      }))
                    }
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="low">Baixa</option>
                    <option value="medium">Média</option>
                    <option value="high">Alta</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Observações
                </label>
                <textarea
                  value={newServiceData.notes}
                  onChange={(e) =>
                    setNewServiceData((prev) => ({
                      ...prev,
                      notes: e.target.value,
                    }))
                  }
                  placeholder="Observações adicionais..."
                  rows={2}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div className="flex justify-end space-x-3 mt-6">
              <button
                onClick={() => setShowNewServiceForm(false)}
                className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Cancelar
              </button>
              <button
                onClick={handleCreateService}
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Criar Serviço
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
