<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceTemplate;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AttendantServiceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $attendant;
    private Client $client;
    private Vehicle $vehicle;
    private ServiceCenter $serviceCenter;
    private ServiceStatus $scheduledStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Create service center
        $this->serviceCenter = ServiceCenter::factory()->create([
            'active' => true
        ]);

        // Create attendant user
        $this->attendant = User::factory()->create([
            'service_center_id' => $this->serviceCenter->id,
            'active' => true
        ]);
        $this->attendant->assignRole('attendant');

        // Create client
        $this->client = Client::factory()->create([
            'active' => true
        ]);

        // Create vehicle for client
        $this->vehicle = Vehicle::factory()->create([
            'client_id' => $this->client->id,
            'active' => true
        ]);

        // Create service status
        $this->scheduledStatus = ServiceStatus::factory()->create([
            'name' => 'scheduled'
        ]);

        // Authenticate as attendant
        $this->actingAs($this->attendant);
    }

    /** @test */
    public function it_can_create_quick_service()
    {
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo e filtro',
            'estimated_duration' => 60,
            'priority' => 'medium',
            'notes' => 'Observações do serviço'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'service_number',
                        'description',
                        'client',
                        'vehicle',
                        'service_center'
                    ]
                ]);

        $this->assertDatabaseHas('services', [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo e filtro',
            'attendant_id' => $this->attendant->id,
            'service_center_id' => $this->serviceCenter->id
        ]);
    }

    /** @test */
    public function it_can_create_quick_service_with_template()
    {
        $template = ServiceTemplate::factory()->create([
            'name' => 'Troca de Óleo',
            'description' => 'Troca de óleo e filtro padrão',
            'estimated_duration' => 60,
            'priority' => 'medium'
        ]);

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo personalizada',
            'template_id' => $template->id
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('services', [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo personalizada'
        ]);
    }

    /** @test */
    public function it_can_create_complete_service()
    {
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Revisão completa do veículo',
            'estimated_duration' => 120,
            'priority' => 'high',
            'scheduled_at' => now()->addDay()->toISOString(),
            'notes' => 'Observações detalhadas',
            'observations' => 'Observações internas'
        ];

        $response = $this->postJson('/api/v1/attendant/services/complete', $serviceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'service_number',
                        'description',
                        'scheduled_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_service_templates()
    {
        ServiceTemplate::factory()->count(5)->create([
            'active' => true
        ]);

        $response = $this->getJson('/api/v1/attendant/services/templates');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'category',
                            'estimated_duration',
                            'priority'
                        ]
                    ]
                ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_can_get_service_templates_by_category()
    {
        ServiceTemplate::factory()->count(3)->create([
            'category' => 'maintenance',
            'active' => true
        ]);

        ServiceTemplate::factory()->count(2)->create([
            'category' => 'repair',
            'active' => true
        ]);

        $response = $this->getJson('/api/v1/attendant/services/templates?category=maintenance');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_validate_service_data()
    {
        $validationData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço de teste',
            'scheduled_at' => now()->addDay()->toISOString()
        ];

        $response = $this->postJson('/api/v1/attendant/services/validate', $validationData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'is_valid',
                        'warnings',
                        'suggestions'
                    ]
                ]);

        $this->assertTrue($response->json('data.is_valid'));
    }

    /** @test */
    public function it_can_get_service_suggestions()
    {
        // Create some previous services for the client
        Service::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo'
        ]);

        $response = $this->getJson("/api/v1/attendant/services/suggestions?client_id={$this->client->id}&vehicle_id={$this->vehicle->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'recent_services',
                        'recommended_services',
                        'maintenance_due'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_quick_stats()
    {
        // Create some services for the attendant
        Service::factory()->count(5)->create([
            'attendant_id' => $this->attendant->id,
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/attendant/services/quick-stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'services_created_today',
                        'pending_services',
                        'completed_today',
                        'average_creation_time'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.services_created_today'));
    }

    /** @test */
    public function it_validates_required_fields_for_quick_service()
    {
        $response = $this->postJson('/api/v1/attendant/services/quick', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['client_id', 'vehicle_id', 'description']);
    }

    /** @test */
    public function it_validates_client_vehicle_relationship()
    {
        $otherVehicle = Vehicle::factory()->create([
            'client_id' => Client::factory()->create()->id
        ]);

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $otherVehicle->id,
            'description' => 'Serviço inválido'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['vehicle_id']);
    }

    /** @test */
    public function it_validates_user_service_center_access()
    {
        $userWithoutServiceCenter = User::factory()->create([
            'service_center_id' => null
        ]);

        $this->actingAs($userWithoutServiceCenter);

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço sem centro'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['service_center']);
    }

    /** @test */
    public function it_validates_estimated_duration_limits()
    {
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço com duração inválida',
            'estimated_duration' => 1000 // Exceeds max limit
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['estimated_duration']);
    }

    /** @test */
    public function it_validates_priority_values()
    {
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço com prioridade inválida',
            'priority' => 'invalid_priority'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function it_validates_template_exists()
    {
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço com template inexistente',
            'template_id' => 99999
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['template_id']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $this->withoutMiddleware();

        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Serviço sem autenticação'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $response->assertStatus(401);
    }
}
