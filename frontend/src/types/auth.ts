export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  role: UserRole;
  permissions?: string[];
  created_at: string;
  updated_at: string;
}

export type UserRole =
  | 'admin'
  | 'manager'
  | 'technician'
  | 'attendant'
  | 'viewer';

export interface UserPermissions {
  canViewDashboard: boolean;
  canManageUsers: boolean;
  canManageProducts: boolean;
  canManageServices: boolean;
  canManageClients: boolean;
  canViewReports: boolean;
  canManageSettings: boolean;
  canAccessTechnicianPanel: boolean;
}

export interface RouteConfig {
  path: string;
  requiredRole?: UserRole;
  requiredPermissions?: string[];
  redirectTo?: string;
  layout?: 'dashboard' | 'technician' | 'minimal';
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
