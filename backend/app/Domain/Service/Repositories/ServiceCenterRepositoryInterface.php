<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ServiceCenterRepositoryInterface
{
    public function getAllActive(): Collection;

    public function findByCode(string $code): ?ServiceCenter;

    public function getByRegion(string $state, ?string $city = null): Collection;

    public function getMainBranch(): ?ServiceCenter;

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10): Collection;

    public function getWithManagerInfo(): Collection;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?ServiceCenter;

    public function create(array $data): ServiceCenter;

    public function update(int $id, array $data): ?ServiceCenter;

    public function delete(int $id): bool;
}
