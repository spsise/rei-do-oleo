import {
  Home,
  Users,
  Settings,
  Calendar,
  Package,
  DollarSign,
} from "lucide-react";
import { Link, useLocation } from "react-router-dom";
import { useAuthStore } from "../../stores/authStore";
import { cn } from "@/lib/utils";
import { usePermissions } from "@/hooks/use-permissions";

const allNavItems = [
  { path: "/", icon: Home, label: "Dashboard" },
  { path: "/clientes", icon: Users, label: "Clientes" },
  { path: "/agenda", icon: Calendar, label: "Agenda" },
  { path: "/servicos", icon: Settings, label: "Serviços" },
  { path: "/estoque", icon: Package, label: "Estoque", requiresManager: true },
  {
    path: "/financeiro",
    icon: DollarSign,
    label: "Financeiro",
    requiresManager: true,
  },
];

export function BottomNavigation() {
  const location = useLocation();
  const { hasManagerAccess } = usePermissions();

  const navItems = allNavItems.filter((item) => {
    if (item.requiresManager && !hasManagerAccess()) {
      return false;
    }
    return true;
  });

  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
      <div className="flex justify-around py-2">
        {navItems.map(({ path, icon: Icon, label }) => {
          const isActive = location.pathname === path;
          return (
            <Link
              key={path}
              to={path}
              className={cn(
                "flex flex-col items-center py-2 px-3 rounded-lg transition-colors",
                isActive
                  ? "text-blue-600 bg-blue-50"
                  : "text-gray-600 hover:text-blue-600"
              )}
            >
              <Icon className="w-5 h-5 mb-1" />
              <span className="text-xs font-medium">{label}</span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
