import { Navigate, useLocation } from "react-router-dom";
import { usePermissions } from "../hooks/use-permissions";
import { UserRole } from "../types";

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRoles?: UserRole | UserRole[];
  requiresManager?: boolean;
  requiresAdmin?: boolean;
}

export function ProtectedRoute({
  children,
  requiredRoles,
  requiresManager,
  requiresAdmin,
}: ProtectedRouteProps) {
  const { user, canAccess, hasRole } = usePermissions();
  const location = useLocation();

  if (!user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (requiredRoles) {
    const roles = Array.isArray(requiredRoles)
      ? requiredRoles
      : [requiredRoles];
    if (!roles.some((role) => hasRole(role))) {
      return <Navigate to="/" replace />;
    }
  }

  if (!canAccess(requiresManager, requiresAdmin)) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
}
