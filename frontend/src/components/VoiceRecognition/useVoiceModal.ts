import { useContext } from 'react';
import { VoiceModalContext } from './VoiceContext';

export const useVoiceModal = () => {
  const ctx = useContext(VoiceModalContext);
  if (!ctx)
    throw new Error('useVoiceModal must be used within a VoiceModalProvider');
  return ctx;
};
