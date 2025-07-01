import { LoginCredentials, LoginResponse, RefreshResponse } from '../types';
import { api } from './api';

export const authService = {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await api.post<LoginResponse>(
      '/api/v1/auth/login',
      credentials
    );
    return response.data;
  },

  async logout(): Promise<void> {
    try {
      await api.post('/api/v1/auth/logout');
    } catch (error) {
      // Ignore errors on logout
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
