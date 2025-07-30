<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Services\ServiceService;
use App\Domain\Service\Services\ServiceItemsOperationService;
use App\Domain\Service\Models\Service;
use App\Services\DataMappingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateServiceWithItemsAction
{
    public function __construct(
        private ServiceService $serviceService,
        private ServiceItemsOperationService $serviceItemsOperationService,
        private DataMappingService $dataMappingService
    ) {}

    /**
     * Execute the action to update service with items in a single transaction
     *
     * @param int $serviceId
     * @param array $data
     * @return Service|null
     * @throws \Exception
     */
    public function execute(int $serviceId, array $data): ?Service
    {
        return DB::transaction(function () use ($serviceId, $data) {
            try {
                // Extract service and items data
                $serviceData = $data['service'] ?? [];
                $itemsData = $data['items'] ?? [];

                // Map service data using existing service
                $mappedServiceData = $this->dataMappingService->mapServiceFields($serviceData);

                // Update service first
                $service = $this->serviceService->updateService($serviceId, $mappedServiceData);

                if (!$service) {
                    throw new \InvalidArgumentException('Service not found');
                }

                // Process items based on operation
                $this->serviceItemsOperationService->executeOperation($serviceId, $itemsData);

                // Recalculate service totals
                $this->recalculateServiceTotals($serviceId);

                // Return fresh service with relationships
                return $this->serviceService->findService($serviceId);

            } catch (\Exception $e) {
                Log::error('Error updating service with items', [
                    'service_id' => $serviceId,
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);

                throw $e;
            }
        });
    }



    /**
     * Recalculate service totals after items update
     *
     * @param int $serviceId
     * @return void
     */
    private function recalculateServiceTotals(int $serviceId): void
    {
        $service = $this->serviceService->findService($serviceId);

        if ($service) {
            $service->calculateTotals();
        }
    }
}
