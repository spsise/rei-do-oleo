import { apiCall, httpClient, type ApiResponse } from './http-client';

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

class AuthService {
  async login(data: LoginData): Promise<ApiResponse<LoginResponse>> {
    const response = await apiCall(() =>
      httpClient.instance.post<ApiResponse<LoginResponse>>('/auth/login', data)
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
      httpClient.instance.post<ApiResponse<LoginResponse>>(
        '/auth/register',
        data
      )
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
      await httpClient.instance.post('/auth/logout');
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
    return apiCall(() =>
      httpClient.instance.get<ApiResponse<User>>('/auth/me')
    );
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

export const authService = new AuthService();
