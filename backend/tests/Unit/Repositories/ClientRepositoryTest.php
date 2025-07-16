<?php

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Client\Repositories\ClientRepository;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ClientRepository $clientRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepository = new ClientRepository();
    }
    #[Test]
    public function find_by_license_plate_returns_client_with_vehicle(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'client_id' => $client->id,
            'license_plate' => 'ABC-1234'
        ]);

        $result = $this->clientRepository->findByLicensePlate('ABC-1234');

        $this->assertNotNull($result);
        $this->assertEquals($client->id, $result->id);
        $this->assertTrue($result->vehicles->contains($vehicle));
    }
    #[Test]
    public function find_by_license_plate_returns_null_when_not_found(): void
    {
        $result = $this->clientRepository->findByLicensePlate('XYZ-9999');

        $this->assertNull($result);
    }
    #[Test]
    public function find_by_license_plate_uses_cache(): void
    {
        $client = Client::factory()->create();
        Vehicle::factory()->create([
            'client_id' => $client->id,
            'license_plate' => 'ABC-1234'
        ]);

        // First call - should cache
        $result1 = $this->clientRepository->findByLicensePlate('ABC-1234');

        // Second call - should use cache
        $result2 = $this->clientRepository->findByLicensePlate('ABC-1234');

        $this->assertEquals($result1->id, $result2->id);
        $this->assertCacheContains("client_plate_ABC-1234");
    }
    #[Test]
    public function get_all_paginated_returns_paginated_results(): void
    {
        Client::factory()->count(25)->create();

        $result = $this->clientRepository->getAllPaginated(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }
    #[Test]
    public function get_all_paginated_includes_relationships(): void
    {
        $client = Client::factory()->create();
        Vehicle::factory()->create(['client_id' => $client->id]);
        Service::factory()->create(['client_id' => $client->id]);

        $result = $this->clientRepository->getAllPaginated(10);

        $firstClient = $result->items()[0];
        $this->assertTrue($firstClient->relationLoaded('vehicles'));
        $this->assertTrue($firstClient->relationLoaded('lastService'));
    }
    #[Test]
    public function create_with_vehicle_creates_client_and_vehicle_in_transaction(): void
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11999887766',
            'document' => $this->generateValidCPF()
        ];

        $vehicleData = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020
        ];

        $result = $this->clientRepository->createWithVehicle($clientData, $vehicleData);

        $this->assertNotNull($result);
        $this->assertEquals('João Silva', $result->name);
        $this->assertCount(1, $result->vehicles);
        $this->assertEquals('ABC-1234', $result->vehicles->first()->license_plate);

        // Verify data is in database
        $this->assertDatabaseHas('clients', ['name' => 'João Silva']);
        $this->assertDatabaseHas('vehicles', ['license_plate' => 'ABC-1234']);
    }
    #[Test]
    public function create_with_vehicle_rolls_back_on_error(): void
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11999887766'
        ];

        $vehicleData = [
            'license_plate' => null, // Invalid data to trigger error
            'brand' => 'Toyota'
        ];

        $this->expectException(\Exception::class);

        $this->clientRepository->createWithVehicle($clientData, $vehicleData);

        // Verify no data was saved
        $this->assertDatabaseMissing('clients', ['name' => 'João Silva']);
    }
    #[Test]
    public function get_active_clients_returns_only_active_clients(): void
    {
        Client::factory()->count(5)->create(['active' => true]);
        Client::factory()->count(3)->create(['active' => false]);

        $result = $this->clientRepository->getActiveClients();

        $this->assertCount(5, $result);
        $this->assertTrue(collect($result)->every(fn(Client $client) => $client->active));
    }
    #[Test]
    public function get_active_clients_includes_relationships(): void
    {
        $client = Client::factory()->create(['active' => true]);
        Vehicle::factory()->create(['client_id' => $client->id]);
        Service::factory()->create(['client_id' => $client->id]);

        $result = $this->clientRepository->getActiveClients();

        $firstClient = $result->first();
        $this->assertTrue($firstClient->relationLoaded('vehicles'));
        $this->assertTrue($firstClient->relationLoaded('lastService'));
    }
    #[Test]
    public function search_by_name_filters_by_name(): void
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);
        Client::factory()->create(['name' => 'Pedro Oliveira']);

        $result = $this->clientRepository->searchByName('João');

        $this->assertCount(1, $result);
        $this->assertEquals('João Silva', $result->first()->name);
    }
    #[Test]
    public function search_by_name_limits_results(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Client::factory()->create(['name' => "João Silva {$i}"]);
        }

        $result = $this->clientRepository->searchByName('João');

        $this->assertCount(10, $result); // Limited to 10
    }
    #[Test]
    public function search_by_filters_applies_search_filter(): void
    {
        Client::factory()->create(['name' => 'João Silva', 'phone' => '11999887766']);
        Client::factory()->create(['name' => 'Maria Santos', 'phone' => '11888776655']);

        $filters = ['search' => 'João'];
        $result = $this->clientRepository->searchByFilters($filters);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('João Silva', $result->items()[0]->name);
    }
    #[Test]
    public function search_by_filters_searches_multiple_fields(): void
    {
        $cpf = $this->generateValidCPF();
        Client::factory()->create([
            'name' => 'João Silva',
            'phone' => '11999887766',
            'document' => $cpf
        ]);
        Client::factory()->create(['name' => 'Maria Santos']);

        // Search by phone
        $result = $this->clientRepository->searchByFilters(['search' => '99988']);
        $this->assertEquals(1, $result->total());

        // Search by CPF
        $result = $this->clientRepository->searchByFilters(['search' => substr($cpf, 0, 5)]);
        $this->assertEquals(1, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_active_filter(): void
    {
        Client::factory()->count(3)->create(['active' => true]);
        Client::factory()->count(2)->create(['active' => false]);

        $result = $this->clientRepository->searchByFilters(['active' => true]);

        $this->assertEquals(3, $result->total());
        $this->assertTrue(collect($result->items())->every(fn(Client $client) => $client->active));
    }
    #[Test]
    public function search_by_filters_applies_location_filters(): void
    {
        Client::factory()->create(['state' => 'SP', 'city' => 'São Paulo']);
        Client::factory()->create(['state' => 'RJ', 'city' => 'Rio de Janeiro']);
        Client::factory()->create(['state' => 'SP', 'city' => 'Campinas']);

        $result = $this->clientRepository->searchByFilters(['state' => 'SP']);
        $this->assertEquals(2, $result->total());

        $result = $this->clientRepository->searchByFilters(['city' => 'São Paulo']);
        $this->assertEquals(1, $result->total());
    }
    #[Test]
    public function get_clients_with_recent_services_filters_by_date(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $client3 = Client::factory()->create();

        // Recent service (within 30 days)
        Service::factory()->create([
            'client_id' => $client1->id,
            'created_at' => now()->subDays(15)
        ]);

        // Old service (more than 30 days)
        Service::factory()->create([
            'client_id' => $client2->id,
            'created_at' => now()->subDays(45)
        ]);

        // No services for client3

        $result = $this->clientRepository->getClientsWithRecentServices(30);

        $this->assertCount(1, $result);
        $this->assertEquals($client1->id, $result->first()->id);
    }
    #[Test]
    public function get_clients_with_recent_services_includes_relationships(): void
    {
        $client = Client::factory()->create();
        Vehicle::factory()->create(['client_id' => $client->id]);
        Service::factory()->create([
            'client_id' => $client->id,
            'created_at' => now()->subDays(15)
        ]);

        $result = $this->clientRepository->getClientsWithRecentServices(30);

        $firstClient = $result->first();
        $this->assertTrue($firstClient->relationLoaded('vehicles'));
        $this->assertTrue($firstClient->relationLoaded('services'));
    }
    #[Test]
    public function find_returns_client_with_relationships(): void
    {
        $client = Client::factory()->create();
        Vehicle::factory()->create(['client_id' => $client->id]);
        Service::factory()->create(['client_id' => $client->id]);

        $result = $this->clientRepository->find($client->id);

        $this->assertNotNull($result);
        $this->assertEquals($client->id, $result->id);
        $this->assertTrue($result->relationLoaded('vehicles'));
        $this->assertTrue($result->relationLoaded('services'));
    }
    #[Test]
    public function find_returns_null_when_not_found(): void
    {
        $result = $this->clientRepository->find(999);

        $this->assertNull($result);
    }
    #[Test]
    public function create_creates_client_successfully(): void
    {
        $data = [
            'name' => 'João Silva',
            'phone' => '11999887766',
            'document' => $this->generateValidCPF(),
            'active' => true
        ];

        $result = $this->clientRepository->create($data);

        $this->assertNotNull($result);
        $this->assertEquals('João Silva', $result->name);
        $this->assertDatabaseHas('clients', ['name' => 'João Silva']);
    }
    #[Test]
    public function update_updates_client_successfully(): void
    {
        $client = Client::factory()->create(['name' => 'João Silva']);

        $result = $this->clientRepository->update($client->id, ['name' => 'João Santos']);

        $this->assertNotNull($result);
        $this->assertEquals('João Santos', $result->name);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'João Santos']);
    }
    #[Test]
    public function update_returns_null_when_client_not_found(): void
    {
        $result = $this->clientRepository->update(999, ['name' => 'João Santos']);

        $this->assertNull($result);
    }
    #[Test]
    public function update_includes_fresh_relationships(): void
    {
        $client = Client::factory()->create();
        Vehicle::factory()->create(['client_id' => $client->id]);

        $result = $this->clientRepository->update($client->id, ['name' => 'Updated Name']);

        $this->assertTrue($result->relationLoaded('vehicles'));
        $this->assertTrue($result->relationLoaded('services'));
    }
    #[Test]
    public function delete_soft_deletes_client(): void
    {
        $client = Client::factory()->create();

        $result = $this->clientRepository->delete($client->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
    #[Test]
    public function delete_returns_false_when_client_not_found(): void
    {
        $result = $this->clientRepository->delete(999);

        $this->assertFalse($result);
    }
    #[Test]
    public function find_by_document_finds_by_cpf(): void
    {
        $cpf = $this->generateValidCPF();
        $client = Client::factory()->create(['document' => $cpf]);

        $result = $this->clientRepository->findByDocument($cpf);

        $this->assertNotNull($result);
        $this->assertEquals($client->id, $result->id);
    }
    #[Test]
    public function find_by_phone_finds_by_phone(): void
    {
        $client = Client::factory()->create(['phone' => '11999887766']);

        $result = $this->clientRepository->findByPhone('11999887766');

        $this->assertNotNull($result);
        $this->assertEquals($client->id, $result->id);
    }
    #[Test]
    public function repository_implements_interface(): void
    {
        $this->assertInstanceOf(
            \App\Domain\Client\Repositories\ClientRepositoryInterface::class,
            $this->clientRepository
        );
    }
    #[Test]
    public function database_queries_are_optimized(): void
    {
        Client::factory()->count(50)->create();

        DB::enableQueryLog();

        $this->clientRepository->getAllPaginated(10);

        $queries = DB::getQueryLog();

        // Should only have a few queries (pagination query + count query)
        $this->assertLessThan(5, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function bulk_operations_are_efficient(): void
    {
        $clients = Client::factory()->count(100)->create();

        DB::enableQueryLog();

        $this->clientRepository->bulkUpdate(
            $clients->pluck('id')->toArray(),
            ['active' => false]
        );

        $queries = DB::getQueryLog();

        // Should use a single query for bulk update
        $this->assertLessThan(3, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function it_creates_client_with_valid_data()
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $this->generateValidCPF(),
            'document_type' => 'cpf',
            'email' => 'joao@example.com',
            'active' => true
        ];

        $client = $this->clientRepository->create($clientData);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals($clientData['name'], $client->name);
        $this->assertEquals($clientData['phone'], $client->phone);
        $this->assertDatabaseHas('clients', $clientData);
    }
    #[Test]
    public function it_finds_client_by_id()
    {
        $client = Client::factory()->create();

        $foundClient = $this->clientRepository->findById($client->id);

        $this->assertInstanceOf(Client::class, $foundClient);
        $this->assertEquals($client->id, $foundClient->id);
    }
    #[Test]
    public function it_returns_null_when_client_not_found()
    {
        $foundClient = $this->clientRepository->findById(999);

        $this->assertNull($foundClient);
    }
    #[Test]
    public function it_updates_client_data()
    {
        $client = Client::factory()->create();
        $updateData = [
            'name' => 'Nome Atualizado',
            'phone' => '11999888777'
        ];

        $updatedClient = $this->clientRepository->update($client->id, $updateData);

        $this->assertInstanceOf(Client::class, $updatedClient);
        $this->assertEquals($updateData['name'], $updatedClient->name);
        $this->assertEquals($updateData['phone'], $updatedClient->phone);
        $this->assertDatabaseHas('clients', array_merge(['id' => $client->id], $updateData));
    }
    #[Test]
    public function it_deletes_client_using_soft_delete()
    {
        $client = Client::factory()->create();

        $result = $this->clientRepository->delete($client->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
    #[Test]
    public function it_finds_all_clients_with_pagination()
    {
        Client::factory()->count(15)->create();

        $result = $this->clientRepository->findAll(10);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }
    #[Test]
    public function it_finds_clients_with_filters()
    {
        Client::factory()->create(['name' => 'João Silva', 'active' => true]);
        Client::factory()->create(['name' => 'Maria Santos', 'active' => true]);
        Client::factory()->create(['name' => 'Pedro João', 'active' => false]);

        $filters = [
            'name' => 'João',
            'active' => true
        ];

        $result = $this->clientRepository->findWithFilters($filters);

        $this->assertEquals(1, $result->count());
        $this->assertEquals('João Silva', $result->first()->name);
    }
    #[Test]
    public function it_searches_clients_by_multiple_criteria()
    {
        Client::factory()->create(['name' => 'João Silva', 'phone' => '11987654321']);
        Client::factory()->create(['name' => 'Maria Santos', 'phone' => '11999888777']);
        Client::factory()->create(['name' => 'Pedro Silva', 'phone' => '21987654321']);

        $searchTerm = 'Silva';
        $result = $this->clientRepository->search($searchTerm);

        $this->assertEquals(2, $result->count());
        $this->assertTrue($result->every(fn($client) => str_contains($client->name, 'Silva')));
    }
    #[Test]
    public function it_finds_client_by_document()
    {
        $document = $this->generateValidCPF();
        $client = Client::factory()->create(['document' => $document]);

        $foundClient = $this->clientRepository->findByDocument($document);

        $this->assertInstanceOf(Client::class, $foundClient);
        $this->assertEquals($client->id, $foundClient->id);
        $this->assertEquals($document, $foundClient->document);
    }
    #[Test]
    public function it_finds_client_by_phone()
    {
        $phone = '11987654321';
        $client = Client::factory()->create(['phone' => $phone]);

        $foundClient = $this->clientRepository->findByPhone($phone);

        $this->assertInstanceOf(Client::class, $foundClient);
        $this->assertEquals($client->id, $foundClient->id);
        $this->assertEquals($phone, $foundClient->phone);
    }
    #[Test]
    public function it_finds_active_clients_only()
    {
        Client::factory()->count(3)->create(['active' => true]);
        Client::factory()->count(2)->create(['active' => false]);

        $activeClients = $this->clientRepository->findActiveClients();

        $this->assertEquals(3, $activeClients->count());
        $this->assertTrue($activeClients->every(fn($client) => $client->active === true));
    }
    #[Test]
    public function it_finds_clients_by_city()
    {
        Client::factory()->create(['city' => 'São Paulo']);
        Client::factory()->create(['city' => 'Rio de Janeiro']);
        Client::factory()->create(['city' => 'São Paulo']);

        $clients = $this->clientRepository->findByCity('São Paulo');

        $this->assertEquals(2, $clients->count());
        $this->assertTrue($clients->every(fn($client) => $client->city === 'São Paulo'));
    }
    #[Test]
    public function it_finds_clients_by_state()
    {
        Client::factory()->create(['state' => 'SP']);
        Client::factory()->create(['state' => 'RJ']);
        Client::factory()->create(['state' => 'SP']);

        $clients = $this->clientRepository->findByState('SP');

        $this->assertEquals(2, $clients->count());
        $this->assertTrue($clients->every(fn($client) => $client->state === 'SP'));
    }
    #[Test]
    public function it_creates_client_with_vehicle_in_transaction()
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $this->generateValidCPF(),
            'document_type' => 'cpf'
        ];

        $vehicleData = [
            'license_plate' => $this->generateValidLicensePlate(),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020
        ];

        DB::beginTransaction();

        try {
            $client = $this->clientRepository->createWithVehicle($clientData, $vehicleData);

            $this->assertInstanceOf(Client::class, $client);
            $this->assertDatabaseHas('clients', ['id' => $client->id]);
            $this->assertDatabaseHas('vehicles', [
                'client_id' => $client->id,
                'license_plate' => $vehicleData['license_plate']
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    #[Test]
    public function it_loads_relationships_efficiently()
    {
        $client = Client::factory()->create();
        Vehicle::factory()->count(2)->create(['client_id' => $client->id]);

        // Enable query log to check for N+1 queries
        DB::enableQueryLog();

        $clientWithRelations = $this->clientRepository->findWithRelations($client->id, ['vehicles']);

        $queries = DB::getQueryLog();

        $this->assertInstanceOf(Client::class, $clientWithRelations);
        $this->assertEquals(2, $clientWithRelations->vehicles->count());
        // Should be 2 queries: one for client, one for vehicles
        $this->assertLessThanOrEqual(2, count($queries));
    }
    #[Test]
    public function it_finds_clients_with_recent_services()
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $client3 = Client::factory()->create();

        // Client with recent service
        \App\Domain\Service\Models\Service::factory()->create([
            'client_id' => $client1->id,
            'created_at' => now()->subDays(5)
        ]);

        // Client with old service
        \App\Domain\Service\Models\Service::factory()->create([
            'client_id' => $client2->id,
            'created_at' => now()->subDays(40)
        ]);

        $daysAgo = 30;
        $recentClients = $this->clientRepository->findWithRecentServices($daysAgo);

        $this->assertEquals(1, $recentClients->count());
        $this->assertEquals($client1->id, $recentClients->first()->id);
    }
    #[Test]
    public function it_caches_frequently_accessed_data()
    {
        $client = Client::factory()->create();
        $cacheKey = "client_{$client->id}";

        // First call should hit database and cache result
        $result1 = $this->clientRepository->findByIdCached($client->id);
        $this->assertTrue(Cache::has($cacheKey));

        // Second call should hit cache
        $result2 = $this->clientRepository->findByIdCached($client->id);

        $this->assertEquals($result1->id, $result2->id);
    }
    #[Test]
    public function it_invalidates_cache_on_update()
    {
        $client = Client::factory()->create();
        $cacheKey = "client_{$client->id}";

        // Cache the client
        $this->clientRepository->findByIdCached($client->id);
        $this->assertTrue(Cache::has($cacheKey));

        // Update should clear cache
        $this->clientRepository->update($client->id, ['name' => 'Updated Name']);

        $this->assertFalse(Cache::has($cacheKey));
    }
    #[Test]
    public function it_handles_bulk_operations()
    {
        $clients = Client::factory()->count(5)->create(['active' => true]);
        $clientIds = $clients->pluck('id')->toArray();

        $result = $this->clientRepository->bulkUpdate($clientIds, ['active' => false]);

        $this->assertTrue($result);
        $this->assertEquals(0, Client::where('active', true)->count());
        $this->assertEquals(5, Client::where('active', false)->count());
    }
    #[Test]
    public function it_counts_clients_by_criteria()
    {
        Client::factory()->count(3)->create(['active' => true, 'city' => 'São Paulo']);
        Client::factory()->count(2)->create(['active' => false, 'city' => 'São Paulo']);
        Client::factory()->count(1)->create(['active' => true, 'city' => 'Rio de Janeiro']);

        $count = $this->clientRepository->countByCriteria([
            'active' => true,
            'city' => 'São Paulo'
        ]);

        $this->assertEquals(3, $count);
    }
    #[Test]
    public function it_finds_clients_needing_service_reminder()
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        // Client with service 7 months ago (needs reminder)
        \App\Domain\Service\Models\Service::factory()->create([
            'client_id' => $client1->id,
            'created_at' => now()->subMonths(7)
        ]);

        // Client with recent service (no reminder needed)
        \App\Domain\Service\Models\Service::factory()->create([
            'client_id' => $client2->id,
            'created_at' => now()->subMonths(2)
        ]);

        $reminderClients = $this->clientRepository->findNeedingServiceReminder(6); // 6 months

        $this->assertEquals(1, $reminderClients->count());
        $this->assertEquals($client1->id, $reminderClients->first()->id);
    }
}
