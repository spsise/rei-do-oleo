import {
  PlusIcon,
  TruckIcon,
  UserIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { toast } from 'react-hot-toast';
import { VoiceButton, VoiceFeedback } from '../components/VoiceRecognition';
import { VoiceModal } from '../components/VoiceRecognition/VoiceModal';
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

  // Função para processar o resultado do modal de voz
  const handleVoiceResult = (value: string) => {
    // Aqui você pode implementar lógica para detectar se é placa ou documento
    // Por enquanto, vamos assumir que é uma placa se contém letras e números
    const hasLetters = /[A-Za-z]/.test(value);
    const hasNumbers = /\d/.test(value);

    if (hasLetters && hasNumbers) {
      // Provavelmente é uma placa
      setSearchType('license_plate');
    } else if (/\d{11,14}/.test(value.replace(/\D/g, ''))) {
      // Provavelmente é um documento (CPF/CNPJ)
      setSearchType('document');
    }

    setSearchValue(value);
    // Auto-executar busca após um pequeno delay
    setTimeout(() => {
      handleSearch();
    }, 500);
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
    <>
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
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
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
            <div className="flex flex-col items-end gap-2">
              <VoiceButton onResult={handleVoiceResult} />
              <button
                onClick={handleSearch}
                disabled={isSearching}
                className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 flex items-center"
              >
                Buscar
              </button>
            </div>
          </div>
          <VoiceFeedback />
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
                      key={vehicle.id}
                      className="border-b pb-2 last:border-b-0"
                    >
                      <div>
                        <span className="font-medium">Placa:</span>{' '}
                        {formatLicensePlate(vehicle.license_plate || '')}
                      </div>
                      <div>
                        <span className="font-medium">Modelo:</span>{' '}
                        {vehicle.brand} {vehicle.model} {vehicle.year}
                      </div>
                      <div>
                        <span className="font-medium">Cor:</span>{' '}
                        {vehicle.color}
                      </div>
                      <div>
                        <span className="font-medium">Quilometragem:</span>{' '}
                        {vehicle.mileage} km
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            {/* Recent Services */}
            <div className="bg-gray-50 rounded-lg p-4 mt-4">
              <h3 className="font-semibold text-gray-900 mb-3 flex items-center">
                <WrenchScrewdriverIcon className="h-5 w-5 mr-2 text-yellow-600" />
                Serviços Recentes
              </h3>
              <div className="space-y-2">
                {searchResult.recent_services?.length ? (
                  searchResult.recent_services.map((service) => (
                    <div
                      key={service.id}
                      className="border-b pb-2 last:border-b-0"
                    >
                      <div>
                        <span className="font-medium">Nº Serviço:</span>{' '}
                        {service.service_number}
                      </div>
                      <div>
                        <span className="font-medium">Descrição:</span>{' '}
                        {service.description}
                      </div>
                      <div>
                        <span className="font-medium">Status:</span>{' '}
                        {service.status}
                      </div>
                      <div>
                        <span className="font-medium">Valor:</span> R${' '}
                        {service.total_amount?.toFixed(2)}
                      </div>
                      <div>
                        <span className="font-medium">Data:</span>{' '}
                        {service.created_at}
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="text-gray-500">
                    Nenhum serviço recente encontrado.
                  </p>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Novo Serviço Modal (mantido igual) */}
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
                    Descrição
                  </label>
                  <textarea
                    value={newServiceData.description}
                    onChange={(e) =>
                      setNewServiceData((prev) => ({
                        ...prev,
                        description: e.target.value,
                      }))
                    }
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div className="flex gap-4">
                  <div className="flex-1">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Duração Estimada (min)
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
                  <div className="flex-1">
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
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div className="flex justify-end gap-2 mt-4">
                  <button
                    onClick={() => setShowNewServiceForm(false)}
                    className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400"
                  >
                    Cancelar
                  </button>
                  <button
                    onClick={async () => {
                      if (!newServiceData.vehicle_id) {
                        toast.error('Selecione um veículo');
                        return;
                      }
                      if (!newServiceData.description.trim()) {
                        toast.error('Digite uma descrição para o serviço');
                        return;
                      }
                      try {
                        const response =
                          await technicianService.createService(newServiceData);
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
                          toast.error(
                            response.message || 'Erro ao criar serviço'
                          );
                        }
                      } catch (error) {
                        console.error('Erro ao criar serviço:', error);
                        toast.error('Erro ao criar serviço');
                      }
                    }}
                    className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                  >
                    Salvar Serviço
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
      <VoiceModal />
    </>
  );
};
