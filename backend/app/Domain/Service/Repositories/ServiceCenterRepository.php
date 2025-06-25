<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ServiceCenterRepository implements ServiceCenterRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return ServiceCenter::getActiveCached();
    }

    public function findByCode(string $code): ?ServiceCenter
    {
        return ServiceCenter::findByCode($code);
    }

    public function getByRegion(string $state, ?string $city = null): Collection
    {
        return ServiceCenter::getByRegionCached($state, $city);
    }

    public function getMainBranch(): ?ServiceCenter
    {
        return ServiceCenter::getMainBranch();
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        return ServiceCenter::nearby($latitude, $longitude, $radiusKm)
                           ->active()
                           ->with('manager')
                           ->get();
    }

    public function getWithManagerInfo(): Collection
    {
        return ServiceCenter::active()->with('manager')->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(ServiceCenter::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('state'),
                AllowedFilter::exact('city'),
                AllowedFilter::exact('active'),
                AllowedFilter::exact('is_main_branch'),
            ])
            ->allowedSorts(['name', 'created_at', 'opening_date'])
            ->with(['manager'])
            ->paginate($perPage);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = ServiceCenter::with(['manager']);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['state'])) {
            $query->byRegion($filters['state'], $filters['city'] ?? null);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?ServiceCenter
    {
        return ServiceCenter::with(['manager', 'users', 'services'])->find($id);
    }

    public function create(array $data): ServiceCenter
    {
        return ServiceCenter::create($data);
    }

    public function update(int $id, array $data): ?ServiceCenter
    {
        $serviceCenter = ServiceCenter::find($id);
        if (!$serviceCenter) return null;

        $serviceCenter->update($data);
        return $serviceCenter->fresh(['manager']);
    }

    public function delete(int $id): bool
    {
        $serviceCenter = ServiceCenter::find($id);
        return $serviceCenter ? $serviceCenter->delete() : false;
    }
}
