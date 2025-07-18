import {
  CalendarIcon,
  ClockIcon,
  CurrencyDollarIcon,
  TruckIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type CreateTechnicianServiceData } from '../../types/technician';

interface ServiceFieldsGridProps {
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: CreateTechnicianServiceData) => void;
}

export const ServiceFieldsGrid: React.FC<ServiceFieldsGridProps> = ({
  serviceData,
  onServiceDataChange,
}) => {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {/* Duração Estimada */}
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
              ...serviceData,
              mileage_at_service: e.target.value
                ? Number(e.target.value)
                : undefined,
            })
          }
          min="0"
          placeholder="Ex: 50000"
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
              ...serviceData,
              scheduled_at: e.target.value || undefined,
            })
          }
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>

      {/* Valor Total */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <CurrencyDollarIcon className="h-4 w-4 text-green-600" />
          Valor Total (R$)
        </label>
        <input
          type="number"
          value={serviceData.total_amount || ''}
          onChange={(e) =>
            onServiceDataChange({
              ...serviceData,
              total_amount: e.target.value ? Number(e.target.value) : undefined,
            })
          }
          min="0"
          step="0.01"
          placeholder="0.00"
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>

      {/* Desconto */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <CurrencyDollarIcon className="h-4 w-4 text-red-600" />
          Desconto (R$)
        </label>
        <input
          type="number"
          value={serviceData.discount_amount || ''}
          onChange={(e) =>
            onServiceDataChange({
              ...serviceData,
              discount_amount: e.target.value
                ? Number(e.target.value)
                : undefined,
            })
          }
          min="0"
          step="0.01"
          placeholder="0.00"
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>
    </div>
  );
};
