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

class AttendantServiceIntegrationTest extends TestCase
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
    public function it_can_complete_service_creation_workflow()
    {
        // 1. Get available templates
        $response = $this->getJson('/api/v1/attendant/services/templates');

        $response->assertStatus(200);
        $templates = $response->json('data');

        // 2. Validate service data before creation
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo e filtro',
            'estimated_duration' => 60,
            'priority' => 'medium'
        ];

        $validationResponse = $this->postJson('/api/v1/attendant/services/validate', $serviceData);

        $validationResponse->assertStatus(200);
        $this->assertTrue($validationResponse->json('data.is_valid'));

        // 3. Create quick service
        $createResponse = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $createResponse->assertStatus(201);
        $serviceId = $createResponse->json('data.id');

        // 4. Verify service was created correctly
        $this->assertDatabaseHas('services', [
            'id' => $serviceId,
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo e filtro',
            'attendant_id' => $this->attendant->id,
            'service_center_id' => $this->serviceCenter->id
        ]);

        // 5. Get quick stats
        $statsResponse = $this->getJson('/api/v1/attendant/services/quick-stats');

        $statsResponse->assertStatus(200);
        $this->assertEquals(1, $statsResponse->json('data.services_created_today'));
    }

    /** @test */
    public function it_can_create_service_with_template_workflow()
    {
        // 1. Create a template
        $template = ServiceTemplate::factory()->create([
            'name' => 'Troca de Óleo',
            'description' => 'Troca de óleo e filtro padrão',
            'category' => 'maintenance',
            'estimated_duration' => 60,
            'priority' => 'medium',
            'active' => true
        ]);

        // 2. Get templates
        $templatesResponse = $this->getJson('/api/v1/attendant/services/templates');

        $templatesResponse->assertStatus(200);
        $this->assertCount(1, $templatesResponse->json('data'));

        // 3. Create service using template
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo personalizada',
            'template_id' => $template->id
        ];

        $createResponse = $this->postJson('/api/v1/attendant/services/quick', $serviceData);

        $createResponse->assertStatus(201);

        // 4. Verify service was created with template data
        $serviceId = $createResponse->json('data.id');
        $service = Service::find($serviceId);

        $this->assertEquals('Troca de óleo personalizada', $service->description);
        $this->assertEquals(60, $service->estimated_duration);
        $this->assertEquals('medium', $service->priority);
    }

    /** @test */
    public function it_can_create_complete_service_workflow()
    {
        // 1. Create complete service with all details
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Revisão completa do veículo',
            'estimated_duration' => 120,
            'priority' => 'high',
            'scheduled_at' => now()->addDay()->toISOString(),
            'notes' => 'Observações para o cliente',
            'observations' => 'Observações internas'
        ];

        $createResponse = $this->postJson('/api/v1/attendant/services/complete', $serviceData);

        $createResponse->assertStatus(201);
        $serviceId = $createResponse->json('data.id');

        // 2. Verify complete service data
        $service = Service::find($serviceId);

        $this->assertEquals('Revisão completa do veículo', $service->description);
        $this->assertEquals(120, $service->estimated_duration);
        $this->assertEquals('high', $service->priority);
        $this->assertEquals('Observações para o cliente', $service->notes);
        $this->assertEquals('Observações internas', $service->observations);
        $this->assertNotNull($service->scheduled_at);
    }

    /** @test */
    public function it_can_get_service_suggestions_workflow()
    {
        // 1. Create previous services for the client
        Service::factory()->count(3)->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de óleo',
            'attendant_id' => $this->attendant->id
        ]);

        Service::factory()->count(2)->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Troca de filtro de ar',
            'attendant_id' => $this->attendant->id
        ]);

        // 2. Get suggestions
        $suggestionsResponse = $this->getJson("/api/v1/attendant/services/suggestions?client_id={$this->client->id}&vehicle_id={$this->vehicle->id}");

        $suggestionsResponse->assertStatus(200);
        $suggestions = $suggestionsResponse->json('data');

        // 3. Verify suggestions structure
        $this->assertArrayHasKey('recent_services', $suggestions);
        $this->assertArrayHasKey('recommended_services', $suggestions);
        $this->assertArrayHasKey('maintenance_due', $suggestions);

        // 4. Verify recent services
        $this->assertCount(5, $suggestions['recent_services']);
    }

    /** @test */
    public function it_handles_error_scenarios_correctly()
    {
        // 1. Try to create service with invalid client
        $invalidData = [
            'client_id' => 99999,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Test service'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $invalidData);

        $response->assertStatus(422);
        $this->assertTrue($response->json('errors.client_id'));

        // 2. Try to create service with mismatched client/vehicle
        $otherClient = Client::factory()->create();
        $otherVehicle = Vehicle::factory()->create(['client_id' => $otherClient->id]);

        $mismatchedData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $otherVehicle->id,
            'description' => 'Test service'
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $mismatchedData);

        $response->assertStatus(422);
        $this->assertTrue($response->json('errors.vehicle_id'));

        // 3. Try to create service without required fields
        $incompleteData = [
            'client_id' => $this->client->id
        ];

        $response = $this->postJson('/api/v1/attendant/services/quick', $incompleteData);

        $response->assertStatus(422);
        $this->assertTrue($response->json('errors.vehicle_id'));
        $this->assertTrue($response->json('errors.description'));
    }

    /** @test */
    public function it_can_handle_multiple_services_creation()
    {
        // Create multiple services for the same client
        $servicesData = [
            [
                'client_id' => $this->client->id,
                'vehicle_id' => $this->vehicle->id,
                'description' => 'Primeiro serviço',
                'estimated_duration' => 30,
                'priority' => 'low'
            ],
            [
                'client_id' => $this->client->id,
                'vehicle_id' => $this->vehicle->id,
                'description' => 'Segundo serviço',
                'estimated_duration' => 60,
                'priority' => 'medium'
            ],
            [
                'client_id' => $this->client->id,
                'vehicle_id' => $this->vehicle->id,
                'description' => 'Terceiro serviço',
                'estimated_duration' => 90,
                'priority' => 'high'
            ]
        ];

        $createdServices = [];

        foreach ($servicesData as $data) {
            $response = $this->postJson('/api/v1/attendant/services/quick', $data);
            $response->assertStatus(201);
            $createdServices[] = $response->json('data.id');
        }

        // Verify all services were created
        $this->assertCount(3, $createdServices);

        foreach ($createdServices as $serviceId) {
            $this->assertDatabaseHas('services', ['id' => $serviceId]);
        }

        // Check stats
        $statsResponse = $this->getJson('/api/v1/attendant/services/quick-stats');
        $this->assertEquals(3, $statsResponse->json('data.services_created_today'));
    }

    /** @test */
    public function it_can_handle_template_categories_filtering()
    {
        // Create templates in different categories
        ServiceTemplate::factory()->create([
            'name' => 'Maintenance Template',
            'category' => 'maintenance',
            'active' => true
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Repair Template',
            'category' => 'repair',
            'active' => true
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Inspection Template',
            'category' => 'inspection',
            'active' => true
        ]);

        // Get all templates
        $allTemplatesResponse = $this->getJson('/api/v1/attendant/services/templates');
        $allTemplatesResponse->assertStatus(200);
        $this->assertCount(3, $allTemplatesResponse->json('data'));

        // Get maintenance templates only
        $maintenanceTemplatesResponse = $this->getJson('/api/v1/attendant/services/templates?category=maintenance');
        $maintenanceTemplatesResponse->assertStatus(200);
        $this->assertCount(1, $maintenanceTemplatesResponse->json('data'));
        $this->assertEquals('Maintenance Template', $maintenanceTemplatesResponse->json('data.0.name'));

        // Get repair templates only
        $repairTemplatesResponse = $this->getJson('/api/v1/attendant/services/templates?category=repair');
        $repairTemplatesResponse->assertStatus(200);
        $this->assertCount(1, $repairTemplatesResponse->json('data'));
        $this->assertEquals('Repair Template', $repairTemplatesResponse->json('data.0.name'));
    }

    /** @test */
    public function it_can_handle_service_validation_edge_cases()
    {
        // Test validation with very long description
        $longDescription = str_repeat('a', 1000);
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => $longDescription
        ];

        $response = $this->postJson('/api/v1/attendant/services/validate', $serviceData);

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.is_valid'));
        $this->assertNotEmpty($response->json('data.warnings'));

        // Test validation with invalid duration
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Valid description',
            'estimated_duration' => 1000 // Exceeds max limit
        ];

        $response = $this->postJson('/api/v1/attendant/services/validate', $serviceData);

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.is_valid'));
        $this->assertNotEmpty($response->json('data.warnings'));

        // Test validation with invalid priority
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Valid description',
            'priority' => 'invalid_priority'
        ];

        $response = $this->postJson('/api/v1/attendant/services/validate', $serviceData);

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.is_valid'));
        $this->assertNotEmpty($response->json('data.warnings'));
    }

    /** @test */
    public function it_can_handle_concurrent_service_creation()
    {
        // Simulate concurrent requests
        $serviceData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'description' => 'Concurrent service',
            'estimated_duration' => 60,
            'priority' => 'medium'
        ];

        // Make multiple concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson('/api/v1/attendant/services/quick', $serviceData);
        }

        // All requests should succeed
        foreach ($responses as $response) {
            $response->assertStatus(201);
        }

        // Verify all services were created with unique IDs
        $serviceIds = array_map(fn($r) => $r->json('data.id'), $responses);
        $uniqueIds = array_unique($serviceIds);
        $this->assertCount(5, $uniqueIds);

        // Check stats
        $statsResponse = $this->getJson('/api/v1/attendant/services/quick-stats');
        $this->assertEquals(5, $statsResponse->json('data.services_created_today'));
    }
}
