import { XMarkIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type ServiceTemplate } from '../../types/attendant';

interface ServiceTemplatesModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSelectTemplate: (template: ServiceTemplate) => void;
}

export const ServiceTemplatesModal: React.FC<ServiceTemplatesModalProps> = ({
  isOpen,
  onClose,
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div className="sticky top-0 bg-white rounded-t-2xl p-6 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-2xl font-bold text-gray-900">
                Templates de Serviço
              </h3>
              <p className="text-gray-600 text-sm">
                Selecione um template para usar
              </p>
            </div>
            <button
              onClick={onClose}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>
        </div>

        <div className="p-6">
          <div className="text-center py-12">
            <div className="text-gray-400 text-6xl mb-4">📋</div>
            <h4 className="text-xl font-semibold text-gray-900 mb-2">
              Templates em Desenvolvimento
            </h4>
            <p className="text-gray-600">
              O modal de templates está sendo implementado
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
