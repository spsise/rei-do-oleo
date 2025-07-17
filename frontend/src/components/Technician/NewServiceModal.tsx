import {
  ClockIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  TruckIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import '../../styles/Technician.css';
import {
  type CreateTechnicianServiceData,
  type TechnicianVehicle,
} from '../../types/technician';

interface NewServiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: CreateTechnicianServiceData) => void;
  vehicles: TechnicianVehicle[];
  onSubmit: () => void;
  isLoading?: boolean;
}

export const NewServiceModal: React.FC<NewServiceModalProps> = ({
  isOpen,
  onClose,
  serviceData,
  onServiceDataChange,
  vehicles,
  onSubmit,
  isLoading = false,
}) => {
  const formatLicensePlate = (plate: string) => {
    if (!plate) return 'N/A';
    return plate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-modalSlideInUp">
        {/* Header */}
        <div className="sticky top-0 bg-white rounded-t-2xl p-6 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
                <PlusIcon className="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 className="text-2xl font-bold text-gray-900">
                  Novo Serviço
                </h3>
                <p className="text-gray-600 text-sm">
                  Registre um novo serviço para o cliente
                </p>
              </div>
            </div>
            <button
              onClick={onClose}
              disabled={isLoading}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
            >
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="p-6 space-y-6">
          {/* Seleção de Veículo */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
              <TruckIcon className="h-4 w-4 text-blue-600" />
              Veículo
            </label>
            <div className="relative">
              <select
                value={serviceData.vehicle_id}
                onChange={(e) =>
                  onServiceDataChange({
                    ...serviceData,
                    vehicle_id: Number(e.target.value),
                  })
                }
                className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md appearance-none"
              >
                <option value={0}>Selecione um veículo</option>
                {vehicles?.map((vehicle) => (
                  <option
                    key={vehicle.id || `vehicle-${Math.random()}`}
                    value={vehicle.id || 0}
                  >
                    {vehicle.brand || 'N/A'} {vehicle.model || 'N/A'} -{' '}
                    {formatLicensePlate(vehicle.license_plate || '')}
                  </option>
                ))}
              </select>
              <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg
                  className="h-4 w-4 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
          </div>

          {/* Descrição do Serviço */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700">
              Descrição do Serviço
            </label>
            <textarea
              value={serviceData.description}
              onChange={(e) =>
                onServiceDataChange({
                  ...serviceData,
                  description: e.target.value,
                })
              }
              placeholder="Descreva o serviço a ser realizado..."
              className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
              rows={3}
            />
          </div>

          {/* Duração e Prioridade */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <ClockIcon className="h-4 w-4 text-purple-600" />
                Duração Estimada (min)
              </label>
              <input
                type="number"
                value={serviceData.estimated_duration}
                onChange={(e) =>
                  onServiceDataChange({
                    ...serviceData,
                    estimated_duration: Number(e.target.value),
                  })
                }
                min="15"
                step="15"
                className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
              />
            </div>
            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <ExclamationTriangleIcon className="h-4 w-4 text-orange-600" />
                Prioridade
              </label>
              <div className="relative">
                <select
                  value={serviceData.priority}
                  onChange={(e) =>
                    onServiceDataChange({
                      ...serviceData,
                      priority: e.target.value as 'low' | 'medium' | 'high',
                    })
                  }
                  className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md appearance-none"
                >
                  <option value="low">🟢 Baixa</option>
                  <option value="medium">🟡 Média</option>
                  <option value="high">🔴 Alta</option>
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg
                    className="h-4 w-4 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          {/* Observações */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700">
              Observações Adicionais
            </label>
            <textarea
              value={serviceData.notes}
              onChange={(e) =>
                onServiceDataChange({
                  ...serviceData,
                  notes: e.target.value,
                })
              }
              placeholder="Observações, instruções especiais ou detalhes adicionais..."
              className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
              rows={3}
            />
          </div>

          {/* Dicas */}
          <div className="p-4 bg-blue-50/50 rounded-xl border border-blue-100">
            <div className="flex items-start gap-3">
              <div className="p-1.5 bg-blue-100 rounded-lg">
                <svg
                  className="w-4 h-4 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div className="text-sm text-blue-800">
                <p className="font-medium mb-1">
                  💡 Dicas para um bom registro:
                </p>
                <ul className="space-y-1 text-xs">
                  <li>• Seja específico na descrição do serviço</li>
                  <li>
                    • Estime a duração com precisão para melhor planejamento
                  </li>
                  <li>• Use observações para detalhes importantes</li>
                  <li>• Defina a prioridade adequada para o serviço</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
          <div className="flex justify-end gap-3">
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
                  <span>Salvar Serviço</span>
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
