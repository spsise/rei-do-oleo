<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceItem;
use Illuminate\Database\Eloquent\Collection;

interface ServiceItemRepositoryInterface
{
    public function findByService(int $serviceId): Collection;

    public function findById(int $serviceId, int $itemId): ?ServiceItem;

    public function create(array $data): ServiceItem;

    public function update(ServiceItem $item, array $data): ServiceItem;

    public function delete(ServiceItem $item): bool;

    public function deleteByService(int $serviceId): int;

    public function bulkCreate(int $serviceId, array $items): Collection;

    public function bulkUpdate(int $serviceId, array $items): Collection;

    public function getServiceItemsWithRelations(int $serviceId): Collection;
}
