<?php

namespace App\Services;

class DataMappingService
{
    /**
     * Map frontend field names to backend field names for services
     */
    public function mapServiceFields(array $data): array
    {
        $mappings = [
            'scheduled_date' => 'scheduled_at',
            'finished_at' => 'completed_at',
            'status_id' => 'service_status_id',
            'mileage' => 'mileage_at_service',
            'discount' => 'discount_amount',
            'total_amount' => 'final_amount',
        ];

        foreach ($mappings as $frontendField => $backendField) {
            if (isset($data[$frontendField])) {
                $data[$backendField] = $data[$frontendField];
                unset($data[$frontendField]);
            }
        }

        return $data;
    }

    /**
     * Map backend field names to frontend field names for services
     */
    public function mapServiceFieldsToFrontend(array $data): array
    {
        $mappings = [
            'scheduled_at' => 'scheduled_date',
            'completed_at' => 'finished_at',
            'service_status_id' => 'status_id',
            'mileage_at_service' => 'mileage',
            'discount_amount' => 'discount',
            'final_amount' => 'total_amount',
        ];

        foreach ($mappings as $backendField => $frontendField) {
            if (isset($data[$backendField])) {
                $data[$frontendField] = $data[$backendField];
                unset($data[$backendField]);
            }
        }

        return $data;
    }

    /**
     * Set default user assignments for technician context
     */
    public function setTechnicianDefaults(array $data, int $userId): array
    {
        // Set user_id as the current user
        $data['user_id'] = $userId;

        // Set technician as the current user if not provided
        if (!isset($data['technician_id']) || !$data['technician_id']) {
            $data['technician_id'] = $userId;
        }

        // Set attendant as the current user if not provided
        if (!isset($data['attendant_id']) || !$data['attendant_id']) {
            $data['attendant_id'] = $userId;
        }

        return $data;
    }

    /**
     * Map service data with all transformations
     */
    public function mapServiceDataForCreation(array $data, int $userId): array
    {
        // First set technician defaults
        $data = $this->setTechnicianDefaults($data, $userId);

        // Then map field names
        $data = $this->mapServiceFields($data);

        return $data;
    }
}
