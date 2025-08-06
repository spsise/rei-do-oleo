<?php

namespace App\Domain\Service\Services;

use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceTemplate;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Repositories\ServiceTemplateRepositoryInterface;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Service\Models\ServiceStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendantServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceTemplateRepositoryInterface $templateRepository,
        private ClientRepositoryInterface $clientRepository,
        private VehicleRepositoryInterface $vehicleRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Create quick service with essential fields only
     */
    public function createQuickService(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            // Validate client and vehicle relationship
            $this->validateClientVehicleRelationship($data['client_id'], $data['vehicle_id']);

            // Get user's service center
            $user = Auth::user();
            $serviceCenterId = $user->service_center_id;

            if (!$serviceCenterId) {
                throw new \InvalidArgumentException('Usuário não está associado a um centro de serviço');
            }

            // Prepare service data
            $serviceData = [
                'client_id' => $data['client_id'],
                'vehicle_id' => $data['vehicle_id'],
                'user_id' => $user->id,
                'service_center_id' => $serviceCenterId,
                'attendant_id' => $user->id,
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'active' => true,
            ];

            // Set default status (scheduled)
            $scheduledStatus = ServiceStatus::findByName('scheduled');
            $serviceData['service_status_id'] = $scheduledStatus ? $scheduledStatus->id : 1;

            // Apply template if provided
            if (isset($data['template_id'])) {
                $template = $this->templateRepository->find($data['template_id']);
                if ($template) {
                    $serviceData = array_merge($serviceData, $this->applyTemplate($template));
                }
            }

            // Override with provided data
            if (isset($data['estimated_duration'])) {
                $serviceData['notes'] = ($serviceData['notes'] ?? '') . "\nDuração estimada: {$data['estimated_duration']} minutos";
            }

            if (isset($data['priority'])) {
                $serviceData['notes'] = ($serviceData['notes'] ?? '') . "\nPrioridade: {$data['priority']}";
            }

            // Create service
            $service = $this->serviceRepository->createService($serviceData);

            // Clear relevant caches
            $this->clearServiceCaches($service);

            return $service;
        });
    }

    /**
     * Create complete service with all available fields
     */
    public function createCompleteService(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            // Validate client and vehicle relationship
            $this->validateClientVehicleRelationship($data['client_id'], $data['vehicle_id']);

            // Get user's service center
            $user = Auth::user();
            $serviceCenterId = $user->service_center_id;

            if (!$serviceCenterId) {
                throw new \InvalidArgumentException('Usuário não está associado a um centro de serviço');
            }

            // Prepare service data
            $serviceData = [
                'client_id' => $data['client_id'],
                'vehicle_id' => $data['vehicle_id'],
                'user_id' => $user->id,
                'service_center_id' => $serviceCenterId,
                'attendant_id' => $user->id,
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'observations' => $data['observations'] ?? null,
                'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
                'technician_id' => $data['technician_id'] ?? null,
                'active' => true,
            ];

            // Set status
            $statusName = $data['status'] ?? 'scheduled';
            $status = ServiceStatus::findByName($statusName);
            $serviceData['service_status_id'] = $status ? $status->id : 1;

            // Create service
            $service = $this->serviceRepository->createService($serviceData);

            // Add service items if provided
            if (isset($data['service_items']) && is_array($data['service_items'])) {
                $this->serviceRepository->addServiceItems($service, $data['service_items']);
            }

            // Clear relevant caches
            $this->clearServiceCaches($service);

            return $service;
        });
    }

    /**
     * Get service templates
     */
    public function getTemplates(?string $category = null): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "service_templates" . ($category ? "_$category" : "");

        return Cache::remember($cacheKey, 3600, function () use ($category) {
            return $this->templateRepository->getActive($category);
        });
    }

    /**
     * Validate service data
     */
    public function validateService(array $data): array
    {
        $validation = [
            'is_valid' => true,
            'warnings' => [],
            'suggestions' => [],
        ];

        // Validate client exists
        $client = $this->clientRepository->find($data['client_id'] ?? 0);
        if (!$client) {
            $validation['is_valid'] = false;
            $validation['warnings'][] = 'Cliente não encontrado';
        }

        // Validate vehicle exists
        $vehicle = $this->vehicleRepository->find($data['vehicle_id'] ?? 0);
        if (!$vehicle) {
            $validation['is_valid'] = false;
            $validation['warnings'][] = 'Veículo não encontrado';
        }

        // Validate client-vehicle relationship
        if ($client && $vehicle && $vehicle->client_id !== $client->id) {
            $validation['is_valid'] = false;
            $validation['warnings'][] = 'Veículo não pertence ao cliente informado';
        }

        // Check for scheduling conflicts
        if (isset($data['scheduled_at']) && isset($data['technician_id'])) {
            $conflicts = $this->checkSchedulingConflicts(
                $data['scheduled_at'],
                $data['technician_id'],
                $data['estimated_duration'] ?? 60
            );

            if (!empty($conflicts)) {
                $validation['warnings'][] = 'Conflito de agendamento detectado';
                $validation['suggestions'][] = 'Considere agendar em outro horário';
            }
        }

        // Check for duplicate services
        if (isset($data['description'])) {
            $duplicates = $this->checkDuplicateServices(
                $data['client_id'] ?? 0,
                $data['vehicle_id'] ?? 0,
                $data['description']
            );

            if (!empty($duplicates)) {
                $validation['warnings'][] = 'Serviço similar já existe';
                $validation['suggestions'][] = 'Verifique se não é um serviço duplicado';
            }
        }

        // Add suggestions based on vehicle history
        if ($vehicle) {
            $suggestions = $this->getVehicleBasedSuggestions($vehicle);
            $validation['suggestions'] = array_merge($validation['suggestions'], $suggestions);
        }

        return $validation;
    }

    /**
     * Get service suggestions based on client/vehicle history
     */
    public function getSuggestions(int $clientId, ?int $vehicleId = null): array
    {
        $suggestions = [
            'recent_services' => [],
            'recommended_services' => [],
            'maintenance_due' => [],
        ];

        // Get recent services
        $recentServices = $this->serviceRepository->getRecentByClient($clientId, 5);
        $suggestions['recent_services'] = $recentServices->pluck('description')->unique()->toArray();

        // Get vehicle-specific suggestions
        if ($vehicleId) {
            $vehicle = $this->vehicleRepository->find($vehicleId);
            if ($vehicle) {
                $suggestions['maintenance_due'] = $this->getMaintenanceDueSuggestions($vehicle);
            }
        }

        // Get recommended services based on patterns
        $suggestions['recommended_services'] = $this->getRecommendedServices($clientId, $vehicleId);

        return $suggestions;
    }

    /**
     * Get quick statistics for attendant
     */
    public function getQuickStats(): array
    {
        $userId = Auth::id();
        $today = Carbon::today();

        return [
            'services_created_today' => $this->serviceRepository->getTodayServicesCount($userId),
            'pending_services' => $this->serviceRepository->getPendingServicesCount($userId),
            'completed_today' => $this->serviceRepository->getCompletedTodayCount($userId),
            'average_creation_time' => $this->calculateAverageCreationTime($userId),
        ];
    }

    /**
     * Validate client-vehicle relationship
     */
    private function validateClientVehicleRelationship(int $clientId, int $vehicleId): void
    {
        $client = $this->clientRepository->find($clientId);
        $vehicle = $this->vehicleRepository->find($vehicleId);

        if (!$client) {
            throw new \InvalidArgumentException('Cliente não encontrado');
        }

        if (!$vehicle) {
            throw new \InvalidArgumentException('Veículo não encontrado');
        }

        if ($vehicle->client_id !== $client->id) {
            throw new \InvalidArgumentException('Veículo não pertence ao cliente informado');
        }
    }

    /**
     * Apply template to service data
     */
    private function applyTemplate(ServiceTemplate $template): array
    {
        $data = [
            'description' => $template->description,
            'notes' => $template->notes,
        ];

        if ($template->estimated_duration) {
            $data['notes'] = ($data['notes'] ?? '') . "\nDuração estimada: {$template->estimated_duration} minutos";
        }

        if ($template->priority) {
            $data['notes'] = ($data['notes'] ?? '') . "\nPrioridade: {$template->priority}";
        }

        return $data;
    }

    /**
     * Check for scheduling conflicts
     */
    private function checkSchedulingConflicts(string $scheduledAt, int $technicianId, int $duration): array
    {
        $startTime = Carbon::parse($scheduledAt);
        $endTime = $startTime->copy()->addMinutes($duration);

        $conflicts = Service::where('technician_id', $technicianId)
            ->where('scheduled_at', '>=', $startTime->subMinutes(30))
            ->where('scheduled_at', '<=', $endTime->addMinutes(30))
            ->get();

        return $conflicts->toArray();
    }

    /**
     * Check for duplicate services
     */
    private function checkDuplicateServices(int $clientId, int $vehicleId, string $description): array
    {
        $recentServices = Service::where('client_id', $clientId)
            ->where('vehicle_id', $vehicleId)
            ->where('description', 'like', "%{$description}%")
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get();

        return $recentServices->toArray();
    }

    /**
     * Get vehicle-based suggestions
     */
    private function getVehicleBasedSuggestions($vehicle): array
    {
        $suggestions = [];

        // Check mileage-based suggestions
        if ($vehicle->mileage) {
            if ($vehicle->mileage >= 5000 && $vehicle->mileage % 5000 == 0) {
                $suggestions[] = 'Troca de óleo recomendada (a cada 5.000 km)';
            }

            if ($vehicle->mileage >= 10000 && $vehicle->mileage % 10000 == 0) {
                $suggestions[] = 'Revisão completa recomendada (a cada 10.000 km)';
            }
        }

        return $suggestions;
    }

    /**
     * Get maintenance due suggestions
     */
    private function getMaintenanceDueSuggestions($vehicle): array
    {
        $suggestions = [];

        // Add vehicle-specific maintenance suggestions
        $suggestions[] = 'Verificar nível de óleo';
        $suggestions[] = 'Verificar filtros';
        $suggestions[] = 'Verificar pneus';

        return $suggestions;
    }

    /**
     * Get recommended services based on patterns
     */
    private function getRecommendedServices(int $clientId, ?int $vehicleId): array
    {
        $recommendations = [
            'Troca de óleo e filtro',
            'Revisão geral',
            'Alinhamento e balanceamento',
        ];

        // Add vehicle-specific recommendations
        if ($vehicleId) {
            $vehicle = $this->vehicleRepository->find($vehicleId);
            if ($vehicle) {
                $recommendations[] = "Verificação específica para {$vehicle->brand} {$vehicle->model}";
            }
        }

        return $recommendations;
    }

    /**
     * Calculate average service creation time
     */
    private function calculateAverageCreationTime(int $userId): float
    {
        // This would typically be calculated from actual usage data
        // For now, return a default value
        return 2.5;
    }

    /**
     * Clear service-related caches
     */
    private function clearServiceCaches(Service $service): void
    {
        Cache::forget("client_services_{$service->client_id}");
        Cache::forget("services_center_{$service->service_center_id}");
        Cache::forget("services_range_" . now()->format('Y-m-d') . "_" . now()->format('Y-m-d'));
    }
}
