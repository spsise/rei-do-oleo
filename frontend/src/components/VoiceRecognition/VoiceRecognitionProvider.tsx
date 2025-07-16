import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  VoiceRecognitionContext,
  type VoiceRecognitionContextType,
} from './VoiceRecognitionContext';

interface VoiceRecognitionProviderProps {
  children: React.ReactNode;
  language?: string;
  continuous?: boolean;
  interimResults?: boolean;
}

export const VoiceRecognitionProvider: React.FC<
  VoiceRecognitionProviderProps
> = ({
  children,
  language = 'pt-BR',
  continuous = true,
  interimResults = true,
}) => {
  const [isListening, setIsListening] = useState(false);
  const [transcript, setTranscript] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSupported, setIsSupported] = useState(false);

  const recognitionRef = useRef<SpeechRecognition | null>(null);

  useEffect(() => {
    // Check if browser supports speech recognition
    const SpeechRecognition =
      window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognition) {
      setIsSupported(true);
      recognitionRef.current = new SpeechRecognition();

      const recognition = recognitionRef.current;

      // Configure recognition settings
      recognition.continuous = continuous;
      recognition.interimResults = interimResults;
      recognition.lang = language;

      recognition.onstart = () => {
        setIsListening(true);
        setError(null);
      };

      recognition.onresult = (event) => {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
          const transcript = event.results[i][0].transcript;
          if (event.results[i].isFinal) {
            finalTranscript += transcript;
          } else {
            interimTranscript += transcript;
          }
        }

        setTranscript(finalTranscript + interimTranscript);
      };

      recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        setError(`Erro de reconhecimento: ${event.error}`);
        setIsListening(false);
      };

      recognition.onend = () => {
        setIsListening(false);
      };
    } else {
      setIsSupported(false);
      setError('Reconhecimento de voz não é suportado neste navegador');
    }

    return () => {
      if (recognitionRef.current) {
        recognitionRef.current.stop();
      }
    };
  }, [language, continuous, interimResults]);

  const startListening = useCallback(() => {
    if (recognitionRef.current && isSupported) {
      try {
        recognitionRef.current.start();
      } catch (error) {
        console.error('Error starting speech recognition:', error);
        setError('Erro ao iniciar reconhecimento de voz');
      }
    }
  }, [isSupported]);

  const stopListening = useCallback(() => {
    if (recognitionRef.current) {
      recognitionRef.current.stop();
    }
  }, []);

  const resetTranscript = useCallback(() => {
    setTranscript('');
    setError(null);
  }, []);

  const clearError = useCallback(() => {
    setError(null);
  }, []);

  const contextValue: VoiceRecognitionContextType = {
    isListening,
    transcript,
    isSupported,
    startListening,
    stopListening,
    resetTranscript,
    error,
    clearError,
  };

  return (
    <VoiceRecognitionContext.Provider value={contextValue}>
      {children}
    </VoiceRecognitionContext.Provider>
  );
};
