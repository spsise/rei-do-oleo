import {
  ArrowsPointingInIcon,
  ArrowsPointingOutIcon,
  DocumentTextIcon,
  PlusIcon,
  ShoppingCartIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface ModalHeaderProps {
  isMaximized: boolean;
  setIsMaximized: (maximized: boolean) => void;
  onClose: () => void;
  isLoading: boolean;
  activeTab: 'details' | 'products';
  setActiveTab: (tab: 'details' | 'products') => void;
  itemsCount: number;
}

export const ModalHeader: React.FC<ModalHeaderProps> = ({
  isMaximized,
  setIsMaximized,
  onClose,
  isLoading,
  activeTab,
  setActiveTab,
  itemsCount,
}) => {
  return (
    <div className="sticky top-0 bg-white rounded-t-2xl p-6 border-b border-gray-100 z-10">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <div className="p-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
            <PlusIcon className="h-7 w-7 text-white" />
          </div>
          <div>
            <h3 className="text-2xl font-bold text-gray-900">Novo Serviço</h3>
            <p className="text-gray-600 text-sm">
              Registre um novo serviço para o cliente
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={() => setIsMaximized(!isMaximized)}
            className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            title={isMaximized ? 'Restaurar' : 'Maximizar'}
          >
            {isMaximized ? (
              <ArrowsPointingInIcon className="h-5 w-5" />
            ) : (
              <ArrowsPointingOutIcon className="h-5 w-5" />
            )}
          </button>
          <button
            onClick={onClose}
            disabled={isLoading}
            className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
          >
            <XMarkIcon className="h-6 w-6" />
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex space-x-1 mt-6">
        <button
          onClick={() => setActiveTab('details')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
            activeTab === 'details'
              ? 'bg-blue-100 text-blue-700'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          }`}
        >
          <DocumentTextIcon className="h-4 w-4" />
          Detalhes do Serviço
        </button>
        <button
          onClick={() => setActiveTab('products')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
            activeTab === 'products'
              ? 'bg-blue-100 text-blue-700'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          }`}
        >
          <ShoppingCartIcon className="h-4 w-4" />
          Produtos ({itemsCount})
        </button>
      </div>
    </div>
  );
};
