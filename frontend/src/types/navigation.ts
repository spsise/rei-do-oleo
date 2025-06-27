import { LucideIcon } from "lucide-react";
import { UserRole } from "./index";

export interface NavigationItem {
  path: string;
  icon: LucideIcon;
  label: string;
  requiresManager?: boolean;
  requiresAdmin?: boolean;
  requiredRoles?: UserRole | UserRole[];
}
