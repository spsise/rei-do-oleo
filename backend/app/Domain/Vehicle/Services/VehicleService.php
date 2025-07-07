<?php

namespace App\Domain\Vehicle\Services;

use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class VehicleService
{
    public function __construct(
        private VehicleRepositoryInterface $vehicleRepository
    ) {}

    /**
     * Get dashboard statistics for vehicles
     */
    public function getDashboardStats(?int $serviceCenterId = null): array
    {
        $cacheKey = "vehicle_dashboard_stats_{$serviceCenterId}";
        
        return Cache::remember($cacheKey, 300, function () use ($serviceCenterId) {
            // Total vehicles
            $totalVehicles = $this->vehicleRepository->getAllPaginated(1)->total();
            
            // Vehicles registered this month
            $vehiclesThisMonth = $this->vehicleRepository->searchByFilters([
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'per_page' => 1
            ])->total();
            
            // Most popular brands
            $popularBrands = $this->getPopularBrands($serviceCenterId);
            
            return [
                'total_vehicles' => $totalVehicles,
                'vehicles_this_month' => $vehiclesThisMonth,
                'popular_brands' => $popularBrands,
            ];
        });
    }

    /**
     * Get most popular brands
     */
    private function getPopularBrands(?int $serviceCenterId = null): array
    {
        $popularBrands = $this->vehicleRepository->getPopularBrands(5, $serviceCenterId);
        
        return $popularBrands->map(function ($brand) {
            return [
                'name' => $brand->brand,
                'vehicle_count' => (int) $brand->vehicle_count,
                'client_count' => (int) $brand->client_count,
                'service_count' => (int) $brand->service_count,
            ];
        })->toArray();
    }

    /**
     * Get vehicle chart data
     */
    public function getVehiclesChartData(?int $serviceCenterId = null, string $period = '30d'): array
    {
        $cacheKey = "vehicle_chart_data_{$serviceCenterId}_{$period}";
        
        return Cache::remember($cacheKey, 600, function () use ($serviceCenterId, $period) {
            return $this->vehicleRepository->getVehicleChartData($period, $serviceCenterId);
        });
    }

    /**
     * Obter veículos recentes
     */
    public function getRecentVehicles(?int $serviceCenterId = null, int $limit = 10): array
    {
        $cacheKey = "recent_vehicles_{$serviceCenterId}_{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($serviceCenterId, $limit) {
            $recentVehicles = $this->vehicleRepository->getRecentVehicles($limit, $serviceCenterId);
            
            return $recentVehicles->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'plate' => $vehicle->license_plate,
                    'client_name' => $vehicle->client?->name ?? 'Cliente não encontrado',
                    'last_service' => $vehicle->last_service_date ? date('Y-m-d', strtotime($vehicle->last_service_date)) : null,
                    'total_services' => (int) $vehicle->total_services,
                    'color' => $vehicle->color,
                    'mileage' => $vehicle->mileage,
                ];
            })->toArray();
        });
    }

    /**
     * Get vehicles with service statistics
     */
    public function getVehiclesWithServiceStats(?int $serviceCenterId = null, int $limit = 10): array
    {
        $vehicles = $this->vehicleRepository->getVehiclesWithServiceStats($limit, $serviceCenterId);
        
        return $vehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'license_plate' => $vehicle->license_plate,
                'color' => $vehicle->color,
                'mileage' => $vehicle->mileage,
                'client_name' => $vehicle->client?->name ?? 'Cliente não encontrado',
                'total_services' => (int) $vehicle->total_services,
                'total_spent' => (float) $vehicle->total_spent,
                'average_service_value' => (float) $vehicle->average_service_value,
                'last_service_date' => $vehicle->last_service_date ? date('Y-m-d', strtotime($vehicle->last_service_date)) : null,
                'fuel_type' => $vehicle->fuel_type,
            ];
        })->toArray();
    }

    /**
     * Get vehicle performance metrics
     */
    public function getVehiclePerformanceMetrics(?int $serviceCenterId = null): array
    {
        $cacheKey = "vehicle_performance_metrics_{$serviceCenterId}";
        
        return Cache::remember($cacheKey, 600, function () use ($serviceCenterId) {
            $vehicles = $this->vehicleRepository->getVehiclesWithServiceStats(50, $serviceCenterId);
            
            $totalVehicles = $vehicles->count();
            $totalServices = $vehicles->sum('total_services');
            $totalSpent = $vehicles->sum('total_spent');
            
            $vehiclesWithServices = $vehicles->filter(function ($vehicle) {
                return $vehicle->total_services > 0;
            });
            
            $averageServicesPerVehicle = $totalVehicles > 0 ? $totalServices / $totalVehicles : 0;
            $averageSpentPerVehicle = $totalVehicles > 0 ? $totalSpent / $totalVehicles : 0;
            $averageSpentPerService = $totalServices > 0 ? $totalSpent / $totalServices : 0;
            
            return [
                'total_vehicles' => $totalVehicles,
                'total_services' => (int) $totalServices,
                'total_spent' => (float) $totalSpent,
                'vehicles_with_services' => $vehiclesWithServices->count(),
                'average_services_per_vehicle' => round($averageServicesPerVehicle, 2),
                'average_spent_per_vehicle' => round($averageSpentPerVehicle, 2),
                'average_spent_per_service' => round($averageSpentPerService, 2),
            ];
        });
    }

    /**
     * Clear vehicle cache
     */
    public function clearCache(?int $serviceCenterId = null): void
    {
        Cache::forget("vehicle_dashboard_stats_{$serviceCenterId}");
        Cache::forget("vehicle_chart_data_{$serviceCenterId}_7d");
        Cache::forget("vehicle_chart_data_{$serviceCenterId}_30d");
        Cache::forget("vehicle_chart_data_{$serviceCenterId}_90d");
        Cache::forget("vehicle_chart_data_{$serviceCenterId}_1y");
        Cache::forget("recent_vehicles_{$serviceCenterId}_10");
        Cache::forget("vehicle_performance_metrics_{$serviceCenterId}");
    }
} 