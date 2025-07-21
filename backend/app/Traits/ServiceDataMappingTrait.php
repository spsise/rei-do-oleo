<?php

namespace App\Traits;

use App\Services\DataMappingService;
use Illuminate\Support\Facades\Auth;

trait ServiceDataMappingTrait
{
    /**
     * Map service data for creation with technician defaults
     */
    protected function mapServiceDataForCreation(array $data): array
    {
        $dataMappingService = app(DataMappingService::class);
        return $dataMappingService->mapServiceDataForCreation($data, Auth::user()->id);
    }

    /**
     * Map service data for update (without technician defaults)
     */
    protected function mapServiceDataForUpdate(array $data): array
    {
        $dataMappingService = app(DataMappingService::class);
        return $dataMappingService->mapServiceFields($data);
    }

    /**
     * Map service data from backend to frontend format
     */
    protected function mapServiceDataToFrontend(array $data): array
    {
        $dataMappingService = app(DataMappingService::class);
        return $dataMappingService->mapServiceFieldsToFrontend($data);
    }
}
