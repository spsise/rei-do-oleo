import {
  MagnifyingGlassIcon,
  MicrophoneIcon,
} from '@heroicons/react/24/outline';
import React, { useRef } from 'react';
import { VoiceButton } from '../VoiceRecognition';

interface ClientSearchFormProps {
  searchType: 'license_plate' | 'document';
  searchValue: string;
  isSearching: boolean;
  onSearchTypeChange: (type: 'license_plate' | 'document') => void;
  onSearchValueChange: (value: string) => void;
  onSearch: () => void;
  onVoiceResult: (value: string) => void;
}

export const ClientSearchForm: React.FC<ClientSearchFormProps> = ({
  searchType,
  searchValue,
  isSearching,
  onSearchTypeChange,
  onSearchValueChange,
  onSearch,
  onVoiceResult,
}) => {
  const voiceButtonRef = useRef<HTMLButtonElement>(null);

  const handleVoiceButtonClick = () => {
    if (voiceButtonRef.current) {
      voiceButtonRef.current.click();
    }
  };

  return (
    <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-8 mb-8">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-2 bg-blue-100 rounded-lg">
          <MagnifyingGlassIcon className="h-6 w-6 text-blue-600" />
        </div>
        <h2 className="text-2xl font-semibold text-gray-900">Buscar Cliente</h2>
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
              onSearchTypeChange(e.target.value as 'license_plate' | 'document')
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
              onChange={(e) => onSearchValueChange(e.target.value)}
              placeholder={
                searchType === 'license_plate' ? 'ABC1234' : '123.456.789-00'
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
            onResult={onVoiceResult}
            size="sm"
            variant="outline"
            showText={false}
            className="hidden"
          />
        </div>
        <button
          onClick={onSearch}
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
  );
};
