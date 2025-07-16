import {
  MagnifyingGlassIcon,
  MicrophoneIcon,
  StopIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import { toast } from 'react-hot-toast';
import { useSpeechRecognition } from '../../hooks/useSpeechRecognition';
import { parseVoiceCommand } from '../../utils/voiceCommandParser';

interface VoiceSearchInputProps {
  searchType: 'license_plate' | 'document';
  searchValue: string;
  onSearchTypeChange: (type: 'license_plate' | 'document') => void;
  onSearchValueChange: (value: string) => void;
  onSearch: () => void;
  isSearching: boolean;
  placeholder?: string;
}

export const VoiceSearchInput: React.FC<VoiceSearchInputProps> = ({
  searchType,
  searchValue,
  onSearchTypeChange,
  onSearchValueChange,
  onSearch,
  isSearching,
  placeholder,
}) => {
  const {
    isListening,
    transcript,
    isSupported,
    startListening,
    stopListening,
    resetTranscript,
    error,
  } = useSpeechRecognition();

  const [showVoiceFeedback, setShowVoiceFeedback] = useState(false);

  // Processar comando de voz quando o transcript mudar
  useEffect(() => {
    if (transcript && !isListening) {
      const result = parseVoiceCommand(transcript);

      if (result.success && result.data) {
        // Atualizar tipo de busca
        onSearchTypeChange(result.data.type);

        // Atualizar valor de busca
        onSearchValueChange(result.data.value);

        // Mostrar feedback visual
        setShowVoiceFeedback(true);
        toast.success(
          `Comando reconhecido: ${result.data.type === 'license_plate' ? 'Placa' : 'Documento'} ${result.data.value}`
        );

        // Auto-executar busca após um pequeno delay
        setTimeout(() => {
          onSearch();
          setShowVoiceFeedback(false);
        }, 1000);
      } else if (result.error) {
        toast.error(result.error);
      }

      // Limpar transcript
      resetTranscript();
    }
  }, [
    transcript,
    isListening,
    onSearchTypeChange,
    onSearchValueChange,
    onSearch,
    resetTranscript,
  ]);

  // Mostrar erro de reconhecimento de voz
  useEffect(() => {
    if (error) {
      toast.error(error);
    }
  }, [error]);

  const handleVoiceButtonClick = () => {
    if (isListening) {
      stopListening();
    } else {
      startListening();
      toast.success(
        'Ouvindo... Diga "Placa ABC1234" ou "Documento 12345678900"'
      );
    }
  };

  const getPlaceholder = () => {
    if (placeholder) return placeholder;

    if (searchType === 'license_plate') {
      return 'Digite a placa ou use o microfone';
    }
    return 'Digite o documento ou use o microfone';
  };

  const getVoiceButtonText = () => {
    if (!isSupported) return 'Voz não suportada';
    if (isListening) return 'Parar gravação';
    return 'Usar voz';
  };

  return (
    <div className="space-y-4">
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
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="license_plate">Placa do Veículo</option>
          <option value="document">CPF/CNPJ</option>
        </select>
      </div>

      {/* Campo de Busca com Voz */}
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">
          {searchType === 'license_plate' ? 'Placa' : 'CPF/CNPJ'}
        </label>
        <div className="flex gap-2">
          <div className="flex-1 relative">
            <input
              type="text"
              value={searchValue}
              onChange={(e) => onSearchValueChange(e.target.value)}
              placeholder={getPlaceholder()}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                showVoiceFeedback
                  ? 'border-green-500 bg-green-50'
                  : 'border-gray-300'
              }`}
            />
            {showVoiceFeedback && (
              <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                <div className="animate-pulse">
                  <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                </div>
              </div>
            )}
          </div>

          {/* Botão de Voz */}
          <button
            type="button"
            onClick={handleVoiceButtonClick}
            disabled={!isSupported || isSearching}
            className={`px-4 py-2 rounded-md flex items-center gap-2 transition-colors ${
              isListening
                ? 'bg-red-600 text-white hover:bg-red-700'
                : isSupported
                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                  : 'bg-gray-400 text-white cursor-not-allowed'
            } focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50`}
            title={getVoiceButtonText()}
          >
            {isListening ? (
              <StopIcon className="h-5 w-5" />
            ) : (
              <MicrophoneIcon className="h-5 w-5" />
            )}
            <span className="hidden sm:inline">
              {isListening ? 'Parar' : 'Voz'}
            </span>
          </button>

          {/* Botão de Busca */}
          <button
            onClick={onSearch}
            disabled={isSearching || !searchValue.trim()}
            className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 flex items-center"
          >
            <MagnifyingGlassIcon className="h-5 w-5 mr-2" />
            Buscar
          </button>
        </div>
      </div>

      {/* Feedback de Voz */}
      {isListening && (
        <div className="bg-blue-50 border border-blue-200 rounded-md p-3">
          <div className="flex items-center gap-2">
            <div className="animate-pulse">
              <div className="w-3 h-3 bg-red-500 rounded-full"></div>
            </div>
            <span className="text-sm text-blue-800 font-medium">
              Ouvindo... Diga o comando
            </span>
          </div>
          <p className="text-xs text-blue-600 mt-1">
            Exemplos: "Placa ABC1234", "Documento 12345678900", "CPF
            12345678900"
          </p>
        </div>
      )}

      {/* Instruções de Voz */}
      {!isListening && isSupported && (
        <div className="bg-gray-50 border border-gray-200 rounded-md p-3">
          <div className="flex items-start gap-2">
            <MicrophoneIcon className="h-5 w-5 text-gray-500 mt-0.5" />
            <div>
              <p className="text-sm text-gray-700 font-medium">
                Comandos de voz suportados:
              </p>
              <ul className="text-xs text-gray-600 mt-1 space-y-1">
                <li>• "Placa ABC1234"</li>
                <li>• "Documento 12345678900"</li>
                <li>• "CPF 12345678900"</li>
                <li>• "CNPJ 12345678000100"</li>
              </ul>
            </div>
          </div>
        </div>
      )}

      {/* Aviso de não suporte */}
      {!isSupported && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-md p-3">
          <p className="text-sm text-yellow-800">
            ⚠️ Reconhecimento de voz não é suportado neste navegador. Use
            Chrome, Edge ou Safari para esta funcionalidade.
          </p>
        </div>
      )}
    </div>
  );
};
