import { useState } from 'react';
import { toast } from 'react-hot-toast';
import { technicianService } from '../services';
import {
  type CreateTechnicianServiceData,
  type TechnicianSearchResult,
} from '../types/technician';

export const useTechnician = () => {
  const [searchType, setSearchType] = useState<'license_plate' | 'document'>(
    'license_plate'
  );
  const [searchValue, setSearchValue] = useState('');
  const [isSearching, setIsSearching] = useState(false);
  const [searchResult, setSearchResult] =
    useState<TechnicianSearchResult | null>(null);
  const [showNewServiceForm, setShowNewServiceForm] = useState(false);
  const [isCreatingService, setIsCreatingService] = useState(false);
  const [newServiceData, setNewServiceData] =
    useState<CreateTechnicianServiceData>({
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

  const handleVoiceResult = (value: string) => {
    // Remover todos os espaços do texto retornado pela voz
    const cleanValue = value.replace(/\s/g, '');

    // Detectar se é placa ou documento
    const hasLetters = /[A-Za-z]/.test(cleanValue);
    const hasNumbers = /\d/.test(cleanValue);

    if (hasLetters && hasNumbers) {
      setSearchType('license_plate');
    } else if (/\d{11,14}/.test(cleanValue.replace(/\D/g, ''))) {
      setSearchType('document');
    }

    setSearchValue(cleanValue);
    // Auto-executar busca após um pequeno delay
    setTimeout(() => {
      handleSearch();
    }, 500);
  };

  const handleCreateNewService = () => {
    if (searchResult?.client?.id) {
      setNewServiceData((prev: CreateTechnicianServiceData) => ({
        ...prev,
        client_id: searchResult.client.id || 0,
      }));
      setShowNewServiceForm(true);
    }
  };

  const handleSubmitService = async () => {
    if (!newServiceData.vehicle_id) {
      toast.error('Selecione um veículo');
      return;
    }
    if (!newServiceData.description.trim()) {
      toast.error('Digite uma descrição para o serviço');
      return;
    }

    setIsCreatingService(true);
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
    } finally {
      setIsCreatingService(false);
    }
  };

  const resetSearch = () => {
    setSearchValue('');
    setSearchResult(null);
    setShowNewServiceForm(false);
  };

  return {
    // Estado
    searchType,
    searchValue,
    isSearching,
    searchResult,
    showNewServiceForm,
    isCreatingService,
    newServiceData,

    // Ações
    setSearchType,
    setSearchValue,
    setNewServiceData,
    setShowNewServiceForm,
    handleSearch,
    handleVoiceResult,
    handleCreateNewService,
    handleSubmitService,
    resetSearch,
  };
};
