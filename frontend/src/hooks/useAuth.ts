import { useCallback } from 'react';
import { useAuthStore } from '../stores/authStore';
import { LoginCredentials } from '../types';

/**
 * Hook personalizado para autenticação
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
   * Login simplificado
   */
  const login = useCallback(
    async (credentials: LoginCredentials) => {
      await storeLogin(credentials);
    },
    [storeLogin]
  );

  /**
   * Logout com limpeza de estado
   */
  const logout = useCallback(() => {
    storeLogout();
  }, [storeLogout]);

  return {
    // Estado
    user,
    loading,
    isAuthenticated: isAuthenticated(),

    // Ações
    login,
    logout,
    refreshToken,
  };
};
