import { Dialog } from '@headlessui/react';
import {
  CheckIcon,
  MicrophoneIcon,
  StopIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useRef, useState } from 'react';
import { useVoiceModal } from './useVoiceModal';
import { useVoiceRecognition } from './useVoiceRecognition';

export const VoiceModal: React.FC = () => {
  const { isOpen, initialValue, closeVoiceModal, onConfirm } = useVoiceModal();
  const {
    isListening,
    transcript,
    startListening,
    stopListening,
    isSupported,
    error,
    resetTranscript,
  } = useVoiceRecognition();
  const [value, setValue] = useState(initialValue);
  const inputRef = useRef<HTMLInputElement>(null);

  // Atualiza valor inicial ao abrir
  useEffect(() => {
    if (isOpen) {
      setValue(initialValue || '');
      resetTranscript();
    }
  }, [isOpen, initialValue, resetTranscript]);

  // Atualiza valor conforme fala
  useEffect(() => {
    if (isListening && transcript) {
      setValue(transcript);
    }
  }, [transcript, isListening]);

  // Foca no input ao abrir
  useEffect(() => {
    if (isOpen && inputRef.current) {
      setTimeout(() => inputRef.current?.focus(), 200);
    }
  }, [isOpen]);

  const handleConfirm = () => {
    if (onConfirm) onConfirm(value.trim());
    closeVoiceModal();
  };

  const handleCancel = () => {
    closeVoiceModal();
  };

  return (
    <Dialog
      open={isOpen}
      onClose={handleCancel}
      className="fixed z-50 inset-0 flex items-center justify-center"
    >
      <div className="fixed inset-0 bg-black bg-opacity-40" />
      <div className="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-auto z-10">
        <button
          className="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
          onClick={handleCancel}
          aria-label="Fechar"
        >
          <XMarkIcon className="h-6 w-6" />
        </button>
        <Dialog.Title className="text-lg font-semibold mb-2 flex items-center gap-2">
          <MicrophoneIcon className="h-6 w-6 text-blue-600" />
          Fale ou edite o texto
        </Dialog.Title>
        <div className="mb-4">
          <input
            ref={inputRef}
            type="text"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
            placeholder="Fale ou digite aqui..."
            autoFocus
          />
        </div>
        <div className="flex items-center gap-2 mb-4">
          <button
            type="button"
            onClick={isListening ? stopListening : startListening}
            disabled={!isSupported}
            className={`px-4 py-2 rounded-md flex items-center gap-2 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              isListening
                ? 'bg-red-600 text-white hover:bg-red-700'
                : isSupported
                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                  : 'bg-gray-400 text-white cursor-not-allowed'
            }`}
          >
            {isListening ? (
              <StopIcon className="h-5 w-5" />
            ) : (
              <MicrophoneIcon className="h-5 w-5" />
            )}
            {isListening ? 'Parar' : 'Falar'}
          </button>
          {isListening && (
            <span className="text-blue-700 animate-pulse">Ouvindo...</span>
          )}
          {error && <span className="text-red-600 text-sm">{error}</span>}
        </div>
        <div className="flex justify-end gap-2">
          <button
            onClick={handleCancel}
            className="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
          >
            Cancelar
          </button>
          <button
            onClick={handleConfirm}
            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center gap-2"
            disabled={!value.trim()}
          >
            <CheckIcon className="h-5 w-5" />
            Confirmar
          </button>
        </div>
      </div>
    </Dialog>
  );
};
