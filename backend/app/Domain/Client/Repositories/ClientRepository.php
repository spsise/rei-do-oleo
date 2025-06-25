<?php

namespace App\Domain\Client\Repositories;

use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ClientRepository implements ClientRepositoryInterface
{
    public function findByLicensePlate(string $plate): ?Client
    {
        return Client::findByLicensePlate($plate);
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Client::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('phone01'),
                AllowedFilter::partial('cpf'),
                AllowedFilter::partial('cnpj'),
                AllowedFilter::exact('active'),
                AllowedFilter::exact('state'),
                AllowedFilter::exact('city'),
            ])
            ->allowedSorts(['name', 'created_at', 'updated_at'])
            ->with(['vehicles', 'lastService'])
            ->paginate($perPage);
    }

    public function createWithVehicle(array $clientData, array $vehicleData): Client
    {
        return DB::transaction(function () use ($clientData, $vehicleData) {
            $client = Client::create($clientData);

            $vehicleData['client_id'] = $client->id;
            Vehicle::create($vehicleData);

            return $client->load('vehicles');
        });
    }

    public function getActiveClients(): Collection
    {
        return Client::active()
            ->with(['vehicles', 'lastService'])
            ->orderBy('name')
            ->get();
    }

    public function searchByName(string $name): Collection
    {
        return Client::searchByName($name)
            ->with(['vehicles'])
            ->limit(10)
            ->get();
    }

    public function searchByFilters(array $filters): LengthAwarePaginator
    {
        $query = Client::query()->with(['vehicles', 'lastService']);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone01', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getClientsWithRecentServices(int $days = 30): Collection
    {
        return Client::whereHas('services', function ($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        })
        ->with(['vehicles', 'services' => function ($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days))
                  ->with(['serviceStatus', 'serviceCenter']);
        }])
        ->orderBy('name')
        ->get();
    }

    public function find(int $id): ?Client
    {
        return Client::with(['vehicles', 'services.serviceStatus'])
                     ->find($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(int $id, array $data): ?Client
    {
        $client = Client::find($id);

        if (!$client) {
            return null;
        }

        $client->update($data);
        return $client->fresh(['vehicles', 'services']);
    }

    public function delete(int $id): bool
    {
        $client = Client::find($id);

        if (!$client) {
            return false;
        }

        return $client->delete();
    }

    public function findByDocument(string $document): ?Client
    {
        return Client::where('document', $document)
                     ->with(['vehicles', 'services'])
                     ->first();
    }

    public function findByPhone(string $phone): ?Client
    {
        return Client::where('phone', $phone)
                     ->orWhere('whatsapp', $phone)
                     ->with(['vehicles', 'services'])
                     ->first();
    }
}
