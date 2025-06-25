<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\PaymentMethod;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected ServiceCenter $serviceCenter;
    protected Client $client;
    protected Vehicle $vehicle;
    protected ServiceStatus $pendingStatus;
    protected ServiceStatus $inProgressStatus;
    protected ServiceStatus $completedStatus;
    protected PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceCenter = ServiceCenter::factory()->create();
        $this->user = User::factory()->create([
            'service_center_id' => $this->serviceCenter->id
        ]);

        // Create and assign role
        $role = Role::create(['name' => 'technician']);
        $this->user->assignRole($role);

        $this->client = Client::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);

        // Create service statuses
        $this->pendingStatus = ServiceStatus::factory()->create(['name' => 'pending']);
        $this->inProgressStatus = ServiceStatus::factory()->create(['name' => 'in_progress']);
        $this->completedStatus = ServiceStatus::factory()->create(['name' => 'completed']);

        $this->paymentMethod = PaymentMethod::factory()->create();
    }
    #[Test]
    public function index_returns_paginated_services_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        Service::factory()->count(15)->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson('/api/services');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'current_page',
                        'data' => [
                            '*' => [
                                'id',
                                'service_number',
                                'client',
                                'vehicle',
                                'service_center',
                                'service_status',
                                'scheduled_at',
                                'total_amount',
                                'final_amount',
                                'mileage_at_service'
                            ]
                        ],
                        'per_page',
                        'total'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
    }
    #[Test]
    public function index_filters_services_by_status(): void
    {
        Sanctum::actingAs($this->user);

        $pendingService = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);
        $completedService = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->completedStatus->id
        ]);

        $response = $this->getJson('/api/services?status=pending');

        $response->assertStatus(200);

        $serviceIds = collect($response->json('data.data'))->pluck('id');
        $this->assertTrue($serviceIds->contains($pendingService->id));
        $this->assertFalse($serviceIds->contains($completedService->id));
    }
    #[Test]
    public function index_filters_services_by_service_center(): void
    {
        Sanctum::actingAs($this->user);

        $otherServiceCenter = ServiceCenter::factory()->create();

        $myService = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);
        $otherService = Service::factory()->create([
            'service_center_id' => $otherServiceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson("/api/services?service_center_id={$this->serviceCenter->id}");

        $response->assertStatus(200);

        $serviceIds = collect($response->json('data.data'))->pluck('id');
        $this->assertTrue($serviceIds->contains($myService->id));
        $this->assertFalse($serviceIds->contains($otherService->id));
    }
    #[Test]
    public function store_creates_service_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'mileage_at_service' => 50000,
            'observations' => 'Troca de óleo e filtros'
        ];

        $response = $this->postJson('/api/services', $serviceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'service_number',
                        'client',
                        'vehicle',
                        'service_center',
                        'service_status',
                        'scheduled_at',
                        'mileage_at_service',
                        'observations'
                    ]
                ]);

        $this->assertDatabaseHas('services', [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'mileage_at_service' => 50000
        ]);
    }
    #[Test]
    public function store_creates_service_with_items(): void
    {
        Sanctum::actingAs($this->user);

        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id, 'price' => 50.00]);
        $product2 = Product::factory()->create(['category_id' => $category->id, 'price' => 25.00]);

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'unit_price' => 50.00
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                    'unit_price' => 25.00
                ]
            ]
        ];

        $response = $this->postJson('/api/services', $serviceData);

        $response->assertStatus(201);

        $service = Service::latest()->first();
        $this->assertEquals(2, $service->serviceItems()->count());
        $this->assertEquals(125.00, $service->total_amount); // (50*2) + (25*1)
    }
    #[Test]
    public function store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/services', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'client_id',
                    'vehicle_id',
                    'service_center_id'
                ]);
    }
    #[Test]
    public function store_validates_vehicle_belongs_to_client(): void
    {
        Sanctum::actingAs($this->user);

        $anotherClient = Client::factory()->create();
        $anotherVehicle = Vehicle::factory()->create(['client_id' => $anotherClient->id]);

        $response = $this->postJson('/api/services', [
            'client_id' => $this->client->id,
            'vehicle_id' => $anotherVehicle->id,
            'service_center_id' => $this->serviceCenter->id
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['vehicle_id']);
    }
    #[Test]
    public function store_validates_scheduled_date_is_future(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/services', [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'scheduled_at' => now()->subDay()->toDateTimeString()
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['scheduled_at']);
    }
    #[Test]
    public function show_returns_service_details(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'service_number',
                        'client' => [
                            'id',
                            'name',
                            'phone01',
                            'cpf'
                        ],
                        'vehicle' => [
                            'id',
                            'license_plate',
                            'brand',
                            'model'
                        ],
                        'service_center',
                        'service_status',
                        'service_items',
                        'scheduled_at',
                        'started_at',
                        'completed_at',
                        'total_amount',
                        'final_amount'
                    ]
                ]);

        $this->assertEquals($service->id, $response->json('data.id'));
    }
    #[Test]
    public function show_returns_404_for_nonexistent_service(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/services/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Serviço não encontrado'
                ]);
    }
    #[Test]
    public function update_modifies_service_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id,
            'observations' => 'Observação original'
        ]);

        $updateData = [
            'mileage_at_service' => 75000,
            'observations' => 'Observação atualizada',
            'discount_amount' => 10.00
        ];

        $response = $this->putJson("/api/services/{$service->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Serviço atualizado com sucesso'
                ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'mileage_at_service' => 75000,
            'observations' => 'Observação atualizada',
            'discount_amount' => 10.00
        ]);
    }
    #[Test]
    public function start_service_changes_status_to_in_progress(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id,
            'started_at' => null
        ]);

        $response = $this->postJson("/api/services/{$service->id}/start");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Serviço iniciado com sucesso'
                ]);

        $service->refresh();
        $this->assertEquals($this->inProgressStatus->id, $service->service_status_id);
        $this->assertNotNull($service->started_at);
    }
    #[Test]
    public function complete_service_changes_status_to_completed(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->inProgressStatus->id,
            'mileage_at_service' => 60000,
            'completed_at' => null
        ]);

        $response = $this->postJson("/api/services/{$service->id}/complete");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Serviço finalizado com sucesso'
                ]);

        $service->refresh();
        $this->assertEquals($this->completedStatus->id, $service->service_status_id);
        $this->assertNotNull($service->completed_at);

        // Check if vehicle mileage was updated
        $this->vehicle->refresh();
        $this->assertEquals(60000, $this->vehicle->mileage);
    }
    #[Test]
    public function cancel_service_changes_status_appropriately(): void
    {
        Sanctum::actingAs($this->user);

        $cancelledStatus = ServiceStatus::factory()->create(['name' => 'cancelled']);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->postJson("/api/services/{$service->id}/cancel", [
            'reason' => 'Cliente cancelou'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Serviço cancelado com sucesso'
                ]);

        $service->refresh();
        $this->assertEquals($cancelledStatus->id, $service->service_status_id);
    }
    #[Test]
    public function add_service_item_to_existing_service(): void
    {
        Sanctum::actingAs($this->user);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 30.00
        ]);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->inProgressStatus->id
        ]);

        $response = $this->postJson("/api/services/{$service->id}/items", [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 30.00,
            'notes' => 'Item adicional'
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Item adicionado ao serviço com sucesso'
                ]);

        $this->assertEquals(1, $service->serviceItems()->count());
        $serviceItem = $service->serviceItems()->first();
        $this->assertEquals(60.00, $serviceItem->total_price);
    }
    #[Test]
    public function remove_service_item_from_service(): void
    {
        Sanctum::actingAs($this->user);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->inProgressStatus->id
        ]);

        $serviceItem = $service->serviceItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 25.00,
            'total_price' => 25.00
        ]);

        $response = $this->deleteJson("/api/services/{$service->id}/items/{$serviceItem->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Item removido do serviço com sucesso'
                ]);

        $this->assertEquals(0, $service->serviceItems()->count());
    }
    #[Test]
    public function get_services_by_client_returns_client_services(): void
    {
        Sanctum::actingAs($this->user);

        $services = Service::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $otherClientService = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson("/api/clients/{$this->client->id}/services");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'service_number',
                            'vehicle',
                            'service_status',
                            'scheduled_at'
                        ]
                    ]
                ]);

        $serviceIds = collect($response->json('data'))->pluck('id');
        foreach ($services as $service) {
            $this->assertTrue($serviceIds->contains($service->id));
        }
        $this->assertFalse($serviceIds->contains($otherClientService->id));
    }
    #[Test]
    public function get_service_statistics_returns_aggregated_data(): void
    {
        Sanctum::actingAs($this->user);

        // Create services with different statuses
        Service::factory()->count(5)->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);
        Service::factory()->count(3)->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->completedStatus->id,
            'final_amount' => 100.00
        ]);

        $response = $this->getJson('/api/services/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_services',
                        'pending_services',
                        'in_progress_services',
                        'completed_services',
                        'total_revenue',
                        'average_service_value',
                        'services_by_month',
                        'top_clients'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(8, $data['total_services']);
        $this->assertEquals(5, $data['pending_services']);
        $this->assertEquals(3, $data['completed_services']);
    }
    #[Test]
    public function generate_service_report_returns_pdf(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->completedStatus->id
        ]);

        $response = $this->getJson("/api/services/{$service->id}/report");

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');
    }
    #[Test]
    public function search_services_by_service_number(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_number' => 'SVC20240101-0001',
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson('/api/services/search?q=SVC20240101-0001');

        $response->assertStatus(200);

        $serviceIds = collect($response->json('data.data'))->pluck('id');
        $this->assertTrue($serviceIds->contains($service->id));
    }
    #[Test]
    public function unauthorized_user_cannot_access_service_endpoints(): void
    {
        $unauthorizedUser = User::factory()->create();
        Sanctum::actingAs($unauthorizedUser);

        $service = Service::factory()->create([
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->getJson('/api/services');
        $response->assertStatus(403);
    }
    #[Test]
    public function service_timeline_shows_status_changes(): void
    {
        Sanctum::actingAs($this->user);

        $service = Service::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->completedStatus->id,
            'started_at' => now()->subHours(2),
            'completed_at' => now()
        ]);

        $response = $this->getJson("/api/services/{$service->id}/timeline");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'status',
                            'timestamp',
                            'user',
                            'notes'
                        ]
                    ]
                ]);
    }
    #[Test]
    public function bulk_status_update_modifies_multiple_services(): void
    {
        Sanctum::actingAs($this->user);

        $services = Service::factory()->count(3)->create([
            'service_center_id' => $this->serviceCenter->id,
            'service_status_id' => $this->pendingStatus->id
        ]);

        $response = $this->putJson('/api/services/bulk-status', [
            'service_ids' => $services->pluck('id')->toArray(),
            'status' => 'in_progress'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Status dos serviços atualizados em lote'
                ]);

        foreach ($services as $service) {
            $service->refresh();
            $this->assertEquals($this->inProgressStatus->id, $service->service_status_id);
        }
    }
}
