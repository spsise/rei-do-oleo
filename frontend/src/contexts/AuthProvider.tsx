import type { ReactNode } from 'react';
import React, { useEffect, useState } from 'react';
import type { ApiResponse, LoginData, RegisterData, User } from '../services';
import { authService } from '../services';
import { AuthContext } from './AuthContext';

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Verificar autenticação na inicialização
  useEffect(() => {
    const checkAuth = async () => {
      try {
        if (authService.isAuthenticated()) {
          const userData = authService.getUser();
          if (userData) {
            setUser(userData);
          } else {
            // Token existe mas usuário não, buscar perfil
            await refreshUser();
          }
        }
      } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        // Limpar dados inválidos
        authService.logout();
      } finally {
        setIsLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (data: LoginData): Promise<ApiResponse> => {
    try {
      const response = await authService.login(data);

      if (response.status === 'success' && response.data) {
        setUser(response.data.user);
      }

      return response;
    } catch (error) {
      console.error('Erro no login:', error);
      throw error;
    }
  };

  const register = async (data: RegisterData): Promise<ApiResponse> => {
    try {
      const response = await authService.register(data);

      if (response.status === 'success' && response.data) {
        setUser(response.data.user);
      }

      return response;
    } catch (error) {
      console.error('Erro no registro:', error);
      throw error;
    }
  };

  const logout = async (): Promise<void> => {
    try {
      await authService.logout();
      setUser(null);
    } catch (error) {
      console.error('Erro no logout:', error);
      // Força limpeza mesmo com erro
      setUser(null);
    }
  };

  const refreshUser = async (): Promise<void> => {
    try {
      const response = await authService.getProfile();

      if (response.status === 'success' && response.data) {
        setUser(response.data);
        localStorage.setItem('user', JSON.stringify(response.data));
      } else {
        // Perfil inválido, fazer logout
        await logout();
      }
    } catch (error) {
      console.error('Erro ao atualizar perfil:', error);
      await logout();
    }
  };

  const value = {
    user,
    isAuthenticated: !!user,
    isLoading,
    login,
    register,
    logout,
    refreshUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
