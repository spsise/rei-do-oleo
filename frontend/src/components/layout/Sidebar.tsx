import {
  Home,
  Users,
  Settings,
  List,
  LogOut,
  Calendar,
  Package,
  DollarSign,
  FileText,
  Wrench,
} from "lucide-react";
import { Link, useLocation } from "react-router-dom";
import { useAuthStore } from "../../stores/authStore";
import { usePermissions } from "../../hooks/use-permissions";
import { NavigationItem } from "../../types/navigation";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";

const allNavItems: NavigationItem[] = [
  {
    path: "/",
    icon: Home,
    label: "Dashboard",
    requiredRoles: ["admin", "manager"],
  },
  { path: "/clientes", icon: Users, label: "Clientes" },
  { path: "/agenda", icon: Calendar, label: "Agenda" },
  { path: "/servicos", icon: Wrench, label: "Serviços" },
  { path: "/estoque", icon: Package, label: "Estoque", requiresManager: true },
  {
    path: "/financeiro",
    icon: DollarSign,
    label: "Financeiro",
    requiresManager: true,
  },
  {
    path: "/relatorios",
    icon: FileText,
    label: "Relatórios",
    requiresManager: true,
  },
  {
    path: "/configuracoes",
    icon: Settings,
    label: "Configurações",
    requiresManager: true,
  },
];

export function Sidebar() {
  const location = useLocation();
  const { logout } = useAuthStore();
  const { user, filterNavigationItems } = usePermissions();

  const navItems = filterNavigationItems(allNavItems);

  return (
    <aside className="hidden md:flex flex-col w-64 bg-white border-r border-gray-200 h-screen">
      {/* Header */}
      <div className="p-6 border-b border-gray-200">
        <h1 className="text-xl font-bold text-gray-900">Rei do Óleo</h1>
        <p className="text-sm text-gray-500 mt-1">Sistema MVP</p>
      </div>

      {/* Navigation */}
      <nav className="flex-1 p-4">
        <ul className="space-y-2">
          {navItems.map(({ path, icon: Icon, label }) => {
            const isActive = location.pathname === path;
            return (
              <li key={path}>
                <Link
                  to={path}
                  className={cn(
                    "flex items-center gap-3 px-3 py-2 rounded-lg transition-colors",
                    isActive
                      ? "bg-blue-50 text-blue-700 border border-blue-200"
                      : "text-gray-700 hover:bg-gray-50"
                  )}
                >
                  <Icon className="w-5 h-5" />
                  <span className="font-medium">{label}</span>
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* Footer */}
      <div className="p-4 border-t border-gray-200">
        <div className="flex items-center gap-3 mb-3">
          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
            <span className="text-sm font-medium text-blue-700">
              {user?.name?.charAt(0).toUpperCase()}
            </span>
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-gray-900 truncate">
              {user?.name}
            </p>
            <p className="text-xs text-gray-500 capitalize">
              {user?.highest_role}
            </p>
          </div>
        </div>
        <Button
          variant="ghost"
          size="sm"
          onClick={logout}
          className="w-full justify-start gap-2 text-gray-700 hover:text-red-700"
        >
          <LogOut className="w-4 h-4" />
          Sair
        </Button>
      </div>
    </aside>
  );
}
