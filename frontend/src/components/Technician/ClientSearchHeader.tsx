import {
  CheckCircleIcon,
  PlusIcon,
  UserIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface ClientSearchHeaderProps {
  onCreateNewService: () => void;
}

export const ClientSearchHeader: React.FC<ClientSearchHeaderProps> = ({
  onCreateNewService,
}) => {
  return (
    <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
      <div className="flex items-center gap-3">
        <div className="relative">
          <div className="p-2.5 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-md">
            <UserIcon className="h-5 w-5 text-white" />
          </div>
          <div className="absolute -top-1 -right-1">
            <CheckCircleIcon className="h-4 w-4 text-green-500 bg-white rounded-full" />
          </div>
        </div>
        <div>
          <h2 className="text-lg sm:text-xl font-bold text-gray-900">
            Cliente Encontrado
          </h2>
          <p className="text-sm text-green-600 font-medium flex items-center gap-2">
            <div className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
            Dados carregados
          </p>
        </div>
      </div>

      <button
        onClick={onCreateNewService}
        className="px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center gap-2 transition-all duration-200 font-medium shadow-md hover:shadow-lg transform hover:scale-105 text-sm sm:text-base"
      >
        <PlusIcon className="h-4 w-4" />
        <span>Novo Serviço</span>
      </button>
    </div>
  );
};
