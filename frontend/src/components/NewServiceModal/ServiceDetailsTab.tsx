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
  onServiceDataChange: (data: Partial<CreateTechnicianServiceData>) => void;
  vehicles: TechnicianVehicle[];
  isReadOnly?: boolean;
}

export const ServiceDetailsTab: React.FC<ServiceDetailsTabProps> = ({
  serviceData,
  onServiceDataChange,
  vehicles,
  isReadOnly = false,
}) => {
  return (
    <div className="space-y-6">
      {/* Seleção de Veículo */}
      <VehicleSelector
        vehicles={vehicles}
        selectedVehicleId={serviceData.vehicle_id}
        onVehicleChange={(vehicleId) =>
          onServiceDataChange({
            vehicle_id: vehicleId,
          })
        }
      />

      {/* Descrição do Serviço */}
      <ServiceDescriptionField
        description={serviceData.description}
        onChange={(description) =>
          onServiceDataChange({
            description,
          })
        }
      />

      {/* Grid de Campos */}
      <ServiceFieldsGrid
        serviceData={serviceData}
        onServiceDataChange={onServiceDataChange}
        isReadOnly={isReadOnly}
      />

      {/* Observações */}
      <ServiceNotesFields
        notes={serviceData.notes}
        observations={serviceData.observations}
        onChange={(field, value) =>
          onServiceDataChange({
            [field]: value,
          })
        }
      />

      {/* Dicas */}
      <ServiceTips />
    </div>
  );
};
