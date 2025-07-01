import { AxiosError, AxiosResponse, InternalAxiosRequestConfig } from 'axios';
import { useAuthStore } from '../stores/authStore';
import { api } from './api';

/**
 * Interface para requisições com flags de retry
 */
interface ExtendedAxiosRequestConfig extends InternalAxiosRequestConfig {
  _retryAuth?: boolean;
}

/**
 * Interceptor de requisição - sem CSRF para rotas API pois não precisam
 */
api.interceptors.request.use(
  async (config) => {
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

/**
 * Interceptor de resposta para lidar apenas com autenticação (401)
 */
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as ExtendedAxiosRequestConfig;

    // Verificar se temos a config da requisição original
    if (!originalRequest) {
      return Promise.reject(error);
    }

    // Erro 419 não deveria acontecer em rotas API - loggar se acontecer
    if (error.response?.status === 419) {
      return Promise.reject(error);
    }

    // Erro 401 - Token expirado ou inválido
    if (error.response?.status === 401 && !originalRequest._retryAuth) {
      originalRequest._retryAuth = true;

      const isAuthRoute = originalRequest.url?.includes('/auth/');

      if (!isAuthRoute) {
        try {
          await useAuthStore.getState().refreshToken();

          // Reenviar requisição com novo token
          const { token, tokenType } = useAuthStore.getState();
          if (token) {
            const authType = tokenType || 'Bearer';
            originalRequest.headers.Authorization = `${authType} ${token}`;
            return api.request(originalRequest);
          }
        } catch (refreshError) {
          useAuthStore.getState().logout();

          // Redirecionar para login apenas se não estivermos em uma rota de auth
          if (
            typeof window !== 'undefined' &&
            !window.location.pathname.includes('/login')
          ) {
            window.location.href = '/login';
          }
        }
      }
    }

    return Promise.reject(error);
  }
);

/**
 * Utilitário para verificar status de conectividade com a API
 */
export const checkApiConnection = async (): Promise<boolean> => {
  try {
    await api.get('/api/health');
    return true;
  } catch (error) {
    return false;
  }
};
