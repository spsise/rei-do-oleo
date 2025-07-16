import { StorageManager } from '../utils/storage';
import { apiCall, httpClient, type ApiResponse } from './http-client';

// Interface para dados do usuário
export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  active: boolean;
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
  rememberMe?: boolean;
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
      // Use StorageManager to handle storage
      StorageManager.setAuthData(
        response.data.token,
        response.data.user,
        data.rememberMe || false
      );
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
      // Always use localStorage for registration (remember me = true)
      StorageManager.setAuthData(response.data.token, response.data.user, true);
    }

    return response;
  }

  async logout(): Promise<ApiResponse> {
    try {
      await httpClient.instance.post('/auth/logout');
    } catch {
      // Ignora erros no logout
    } finally {
      // Use StorageManager to clear data but preserve email if remember me was enabled
      StorageManager.clearAuthDataPreserveEmail();
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
    return StorageManager.isAuthenticated();
  }

  getToken(): string | null {
    return StorageManager.getAuthToken();
  }

  getUser(): User | null {
    return StorageManager.getUser();
  }

  isRememberMeEnabled(): boolean {
    return StorageManager.isRememberMeEnabled();
  }
}

export const authService = new AuthService();
