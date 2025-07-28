import React from 'react';
import { LoadingSpinner } from './LoadingSpinner';

interface SmartButtonProps {
  isDirty: boolean;
  isSubmitting?: boolean;
  children: React.ReactNode;
  disabled?: boolean;
  className?: string;
  onClick?: () => void;
  variant?: 'primary' | 'secondary' | 'danger' | 'success';
  size?: 'sm' | 'md' | 'lg';
  showChangesIndicator?: boolean;
  changedFieldsCount?: number;
}

export const SmartButton: React.FC<SmartButtonProps> = ({
  isDirty,
  isSubmitting = false,
  children,
  disabled = false,
  className = '',
  onClick,
  variant = 'primary',
  size = 'md',
  showChangesIndicator = false,
  changedFieldsCount = 0,
}) => {
  const isDisabled = !isDirty || isSubmitting || disabled;

  // Classes base do botão
  const baseClasses =
    'inline-flex items-center justify-center font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

  // Classes de tamanho
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
  };

  // Classes de variante
  const variantClasses = {
    primary: {
      enabled: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
      disabled: 'bg-gray-300 text-gray-500',
    },
    secondary: {
      enabled: 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
      disabled: 'bg-gray-200 text-gray-400',
    },
    danger: {
      enabled: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
      disabled: 'bg-gray-300 text-gray-500',
    },
    success: {
      enabled:
        'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
      disabled: 'bg-gray-300 text-gray-500',
    },
  };

  // Determinar classes baseado no estado
  const stateClasses = isDirty
    ? variantClasses[variant].enabled
    : variantClasses[variant].disabled;

  const buttonClasses = `${baseClasses} ${sizeClasses[size]} ${stateClasses} ${className}`;

  // Indicador de mudanças
  const ChangesIndicator = () => {
    if (!showChangesIndicator || !isDirty) return null;

    return (
      <div className="flex items-center gap-2">
        <div className="flex items-center gap-1">
          <div className="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
          {changedFieldsCount > 0 && (
            <span className="text-xs bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full font-medium">
              {changedFieldsCount}
            </span>
          )}
        </div>
        {children}
      </div>
    );
  };

  // Conteúdo do botão
  const ButtonContent = () => {
    if (isSubmitting) {
      return (
        <div className="flex items-center gap-2">
          <LoadingSpinner size="sm" />
          <span>Salvando...</span>
        </div>
      );
    }

    if (showChangesIndicator) {
      return <ChangesIndicator />;
    }

    return children;
  };

  // Tooltip para botão desabilitado
  const getTooltipText = () => {
    if (isSubmitting) return 'Salvando...';
    if (disabled) return 'Botão desabilitado';
    if (!isDirty) return 'Nenhuma alteração detectada';
    return '';
  };

  return (
    <button
      onClick={onClick}
      disabled={isDisabled}
      className={buttonClasses}
      title={getTooltipText()}
    >
      <ButtonContent />
    </button>
  );
};

// Componente de botão com confirmação para mudanças não salvas
interface SmartButtonWithConfirmationProps extends SmartButtonProps {
  onConfirm?: () => void;
  confirmationMessage?: string;
}

export const SmartButtonWithConfirmation: React.FC<
  SmartButtonWithConfirmationProps
> = ({
  isDirty,
  onConfirm,
  confirmationMessage = 'Você tem alterações não salvas. Deseja continuar?',
  ...props
}) => {
  const handleClick = () => {
    if (isDirty && onConfirm) {
      const confirmed = window.confirm(confirmationMessage);
      if (confirmed) {
        onConfirm();
      }
    } else if (props.onClick) {
      props.onClick();
    }
  };

  return <SmartButton {...props} isDirty={isDirty} onClick={handleClick} />;
};

// Componente de grupo de botões para formulários
interface SmartButtonGroupProps {
  isDirty: boolean;
  isSubmitting?: boolean;
  onSave?: () => void;
  onCancel?: () => void;
  onReset?: () => void;
  saveText?: string;
  cancelText?: string;
  resetText?: string;
  showReset?: boolean;
  className?: string;
}

export const SmartButtonGroup: React.FC<SmartButtonGroupProps> = ({
  isDirty,
  isSubmitting = false,
  onSave,
  onCancel,
  onReset,
  saveText = 'Salvar',
  cancelText = 'Cancelar',
  resetText = 'Descartar',
  showReset = true,
  className = '',
}) => {
  return (
    <div className={`flex items-center gap-3 ${className}`}>
      <SmartButton
        isDirty={isDirty}
        isSubmitting={isSubmitting}
        onClick={onSave}
        variant="primary"
        showChangesIndicator={true}
      >
        {saveText}
      </SmartButton>

      {showReset && isDirty && onReset && (
        <SmartButton
          isDirty={true}
          onClick={onReset}
          variant="secondary"
          size="md"
        >
          {resetText}
        </SmartButton>
      )}

      {onCancel && (
        <SmartButton
          isDirty={true}
          onClick={onCancel}
          variant="secondary"
          size="md"
        >
          {cancelText}
        </SmartButton>
      )}
    </div>
  );
};
