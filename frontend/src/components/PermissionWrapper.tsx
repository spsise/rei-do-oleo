import { ReactNode } from "react";
import { usePermissions } from "../hooks/use-permissions";

interface PermissionWrapperProps {
  children: ReactNode;
  requiresManager?: boolean;
  requiresAdmin?: boolean;
  fallback?: ReactNode;
}

export function PermissionWrapper({
  children,
  requiresManager,
  requiresAdmin,
  fallback = null,
}: PermissionWrapperProps) {
  const { canAccess } = usePermissions();

  if (!canAccess(requiresManager, requiresAdmin)) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
}
