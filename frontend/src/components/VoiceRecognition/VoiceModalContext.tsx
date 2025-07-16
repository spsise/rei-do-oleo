import type { ReactNode } from 'react';
import { useState } from 'react';
import { VoiceModalContext, type VoiceModalOptions } from './VoiceContext';
import { VoiceModal } from './VoiceModal';

export const VoiceModalProvider: React.FC<{ children: ReactNode }> = ({
  children,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [initialValue, setInitialValue] = useState('');
  const [autoStart, setAutoStart] = useState(false);
  const [onConfirm, setOnConfirm] = useState<((value: string) => void) | null>(
    null
  );

  const openVoiceModal = (options: VoiceModalOptions) => {
    setInitialValue(options.initialValue || '');
    setAutoStart(options.autoStart || false);
    setOnConfirm(() => options.onConfirm);
    setIsOpen(true);
  };

  const closeVoiceModal = () => {
    setIsOpen(false);
    setOnConfirm(null);
    setInitialValue('');
    setAutoStart(false);
  };

  return (
    <VoiceModalContext.Provider
      value={{
        isOpen,
        initialValue,
        autoStart,
        openVoiceModal,
        closeVoiceModal,
        onConfirm,
      }}
    >
      {children}
      <VoiceModal autoStart={autoStart} />
    </VoiceModalContext.Provider>
  );
};
