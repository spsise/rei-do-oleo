import { createContext } from 'react';

interface VoiceModalContextType {
  isOpen: boolean;
  initialValue: string;
  openVoiceModal: (options: VoiceModalOptions) => void;
  closeVoiceModal: () => void;
  onConfirm: ((value: string) => void) | null;
}

interface VoiceModalOptions {
  initialValue?: string;
  onConfirm: (value: string) => void;
}

export const VoiceModalContext = createContext<
  VoiceModalContextType | undefined
>(undefined);

export type { VoiceModalContextType, VoiceModalOptions };
