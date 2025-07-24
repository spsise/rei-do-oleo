<?php

namespace App\Domain\Service\Actions;

use App\Domain\Service\Services\ServiceService;

class UpdateServiceStatusAction
{
    public function __construct(
        private ServiceService $serviceService
    ) {}

    public function execute(int $id, array $data): bool
    {
        $statusId = $data['status_id'];
        $notes = $data['notes'] ?? null;

        try {
            $this->serviceService->updateStatus($id, $statusId, $notes);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
