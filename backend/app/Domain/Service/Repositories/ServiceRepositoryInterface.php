<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    public function createService(array $serviceData): Service;

    public function addServiceItems(Service $service, array $items): void;

    public function updateServiceStatus(int $serviceId, string $status): bool;

    public function getServicesByDateRange(string $startDate, string $endDate): Collection;

    public function getServicesByClient(int $clientId): Collection;

    public function getServicesByCenter(int $serviceCenterId): Collection;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Service;

    public function create(array $data): Service;

    public function update(int $id, array $data): ?Service;

    public function delete(int $id): bool;
}
