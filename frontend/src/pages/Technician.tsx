import {
  MagnifyingGlassIcon,
  MicrophoneIcon,
  PlusIcon,
  TruckIcon,
  UserIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React, { useRef, useState } from 'react';
import { toast } from 'react-hot-toast';
import { VoiceButton } from '../components/VoiceRecognition';
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

  // Referência para o botão de voz
  const voiceButtonRef = useRef<HTMLButtonElement>(null);

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

  // Função para abrir o modal de voz
  const handleVoiceButtonClick = () => {
    if (voiceButtonRef.current) {
      voiceButtonRef.current.click();
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

        {/* Search Section - Design Melhorado */}
        <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-8 mb-8">
          <div className="flex items-center gap-3 mb-6">
            <div className="p-2 bg-blue-100 rounded-lg">
              <MagnifyingGlassIcon className="h-6 w-6 text-blue-600" />
            </div>
            <h2 className="text-2xl font-semibold text-gray-900">
              Buscar Cliente
            </h2>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Tipo de Busca */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Busca
              </label>
              <select
                value={searchType}
                onChange={(e) =>
                  setSearchType(e.target.value as 'license_plate' | 'document')
                }
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-colors"
              >
                <option value="license_plate">Placa do Veículo</option>
                <option value="document">CPF/CNPJ</option>
              </select>
            </div>

            {/* Campo de Busca com Ícone de Microfone */}
            <div className="lg:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                {searchType === 'license_plate' ? 'Placa' : 'CPF/CNPJ'}
              </label>
              <div className="relative">
                <input
                  type="text"
                  value={searchValue}
                  onChange={(e) => setSearchValue(e.target.value)}
                  placeholder={
                    searchType === 'license_plate'
                      ? 'ABC1234'
                      : '123.456.789-00'
                  }
                  className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-colors"
                />
                <button
                  type="button"
                  onClick={handleVoiceButtonClick}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-blue-600 transition-colors"
                  title="Usar voz para busca"
                >
                  <MicrophoneIcon className="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>

          {/* Botões de Ação */}
          <div className="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
            <div className="flex items-center gap-3">
              <VoiceButton
                ref={voiceButtonRef}
                onResult={handleVoiceResult}
                size="sm"
                variant="outline"
                showText={false}
                className="hidden"
              />
            </div>
            <button
              onClick={handleSearch}
              disabled={isSearching || !searchValue.trim()}
              className="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors font-medium"
            >
              {isSearching ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                  Buscando...
                </>
              ) : (
                <>
                  <MagnifyingGlassIcon className="h-5 w-5" />
                  Buscar Cliente
                </>
              )}
            </button>
          </div>
        </div>

        {/* Search Results - Design Melhorado */}
        {searchResult && (
          <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-8 mb-8">
            <div className="flex justify-between items-start mb-8">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-green-100 rounded-lg">
                  <UserIcon className="h-6 w-6 text-green-600" />
                </div>
                <h2 className="text-2xl font-semibold text-gray-900">
                  Cliente Encontrado
                </h2>
              </div>
              <button
                onClick={() => {
                  setNewServiceData((prev) => ({
                    ...prev,
                    client_id: searchResult.client?.id || 0,
                  }));
                  setShowNewServiceForm(true);
                }}
                className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center gap-2 transition-colors font-medium"
              >
                <PlusIcon className="h-5 w-5" />
                Novo Serviço
              </button>
            </div>

            {/* Client Info - Design Melhorado */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
              <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 className="font-semibold text-gray-900 mb-4 text-lg">
                  Dados do Cliente
                </h3>
                <div className="space-y-3">
                  <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <span className="font-medium text-gray-700">Nome:</span>
                    <span className="text-gray-900">
                      {searchResult.client?.name || 'N/A'}
                    </span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <span className="font-medium text-gray-700">Email:</span>
                    <span className="text-gray-900">
                      {searchResult.client?.email || 'N/A'}
                    </span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <span className="font-medium text-gray-700">Telefone:</span>
                    <span className="text-gray-900">
                      {searchResult.client?.phone || 'N/A'}
                    </span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <span className="font-medium text-gray-700">
                      Documento:
                    </span>
                    <span className="text-gray-900">
                      {formatDocument(searchResult.client?.document || '')}
                    </span>
                  </div>
                </div>
              </div>

              <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <div className="flex items-center gap-3 mb-4">
                  <div className="p-2 bg-blue-100 rounded-lg">
                    <TruckIcon className="h-5 w-5 text-blue-600" />
                  </div>
                  <h3 className="font-semibold text-gray-900 text-lg">
                    Veículos ({searchResult.vehicles?.length || 0})
                  </h3>
                </div>
                <div className="space-y-4">
                  {searchResult.vehicles?.map((vehicle) => (
                    <div
                      key={vehicle.id}
                      className="bg-white rounded-lg p-4 border border-gray-200"
                    >
                      <div className="grid grid-cols-2 gap-3 text-sm">
                        <div>
                          <span className="font-medium text-gray-700">
                            Placa:
                          </span>
                          <p className="text-gray-900 font-mono">
                            {formatLicensePlate(vehicle.license_plate || '')}
                          </p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Modelo:
                          </span>
                          <p className="text-gray-900">
                            {vehicle.brand} {vehicle.model} {vehicle.year}
                          </p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Cor:
                          </span>
                          <p className="text-gray-900">{vehicle.color}</p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Quilometragem:
                          </span>
                          <p className="text-gray-900">{vehicle.mileage} km</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Recent Services - Design Melhorado */}
            <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
              <div className="flex items-center gap-3 mb-4">
                <div className="p-2 bg-yellow-100 rounded-lg">
                  <WrenchScrewdriverIcon className="h-5 w-5 text-yellow-600" />
                </div>
                <h3 className="font-semibold text-gray-900 text-lg">
                  Serviços Recentes
                </h3>
              </div>
              <div className="space-y-3">
                {searchResult.recent_services?.length ? (
                  searchResult.recent_services.map((service) => (
                    <div
                      key={service.id}
                      className="bg-white rounded-lg p-4 border border-gray-200"
                    >
                      <div className="grid grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
                        <div>
                          <span className="font-medium text-gray-700">
                            Nº Serviço:
                          </span>
                          <p className="text-gray-900 font-mono">
                            {service.service_number}
                          </p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Descrição:
                          </span>
                          <p className="text-gray-900">{service.description}</p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Status:
                          </span>
                          <p className="text-gray-900">{service.status}</p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Valor:
                          </span>
                          <p className="text-gray-900 font-mono">
                            R$ {service.total_amount?.toFixed(2)}
                          </p>
                        </div>
                        <div>
                          <span className="font-medium text-gray-700">
                            Data:
                          </span>
                          <p className="text-gray-900">{service.created_at}</p>
                        </div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <WrenchScrewdriverIcon className="h-12 w-12 text-gray-300 mx-auto mb-3" />
                    <p className="text-gray-500">
                      Nenhum serviço recente encontrado.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Novo Serviço Modal - Design Melhorado */}
        {showNewServiceForm && searchResult && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
              <div className="flex items-center gap-3 mb-6">
                <div className="p-2 bg-green-100 rounded-lg">
                  <PlusIcon className="h-6 w-6 text-green-600" />
                </div>
                <h3 className="text-2xl font-semibold text-gray-900">
                  Novo Serviço
                </h3>
              </div>

              <div className="space-y-6">
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
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
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
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white resize-none"
                    rows={3}
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
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
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
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
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
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
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white resize-none"
                    rows={3}
                  />
                </div>

                <div className="flex justify-end gap-3 pt-6 border-t border-gray-200">
                  <button
                    onClick={() => setShowNewServiceForm(false)}
                    className="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium"
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
                    className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors font-medium"
                  >
                    Salvar Serviço
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
};
