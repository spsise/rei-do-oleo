import { useAuthStore } from "../stores/authStore";
import { UserRole } from "../types";

export function usePermissions() {
  const { user, isAuthenticated } = useAuthStore();

  const hasRole = (role: UserRole) => {
    return user?.highest_role === role;
  };

  const hasAnyRole = (roles: UserRole[]) => {
    return roles.some((role) => hasRole(role));
  };

  const hasManagerAccess = () => {
    return user?.highest_role === "manager" || user?.highest_role === "admin";
  };

  const hasAdminAccess = () => {
    return user?.highest_role === "admin";
  };

  const canAccess = (requiresManager?: boolean, requiresAdmin?: boolean) => {
    if (requiresAdmin) {
      return hasAdminAccess();
    }
    if (requiresManager) {
      return hasManagerAccess();
    }
    return true; // Acesso público
  };

  const filterNavigationItems = <
    T extends {
      requiresManager?: boolean;
      requiresAdmin?: boolean;
      requiredRoles?: UserRole | UserRole[];
    }
  >(
    items: T[]
  ): T[] => {
    return items.filter((item) => {
      // Verificação por roles específicos
      if (item.requiredRoles) {
        const roles = Array.isArray(item.requiredRoles)
          ? item.requiredRoles
          : [item.requiredRoles];
        return hasAnyRole(roles);
      }
      // Verificação por flags booleanas (retrocompatibilidade)
      return canAccess(item.requiresManager, item.requiresAdmin);
    });
  };

  return {
    user,
    isAuthenticated,
    hasRole,
    hasAnyRole,
    hasManagerAccess,
    hasAdminAccess,
    canAccess,
    filterNavigationItems,
  };
}
