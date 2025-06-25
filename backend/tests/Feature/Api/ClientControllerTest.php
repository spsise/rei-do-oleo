<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected ServiceCenter $serviceCenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceCenter = ServiceCenter::factory()->create();
        $this->user = User::factory()->create([
            'service_center_id' => $this->serviceCenter->id
        ]);

        // Create and assign role
        $role = Role::create(['name' => 'manager']);
        $this->user->assignRole($role);
    }
    #[Test]
    public function index_returns_paginated_clients_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        Client::factory()->count(15)->create();

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'current_page',
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'email',
                                'phone01',
                                'cpf',
                                'cnpj',
                                'address',
                                'city',
                                'state',
                                'active',
                                'vehicles_count',
                                'services_count'
                            ]
                        ],
                        'per_page',
                        'total'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
    }
    #[Test]
    public function index_filters_clients_by_search_parameter(): void
    {
        Sanctum::actingAs($this->user);

        $client1 = Client::factory()->create(['name' => 'João Silva']);
        $client2 = Client::factory()->create(['name' => 'Maria Santos']);
        $client3 = Client::factory()->create(['name' => 'José João']);

        $response = $this->getJson('/api/clients?search=João');

        $response->assertStatus(200);

        $clientNames = collect($response->json('data.data'))->pluck('name');
        $this->assertTrue($clientNames->contains('João Silva'));
        $this->assertTrue($clientNames->contains('José João'));
        $this->assertFalse($clientNames->contains('Maria Santos'));
    }
    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->getJson('/api/clients');

        $response->assertStatus(401);
    }
    #[Test]
    public function store_creates_client_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $clientData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone01' => '(11) 99999-9999',
            'cpf' => $this->generateValidCpf(),
            'address' => 'Rua das Flores, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567'
        ];

        $response = $this->postJson('/api/clients', $clientData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone01',
                        'cpf',
                        'address',
                        'city',
                        'state',
                        'zip_code'
                    ]
                ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => $clientData['cpf']
        ]);
    }
    #[Test]
    public function store_creates_client_with_vehicle(): void
    {
        Sanctum::actingAs($this->user);

        $clientData = [
            'name' => 'João Silva',
            'cpf' => $this->generateValidCpf(),
            'phone01' => '(11) 99999-9999',
            'with_vehicle' => true,
            'vehicle' => [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2020,
                'color' => 'Branco'
            ]
        ];

        $response = $this->postJson('/api/clients', $clientData);

        $response->assertStatus(201);

        $client = Client::where('cpf', $clientData['cpf'])->first();
        $this->assertNotNull($client);
        $this->assertEquals(1, $client->vehicles()->count());

        $vehicle = $client->vehicles()->first();
        $this->assertEquals('ABC-1234', $vehicle->license_plate);
        $this->assertEquals('Toyota', $vehicle->brand);
    }
    #[Test]
    public function store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/clients', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'phone01']);
    }
    #[Test]
    public function store_validates_cpf_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/clients', [
            'name' => 'João Silva',
            'phone01' => '(11) 99999-9999',
            'cpf' => '123.456.789-00' // Invalid CPF
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }
    #[Test]
    public function store_validates_unique_cpf(): void
    {
        Sanctum::actingAs($this->user);

        $cpf = $this->generateValidCpf();
        Client::factory()->create(['cpf' => $cpf]);

        $response = $this->postJson('/api/clients', [
            'name' => 'João Silva',
            'phone01' => '(11) 99999-9999',
            'cpf' => $cpf
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }
    #[Test]
    public function store_validates_email_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/clients', [
            'name' => 'João Silva',
            'phone01' => '(11) 99999-9999',
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
    #[Test]
    public function store_validates_phone_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/clients', [
            'name' => 'João Silva',
            'phone01' => '123456' // Invalid phone
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['phone01']);
    }
    #[Test]
    public function store_validates_license_plate_when_creating_with_vehicle(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/clients', [
            'name' => 'João Silva',
            'cpf' => $this->generateValidCpf(),
            'phone01' => '(11) 99999-9999',
            'with_vehicle' => true,
            'vehicle' => [
                'license_plate' => 'INVALID', // Invalid plate format
                'brand' => 'Toyota'
            ]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['vehicle.license_plate']);
    }
    #[Test]
    public function show_returns_client_details(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::factory()->create();
        Vehicle::factory()->count(2)->create(['client_id' => $client->id]);

        $response = $this->getJson("/api/clients/{$client->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone01',
                        'phone02',
                        'cpf',
                        'cnpj',
                        'address',
                        'city',
                        'state',
                        'zip_code',
                        'vehicles' => [
                            '*' => [
                                'id',
                                'license_plate',
                                'brand',
                                'model',
                                'year'
                            ]
                        ],
                        'services_count',
                        'last_service'
                    ]
                ]);

        $this->assertEquals($client->id, $response->json('data.id'));
        $this->assertCount(2, $response->json('data.vehicles'));
    }
    #[Test]
    public function show_returns_404_for_nonexistent_client(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/clients/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
    }
    #[Test]
    public function update_modifies_client_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::factory()->create([
            'name' => 'Nome Original',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
            'phone01' => '(11) 88888-8888'
        ];

        $response = $this->putJson("/api/clients/{$client->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cliente atualizado com sucesso'
                ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@example.com'
        ]);
    }
    #[Test]
    public function update_validates_unique_cpf_excluding_current_client(): void
    {
        Sanctum::actingAs($this->user);

        $cpf = $this->generateValidCpf();
        $anotherCpf = $this->generateValidCpf();

        $client1 = Client::factory()->create(['cpf' => $cpf]);
        $client2 = Client::factory()->create(['cpf' => $anotherCpf]);

        // Should fail - trying to use CPF from another client
        $response = $this->putJson("/api/clients/{$client1->id}", [
            'name' => $client1->name,
            'phone01' => $client1->phone01,
            'cpf' => $anotherCpf
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);

        // Should succeed - using same CPF
        $response = $this->putJson("/api/clients/{$client1->id}", [
            'name' => 'Nome Atualizado',
            'phone01' => $client1->phone01,
            'cpf' => $cpf
        ]);

        $response->assertStatus(200);
    }
    #[Test]
    public function destroy_soft_deletes_client(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::factory()->create();

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cliente removido com sucesso'
                ]);

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
    #[Test]
    public function destroy_returns_404_for_nonexistent_client(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/clients/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ]);
    }
    #[Test]
    public function search_by_license_plate_returns_client(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'client_id' => $client->id,
            'license_plate' => 'ABC-1234'
        ]);

        $response = $this->getJson('/api/clients/search/license-plate/ABC-1234');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'phone01',
                        'vehicle' => [
                            'id',
                            'license_plate',
                            'brand',
                            'model'
                        ]
                    ]
                ]);

        $this->assertEquals($client->id, $response->json('data.id'));
        $this->assertEquals('ABC-1234', $response->json('data.vehicle.license_plate'));
    }
    #[Test]
    public function search_by_license_plate_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/clients/search/license-plate/XYZ-9999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cliente não encontrado para esta placa'
                ]);
    }
    #[Test]
    public function search_by_phone_returns_matching_clients(): void
    {
        Sanctum::actingAs($this->user);

        $client1 = Client::factory()->create(['phone01' => '(11) 99999-9999']);
        $client2 = Client::factory()->create(['phone01' => '(11) 88888-8888']);
        $client3 = Client::factory()->create(['phone02' => '(11) 99999-9999']);

        $response = $this->getJson('/api/clients/search/phone/(11) 99999-9999');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'phone01',
                            'phone02'
                        ]
                    ]
                ]);

        $clientIds = collect($response->json('data'))->pluck('id');
        $this->assertTrue($clientIds->contains($client1->id));
        $this->assertTrue($clientIds->contains($client3->id));
        $this->assertFalse($clientIds->contains($client2->id));
    }
    #[Test]
    public function get_client_stats_returns_statistics(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::factory()->create();

        $response = $this->getJson("/api/clients/{$client->id}/stats");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_services',
                        'completed_services',
                        'pending_services',
                        'total_spent',
                        'average_service_value',
                        'last_service_date',
                        'vehicles_count',
                        'services_by_month'
                    ]
                ]);
    }
    #[Test]
    public function inactive_client_is_filtered_in_index(): void
    {
        Sanctum::actingAs($this->user);

        $activeClient = Client::factory()->create(['active' => true]);
        $inactiveClient = Client::factory()->create(['active' => false]);

        $response = $this->getJson('/api/clients?active=1');

        $response->assertStatus(200);

        $clientIds = collect($response->json('data.data'))->pluck('id');
        $this->assertTrue($clientIds->contains($activeClient->id));
        $this->assertFalse($clientIds->contains($inactiveClient->id));
    }
    #[Test]
    public function export_clients_returns_downloadable_file(): void
    {
        Sanctum::actingAs($this->user);

        Client::factory()->count(5)->create();

        $response = $this->getJson('/api/clients/export');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'text/csv')
                ->assertHeader('Content-Disposition');
    }
    #[Test]
    public function bulk_update_modifies_multiple_clients(): void
    {
        Sanctum::actingAs($this->user);

        $clients = Client::factory()->count(3)->create(['active' => true]);

        $response = $this->putJson('/api/clients/bulk-update', [
            'client_ids' => $clients->pluck('id')->toArray(),
            'data' => ['active' => false]
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Clientes atualizados em lote com sucesso'
                ]);

        foreach ($clients as $client) {
            $this->assertDatabaseHas('clients', [
                'id' => $client->id,
                'active' => false
            ]);
        }
    }
    #[Test]
    public function unauthorized_user_cannot_access_client_endpoints(): void
    {
        // Create user without proper permissions
        $unauthorizedUser = User::factory()->create();
        Sanctum::actingAs($unauthorizedUser);

        $client = Client::factory()->create();

        $endpoints = [
            ['GET', '/api/clients'],
            ['POST', '/api/clients'],
            ['GET', "/api/clients/{$client->id}"],
            ['PUT', "/api/clients/{$client->id}"],
            ['DELETE', "/api/clients/{$client->id}"]
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint, []);
            $response->assertStatus(403); // Forbidden
        }
    }
}
