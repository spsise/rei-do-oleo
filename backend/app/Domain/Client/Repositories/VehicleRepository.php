<?php

namespace App\Domain\Client\Repositories;

use App\Domain\Client\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function findByLicensePlate(string $plate): ?Vehicle
    {
        return Vehicle::where('license_plate', $plate)->with('client')->first();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Vehicle::class)
            ->allowedFilters([
                AllowedFilter::partial('license_plate'),
                AllowedFilter::partial('brand'),
                AllowedFilter::partial('model'),
                AllowedFilter::exact('client_id'),
            ])
            ->allowedSorts(['created_at', 'last_service'])
            ->with(['client'])
            ->paginate($perPage);
    }

    public function getByClient(int $clientId): Collection
    {
        return Vehicle::where('client_id', $clientId)->get();
    }

    public function updateMileage(int $vehicleId, int $newMileage): bool
    {
        return Vehicle::where('id', $vehicleId)->update(['mileage' => $newMileage]);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = Vehicle::with(['client']);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?Vehicle
    {
        return Vehicle::with(['client', 'services'])->find($id);
    }

    public function findById(int $id): ?Vehicle
    {
        return $this->find($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(int $id, array $data): ?Vehicle
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) return null;

        $vehicle->update($data);
        return $vehicle->fresh(['client']);
    }

    public function delete(int $id): bool
    {
        $vehicle = Vehicle::find($id);
        return $vehicle ? $vehicle->delete() : false;
    }
}
