<?php

namespace App\Domain\Client\Services;

use App\Domain\Client\Models\Client;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function findByLicensePlate(string $plate): ?Client
    {
        return Cache::remember(
            "client_plate_{$plate}",
            3600,
            fn() => $this->clientRepository->findByLicensePlate($plate)
        );
    }

    public function createWithVehicle(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            // Validate license plate format
            if (!$this->isValidLicensePlate($data['vehicle']['license_plate'])) {
                throw new \InvalidArgumentException('Formato de placa inválido');
            }

            // Check if license plate already exists
            if ($this->vehicleRepository->findByLicensePlate($data['vehicle']['license_plate'])) {
                throw new \InvalidArgumentException('Placa já cadastrada');
            }

            $client = $this->clientRepository->createWithVehicle(
                $data['client'],
                $data['vehicle']
            );

            // Invalidate cache
            Cache::forget("client_plate_{$data['vehicle']['license_plate']}");

            return $client;
        });
    }

    public function updateVehicleMileage(int $vehicleId, int $newMileage): bool
    {
        $result = $this->vehicleRepository->updateMileage($vehicleId, $newMileage);

        if ($result) {
            // Invalidate cache for vehicle's client
            $vehicle = $this->vehicleRepository->findById($vehicleId);
            if ($vehicle) {
                Cache::forget("client_plate_{$vehicle->license_plate}");
            }
        }

        return $result;
    }

    public function searchClients(array $filters): LengthAwarePaginator
    {
        return $this->clientRepository->searchByFilters($filters);
    }

    public function getActiveClients(): Collection
    {
        return Cache::remember('active_clients', 1800, function () {
            return $this->clientRepository->getActiveClients();
        });
    }

    public function getClientsWithRecentServices(int $days = 30): Collection
    {
        return Cache::remember(
            "clients_recent_services_{$days}",
            1800,
            fn() => $this->clientRepository->getClientsWithRecentServices($days)
        );
    }

    public function findClient(int $id): ?Client
    {
        return $this->clientRepository->find($id);
    }

    public function createClient(array $data): Client
    {
        return $this->clientRepository->create($data);
    }

    public function updateClient(int $id, array $data): ?Client
    {
        $client = $this->clientRepository->update($id, $data);

        if ($client) {
            // Clear cache
            $client->clearCache();
        }

        return $client;
    }

    public function deleteClient(int $id): bool
    {
        $client = $this->clientRepository->find($id);

        if ($client) {
            $client->clearCache();
        }

        return $this->clientRepository->delete($id);
    }

    public function getClientStats(int $clientId): array
    {
        return Cache::remember(
            "client_stats_{$clientId}",
            3600,
            function () use ($clientId) {
                $client = $this->clientRepository->find($clientId);

                if (!$client) {
                    return [];
                }

                return [
                    'total_services' => $client->services()->count(),
                    'completed_services' => $client->services()
                        ->whereHas('serviceStatus', fn($q) => $q->where('name', 'completed'))
                        ->count(),
                    'total_spent' => $client->services()
                        ->whereNotNull('final_amount')
                        ->sum('final_amount'),
                    'last_service_date' => $client->services()
                        ->latest('completed_at')
                        ->value('completed_at'),
                    'vehicles_count' => $client->vehicles()->count(),
                ];
            }
        );
    }

    private function isValidLicensePlate(string $plate): bool
    {
        return \App\Domain\Client\Models\Vehicle::validateLicensePlate($plate);
    }

    public function searchByName(string $name): Collection
    {
        return $this->clientRepository->searchByName($name);
    }
}
