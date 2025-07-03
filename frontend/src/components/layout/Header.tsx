import {
  BellIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';

const getPageTitle = (pathname: string): string => {
  const titles: Record<string, string> = {
    '/dashboard': 'Dashboard',
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
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        {/* Page title */}
        <div className="flex items-center">
          <h1 className="text-xl font-semibold text-gray-900">{pageTitle}</h1>
        </div>

        {/* Right side */}
        <div className="flex items-center space-x-4">
          {/* Search */}
          <div className="relative hidden md:block">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              placeholder="Buscar..."
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 sm:text-sm"
            />
          </div>

          {/* Notifications */}
          <div className="relative">
            <button
              onClick={() => setIsNotificationsOpen(!isNotificationsOpen)}
              className="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 rounded-lg"
            >
              <BellIcon className="h-6 w-6" />
              <span className="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400"></span>
            </button>

            {/* Notifications dropdown */}
            {isNotificationsOpen && (
              <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div className="py-2">
                  <div className="px-4 py-2 border-b border-gray-200">
                    <h3 className="text-sm font-medium text-gray-900">
                      Notificações
                    </h3>
                  </div>
                  <div className="max-h-64 overflow-y-auto">
                    <div className="px-4 py-3 hover:bg-gray-50">
                      <p className="text-sm text-gray-900">
                        Novo serviço agendado
                      </p>
                      <p className="text-xs text-gray-500 mt-1">Há 5 minutos</p>
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
              className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
            >
              <div className="flex items-center">
                <UserCircleIcon className="h-8 w-8 text-gray-400" />
                <div className="ml-3 hidden md:block">
                  <p className="text-sm font-medium text-gray-900">
                    {user?.name}
                  </p>
                  <p className="text-xs text-gray-500">{user?.email}</p>
                </div>
                <ChevronDownIcon className="ml-2 h-4 w-4 text-gray-400" />
              </div>
            </button>

            {/* Profile dropdown menu */}
            {isProfileOpen && (
              <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div className="py-1">
                  <a
                    href="#profile"
                    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  >
                    Meu Perfil
                  </a>
                  <a
                    href="#settings"
                    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  >
                    Configurações
                  </a>
                  <div className="border-t border-gray-200">
                    <button
                      onClick={() => {
                        // Logout será tratado pelo Sidebar
                        setIsProfileOpen(false);
                      }}
                      className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                      Sair
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  );
};
