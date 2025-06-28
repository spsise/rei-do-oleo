import { LoginCredentials, LoginResponse, RefreshResponse } from '../types';
import { api } from './api';
import { csrfService } from './csrfService';

export const authService = {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    try {
      // PASSO 1: Obter CSRF cookie antes do login
      await csrfService.getCsrfCookie();

      // PASSO 2: Fazer login com o cookie CSRF
      const response = await api.post<LoginResponse>(
        '/api/v1/auth/login',
        credentials
      );
      return response.data;
    } catch (error: unknown) {
      // Se erro 419 (CSRF token mismatch), tentar renovar cookie
      const axiosError = error as { response?: { status?: number } };
      if (axiosError.response?.status === 419) {
        console.log('🔄 Erro 419 detectado, renovando CSRF cookie...');
        await csrfService.refreshCsrfCookie();

        // Tentar novamente após renovar cookie
        const response = await api.post<LoginResponse>(
          '/api/v1/auth/login',
          credentials
        );
        return response.data;
      }
      throw error;
    }
  },

  async logout(): Promise<void> {
    try {
      await api.post('/api/v1/auth/logout');
    } catch (error) {
      // Ignore errors on logout
      console.warn('Erro ao fazer logout:', error);
    }
  },

  async refreshToken(): Promise<RefreshResponse> {
    const response = await api.post<RefreshResponse>('/api/v1/auth/refresh');
    return response.data;
  },

  async forgotPassword(email: string): Promise<void> {
    await api.post('/api/v1/auth/forgot-password', { email });
  },
};
