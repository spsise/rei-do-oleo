<?php

namespace App\Domain\Service\Repositories;

use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Models\ServiceStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function createService(array $serviceData): Service
    {
        return DB::transaction(function () use ($serviceData) {
            $service = Service::create($serviceData);

            // Add service items if provided
            if (isset($serviceData['items']) && is_array($serviceData['items'])) {
                $this->addServiceItems($service, $serviceData['items']);
            }

            return $service->load([
                'client',
                'vehicle',
                'serviceCenter',
                'serviceStatus',
                'serviceItems.product'
            ]);
        });
    }

    public function addServiceItems(Service $service, array $items): void
    {
        foreach ($items as $item) {
            ServiceItem::create([
                'service_id' => $service->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        // Recalculate service totals
        $service->calculateTotals();
    }

    public function updateServiceStatus(int $serviceId, string $status): bool
    {
        $service = Service::find($serviceId);
        $serviceStatus = ServiceStatus::findByName($status);

        if (!$service || !$serviceStatus) {
            return false;
        }

        $updateData = ['service_status_id' => $serviceStatus->id];

        // Update timestamps based on status
        switch ($status) {
            case 'in_progress':
                $updateData['started_at'] = now();
                break;
            case 'completed':
                $updateData['completed_at'] = now();
                if ($service->mileage_at_service) {
                    $service->vehicle->updateServiceInfo($service->mileage_at_service);
                }
                break;
        }

        return $service->update($updateData);
    }

    public function getServicesByDateRange(string $startDate, string $endDate): Collection
    {
        return Service::byPeriod($startDate, $endDate)
            ->with([
                'client',
                'vehicle',
                'serviceCenter',
                'serviceStatus',
                'serviceItems.product'
            ])
            ->orderBy('scheduled_at', 'desc')
            ->get();
    }

    public function getServicesByClient(int $clientId): Collection
    {
        return Service::getServicesByClient($clientId);
    }

    public function getServicesByCenter(int $serviceCenterId): Collection
    {
        return Service::byServiceCenter($serviceCenterId)
            ->with([
                'client',
                'vehicle',
                'serviceStatus',
                'serviceItems.product'
            ])
            ->orderBy('scheduled_at', 'desc')
            ->get();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Service::class)
            ->allowedFilters([
                AllowedFilter::exact('service_center_id'),
                AllowedFilter::exact('service_status_id'),
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('vehicle_id'),
                AllowedFilter::exact('active'),
                AllowedFilter::scope('by_period'),
            ])
            ->allowedSorts(['scheduled_at', 'created_at', 'service_number'])
            ->with([
                'client',
                'vehicle',
                'serviceCenter',
                'serviceStatus',
                'serviceItems.product'
            ])
            ->paginate($perPage);
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = Service::query()->with([
            'client',
            'vehicle',
            'serviceCenter',
            'serviceStatus',
            'serviceItems.product'
        ]);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                      $vehicleQuery->where('license_plate', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['service_center_id'])) {
            $query->byServiceCenter($filters['service_center_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byPeriod($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('scheduled_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?Service
    {
        return Service::with([
            'client',
            'vehicle',
            'serviceCenter',
            'serviceStatus',
            'paymentMethod',
            'serviceItems.product.category'
        ])->find($id);
    }

    public function create(array $data): Service
    {
        return $this->createService($data);
    }

    public function update(int $id, array $data): ?Service
    {
        $service = Service::find($id);

        if (!$service) {
            return null;
        }

        return DB::transaction(function () use ($service, $data) {
            $service->update($data);

            // Update service items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Remove existing items
                $service->serviceItems()->delete();

                // Add new items
                $this->addServiceItems($service, $data['items']);
            }

            return $service->fresh([
                'client',
                'vehicle',
                'serviceCenter',
                'serviceStatus',
                'serviceItems.product'
            ]);
        });
    }

    public function delete(int $id): bool
    {
        $service = Service::find($id);

        if (!$service) {
            return false;
        }

        return $service->delete();
    }
}
