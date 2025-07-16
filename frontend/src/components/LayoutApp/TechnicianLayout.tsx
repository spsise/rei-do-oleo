import {
  ArrowRightOnRectangleIcon,
  UserIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { toast } from 'react-hot-toast';
import { useAuth } from '../../hooks/useAuth';

interface TechnicianLayoutProps {
  children: React.ReactNode;
}

export const TechnicianLayout: React.FC<TechnicianLayoutProps> = ({
  children,
}) => {
  const { user, logout } = useAuth();

  const handleLogout = async () => {
    try {
      await logout();
      toast.success('Logout realizado com sucesso!');
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
      toast.error('Erro ao fazer logout. Tente novamente.');
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header simples */}
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <h1 className="text-xl font-semibold text-gray-900">
                Rei do Óleo
              </h1>
            </div>

            <div className="flex items-center space-x-4">
              <div className="flex items-center text-sm text-gray-700">
                <UserIcon className="h-5 w-5 mr-2" />
                <span>{user?.name}</span>
              </div>

              <button
                onClick={handleLogout}
                className="flex items-center px-3 py-2 text-sm text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
              >
                <ArrowRightOnRectangleIcon className="h-5 w-5 mr-2" />
                Sair
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Conteúdo principal */}
      <main className="flex-1">{children}</main>
    </div>
  );
};
