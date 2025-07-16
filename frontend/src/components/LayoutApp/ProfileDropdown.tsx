import {
  ArrowRightOnRectangleIcon,
  Cog6ToothIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import toast from 'react-hot-toast';
import { useAuth } from '../../hooks/useAuth';

interface ProfileDropdownProps {
  isOpen: boolean;
  onClose: () => void;
}

export const ProfileDropdown: React.FC<ProfileDropdownProps> = ({
  isOpen,
  onClose,
}) => {
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const { logout } = useAuth();

  if (!isOpen) return null;

  const handleLogout = async () => {
    setIsLoggingOut(true);
    try {
      await logout();
      toast.success('Logout realizado com sucesso!');
      onClose();
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
      toast.error('Erro ao fazer logout. Tente novamente.');
    } finally {
      setIsLoggingOut(false);
    }
  };

  return (
    <div className="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 z-50 animate-fade-in">
      <div className="py-1">
        <a
          href="#profile"
          className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
          onClick={onClose}
        >
          <UserCircleIcon className="h-4 w-4 mr-3 text-gray-400" />
          Meu Perfil
        </a>
        <a
          href="#settings"
          className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
          onClick={onClose}
        >
          <Cog6ToothIcon className="h-4 w-4 mr-3 text-gray-400" />
          Configurações
        </a>
        <div className="border-t border-gray-100 my-1" />
        <button
          onClick={handleLogout}
          disabled={isLoggingOut}
          className={`flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-all duration-200 active:scale-95 ${
            isLoggingOut
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:text-gray-900'
          }`}
        >
          <ArrowRightOnRectangleIcon className="h-4 w-4 mr-3 text-gray-400" />
          {isLoggingOut ? (
            <>
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-400 mr-2"></div>
              Saindo...
            </>
          ) : (
            'Sair'
          )}
        </button>
      </div>
    </div>
  );
};
