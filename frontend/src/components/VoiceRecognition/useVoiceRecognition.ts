import { useContext } from 'react';
import { VoiceRecognitionContext } from './VoiceRecognitionContext';

export const useVoiceRecognition = () => {
  const context = useContext(VoiceRecognitionContext);
  if (!context) {
    throw new Error(
      'useVoiceRecognition must be used within a VoiceRecognitionProvider'
    );
  }
  return context;
};
