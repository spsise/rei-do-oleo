import { useCallback, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

interface UseUnsavedChangesOptions {
  isDirty: boolean;
  onSave?: () => Promise<void> | void;
  onDiscard?: () => void;
  message?: string;
  enabled?: boolean;
}

/**
 * Hook para gerenciar avisos de mudanças não salvas durante navegação
 */
export function useUnsavedChanges({
  isDirty,
  onSave,
  onDiscard,
  message = 'Você tem alterações não salvas. Deseja sair sem salvar?',
  enabled = true,
}: UseUnsavedChangesOptions) {
  const navigate = useNavigate();

  // Função para mostrar aviso antes de sair
  const showBeforeUnloadWarning = useCallback(
    (event: BeforeUnloadEvent) => {
      if (isDirty && enabled) {
        event.preventDefault();
        event.returnValue = message;
        return message;
      }
    },
    [isDirty, enabled, message]
  );

  // Função para confirmar navegação
  const confirmNavigation = useCallback(async () => {
    if (!isDirty || !enabled) {
      return true;
    }

    const confirmed = window.confirm(message);

    if (confirmed) {
      if (onSave) {
        try {
          await onSave();
          return true;
        } catch (error) {
          console.error('Erro ao salvar:', error);
          return false;
        }
      }
      return true;
    }

    return false;
  }, [isDirty, enabled, message, onSave]);

  // Função para navegar com confirmação
  const navigateWithConfirmation = useCallback(
    async (to: string, options?: { replace?: boolean; state?: any }) => {
      const canNavigate = await confirmNavigation();

      if (canNavigate) {
        navigate(to, options);
      }
    },
    [navigate, confirmNavigation]
  );

  // Função para descartar mudanças
  const discardChanges = useCallback(() => {
    if (onDiscard) {
      onDiscard();
    }
  }, [onDiscard]);

  // Configurar aviso antes de sair da página
  useEffect(() => {
    if (enabled) {
      window.addEventListener('beforeunload', showBeforeUnloadWarning);

      return () => {
        window.removeEventListener('beforeunload', showBeforeUnloadWarning);
      };
    }
  }, [showBeforeUnloadWarning, enabled]);

  // Configurar aviso para navegação interna (usando React Router)
  useEffect(() => {
    if (!enabled) return;

    const handleBeforeUnload = (event: BeforeUnloadEvent) => {
      showBeforeUnloadWarning(event);
    };

    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [isDirty, enabled, message, showBeforeUnloadWarning]);

  return {
    navigateWithConfirmation,
    discardChanges,
    confirmNavigation,
  };
}

/**
 * Hook para gerenciar estado de formulário com navegação segura
 */
export function useFormNavigation(
  isDirty: boolean,
  onSave?: () => Promise<void> | void,
  onDiscard?: () => void
) {
  const { navigateWithConfirmation, discardChanges } = useUnsavedChanges({
    isDirty,
    onSave,
    onDiscard,
  });

  const handleSaveAndNavigate = async (to: string) => {
    if (onSave) {
      try {
        await onSave();
        navigateWithConfirmation(to);
      } catch (error) {
        console.error('Erro ao salvar e navegar:', error);
      }
    } else {
      navigateWithConfirmation(to);
    }
  };

  const handleDiscardAndNavigate = (to: string) => {
    discardChanges();
    navigateWithConfirmation(to);
  };

  return {
    navigateWithConfirmation,
    handleSaveAndNavigate,
    handleDiscardAndNavigate,
    discardChanges,
  };
}
