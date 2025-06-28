import { AxiosError, AxiosResponse } from 'axios';
import { useAuthStore } from '../stores/authStore';
import { api } from './api';
import { csrfService } from './csrfService';

/**
 * Interceptor de requisição para garantir CSRF token
 */
api.interceptors.request.use(
  async (config) => {
    // Verificar se é uma requisição que precisa de CSRF
    const needsCsrf = ['post', 'put', 'patch', 'delete'].includes(
      config.method?.toLowerCase() || ''
    );

    if (needsCsrf && !csrfService.hasXsrfToken()) {
      console.log('🔐 CSRF token não encontrado, obtendo...');
      try {
        await csrfService.getCsrfCookie();
      } catch (error) {
        console.warn('⚠️ Falha ao obter CSRF token:', error);
      }
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

/**
 * Interceptor de resposta para lidar com erros CSRF
 */
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as any;

    // Erro 419 - CSRF Token Mismatch
    if (error.response?.status === 419 && !originalRequest._retryCSRF) {
      originalRequest._retryCSRF = true;

      console.log('🔄 Erro 419 - Renovando CSRF token...');

      try {
        await csrfService.refreshCsrfCookie();
        console.log('✅ CSRF token renovado, repetindo requisição...');
        return api.request(originalRequest);
      } catch (csrfError) {
        console.error('❌ Falha ao renovar CSRF token:', csrfError);
      }
    }

    // Erro 401 - Token expirado ou inválido
    if (error.response?.status === 401 && !originalRequest._retryAuth) {
      originalRequest._retryAuth = true;

      const isAuthRoute = originalRequest.url?.includes('/auth/');

      if (!isAuthRoute) {
        try {
          console.log('🔄 Token expirado, tentando refresh...');
          await useAuthStore.getState().refreshToken();

          // Reenviar requisição com novo token
          const { token, tokenType } = useAuthStore.getState();
          if (token) {
            const authType = tokenType || 'Bearer';
            originalRequest.headers.Authorization = `${authType} ${token}`;
            return api.request(originalRequest);
          }
        } catch (refreshError) {
          console.log('❌ Falha no refresh token, fazendo logout...');
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
    console.warn('⚠️ API não está acessível:', error);
    return false;
  }
};

/**
 * Utilitário para verificar se CSRF está funcionando
 */
export const checkCsrfStatus = async (): Promise<boolean> => {
  try {
    if (!csrfService.hasXsrfToken()) {
      await csrfService.getCsrfCookie();
    }
    return true;
  } catch (error) {
    console.warn('⚠️ CSRF não está funcionando:', error);
    return false;
  }
};
