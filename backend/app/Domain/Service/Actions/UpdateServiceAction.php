<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Services\ServiceService;
use App\Domain\Service\Models\Service;
use App\Services\DataMappingService;

class UpdateServiceAction
{
    public function __construct(
        private ServiceService $serviceService,
        private DataMappingService $dataMappingService
    ) {}

    public function execute(int $id, array $data): ?Service
    {
        $mappedData = $this->dataMappingService->mapServiceFields($data);

        return $this->serviceService->updateService($id, $mappedData);
    }
}
