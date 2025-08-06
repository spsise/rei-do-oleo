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
    <div className="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
      {/* Header simplificado */}
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg">
          <MagnifyingGlassIcon className="h-5 w-5 text-white" />
        </div>
        <div>
          <h2 className="text-lg font-semibold text-gray-900">
            Buscar Cliente
          </h2>
          <p className="text-gray-600 text-xs">
            Por placa ou documento
          </p>
        </div>
      </div>

      {/* Form Grid - Responsivo */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        {/* Tipo de Busca */}
        <div className="space-y-1">
          <label className="block text-xs font-medium text-gray-700">
            Tipo
          </label>
          <select
            value={searchType}
            onChange={(e) =>
              onSearchTypeChange(
                e.target.value as 'license_plate' | 'document'
              )
            }
            className="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all duration-200"
          >
            <option value="license_plate">🚗 Placa</option>
            <option value="document">📄 CPF/CNPJ</option>
          </select>
        </div>

        {/* Campo de Busca */}
        <div className="sm:col-span-2 space-y-1">
          <label className="block text-xs font-medium text-gray-700">
            {searchType === 'license_plate' ? 'Placa' : 'CPF/CNPJ'}
          </label>
          <div className="relative">
            <input
              type="text"
              value={searchValue}
              onChange={(e) => onSearchValueChange(e.target.value)}
              placeholder={
                searchType === 'license_plate'
                  ? 'Digite a placa (ex: ABC1234)'
                  : 'Digite o CPF/CNPJ'
              }
              className="w-full px-3 py-2.5 pr-10 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all duration-200"
            />
            <button
              type="button"
              onClick={handleVoiceButtonClick}
              className="absolute right-2 top-1/2 transform -translate-y-1/2 p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200"
              title="Busca por voz"
            >
              <MicrophoneIcon className="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      {/* Botão de Busca */}
      <div className="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
        <VoiceButton
          ref={voiceButtonRef}
          onResult={onVoiceResult}
          autoStart={true}
          size="sm"
          variant="outline"
          showText={false}
          className="hidden"
        />
        
        <button
          onClick={onSearch}
          disabled={isSearching || !searchValue.trim()}
          className="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-all duration-200 font-medium"
        >
          {isSearching ? (
            <>
              <div className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
              <span>Buscando...</span>
            </>
          ) : (
            <>
              <MagnifyingGlassIcon className="h-4 w-4" />
              <span>Buscar</span>
            </>
          )}
        </button>
      </div>
    </div>
  );
};
