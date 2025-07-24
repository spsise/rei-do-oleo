<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Services\ServiceService;

class DeleteServiceAction
{
    public function __construct(
        private ServiceService $serviceService
    ) {}

    public function execute(int $id): bool
    {
        return $this->serviceService->deleteService($id);
    }
}
