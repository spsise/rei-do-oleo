import { MicrophoneIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { useVoiceRecognition } from './useVoiceRecognition';

interface VoiceFeedbackProps {
  showInstructions?: boolean;
  showTranscript?: boolean;
  className?: string;
}

export const VoiceFeedback: React.FC<VoiceFeedbackProps> = ({
  showInstructions = true,
  showTranscript = true,
  className = '',
}) => {
  const { isListening, transcript, isSupported, error } = useVoiceRecognition();

  if (!isSupported) {
    return (
      <div
        className={`bg-yellow-50 border border-yellow-200 rounded-md p-3 ${className}`}
      >
        <p className="text-sm text-yellow-800">
          ⚠️ Reconhecimento de voz não é suportado neste navegador. Use Chrome,
          Edge ou Safari para esta funcionalidade.
        </p>
      </div>
    );
  }

  if (error) {
    return (
      <div
        className={`bg-red-50 border border-red-200 rounded-md p-3 ${className}`}
      >
        <p className="text-sm text-red-800">❌ {error}</p>
      </div>
    );
  }

  if (isListening) {
    return (
      <div
        className={`bg-blue-50 border border-blue-200 rounded-md p-3 ${className}`}
      >
        <div className="flex items-center gap-2">
          <div className="animate-pulse">
            <div className="w-3 h-3 bg-red-500 rounded-full"></div>
          </div>
          <span className="text-sm text-blue-800 font-medium">
            Ouvindo... Diga o comando
          </span>
        </div>
        {showTranscript && transcript && (
          <p className="text-xs text-blue-600 mt-2 italic">"{transcript}"</p>
        )}
      </div>
    );
  }

  if (showInstructions) {
    return (
      <div
        className={`bg-gray-50 border border-gray-200 rounded-md p-3 ${className}`}
      >
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
    );
  }

  return null;
};
