import { MicrophoneIcon } from '@heroicons/react/24/outline';
import { forwardRef } from 'react';
import { useVoiceModal } from './useVoiceModal';

interface VoiceButtonProps {
  onResult?: (value: string) => void;
  initialValue?: string;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'primary' | 'secondary' | 'outline';
  showText?: boolean;
  className?: string;
}

export const VoiceButton = forwardRef<HTMLButtonElement, VoiceButtonProps>(
  (
    {
      onResult,
      initialValue = '',
      size = 'md',
      variant = 'primary',
      showText = true,
      className = '',
    },
    ref
  ) => {
    const { openVoiceModal } = useVoiceModal();

    const handleClick = () => {
      openVoiceModal({
        initialValue,
        onConfirm: (value: string) => {
          if (onResult) onResult(value);
        },
      });
    };

    const getSizeClasses = () => {
      switch (size) {
        case 'sm':
          return 'px-3 py-1.5 text-sm';
        case 'lg':
          return 'px-6 py-3 text-lg';
        default:
          return 'px-4 py-2 text-base';
      }
    };

    const getVariantClasses = () => {
      const baseClasses =
        'rounded-md flex items-center gap-2 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50';
      switch (variant) {
        case 'secondary':
          return `${baseClasses} bg-gray-600 text-white hover:bg-gray-700`;
        case 'outline':
          return `${baseClasses} border border-gray-300 bg-white text-gray-700 hover:bg-gray-50`;
        default:
          return `${baseClasses} bg-blue-600 text-white hover:bg-blue-700`;
      }
    };

    const getIconSize = () => {
      switch (size) {
        case 'sm':
          return 'h-4 w-4';
        case 'lg':
          return 'h-6 w-6';
        default:
          return 'h-5 w-5';
      }
    };

    return (
      <button
        ref={ref}
        type="button"
        onClick={handleClick}
        className={`${getSizeClasses()} ${getVariantClasses()} ${className}`}
        title="Usar voz"
      >
        <MicrophoneIcon className={getIconSize()} />
        {showText && <span className="hidden sm:inline">Voz</span>}
      </button>
    );
  }
);

VoiceButton.displayName = 'VoiceButton';
