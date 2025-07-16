import {
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  BuildingStorefrontIcon,
  ChartBarIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  Cog6ToothIcon,
  CubeIcon,
  HomeIcon,
  TagIcon,
  TruckIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import toast from 'react-hot-toast';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { usePermissions } from '../../hooks/usePermissions';
import { type UserPermissions, type UserRole } from '../../types/auth';

interface MenuItem {
  name: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: string;
  requiredPermission?: string;
  requiredRole?: string;
}

const allMenuItems: MenuItem[] = [
  {
    name: 'Dashboard',
    href: '/home',
    icon: HomeIcon,
    requiredPermission: 'canViewDashboard',
  },
  {
    name: 'Clientes',
    href: '/clients',
    icon: UsersIcon,
    requiredPermission: 'canManageClients',
  },
  {
    name: 'Veículos',
    href: '/vehicles',
    icon: TruckIcon,
    requiredPermission: 'canManageClients',
  },
  {
    name: 'Serviços',
    href: '/services',
    icon: WrenchScrewdriverIcon,
    requiredPermission: 'canManageServices',
  },
  {
    name: 'Categorias',
    href: '/categories',
    icon: TagIcon,
    requiredPermission: 'canManageProducts',
  },
  {
    name: 'Produtos',
    href: '/products',
    icon: CubeIcon,
    requiredPermission: 'canManageProducts',
  },
  {
    name: 'Centros de Serviço',
    href: '/service-centers',
    icon: BuildingStorefrontIcon,
    requiredPermission: 'canManageSettings',
  },
  {
    name: 'Relatórios',
    href: '/reports',
    icon: ChartBarIcon,
    requiredPermission: 'canViewReports',
  },
  {
    name: 'Configurações',
    href: '/settings',
    icon: Cog6ToothIcon,
    requiredPermission: 'canManageSettings',
  },
];

export const SmartMenu: React.FC = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const location = useLocation();
  const { logout } = useAuth();
  const { hasPermission, hasRole, canAccessRoute } = usePermissions();

  // Filtrar itens do menu baseado nas permissões
  const menuItems = allMenuItems.filter((item) => {
    // Verificar permissão específica
    if (
      item.requiredPermission &&
      !hasPermission(item.requiredPermission as keyof UserPermissions)
    ) {
      return false;
    }

    // Verificar role específico
    if (item.requiredRole && !hasRole(item.requiredRole as UserRole)) {
      return false;
    }

    // Verificar se pode acessar a rota
    return canAccessRoute(item.href);
  });

  const handleLogout = async () => {
    setIsLoggingOut(true);
    try {
      await logout();
      toast.success('Logout realizado com sucesso!');
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
      toast.error('Erro ao fazer logout. Tente novamente.');
    } finally {
      setIsLoggingOut(false);
    }
  };

  const isActive = (href: string) => {
    return location.pathname === href;
  };

  // Tooltip helper
  const Tooltip = ({
    children,
    label,
  }: {
    children: React.ReactNode;
    label: string;
  }) => (
    <div className="group relative flex items-center">
      {children}
      <span className="pointer-events-none absolute left-full ml-2 z-10 w-max min-w-[100px] rounded bg-gray-900 px-2 py-1 text-xs text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg whitespace-nowrap">
        {label}
      </span>
    </div>
  );

  return (
    <div className="flex h-full flex-col bg-white border-r border-gray-200">
      {/* Header */}
      <div className="flex h-16 items-center justify-between px-4 border-b border-gray-200">
        {!collapsed && (
          <h1 className="text-lg font-semibold text-gray-900">Rei do Óleo</h1>
        )}

        <div className="flex items-center gap-2">
          {/* Mobile menu button */}
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100"
          >
            {isMobileMenuOpen ? (
              <XMarkIcon className="h-5 w-5" />
            ) : (
              <Bars3Icon className="h-5 w-5" />
            )}
          </button>

          {/* Collapse button */}
          <button
            onClick={() => setCollapsed(!collapsed)}
            className="hidden lg:flex p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100"
          >
            {collapsed ? (
              <ChevronRightIcon className="h-4 w-4" />
            ) : (
              <ChevronLeftIcon className="h-4 w-4" />
            )}
          </button>
        </div>
      </div>

      {/* Navigation */}
      <nav className="mt-6 flex-1 px-2 space-y-1">
        {menuItems.map((item) => {
          const Icon = item.icon;
          const active = isActive(item.href);
          return (
            <Link
              key={item.name}
              to={item.href}
              className={`group flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 ${
                active
                  ? 'bg-brand-50 text-brand-600 border-r-4 border-brand-500 shadow-sm'
                  : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
              } ${collapsed ? 'justify-center px-2' : ''}`}
            >
              {collapsed ? (
                <Tooltip label={item.name}>
                  <Icon
                    className={`h-6 w-6 flex-shrink-0 ${
                      active
                        ? 'text-brand-600'
                        : 'text-gray-400 group-hover:text-gray-500'
                    }`}
                  />
                </Tooltip>
              ) : (
                <Icon
                  className={`h-6 w-6 flex-shrink-0 ${
                    active
                      ? 'text-brand-600'
                      : 'text-gray-400 group-hover:text-gray-500'
                  }`}
                />
              )}
              {!collapsed && <span className="truncate">{item.name}</span>}
              {item.badge && !collapsed && (
                <span className="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  {item.badge}
                </span>
              )}
            </Link>
          );
        })}
      </nav>

      {/* User section */}
      <div className="border-t border-gray-200 p-4">
        <button
          onClick={handleLogout}
          disabled={isLoggingOut}
          className={`group flex w-full items-center gap-3 px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-50 hover:text-gray-900 ${
            collapsed ? 'justify-center px-2' : ''
          }`}
        >
          {collapsed ? (
            <Tooltip label="Sair">
              <ArrowRightOnRectangleIcon className="h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" />
            </Tooltip>
          ) : (
            <>
              <ArrowRightOnRectangleIcon className="h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" />
              <span className="truncate">
                {isLoggingOut ? 'Saindo...' : 'Sair'}
              </span>
            </>
          )}
        </button>
      </div>
    </div>
  );
};
