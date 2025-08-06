import { useCallback, useMemo } from 'react';
import type { RouteConfig, UserPermissions, UserRole } from '../types/auth';
import { useAuth } from './useAuth';

// Configuração de permissões por role
const ROLE_PERMISSIONS: Record<UserRole, UserPermissions> = {
  admin: {
    canViewDashboard: true,
    canManageUsers: true,
    canManageProducts: true,
    canManageServices: true,
    canManageClients: true,
    canViewReports: true,
    canManageSettings: true,
    canAccessTechnicianPanel: true,
  },
  manager: {
    canViewDashboard: true,
    canManageUsers: false,
    canManageProducts: true,
    canManageServices: true,
    canManageClients: true,
    canViewReports: true,
    canManageSettings: false,
    canAccessTechnicianPanel: true,
  },
  technician: {
    canViewDashboard: false,
    canManageUsers: false,
    canManageProducts: false,
    canManageServices: true,
    canManageClients: false,
    canViewReports: false,
    canManageSettings: false,
    canAccessTechnicianPanel: true,
  },
  attendant: {
    canViewDashboard: true,
    canManageUsers: false,
    canManageProducts: false,
    canManageServices: true,
    canManageClients: true,
    canViewReports: false,
    canManageSettings: false,
    canAccessTechnicianPanel: false,
  },
  viewer: {
    canViewDashboard: true,
    canManageUsers: false,
    canManageProducts: false,
    canManageServices: false,
    canManageClients: false,
    canViewReports: true,
    canManageSettings: false,
    canAccessTechnicianPanel: false,
  },
};

// Configuração de rotas com permissões
export const ROUTE_CONFIGS: Record<string, RouteConfig> = {
  '/home': {
    path: '/home',
    requiredRole: 'admin',
    redirectTo: '/servicos',
    layout: 'dashboard',
  },
  '/servicos': {
    path: '/servicos',
    requiredRole: 'technician',
    layout: 'technician',
  },
  '/clients': {
    path: '/clients',
    requiredPermissions: ['canManageClients'],
    layout: 'dashboard',
  },
  '/products': {
    path: '/products',
    requiredPermissions: ['canManageProducts'],
    layout: 'dashboard',
  },
  '/services': {
    path: '/services',
    requiredPermissions: ['canManageServices'],
    layout: 'dashboard',
  },
  '/reports': {
    path: '/reports',
    requiredPermissions: ['canViewReports'],
    layout: 'dashboard',
  },
  '/settings': {
    path: '/settings',
    requiredPermissions: ['canManageSettings'],
    layout: 'dashboard',
  },
};

export const usePermissions = () => {
  const { user, isAuthenticated } = useAuth();

  const permissions = useMemo(() => {
    // Se não está autenticado, retornar permissões mínimas
    if (!isAuthenticated || !user?.role) {
      return ROLE_PERMISSIONS.viewer;
    }
    return ROLE_PERMISSIONS[user.role as UserRole] || ROLE_PERMISSIONS.viewer;
  }, [isAuthenticated, user?.role]);

  const hasPermission = useCallback(
    (permission: keyof UserPermissions): boolean => {
      return permissions[permission] || false;
    },
    [permissions]
  );

  const hasRole = useCallback(
    (role: UserRole): boolean => {
      return user?.role === role;
    },
    [user?.role]
  );

  const canAccessRoute = useCallback(
    (path: string): boolean => {
      const config = ROUTE_CONFIGS[path];
      if (!config) return true; // Rotas sem configuração são acessíveis

      // Verificar role específico
      if (config.requiredRole && !hasRole(config.requiredRole)) {
        return false;
      }

      // Verificar permissões específicas
      if (config.requiredPermissions) {
        return config.requiredPermissions.every((permission) =>
          hasPermission(permission as keyof UserPermissions)
        );
      }

      return true;
    },
    [hasRole, hasPermission]
  );

  const getDefaultRoute = useCallback((): string => {
    if (hasRole('technician')) {
      return '/servicos';
    }

    if (hasPermission('canViewDashboard')) {
      return '/home';
    }

    // Fallback para primeira rota acessível
    const accessibleRoutes = Object.entries(ROUTE_CONFIGS)
      .filter(([path]) => canAccessRoute(path))
      .map(([path]) => path);

    return accessibleRoutes[0] || '/home';
  }, [hasRole, hasPermission, canAccessRoute]);

  const getRouteLayout = useCallback(
    (path: string): 'dashboard' | 'technician' | 'minimal' => {
      const config = ROUTE_CONFIGS[path];
      return config?.layout || 'dashboard';
    },
    []
  );

  return {
    permissions,
    hasPermission,
    hasRole,
    canAccessRoute,
    getDefaultRoute,
    getRouteLayout,
    userRole: user?.role,
    isAuthenticated,
  };
};
