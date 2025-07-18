import React from 'react';
import {
  type CreateTechnicianServiceData,
  type TechnicianVehicle,
} from '../../types/technician';
import { ServiceDescriptionField } from './ServiceDescriptionField';
import { ServiceFieldsGrid } from './ServiceFieldsGrid';
import { ServiceNotesFields } from './ServiceNotesFields';
import { ServiceTips } from './ServiceTips';
import { VehicleSelector } from './VehicleSelector';

interface ServiceDetailsTabProps {
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: CreateTechnicianServiceData) => void;
  vehicles: TechnicianVehicle[];
}

export const ServiceDetailsTab: React.FC<ServiceDetailsTabProps> = ({
  serviceData,
  onServiceDataChange,
  vehicles,
}) => {
  return (
    <div className="space-y-6">
      {/* Seleção de Veículo */}
      <VehicleSelector
        vehicles={vehicles}
        selectedVehicleId={serviceData.vehicle_id}
        onVehicleChange={(vehicleId) =>
          onServiceDataChange({
            ...serviceData,
            vehicle_id: vehicleId,
          })
        }
      />

      {/* Descrição do Serviço */}
      <ServiceDescriptionField
        description={serviceData.description}
        onChange={(description) =>
          onServiceDataChange({
            ...serviceData,
            description,
          })
        }
      />

      {/* Grid de Campos */}
      <ServiceFieldsGrid
        serviceData={serviceData}
        onServiceDataChange={onServiceDataChange}
      />

      {/* Observações */}
      <ServiceNotesFields
        notes={serviceData.notes}
        observations={serviceData.observations}
        onChange={(field, value) =>
          onServiceDataChange({
            ...serviceData,
            [field]: value,
          })
        }
      />

      {/* Dicas */}
      <ServiceTips />
    </div>
  );
};
