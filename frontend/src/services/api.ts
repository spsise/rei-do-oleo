import axios from 'axios';
import { useAuthStore } from '../stores/authStore';

const API_BASE_URL =
  import.meta.env.VITE_API_URL ||
  (import.meta.env.DEV ? '' : 'http://localhost:8000');

export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Instância separada para CSRF cookie (sem Bearer token)
export const csrfApi = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
});

// Interceptor para adicionar token nas requisições
api.interceptors.request.use((config) => {
  const { token, tokenType } = useAuthStore.getState();
  if (token) {
    const authType = tokenType || 'Bearer';
    config.headers.Authorization = `${authType} ${token}`;
  }
  return config;
});

// Interceptor para tratamento de erros
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // Não tentar refresh token para rotas de autenticação
    const isAuthRoute = originalRequest.url?.includes('/auth/');

    if (
      error.response?.status === 401 &&
      !isAuthRoute &&
      !originalRequest._retry
    ) {
      originalRequest._retry = true;

      try {
        await useAuthStore.getState().refreshToken();
        // Retry da requisição original
        const { token, tokenType } = useAuthStore.getState();
        if (token) {
          const authType = tokenType || 'Bearer';
          originalRequest.headers.Authorization = `${authType} ${token}`;
        }
        return api.request(originalRequest);
      } catch (refreshError) {
        useAuthStore.getState().logout();
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);
