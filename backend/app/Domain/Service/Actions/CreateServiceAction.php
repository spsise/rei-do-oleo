<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Services\ServiceService;
use App\Domain\Service\Models\Service;
use App\Services\DataMappingService;
use Illuminate\Support\Facades\Auth;

class CreateServiceAction
{
    public function __construct(
        private ServiceService $serviceService,
        private DataMappingService $dataMappingService
    ) {}

    public function execute(array $data): Service
    {
        // Map frontend field names to backend field names
        $mappedData = $this->dataMappingService->mapServiceFields($data);

        // Add user_id to the mapped data
        $mappedData['user_id'] = Auth::user()->id;

        return $this->serviceService->createService($mappedData);
    }
}
