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
use Illuminate\Support\Facades\Cache;

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
                'technician',
                'attendant',
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
                'discount' => $item['discount'] ?? 0,
                'total_price' => $item['quantity'] * $item['unit_price'] * (1 - ($item['discount'] ?? 0) / 100),
                'notes' => $item['notes'] ?? null,
            ]);
        }

        // Recalculate service totals
        $service->calculateTotals();
    }

    public function updateServiceStatus(int $serviceId, string $status, ?string $notes = null): bool
    {
        $service = Service::find($serviceId);
        $serviceStatus = ServiceStatus::findByName($status);

        if (!$service || !$serviceStatus) {
            return false;
        }

        $updateData = ['service_status_id' => $serviceStatus->id];

        // Add notes if provided
        if ($notes) {
            $updateData['notes'] = $notes;
        }

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
                'technician',
                'attendant',
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
                'technician',
                'attendant',
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
                'technician',
                'attendant',
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
            'technician',
            'attendant',
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
            $query->whereHas('serviceStatus', function ($statusQuery) use ($filters) {
                $statusQuery->where('name', $filters['status']);
            });
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

        if (isset($filters['technician_id'])) {
            $query->where('technician_id', $filters['technician_id']);
        }

        return $query->orderBy('scheduled_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): ?Service
    {
        // Usar cache para detalhes de serviço
        $cacheKey = "service_details_{$id}";

        return Cache::remember($cacheKey, 300, function () use ($id) {
            // Otimizar eager loading - carregar apenas o necessário
            $service = Service::with([
                'client:id,name,phone,document',
                'vehicle:id,license_plate,brand,model,year',
                'serviceCenter:id,name,code',
                'serviceStatus:id,name,color',
                'paymentMethod:id,name',
                'technician:id,name',
                'attendant:id,name',
                'serviceItems' => function ($query) {
                    $query->with([
                        'product:id,name,sku,category_id',
                        'product.category:id,name'
                    ]);
                }
            ])->find($id);

            // Se não encontrar, retornar null
            if (!$service) {
                return null;
            }

            return $service;
        });
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

    // Métodos para o TechnicianController
    public function getRecentByClient(int $clientId, int $limit = 5): Collection
    {
        return Service::where('client_id', $clientId)
            ->with(['client', 'vehicle', 'serviceStatus', 'serviceItems.product.category'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTodayServicesCount(int $technicianId): int
    {
        return Service::where('technician_id', $technicianId)
            ->whereDate('created_at', today())
            ->count();
    }

    public function getPendingServicesCount(int $technicianId): int
    {
        return Service::where('technician_id', $technicianId)
            ->whereHas('serviceStatus', function ($query) {
                $query->whereIn('name', ['pending', 'in_progress']);
            })
            ->count();
    }

    public function getCompletedTodayCount(int $technicianId): int
    {
        return Service::where('technician_id', $technicianId)
            ->whereHas('serviceStatus', function ($query) {
                $query->where('name', 'completed');
            })
            ->whereDate('completed_at', today())
            ->count();
    }

    public function getRecentByTechnician(int $technicianId, int $limit = 10): Collection
    {
        return Service::where('technician_id', $technicianId)
            ->with(['client', 'vehicle', 'serviceStatus'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByVehicle(int $vehicleId): Collection
    {
        return Service::where('vehicle_id', $vehicleId)
            ->with(['client', 'vehicle', 'serviceStatus', 'technician'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByTechnician(int $technicianId): Collection
    {
        return Service::where('technician_id', $technicianId)
            ->with(['client', 'vehicle', 'serviceStatus'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByServiceNumber(string $serviceNumber): ?Service
    {
        return Service::where('service_number', $serviceNumber)
            ->with([
                'client',
                'vehicle',
                'serviceCenter',
                'serviceStatus',
                'technician',
                'attendant',
                'serviceItems.product'
            ])
            ->first();
    }

    public function getDashboardStats(?int $serviceCenterId = null): array
    {
        $query = Service::query();

        if ($serviceCenterId) {
            $query->where('service_center_id', $serviceCenterId);
        }

        $services = $query->with(['serviceStatus'])->get();

        return [
            'total_services' => $services->count(),
            'services_in_progress' => $services->where('serviceStatus.name', 'in_progress')->count(),
            'services_completed' => $services->where('serviceStatus.name', 'completed')->count(),
            'services_cancelled' => $services->where('serviceStatus.name', 'cancelled')->count(),
            'total_revenue' => $services->where('serviceStatus.name', 'completed')->sum('final_amount'),
            'average_service_duration' => $services->whereNotNull('started_at')->whereNotNull('completed_at')->avg(function ($service) {
                return $service->started_at->diffInMinutes($service->completed_at);
            }) ?? 0,
        ];
    }
}
