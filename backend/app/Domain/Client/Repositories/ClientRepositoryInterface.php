<?php

namespace App\Domain\Client\Repositories;

use App\Domain\Client\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    public function findByLicensePlate(string $plate): ?Client;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function createWithVehicle(array $clientData, array $vehicleData): Client;

    public function getActiveClients(): Collection;

    public function searchByName(string $name): Collection;

    public function searchByFilters(array $filters): LengthAwarePaginator;

    public function getClientsWithRecentServices(int $days = 30): Collection;

    public function find(int $id): ?Client;

    public function create(array $data): Client;

    public function update(int $id, array $data): ?Client;

    public function delete(int $id): bool;

    public function findByDocument(string $document): ?Client;

    public function findByPhone(string $phone): ?Client;
}
