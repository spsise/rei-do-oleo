import { createContext } from 'react';

export interface VoiceRecognitionContextType {
  isListening: boolean;
  transcript: string;
  isSupported: boolean;
  startListening: () => void;
  stopListening: () => void;
  resetTranscript: () => void;
  error: string | null;
  clearError: () => void;
}

export const VoiceRecognitionContext =
  createContext<VoiceRecognitionContextType | null>(null);
