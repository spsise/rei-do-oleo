<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Service\Services\AttendantServiceService;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceTemplate;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Repositories\ServiceTemplateRepositoryInterface;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

class AttendantServiceServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AttendantServiceService $service;
    private $serviceRepository;
    private $templateRepository;
    private $clientRepository;
    private $vehicleRepository;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->serviceRepository = Mockery::mock(ServiceRepositoryInterface::class);
        $this->templateRepository = Mockery::mock(ServiceTemplateRepositoryInterface::class);
        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->vehicleRepository = Mockery::mock(VehicleRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        // Create service instance
        $this->service = new AttendantServiceService(
            $this->serviceRepository,
            $this->templateRepository,
            $this->clientRepository,
            $this->vehicleRepository,
            $this->userRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_quick_service()
    {
        // Create test data
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);
        $user = User::factory()->create(['service_center_id' => 1]);
        $serviceStatus = ServiceStatus::factory()->create(['name' => 'scheduled']);

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client->id)
            ->andReturn($client);

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        $this->serviceRepository->shouldReceive('createService')
            ->once()
            ->andReturn(Service::factory()->make());

        // Test data
        $serviceData = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service',
            'estimated_duration' => 60,
            'priority' => 'medium'
        ];

        // Execute
        $result = $this->service->createQuickService($serviceData);

        // Assert
        $this->assertInstanceOf(Service::class, $result);
    }

    /** @test */
    public function it_can_create_quick_service_with_template()
    {
        // Create test data
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);
        $user = User::factory()->create(['service_center_id' => 1]);
        $template = ServiceTemplate::factory()->create([
            'name' => 'Test Template',
            'description' => 'Template description',
            'estimated_duration' => 90,
            'priority' => 'high'
        ]);

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client->id)
            ->andReturn($client);

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        $this->templateRepository->shouldReceive('find')
            ->with($template->id)
            ->andReturn($template);

        $this->serviceRepository->shouldReceive('createService')
            ->once()
            ->andReturn(Service::factory()->make());

        // Test data
        $serviceData = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service',
            'template_id' => $template->id
        ];

        // Execute
        $result = $this->service->createQuickService($serviceData);

        // Assert
        $this->assertInstanceOf(Service::class, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_client()
    {
        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        // Test data
        $serviceData = [
            'client_id' => 999,
            'vehicle_id' => 1,
            'description' => 'Test service'
        ];

        // Execute and assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cliente não encontrado');

        $this->service->createQuickService($serviceData);
    }

    /** @test */
    public function it_throws_exception_for_invalid_vehicle()
    {
        // Create test data
        $client = Client::factory()->create();

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client->id)
            ->andReturn($client);

        $this->vehicleRepository->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        // Test data
        $serviceData = [
            'client_id' => $client->id,
            'vehicle_id' => 999,
            'description' => 'Test service'
        ];

        // Execute and assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Veículo não encontrado');

        $this->service->createQuickService($serviceData);
    }

    /** @test */
    public function it_throws_exception_for_mismatched_client_vehicle()
    {
        // Create test data
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client2->id]);

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client1->id)
            ->andReturn($client1);

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        // Test data
        $serviceData = [
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service'
        ];

        // Execute and assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Veículo não pertence ao cliente informado');

        $this->service->createQuickService($serviceData);
    }

    /** @test */
    public function it_can_get_templates()
    {
        // Create test templates
        $templates = ServiceTemplate::factory()->count(3)->create(['active' => true]);

        // Mock repository
        $this->templateRepository->shouldReceive('getActive')
            ->with(null)
            ->andReturn($templates);

        // Execute
        $result = $this->service->getTemplates();

        // Assert
        $this->assertCount(3, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    /** @test */
    public function it_can_get_templates_by_category()
    {
        // Create test templates
        $maintenanceTemplates = ServiceTemplate::factory()->count(2)->create([
            'category' => 'maintenance',
            'active' => true
        ]);

        // Mock repository
        $this->templateRepository->shouldReceive('getActive')
            ->with('maintenance')
            ->andReturn($maintenanceTemplates);

        // Execute
        $result = $this->service->getTemplates('maintenance');

        // Assert
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_can_validate_service_data()
    {
        // Create test data
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client->id)
            ->andReturn($client);

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        // Test data
        $validationData = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Valid service'
        ];

        // Execute
        $result = $this->service->validateService($validationData);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertTrue($result['is_valid']);
    }

    /** @test */
    public function it_validates_invalid_client_in_validation()
    {
        // Mock repository
        $this->clientRepository->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        // Test data
        $validationData = [
            'client_id' => 999,
            'vehicle_id' => 1,
            'description' => 'Invalid service'
        ];

        // Execute
        $result = $this->service->validateService($validationData);

        // Assert
        $this->assertFalse($result['is_valid']);
        $this->assertContains('Cliente não encontrado', $result['warnings']);
    }

    /** @test */
    public function it_can_get_suggestions()
    {
        // Create test data
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        // Mock repositories
        $this->serviceRepository->shouldReceive('getRecentByClient')
            ->with($client->id, 5)
            ->andReturn(collect([]));

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        // Execute
        $result = $this->service->getSuggestions($client->id, $vehicle->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('recent_services', $result);
        $this->assertArrayHasKey('recommended_services', $result);
        $this->assertArrayHasKey('maintenance_due', $result);
    }

    /** @test */
    public function it_can_get_quick_stats()
    {
        // Create test user
        $user = User::factory()->create();

        // Mock repositories
        $this->serviceRepository->shouldReceive('getTodayServicesCount')
            ->with($user->id)
            ->andReturn(5);

        $this->serviceRepository->shouldReceive('getPendingServicesCount')
            ->with($user->id)
            ->andReturn(3);

        $this->serviceRepository->shouldReceive('getCompletedTodayCount')
            ->with($user->id)
            ->andReturn(2);

        // Execute
        $result = $this->service->getQuickStats();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('services_created_today', $result);
        $this->assertArrayHasKey('pending_services', $result);
        $this->assertArrayHasKey('completed_today', $result);
        $this->assertArrayHasKey('average_creation_time', $result);
    }

    /** @test */
    public function it_can_create_complete_service()
    {
        // Create test data
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);
        $user = User::factory()->create(['service_center_id' => 1]);

        // Mock repositories
        $this->clientRepository->shouldReceive('find')
            ->with($client->id)
            ->andReturn($client);

        $this->vehicleRepository->shouldReceive('find')
            ->with($vehicle->id)
            ->andReturn($vehicle);

        $this->serviceRepository->shouldReceive('createService')
            ->once()
            ->andReturn(Service::factory()->make());

        // Test data
        $serviceData = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Complete service',
            'scheduled_at' => now()->addDay()->toISOString(),
            'notes' => 'Test notes',
            'observations' => 'Test observations'
        ];

        // Execute
        $result = $this->service->createCompleteService($serviceData);

        // Assert
        $this->assertInstanceOf(Service::class, $result);
    }
}
