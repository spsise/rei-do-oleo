import type { AxiosInstance, AxiosResponse } from 'axios';
import axios from 'axios';
import type { AuthResponse } from '../types/auth';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: 'http://localhost:8000/api',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    });

    // Request interceptor para adicionar token
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

    // Response interceptor para tratamento de erros
    this.api.interceptors.response.use(
      (response: AxiosResponse) => response,
      (error) => {
        if (error.response?.status === 401) {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // Métodos de autenticação
  async login(email: string, password: string): Promise<AuthResponse> {
    const response = await this.api.post<AuthResponse>('/login', {
      email,
      password,
    });
    return response.data;
  }

  async register(
    name: string,
    email: string,
    password: string,
    password_confirmation: string
  ): Promise<AuthResponse> {
    const response = await this.api.post<AuthResponse>('/register', {
      name,
      email,
      password,
      password_confirmation,
    });
    return response.data;
  }

  async logout(): Promise<void> {
    await this.api.post('/logout');
  }

  async getUser(): Promise<AuthResponse> {
    const response = await this.api.get<AuthResponse>('/user');
    return response.data;
  }

  // Método genérico para outras requisições
  get<T>(url: string) {
    return this.api.get<T>(url);
  }

  post<T>(url: string, data?: any) {
    return this.api.post<T>(url, data);
  }

  put<T>(url: string, data?: any) {
    return this.api.put<T>(url, data);
  }

  delete<T>(url: string) {
    return this.api.delete<T>(url);
  }
}

export const apiService = new ApiService();
export default apiService;
