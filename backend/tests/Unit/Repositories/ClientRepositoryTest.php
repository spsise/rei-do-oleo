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
        Client::factory()->create(['name' => 'João Silva', 'phone01' => '11999887766']);
        Client::factory()->create(['name' => 'Maria Santos', 'phone01' => '11888776655']);

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
            'phone01' => '11999887766',
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
        $client = Client::factory()->create(['phone01' => '11999887766']);

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


}
