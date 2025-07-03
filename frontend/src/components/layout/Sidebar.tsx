import {
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  BuildingStorefrontIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  CubeIcon,
  HomeIcon,
  TruckIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';

interface MenuItem {
  name: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: string;
}

const menuItems: MenuItem[] = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'Clientes', href: '/clients', icon: UsersIcon },
  { name: 'Veículos', href: '/vehicles', icon: TruckIcon },
  { name: 'Serviços', href: '/services', icon: WrenchScrewdriverIcon },
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
  const location = useLocation();
  const { logout } = useAuth();

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    }
  };

  const isActive = (href: string) => {
    return location.pathname === href;
  };

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
        className={`fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 ${
          isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        {/* Logo */}
        <div className="flex items-center justify-between h-16 px-6 border-b border-gray-200">
          <div className="flex items-center">
            <div className="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-sm">R</span>
            </div>
            <span className="ml-3 text-xl font-bold text-gray-900">
              Rei do Óleo
            </span>
          </div>
        </div>

        {/* Navigation */}
        <nav className="mt-6 px-3">
          <div className="space-y-1">
            {menuItems.map((item) => {
              const Icon = item.icon;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={`group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 ${
                    isActive(item.href)
                      ? 'bg-brand-50 text-brand-600 border-r-2 border-brand-500'
                      : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  <Icon
                    className={`mr-3 h-5 w-5 flex-shrink-0 ${
                      isActive(item.href)
                        ? 'text-brand-600'
                        : 'text-gray-400 group-hover:text-gray-500'
                    }`}
                  />
                  {item.name}
                  {item.badge && (
                    <span className="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      {item.badge}
                    </span>
                  )}
                </Link>
              );
            })}
          </div>
        </nav>

        {/* User section */}
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
          <button
            onClick={handleLogout}
            className="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200"
          >
            <ArrowRightOnRectangleIcon className="mr-3 h-5 w-5 text-gray-400" />
            Sair
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
