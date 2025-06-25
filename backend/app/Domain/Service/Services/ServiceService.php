<?php

namespace App\Domain\Service\Services;

use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ClientRepositoryInterface $clientRepository,
        private VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function createService(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            // Validate client and vehicle exist
            $client = $this->clientRepository->find($data['client_id']);
            $vehicle = $this->vehicleRepository->find($data['vehicle_id']);

            if (!$client) {
                throw new \InvalidArgumentException('Cliente não encontrado');
            }

            if (!$vehicle) {
                throw new \InvalidArgumentException('Veículo não encontrado');
            }

            // Ensure vehicle belongs to client
            if ($vehicle->client_id !== $client->id) {
                throw new \InvalidArgumentException('Veículo não pertence ao cliente informado');
            }

            $service = $this->serviceRepository->createService($data);

            // Clear related caches
            $this->clearServiceCaches($service);

            return $service;
        });
    }

    public function updateServiceStatus(int $serviceId, string $status): bool
    {
        $result = $this->serviceRepository->updateServiceStatus($serviceId, $status);

        if ($result) {
            $service = $this->serviceRepository->find($serviceId);
            $this->clearServiceCaches($service);
        }

        return $result;
    }

    public function startService(int $serviceId): bool
    {
        return $this->updateServiceStatus($serviceId, 'in_progress');
    }

    public function completeService(int $serviceId): bool
    {
        return $this->updateServiceStatus($serviceId, 'completed');
    }

    public function cancelService(int $serviceId): bool
    {
        return $this->updateServiceStatus($serviceId, 'cancelled');
    }

    public function addServiceItems(int $serviceId, array $items): void
    {
        $service = $this->serviceRepository->find($serviceId);

        if (!$service) {
            throw new \InvalidArgumentException('Serviço não encontrado');
        }

        $this->serviceRepository->addServiceItems($service, $items);
        $this->clearServiceCaches($service);
    }

    public function getServicesByDateRange(string $startDate, string $endDate): Collection
    {
        $cacheKey = "services_range_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
            return $this->serviceRepository->getServicesByDateRange($startDate, $endDate);
        });
    }

    public function getServicesByClient(int $clientId): Collection
    {
        return $this->serviceRepository->getServicesByClient($clientId);
    }

    public function getServicesByCenter(int $serviceCenterId): Collection
    {
        return Cache::remember(
            "services_center_{$serviceCenterId}",
            1800,
            fn() => $this->serviceRepository->getServicesByCenter($serviceCenterId)
        );
    }

    public function searchServices(array $filters): LengthAwarePaginator
    {
        return $this->serviceRepository->searchByFilters($filters);
    }

    public function findService(int $id): ?Service
    {
        return $this->serviceRepository->find($id);
    }

    public function updateService(int $id, array $data): ?Service
    {
        $service = $this->serviceRepository->update($id, $data);

        if ($service) {
            $this->clearServiceCaches($service);
        }

        return $service;
    }

    public function deleteService(int $id): bool
    {
        $service = $this->serviceRepository->find($id);

        if ($service) {
            $this->clearServiceCaches($service);
        }

        return $this->serviceRepository->delete($id);
    }

    public function getDashboardMetrics(int $serviceCenterId = null, string $period = 'today'): array
    {
        $cacheKey = "dashboard_metrics_{$serviceCenterId}_{$period}";

        return Cache::remember($cacheKey, 1800, function () use ($serviceCenterId, $period) {
            $query = Service::query();

            if ($serviceCenterId) {
                $query->byServiceCenter($serviceCenterId);
            }

            // Apply period filter
            switch ($period) {
                case 'today':
                    $query->whereDate('scheduled_at', today());
                    break;
                case 'week':
                    $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('scheduled_at', now()->month)
                          ->whereYear('scheduled_at', now()->year);
                    break;
            }

            $services = $query->with(['serviceStatus'])->get();

            return [
                'total_services' => $services->count(),
                'scheduled' => $services->where('serviceStatus.name', 'scheduled')->count(),
                'in_progress' => $services->where('serviceStatus.name', 'in_progress')->count(),
                'completed' => $services->where('serviceStatus.name', 'completed')->count(),
                'cancelled' => $services->where('serviceStatus.name', 'cancelled')->count(),
                'total_revenue' => $services->where('serviceStatus.name', 'completed')
                                          ->sum('final_amount'),
                'average_service_time' => $services->where('serviceStatus.name', 'completed')
                                                  ->avg('duration_in_minutes'),
            ];
        });
    }

    public function getServiceReport(array $filters): array
    {
        $services = $this->serviceRepository->searchByFilters($filters);

        $stats = [
            'total_services' => $services->total(),
            'total_revenue' => 0,
            'average_ticket' => 0,
            'services_by_status' => [],
            'services_by_center' => [],
            'top_clients' => [],
        ];

        // Calculate stats from services
        $allServices = $services->getCollection();

        $stats['total_revenue'] = $allServices->sum('final_amount');
        $stats['average_ticket'] = $allServices->count() > 0
            ? $stats['total_revenue'] / $allServices->count()
            : 0;

        $stats['services_by_status'] = $allServices->groupBy('serviceStatus.name')
            ->map->count()
            ->toArray();

        $stats['services_by_center'] = $allServices->groupBy('serviceCenter.name')
            ->map->count()
            ->toArray();

        return $stats;
    }

    private function clearServiceCaches(?Service $service): void
    {
        if (!$service) {
            return;
        }

        // Clear general caches
        Cache::forget("services_center_{$service->service_center_id}");
        Cache::forget("client_services_{$service->client_id}");

        // Clear dashboard metrics
        Cache::forget("dashboard_metrics_{$service->service_center_id}_today");
        Cache::forget("dashboard_metrics_{$service->service_center_id}_week");
        Cache::forget("dashboard_metrics_{$service->service_center_id}_month");

        // Clear date range caches (could be optimized)
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        Cache::forget("services_range_{$yesterday}_{$today}");
    }
}
