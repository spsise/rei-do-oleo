import type { AxiosInstance, AxiosResponse } from 'axios';
import axios from 'axios';
import { API_CONFIG } from '../config/api';

// Configuração base da API
const API_BASE_URL = API_CONFIG.BASE_URL;

// Wrapper para métodos da API com tratamento de erro automático
async function apiCall<T>(
  apiMethod: () => Promise<AxiosResponse<ApiResponse<T>>>
): Promise<ApiResponse<T>> {
  try {
    const response = await apiMethod();
    return response.data;
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data) {
      return error.response.data;
    }
    throw error;
  }
}

// Interface para resposta da API
export interface ApiResponse<T = unknown> {
  status: 'success' | 'error';
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

// Interface para dados do usuário
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

// Interface para resposta de login
export interface LoginResponse {
  user: User;
  token: string;
  token_type: string;
}

// Interface para dados de login
export interface LoginData {
  email: string;
  password: string;
}

// Interface para dados de registro
export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    });

    // Interceptor para adicionar token de autenticação
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Interceptor para tratar respostas
    this.api.interceptors.response.use(
      (response: AxiosResponse) => {
        return response;
      },
      (error) => {
        if (error.response?.status === 401) {
          // Token expirado ou inválido
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // Métodos de autenticação
  async login(data: LoginData): Promise<ApiResponse<LoginResponse>> {
    const response = await apiCall(() =>
      this.api.post<ApiResponse<LoginResponse>>('/auth/login', data)
    );

    if (response.status === 'success' && response.data) {
      // Salvar token e dados do usuário
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }

    return response;
  }

  async register(data: RegisterData): Promise<ApiResponse<LoginResponse>> {
    const response = await apiCall(() =>
      this.api.post<ApiResponse<LoginResponse>>('/auth/register', data)
    );

    if (response.status === 'success' && response.data) {
      // Salvar token e dados do usuário
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }

    return response;
  }

  async logout(): Promise<ApiResponse> {
    try {
      await this.api.post('/auth/logout');
    } catch {
      // Ignora erros no logout
    } finally {
      // Limpar dados locais
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }

    return {
      status: 'success',
      message: 'Logout successful',
    };
  }

  async getProfile(): Promise<ApiResponse<User>> {
    return apiCall(() => this.api.get<ApiResponse<User>>('/auth/me'));
  }

  // Métodos para dashboard
  async getDashboardStats(): Promise<ApiResponse<unknown>> {
    return apiCall(() =>
      this.api.get<ApiResponse<unknown>>('/services/dashboard/stats')
    );
  }

  // Métodos para clientes
  async getClients(): Promise<ApiResponse<unknown[]>> {
    return apiCall(() => this.api.get<ApiResponse<unknown[]>>('/clients'));
  }

  // Métodos para veículos
  async getVehicles(): Promise<ApiResponse<unknown[]>> {
    return apiCall(() => this.api.get<ApiResponse<unknown[]>>('/vehicles'));
  }

  // Métodos para serviços
  async getServices(): Promise<ApiResponse<unknown[]>> {
    return apiCall(() => this.api.get<ApiResponse<unknown[]>>('/services'));
  }

  // Métodos para produtos
  async getProducts(): Promise<ApiResponse<unknown[]>> {
    return apiCall(() => this.api.get<ApiResponse<unknown[]>>('/products'));
  }

  // Health check
  async healthCheck(): Promise<ApiResponse> {
    return apiCall(() => this.api.get<ApiResponse>('/health'));
  }

  // Métodos utilitários
  isAuthenticated(): boolean {
    return !!localStorage.getItem('auth_token');
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  getUser(): User | null {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      try {
        return JSON.parse(userStr);
      } catch {
        return null;
      }
    }
    return null;
  }
}

export const apiService = new ApiService();
