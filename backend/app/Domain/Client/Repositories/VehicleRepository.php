<?php

namespace App\Domain\Client\Repositories;

use App\Domain\Client\Models\Vehicle;
use App\Domain\Service\Models\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
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

    public function getPopularBrands(int $limit = 10, ?int $serviceCenterId = null): Collection
    {
        $query = Vehicle::select([
                'vehicles.brand',
                DB::raw('COUNT(vehicles.id) as vehicle_count'),
                DB::raw('COUNT(DISTINCT vehicles.client_id) as client_count'),
                DB::raw('COUNT(services.id) as service_count')
            ])
            ->leftJoin('services', 'vehicles.id', '=', 'services.vehicle_id')
            ->whereNotNull('vehicles.brand')
            ->groupBy('vehicles.brand')
            ->orderBy('vehicle_count', 'desc')
            ->orderBy('service_count', 'desc')
            ->limit($limit);

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->get();
    }

    public function getVehicleChartData(string $period = '30d', ?int $serviceCenterId = null): array
    {
        $dateFilter = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30)
        };

        $query = Vehicle::select([
                'vehicles.brand',
                DB::raw('COUNT(vehicles.id) as vehicle_count'),
                DB::raw('COUNT(services.id) as service_count')
            ])
            ->leftJoin('services', 'vehicles.id', '=', 'services.vehicle_id')
            ->whereNotNull('vehicles.brand')
            ->where('vehicles.created_at', '>=', $dateFilter)
            ->groupBy('vehicles.brand')
            ->orderBy('vehicle_count', 'desc')
            ->orderBy('service_count', 'desc');

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        $brands = $query->get();
        $totalVehicles = $brands->sum('vehicle_count');

        return $brands->map(function ($brand) use ($totalVehicles) {
            return [
                'name' => $brand->brand,
                'count' => (int) $brand->vehicle_count,
                'percentage' => $totalVehicles > 0 ? round(($brand->vehicle_count / $totalVehicles) * 100, 1) : 0,
                'service_count' => (int) $brand->service_count,
            ];
        })->toArray();
    }

    public function getRecentVehicles(int $limit = 10, ?int $serviceCenterId = null): Collection
    {
        $query = Vehicle::select([
                'vehicles.*',
                DB::raw('MAX(services.created_at) as last_service_date'),
                DB::raw('COUNT(services.id) as total_services')
            ])
            ->leftJoin('services', 'vehicles.id', '=', 'services.vehicle_id')
            ->with(['client', 'lastService'])
            ->groupBy('vehicles.id', 'vehicles.client_id', 'vehicles.license_plate', 'vehicles.brand', 'vehicles.model', 'vehicles.year', 'vehicles.color', 'vehicles.fuel_type', 'vehicles.mileage', 'vehicles.last_service', 'vehicles.created_at', 'vehicles.updated_at')
            ->orderBy('vehicles.created_at', 'desc')
            ->limit($limit);

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->get();
    }

    public function getVehiclesWithServiceStats(int $limit = 10, ?int $serviceCenterId = null): Collection
    {
        $query = Vehicle::select([
                'vehicles.*',
                DB::raw('COUNT(services.id) as total_services'),
                DB::raw('SUM(services.final_amount) as total_spent'),
                DB::raw('MAX(services.created_at) as last_service_date'),
                DB::raw('AVG(services.final_amount) as average_service_value')
            ])
            ->leftJoin('services', 'vehicles.id', '=', 'services.vehicle_id')
            ->with(['client', 'lastService'])
            ->groupBy('vehicles.id', 'vehicles.client_id', 'vehicles.license_plate', 'vehicles.brand', 'vehicles.model', 'vehicles.year', 'vehicles.color', 'vehicles.fuel_type', 'vehicles.mileage', 'vehicles.last_service', 'vehicles.created_at', 'vehicles.updated_at')
            ->orderBy('total_services', 'desc')
            ->orderBy('total_spent', 'desc')
            ->limit($limit);

        if ($serviceCenterId) {
            $query->where('services.service_center_id', $serviceCenterId);
        }

        return $query->get();
    }
}
