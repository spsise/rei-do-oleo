import { TruckIcon } from '@heroicons/react/24/outline';
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

  return (
    <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 bg-blue-100 rounded-lg">
          <TruckIcon className="h-5 w-5 text-blue-600" />
        </div>
        <h3 className="font-semibold text-gray-900 text-lg">
          Veículos ({vehicles?.length || 0})
        </h3>
      </div>
      <div className="space-y-4">
        {vehicles?.map((vehicle) => (
          <div
            key={vehicle.id}
            className="bg-white rounded-lg p-4 border border-gray-200"
          >
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div>
                <span className="font-medium text-gray-700">Placa:</span>
                <p className="text-gray-900 font-mono">
                  {formatLicensePlate(vehicle.license_plate || '')}
                </p>
              </div>
              <div>
                <span className="font-medium text-gray-700">Modelo:</span>
                <p className="text-gray-900">
                  {vehicle.brand} {vehicle.model} {vehicle.year}
                </p>
              </div>
              <div>
                <span className="font-medium text-gray-700">Cor:</span>
                <p className="text-gray-900">{vehicle.color}</p>
              </div>
              <div>
                <span className="font-medium text-gray-700">
                  Quilometragem:
                </span>
                <p className="text-gray-900">{vehicle.mileage} km</p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
