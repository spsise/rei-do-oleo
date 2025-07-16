import { Dialog } from '@headlessui/react';
import {
  CheckIcon,
  MicrophoneIcon,
  SparklesIcon,
  StopIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useRef, useState } from 'react';
import { useVoiceModal } from './useVoiceModal';
import { useVoiceRecognition } from './useVoiceRecognition';
import './VoiceModal.css';

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
      className="fixed z-50 inset-0 flex items-center justify-center p-4"
    >
      {/* Backdrop com blur */}
      <div className="fixed inset-0 bg-black/50 voice-modal-backdrop transition-opacity" />

      {/* Modal com animação */}
      <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto z-10 voice-modal-content">
        {/* Header com gradiente animado */}
        <div className="relative bg-gradient-to-r from-blue-600 via-purple-600 to-blue-700 rounded-t-2xl p-6 text-white gradient-animate">
          <button
            className="absolute top-4 right-4 text-white/80 hover:text-white transition-colors duration-200 p-1 rounded-full hover:bg-white/10"
            onClick={handleCancel}
            aria-label="Fechar"
          >
            <XMarkIcon className="h-6 w-6" />
          </button>

          <div className="flex items-center gap-3">
            <div
              className={`p-3 rounded-full ${isListening ? 'bg-red-500/20 microphone-pulse' : 'bg-white/20'} transition-all duration-300`}
            >
              <MicrophoneIcon className="h-8 w-8" />
            </div>
            <div>
              <Dialog.Title className="text-xl font-bold">
                Reconhecimento de Voz
              </Dialog.Title>
              <p className="text-blue-100 text-sm mt-1">
                Fale ou edite o texto abaixo
              </p>
            </div>
          </div>
        </div>

        {/* Conteúdo */}
        <div className="p-6">
          {/* Input com design melhorado */}
          <div className="mb-6">
            <div className="relative">
              <input
                ref={inputRef}
                type="text"
                value={value}
                onChange={(e) => setValue(e.target.value)}
                className="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 text-lg transition-all duration-200 bg-gray-50 hover:bg-white voice-input-focus"
                placeholder="Fale ou digite aqui..."
                autoFocus
              />
              {isListening && (
                <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                  <div className="flex space-x-1">
                    <div className="w-1 h-4 bg-red-500 rounded-full voice-wave"></div>
                    <div className="w-1 h-4 bg-red-500 rounded-full voice-wave"></div>
                    <div className="w-1 h-4 bg-red-500 rounded-full voice-wave"></div>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Botão de microfone com design melhorado */}
          <div className="flex items-center justify-center mb-6">
            <button
              type="button"
              onClick={isListening ? stopListening : startListening}
              disabled={!isSupported}
              className={`relative group px-8 py-4 rounded-2xl flex items-center gap-3 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 voice-button-hover ${
                isListening
                  ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30 hover:shadow-red-500/40'
                  : isSupported
                    ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'
              }`}
            >
              {isListening ? (
                <>
                  <div className="relative recording-indicator">
                    <StopIcon className="h-6 w-6" />
                  </div>
                  <span className="font-semibold">Parar</span>
                </>
              ) : (
                <>
                  <MicrophoneIcon className="h-6 w-6" />
                  <span className="font-semibold">Iniciar</span>
                </>
              )}
            </button>
          </div>

          {/* Status e mensagens */}
          <div className="space-y-2 mb-6">
            {isListening && (
              <div className="flex items-center justify-center gap-2 text-blue-600 font-medium">
                <SparklesIcon className="h-5 w-5 animate-bounce" />
                <span>Ouvindo... Fale agora!</span>
              </div>
            )}
            {error && (
              <div className="flex items-center justify-center gap-2 text-red-600 text-sm bg-red-50 p-3 rounded-lg">
                <span>⚠️ {error}</span>
              </div>
            )}
            {!isSupported && (
              <div className="text-center text-gray-500 text-sm bg-gray-50 p-3 rounded-lg">
                Seu navegador não suporta reconhecimento de voz
              </div>
            )}
          </div>

          {/* Botões de ação */}
          <div className="flex gap-3">
            <button
              onClick={handleCancel}
              className="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors duration-200 font-medium voice-button-hover"
            >
              Cancelar
            </button>
            <button
              onClick={handleConfirm}
              className="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 font-medium flex items-center justify-center gap-2 shadow-lg shadow-green-500/30 hover:shadow-green-500/40 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none voice-button-hover"
              disabled={!value.trim()}
            >
              <CheckIcon className="h-5 w-5" />
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </Dialog>
  );
};
