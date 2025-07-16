import { type ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { usePermissions } from '../../hooks/usePermissions';
import { LoadingSpinner } from '../ui/LoadingSpinner';

interface ProtectedRouteProps {
  children: ReactNode;
  path?: string;
}

export const ProtectedRoute = ({ children, path }: ProtectedRouteProps) => {
  const { isAuthenticated, isLoading } = useAuth();
  const { canAccessRoute, getDefaultRoute } = usePermissions();
  const location = useLocation();
  const currentPath = path || location.pathname;

  if (isLoading) {
    return <LoadingSpinner fullScreen />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  // Verificar se o usuário pode acessar a rota atual
  if (!canAccessRoute(currentPath)) {
    const redirectTo = getDefaultRoute();
    return <Navigate to={redirectTo} replace />;
  }

  return <>{children}</>;
};
