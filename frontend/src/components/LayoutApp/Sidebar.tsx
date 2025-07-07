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

interface MenuItem {
  name: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: string;
}

const menuItems: MenuItem[] = [
  { name: 'Dashboard', href: '/home', icon: HomeIcon },
  { name: 'Clientes', href: '/clients', icon: UsersIcon },
  { name: 'Veículos', href: '/vehicles', icon: TruckIcon },
  { name: 'Serviços', href: '/services', icon: WrenchScrewdriverIcon },
  { name: 'Categorias', href: '/categories', icon: TagIcon },
  { name: 'Produtos', href: '/products', icon: CubeIcon },
  {
    name: 'Centros de Serviço',
    href: '/service-centers',
    icon: BuildingStorefrontIcon,
  },
  { name: 'Relatórios', href: '/reports', icon: ChartBarIcon },
  { name: 'Configurações', href: '/settings', icon: Cog6ToothIcon },
];

export const Sidebar: React.FC = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const location = useLocation();
  const { logout } = useAuth();

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
    <>
      {/* Mobile menu button */}
      <div className="lg:hidden fixed top-4 left-4 z-50">
        <button
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          className="p-2 bg-white rounded-lg shadow-lg border border-gray-200"
        >
          {isMobileMenuOpen ? (
            <XMarkIcon className="h-6 w-6 text-gray-600" />
          ) : (
            <Bars3Icon className="h-6 w-6 text-gray-600" />
          )}
        </button>
      </div>

      {/* Sidebar */}
      <div
        className={`fixed inset-y-0 left-0 z-40 ${collapsed ? 'w-20' : 'w-64'} bg-white shadow-xl border-gray-100 transform transition-all duration-300 ease-in-out flex flex-col min-h-screen lg:translate-x-0 lg:static lg:inset-0 ${
          isMobileMenuOpen
            ? 'translate-x-0'
            : '-translate-x-full lg:translate-x-0'
        }`}
      >
        {/* Collapse button */}
        <div className="flex items-center justify-between h-16 px-4 border-b border-gray-100">
          <div className="flex items-center">
            <div className="w-9 h-9 bg-brand-500 rounded-xl flex items-center justify-center shadow-md">
              <span className="text-white font-bold text-lg">R</span>
            </div>
            {!collapsed && (
              <span className="ml-3 text-xl font-bold text-gray-900 transition-all duration-200">
                Rei do Óleo
              </span>
            )}
          </div>
          <button
            onClick={() => setCollapsed((c) => !c)}
            className="hidden lg:flex items-center justify-center p-1 rounded-lg hover:bg-gray-100 transition-colors"
            aria-label={collapsed ? 'Expandir sidebar' : 'Colapsar sidebar'}
          >
            {collapsed ? (
              <ChevronRightIcon className="h-6 w-6 text-gray-400" />
            ) : (
              <ChevronLeftIcon className="h-6 w-6 text-gray-400" />
            )}
          </button>
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
        <div
          className={`p-4 border-t border-gray-100 ${collapsed ? 'flex flex-col items-center' : ''} mt-auto`}
        >
          <button
            onClick={handleLogout}
            disabled={isLoggingOut}
            className={`flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 active:scale-95 ${collapsed ? 'justify-center px-2' : ''} ${isLoggingOut ? 'opacity-50 cursor-not-allowed' : ''}`}
          >
            <ArrowRightOnRectangleIcon className="h-5 w-5 text-gray-400" />
            {!collapsed &&
              (isLoggingOut ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-400 ml-2 mr-2"></div>
                  Saindo...
                </>
              ) : (
                <span className="ml-2">Sair</span>
              ))}
          </button>
        </div>
      </div>

      {/* Mobile overlay */}
      {isMobileMenuOpen && (
        <div
          className="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}
    </>
  );
};
