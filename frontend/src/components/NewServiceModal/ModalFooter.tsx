import { PlusIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type CreateTechnicianServiceData } from '../../types/technician';

interface ModalFooterProps {
  onClose: () => void;
  onSubmit: () => void;
  isLoading: boolean;
  activeTab: 'details' | 'products';
  serviceData: CreateTechnicianServiceData;
  calculateFinalTotal: () => number;
  submitButtonText?: string;
}

export const ModalFooter: React.FC<ModalFooterProps> = ({
  onClose,
  onSubmit,
  isLoading,
  activeTab,
  serviceData,
  calculateFinalTotal,
  submitButtonText = 'Salvar Serviço',
}) => {
  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  return (
    <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
      <div className="flex justify-between items-center">
        <div className="text-sm text-gray-600">
          {activeTab === 'products' && (
            <span>
              Total:{' '}
              <span className="font-semibold text-green-600">
                {formatPrice(calculateFinalTotal())}
              </span>
            </span>
          )}
        </div>
        <div className="flex gap-3">
          <button
            onClick={onClose}
            disabled={isLoading}
            className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50"
          >
            Cancelar
          </button>
          <button
            onClick={onSubmit}
            disabled={
              isLoading ||
              !serviceData.vehicle_id ||
              !serviceData.description.trim()
            }
            className="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3"
          >
            {isLoading ? (
              <>
                <div className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                <span>Salvando...</span>
              </>
            ) : (
              <>
                <PlusIcon className="h-5 w-5" />
                <span>{submitButtonText}</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};
