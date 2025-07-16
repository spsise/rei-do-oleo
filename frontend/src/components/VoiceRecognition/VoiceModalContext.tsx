import type { ReactNode } from 'react';
import { useState } from 'react';
import { VoiceModalContext, type VoiceModalOptions } from './VoiceContext';

export const VoiceModalProvider: React.FC<{ children: ReactNode }> = ({
  children,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [initialValue, setInitialValue] = useState('');
  const [onConfirm, setOnConfirm] = useState<((value: string) => void) | null>(
    null
  );

  const openVoiceModal = (options: VoiceModalOptions) => {
    setInitialValue(options.initialValue || '');
    setOnConfirm(() => options.onConfirm);
    setIsOpen(true);
  };

  const closeVoiceModal = () => {
    setIsOpen(false);
    setOnConfirm(null);
    setInitialValue('');
  };

  return (
    <VoiceModalContext.Provider
      value={{
        isOpen,
        initialValue,
        openVoiceModal,
        closeVoiceModal,
        onConfirm,
      }}
    >
      {children}
    </VoiceModalContext.Provider>
  );
};
