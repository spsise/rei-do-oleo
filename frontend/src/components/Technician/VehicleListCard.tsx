import {
  CalendarIcon,
  ClockIcon,
  PaintBrushIcon,
  TruckIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianVehicle } from '../../types/technician';

interface VehicleListCardProps {
  vehicles: TechnicianVehicle[];
}

export const VehicleListCard: React.FC<VehicleListCardProps> = ({
  vehicles,
}) => {
  const formatLicensePlate = (plate: string) => {
    if (!plate) return 'N/A';
    return plate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  };

  const formatMileage = (mileage: number | string) => {
    if (!mileage) return 'N/A';
    const num = typeof mileage === 'string' ? parseInt(mileage) : mileage;
    return num.toLocaleString('pt-BR') + ' km';
  };

  return (
    <div className="space-y-3">
      {vehicles?.length ? (
        vehicles.map((vehicle, index) => (
          <div
            key={vehicle.id || index}
            className="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-purple-100 hover:border-purple-200 transition-all duration-200 hover:shadow-md"
          >
            {/* Header compacto */}
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center gap-2">
                <div className="p-1.5 bg-gradient-to-r from-purple-100 to-pink-100 rounded-md">
                  <TruckIcon className="h-3.5 w-3.5 text-purple-600" />
                </div>
                <div>
                  <div className="font-bold text-gray-900 text-sm font-mono">
                    {formatLicensePlate(vehicle.license_plate || '')}
                  </div>
                  <div className="text-xs text-gray-600">
                    {vehicle.brand} {vehicle.model}
                  </div>
                </div>
              </div>
              <div className="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">
                #{index + 1}
              </div>
            </div>

            {/* Detalhes em grid compacto */}
            <div className="grid grid-cols-3 gap-2 text-xs">
              <div className="flex items-center gap-1.5">
                <CalendarIcon className="h-3.5 w-3.5 text-gray-500" />
                <span className="text-gray-500 font-medium">Ano:</span>
                <span className="text-gray-900 font-semibold">
                  {vehicle.year || 'N/A'}
                </span>
              </div>
              <div className="flex items-center gap-1.5">
                <PaintBrushIcon className="h-3.5 w-3.5 text-gray-500" />
                <span className="text-gray-500 font-medium">Cor:</span>
                <span className="text-gray-900 font-semibold">
                  {vehicle.color || 'N/A'}
                </span>
              </div>
              <div className="flex items-center gap-1.5">
                <ClockIcon className="h-3.5 w-3.5 text-gray-500" />
                <span className="text-gray-500 font-medium">KM:</span>
                <span className="text-gray-900 font-semibold">
                  {formatMileage(vehicle.mileage || 0)}
                </span>
              </div>
            </div>

            {/* Status compacto */}
            <div className="mt-2 pt-2 border-t border-gray-100">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-1.5">
                  <div className="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                  <span className="text-xs text-gray-600">Ativo</span>
                </div>
                <div className="text-xs text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full font-medium">
                  Disponível
                </div>
              </div>
            </div>
          </div>
        ))
      ) : (
        <div className="bg-white/80 backdrop-blur-sm rounded-lg p-6 text-center border border-purple-100">
          <div className="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <TruckIcon className="h-4 w-4 text-purple-500" />
          </div>
          <h4 className="text-gray-900 font-semibold text-sm mb-1">
            Nenhum veículo cadastrado
          </h4>
          <p className="text-gray-600 text-xs">
            Este cliente ainda não possui veículos cadastrados no sistema.
          </p>
        </div>
      )}
    </div>
  );
};
