import { PlusIcon } from '@heroicons/react/24/outline';
import React from 'react';
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
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div className="flex items-center gap-3 mb-6">
          <div className="p-2 bg-green-100 rounded-lg">
            <PlusIcon className="h-6 w-6 text-green-600" />
          </div>
          <h3 className="text-2xl font-semibold text-gray-900">Novo Serviço</h3>
        </div>

        <div className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Veículo
            </label>
            <select
              value={serviceData.vehicle_id}
              onChange={(e) =>
                onServiceDataChange({
                  ...serviceData,
                  vehicle_id: Number(e.target.value),
                })
              }
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
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
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Descrição
            </label>
            <textarea
              value={serviceData.description}
              onChange={(e) =>
                onServiceDataChange({
                  ...serviceData,
                  description: e.target.value,
                })
              }
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white resize-none"
              rows={3}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
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
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Prioridade
              </label>
              <select
                value={serviceData.priority}
                onChange={(e) =>
                  onServiceDataChange({
                    ...serviceData,
                    priority: e.target.value as 'low' | 'medium' | 'high',
                  })
                }
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
              >
                <option value="low">Baixa</option>
                <option value="medium">Média</option>
                <option value="high">Alta</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Observações
            </label>
            <textarea
              value={serviceData.notes}
              onChange={(e) =>
                onServiceDataChange({
                  ...serviceData,
                  notes: e.target.value,
                })
              }
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white resize-none"
              rows={3}
            />
          </div>

          <div className="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <button
              onClick={onClose}
              disabled={isLoading}
              className="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium disabled:opacity-50"
            >
              Cancelar
            </button>
            <button
              onClick={onSubmit}
              disabled={isLoading}
              className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              {isLoading ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                  Salvando...
                </>
              ) : (
                'Salvar Serviço'
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
