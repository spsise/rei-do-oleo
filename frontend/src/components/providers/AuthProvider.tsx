import React, { createContext, useContext, useEffect, useState } from 'react';
import { useAuth } from '../../hooks/useAuth';

interface AuthContextType {
  csrfInitialized: boolean;
  initializingCsrf: boolean;
}

const AuthContext = createContext<AuthContextType>({
  csrfInitialized: false,
  initializingCsrf: false,
});

export const useAuthContext = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuthContext must be used within AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: React.ReactNode;
}

/**
 * Provider para gerenciar autenticação e inicialização do CSRF
 */
export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const { initializeCsrf, hasValidCsrf } = useAuth();
  const [csrfInitialized, setCsrfInitialized] = useState(false);
  const [initializingCsrf, setInitializingCsrf] = useState(false);

  useEffect(() => {
    const initCsrf = async () => {
      // Se já tem CSRF válido, marcar como inicializado
      if (hasValidCsrf) {
        setCsrfInitialized(true);
        return;
      }

      setInitializingCsrf(true);
      try {
        await initializeCsrf();
        setCsrfInitialized(true);
        console.log('✅ CSRF inicializado com sucesso');
      } catch (error) {
        console.warn('⚠️ Falha ao inicializar CSRF:', error);
        // Continuar mesmo se falhar - pode funcionar sem CSRF em alguns casos
        setCsrfInitialized(true);
      } finally {
        setInitializingCsrf(false);
      }
    };

    initCsrf();
  }, [initializeCsrf, hasValidCsrf]);

  const value: AuthContextType = {
    csrfInitialized,
    initializingCsrf,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
