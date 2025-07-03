import { createContext } from 'react';
import type {
  ApiResponse,
  LoginData,
  RegisterData,
  User,
} from '../services/api';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (data: LoginData) => Promise<ApiResponse>;
  register: (data: RegisterData) => Promise<ApiResponse>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

export const AuthContext = createContext<AuthContextType | undefined>(
  undefined
);
