import { useCallback, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import {
  parseVoiceCommand,
  type ParsedVoiceCommand,
} from '../../utils/voiceCommandParser';
import { useVoiceRecognition } from './useVoiceRecognition';

interface UseVoiceCommandProcessorOptions {
  onCommandRecognized?: (command: ParsedVoiceCommand) => void;
  onError?: (error: string) => void;
  autoExecute?: boolean;
  autoExecuteDelay?: number;
  showToast?: boolean;
}

export const useVoiceCommandProcessor = (
  options: UseVoiceCommandProcessorOptions = {}
) => {
  const {
    onCommandRecognized,
    onError,
    autoExecute = false,
    autoExecuteDelay = 1000,
    showToast = true,
  } = options;

  const { transcript, isListening, resetTranscript, error } =
    useVoiceRecognition();

  const processCommand = useCallback(
    (text: string) => {
      const result = parseVoiceCommand(text);

      if (result.success && result.data) {
        if (showToast) {
          toast.success(
            `Comando reconhecido: ${result.data.type === 'license_plate' ? 'Placa' : 'Documento'} ${result.data.value}`
          );
        }

        if (onCommandRecognized) {
          onCommandRecognized(result.data);
        }

        if (autoExecute && onCommandRecognized) {
          setTimeout(() => {
            onCommandRecognized(result.data!);
          }, autoExecuteDelay);
        }

        return result.data;
      } else if (result.error) {
        if (showToast) {
          toast.error(result.error);
        }
        onError?.(result.error);
      }

      return null;
    },
    [onCommandRecognized, onError, autoExecute, autoExecuteDelay, showToast]
  );

  // Processar comando quando o transcript mudar
  useEffect(() => {
    if (transcript && !isListening) {
      processCommand(transcript);
      resetTranscript();
    }
  }, [transcript, isListening, processCommand, resetTranscript]);

  // Mostrar erro de reconhecimento
  useEffect(() => {
    if (error) {
      if (showToast) {
        toast.error(error);
      }
      onError?.(error);
    }
  }, [error, onError, showToast]);

  return {
    processCommand,
    transcript,
    isListening,
    error,
  };
};
