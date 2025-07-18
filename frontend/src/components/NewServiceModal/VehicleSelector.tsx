import { TruckIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type TechnicianVehicle } from '../../types/technician';

interface VehicleSelectorProps {
  vehicles: TechnicianVehicle[];
  selectedVehicleId: number;
  onVehicleChange: (vehicleId: number) => void;
}

export const VehicleSelector: React.FC<VehicleSelectorProps> = ({
  vehicles,
  selectedVehicleId,
  onVehicleChange,
}) => {
  const formatLicensePlate = (plate: string) => {
    if (!plate) return 'N/A';
    return plate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  };

  return (
    <div className="space-y-3">
      <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
        <TruckIcon className="h-4 w-4 text-blue-600" />
        Veículo
      </label>
      <div className="relative">
        <select
          value={selectedVehicleId}
          onChange={(e) => onVehicleChange(Number(e.target.value))}
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
  );
};
