import { useCallback } from 'react';
import { csrfService } from '../services/csrfService';
import { useAuthStore } from '../stores/authStore';
import { LoginCredentials } from '../types';

/**
 * Hook personalizado para autenticação com suporte a CSRF
 */
export const useAuth = () => {
  const {
    user,
    loading,
    login: storeLogin,
    logout: storeLogout,
    isAuthenticated,
    refreshToken,
  } = useAuthStore();

  /**
   * Login com fluxo CSRF automático
   */
  const login = useCallback(
    async (credentials: LoginCredentials) => {
      try {
        await storeLogin(credentials);
      } catch (error) {
        // Se erro de CSRF, tentar obter cookie novamente
        const axiosError = error as {
          response?: { status?: number; data?: { message?: string } };
        };

        if (axiosError.response?.status === 419) {
          console.log('🔄 Renovando CSRF cookie e tentando novamente...');
          await csrfService.refreshCsrfCookie();
          await storeLogin(credentials);
        } else {
          throw error;
        }
      }
    },
    [storeLogin]
  );

  /**
   * Logout com limpeza de estado
   */
  const logout = useCallback(() => {
    storeLogout();
  }, [storeLogout]);

  /**
   * Inicializar CSRF cookie (útil na inicialização da app)
   */
  const initializeCsrf = useCallback(async () => {
    try {
      if (!csrfService.hasXsrfToken()) {
        console.log('🔐 Inicializando CSRF cookie...');
        await csrfService.getCsrfCookie();
      }
    } catch (error) {
      console.warn('⚠️ Falha ao inicializar CSRF cookie:', error);
    }
  }, []);

  /**
   * Verificar se tem CSRF token
   */
  const hasValidCsrf = useCallback(() => {
    return csrfService.hasXsrfToken();
  }, []);

  return {
    // Estado
    user,
    loading,
    isAuthenticated: isAuthenticated(),
    hasValidCsrf: hasValidCsrf(),

    // Ações
    login,
    logout,
    refreshToken,
    initializeCsrf,

    // Utilitários CSRF
    refreshCsrf: csrfService.refreshCsrfCookie,
  };
};
