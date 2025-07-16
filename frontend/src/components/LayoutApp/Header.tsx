import {
  BellIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { ProfileDropdown } from './ProfileDropdown';

const getPageTitle = (pathname: string): string => {
  const titles: Record<string, string> = {
    '/home': 'Dashboard',
    '/clients': 'Clientes',
    '/vehicles': 'Veículos',
    '/services': 'Serviços',
    '/products': 'Produtos',
    '/service-centers': 'Centros de Serviço',
    '/reports': 'Relatórios',
    '/settings': 'Configurações',
  };
  return titles[pathname] || 'Página';
};

export const Header: React.FC = () => {
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const location = useLocation();
  const { user } = useAuth();

  const pageTitle = getPageTitle(location.pathname);

  return (
    <header className="bg-white/90 backdrop-blur shadow-md border-b border-gray-100 sticky top-0 z-30">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Page title */}
          <div className="flex items-center">
            <h1 className="text-2xl font-bold text-gray-900 tracking-tight drop-shadow-sm">
              {pageTitle}
            </h1>
          </div>

          {/* Right side */}
          <div className="flex items-center gap-4">
            {/* Search */}
            <div className="relative hidden md:block">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Buscar..."
                className="block w-64 pl-10 pr-3 py-2 border border-gray-200 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-sm"
              />
            </div>

            {/* Notifications */}
            <div className="relative">
              <button
                onClick={() => setIsNotificationsOpen(!isNotificationsOpen)}
                className="p-2 text-gray-400 hover:text-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 rounded-full transition-colors"
              >
                <BellIcon className="h-6 w-6" />
                <span className="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400"></span>
              </button>

              {/* Notifications dropdown */}
              {isNotificationsOpen && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 z-50 animate-fade-in">
                  <div className="py-2">
                    <div className="px-4 py-2 border-b border-gray-100">
                      <h3 className="text-sm font-semibold text-gray-900">
                        Notificações
                      </h3>
                    </div>
                    <div className="max-h-64 overflow-y-auto divide-y divide-gray-50">
                      <div className="px-4 py-3 hover:bg-gray-50">
                        <p className="text-sm text-gray-900">
                          Novo serviço agendado
                        </p>
                        <p className="text-xs text-gray-500 mt-1">
                          Há 5 minutos
                        </p>
                      </div>
                      <div className="px-4 py-3 hover:bg-gray-50">
                        <p className="text-sm text-gray-900">
                          Produto com estoque baixo
                        </p>
                        <p className="text-xs text-gray-500 mt-1">Há 1 hora</p>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Profile dropdown */}
            <div className="relative">
              <button
                onClick={() => setIsProfileOpen(!isProfileOpen)}
                className="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors"
              >
                <div className="flex items-center">
                  <UserCircleIcon className="h-9 w-9 text-gray-400 rounded-full bg-gray-100 shadow-sm" />
                  <div className="ml-3 hidden md:block text-left">
                    <p className="text-sm font-semibold text-gray-900 leading-tight">
                      {user?.name}
                    </p>
                    <p className="text-xs text-gray-500">{user?.email}</p>
                  </div>
                  <ChevronDownIcon className="ml-2 h-4 w-4 text-gray-400" />
                </div>
              </button>

              {/* Profile dropdown menu */}
              <ProfileDropdown
                isOpen={isProfileOpen}
                onClose={() => setIsProfileOpen(false)}
              />
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};
