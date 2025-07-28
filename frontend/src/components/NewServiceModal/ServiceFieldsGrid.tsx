import {
  CalendarIcon,
  ClockIcon,
  CurrencyDollarIcon,
  MinusIcon,
  PlusIcon,
  TruckIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type CreateTechnicianServiceData } from '../../types/technician';

interface ServiceFieldsGridProps {
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: Partial<CreateTechnicianServiceData>) => void;
  isReadOnly?: boolean;
}

export const ServiceFieldsGrid: React.FC<ServiceFieldsGridProps> = ({
  serviceData,
  onServiceDataChange,
  isReadOnly = false,
}) => {
  const handleDurationChange = (increment: boolean) => {
    const currentDuration = serviceData.estimated_duration || 60;
    const newDuration = increment
      ? Math.min(currentDuration + 15, 480) // Máximo 8 horas
      : Math.max(currentDuration - 15, 15); // Mínimo 15 minutos

    onServiceDataChange({
      estimated_duration: newDuration,
    });
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {/* Duração Estimada */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <ClockIcon className="h-4 w-4 text-purple-600" />
          Duração Estimada (min)
        </label>
        <div className="relative">
          <input
            type="number"
            value={serviceData.estimated_duration}
            onChange={(e) =>
              onServiceDataChange({
                estimated_duration: Number(e.target.value),
              })
            }
            min="15"
            max="480"
            step="15"
            readOnly={isReadOnly}
            className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md pr-20"
          />
          {/* Controles de incremento/decremento para mobile */}
          <div className="absolute right-2 top-1/2 transform -translate-y-1/2 flex flex-col gap-1 md:hidden">
            <button
              type="button"
              onClick={() => handleDurationChange(true)}
              disabled={
                isReadOnly || (serviceData.estimated_duration || 0) >= 480
              }
              className="w-6 h-6 bg-blue-500 text-white rounded-md flex items-center justify-center hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              <PlusIcon className="h-3 w-3" />
            </button>
            <button
              type="button"
              onClick={() => handleDurationChange(false)}
              disabled={
                isReadOnly || (serviceData.estimated_duration || 0) <= 15
              }
              className="w-6 h-6 bg-blue-500 text-white rounded-md flex items-center justify-center hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              <MinusIcon className="h-3 w-3" />
            </button>
          </div>
        </div>
      </div>

      {/* Quilometragem */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <TruckIcon className="h-4 w-4 text-gray-600" />
          Quilometragem
        </label>
        <input
          type="number"
          value={serviceData.mileage_at_service || ''}
          onChange={(e) =>
            onServiceDataChange({
              mileage_at_service: e.target.value
                ? Number(e.target.value)
                : undefined,
            })
          }
          min="0"
          placeholder="Ex: 50000"
          readOnly={isReadOnly}
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>

      {/* Data de Agendamento */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <CalendarIcon className="h-4 w-4 text-green-600" />
          Data de Agendamento
        </label>
        <input
          type="datetime-local"
          value={serviceData.scheduled_at || ''}
          onChange={(e) =>
            onServiceDataChange({
              scheduled_at: e.target.value || undefined,
            })
          }
          readOnly={isReadOnly}
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>

      {/* Valor Total - Somente Leitura */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <CurrencyDollarIcon className="h-4 w-4 text-green-600" />
          Valor Total (R$)
        </label>
        <input
          type="number"
          value={serviceData.total_amount || 0}
          readOnly={true}
          min="0"
          step="0.01"
          placeholder="0.00"
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed"
        />
        <p className="text-xs text-gray-500">
          Valor calculado automaticamente com base nos produtos
        </p>
      </div>

      {/* Desconto - Somente Leitura */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <CurrencyDollarIcon className="h-4 w-4 text-red-600" />
          Desconto (R$)
        </label>
        <input
          type="number"
          value={serviceData.discount_amount || 0}
          readOnly={true}
          min="0"
          step="0.01"
          placeholder="0.00"
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed"
        />
        <p className="text-xs text-gray-500">
          Desconto calculado automaticamente
        </p>
      </div>
    </div>
  );
};
