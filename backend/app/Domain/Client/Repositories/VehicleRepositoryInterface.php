<?php

namespace App\Domain\Client\Repositories;

use App\Domain\Client\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function findByLicensePlate(string $plate): ?Vehicle;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function getByClient(int $clientId): Collection;

    public function updateMileage(int $vehicleId, int $newMileage): bool;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Vehicle;

    public function findById(int $id): ?Vehicle;

    public function create(array $data): Vehicle;

    public function update(int $id, array $data): ?Vehicle;

    public function delete(int $id): bool;

    /**
     * Get popular vehicle brands with statistics
     */
    public function getPopularBrands(int $limit = 10, ?int $serviceCenterId = null): Collection;

    /**
     * Get vehicle chart data for dashboard
     */
    public function getVehicleChartData(string $period = '30d', ?int $serviceCenterId = null): array;

    /**
     * Get recent vehicles with service information
     */
    public function getRecentVehicles(int $limit = 10, ?int $serviceCenterId = null): Collection;

    /**
     * Get vehicles with service statistics
     */
    public function getVehiclesWithServiceStats(int $limit = 10, ?int $serviceCenterId = null): Collection;
}
