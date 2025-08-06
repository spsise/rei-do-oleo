<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Repositories\ServiceRepositoryInterface;

class GetServiceStatsAction
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function execute(?int $serviceCenterId = null): array
    {
        return $this->serviceRepository->getDashboardStats($serviceCenterId);
    }
}
