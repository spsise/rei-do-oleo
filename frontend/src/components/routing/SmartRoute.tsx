import { type ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { ROUTE_CONFIGS, usePermissions } from '../../hooks/usePermissions';
import { LoadingSpinner } from '../ui/LoadingSpinner';

interface SmartRouteProps {
  children: ReactNode;
  path: string;
  fallbackRoute?: string;
}

export const SmartRoute = ({
  children,
  path,
  fallbackRoute,
}: SmartRouteProps) => {
  const { isAuthenticated, isLoading } = useAuth();
  const { canAccessRoute, getDefaultRoute } = usePermissions();
  const location = useLocation();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  // Verificar se o usuário pode acessar a rota atual
  if (!canAccessRoute(path)) {
    // Se não pode acessar, redirecionar para rota padrão ou fallback
    const redirectTo = fallbackRoute || getDefaultRoute();
    return <Navigate to={redirectTo} replace />;
  }

  // Verificar se há redirecionamento específico configurado
  const routeConfig = ROUTE_CONFIGS[path];
  if (routeConfig?.redirectTo && location.pathname === path) {
    return <Navigate to={routeConfig.redirectTo} replace />;
  }

  return <>{children}</>;
};
