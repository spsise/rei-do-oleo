<?php

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Repositories\ServiceRepository;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ServiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceRepository $serviceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceRepository = new ServiceRepository();
    }
    #[Test]
    public function create_service_creates_service_with_items(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);
        $serviceCenter = ServiceCenter::factory()->create();

        $serviceData = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'service_center_id' => $serviceCenter->id,
            'description' => 'Troca de óleo',
            'service_number' => 'SVC20240101-0001',
            'status_id' => 1,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'unit_price' => 50.00
                ]
            ]
        ];

        $result = $this->serviceRepository->createService($serviceData);

        $this->assertNotNull($result);
        $this->assertEquals('Troca de óleo', $result->description);
        $this->assertDatabaseHas('services', ['service_number' => 'SVC20240101-0001']);
    }
    #[Test]
    public function add_service_items_adds_items_to_service(): void
    {
        $service = Service::factory()->create();
        $items = [
            [
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => 50.00,
                'total_price' => 100.00
            ],
            [
                'product_id' => 2,
                'quantity' => 1,
                'unit_price' => 75.00,
                'total_price' => 75.00
            ]
        ];

        $this->serviceRepository->addServiceItems($service, $items);

        $this->assertDatabaseHas('service_items', [
            'service_id' => $service->id,
            'product_id' => 1,
            'quantity' => 2
        ]);
        $this->assertDatabaseHas('service_items', [
            'service_id' => $service->id,
            'product_id' => 2,
            'quantity' => 1
        ]);
    }
    #[Test]
    public function update_service_status_updates_status(): void
    {
        $service = Service::factory()->create(['status_id' => 1]);

        $result = $this->serviceRepository->updateServiceStatus($service->id, 'in_progress');

        $this->assertTrue($result);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'status_id' => 2 // in_progress
        ]);
    }
    #[Test]
    public function update_service_status_returns_false_when_service_not_found(): void
    {
        $result = $this->serviceRepository->updateServiceStatus(999, 'completed');

        $this->assertFalse($result);
    }
    #[Test]
    public function get_services_by_date_range_filters_by_dates(): void
    {
        $service1 = Service::factory()->create(['created_at' => '2024-01-15']);
        $service2 = Service::factory()->create(['created_at' => '2024-01-25']);
        $service3 = Service::factory()->create(['created_at' => '2024-02-15']);

        $result = $this->serviceRepository->getServicesByDateRange('2024-01-01', '2024-01-31');

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($service1));
        $this->assertTrue($result->contains($service2));
        $this->assertFalse($result->contains($service3));
    }
    #[Test]
    public function get_services_by_client_returns_client_services(): void
    {
        $client = Client::factory()->create();
        $otherClient = Client::factory()->create();

        $service1 = Service::factory()->create(['client_id' => $client->id]);
        $service2 = Service::factory()->create(['client_id' => $client->id]);
        $service3 = Service::factory()->create(['client_id' => $otherClient->id]);

        $result = $this->serviceRepository->getServicesByClient($client->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($service1));
        $this->assertTrue($result->contains($service2));
        $this->assertFalse($result->contains($service3));
    }
    #[Test]
    public function get_services_by_center_returns_center_services(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();
        $otherCenter = ServiceCenter::factory()->create();

        $service1 = Service::factory()->create(['service_center_id' => $serviceCenter->id]);
        $service2 = Service::factory()->create(['service_center_id' => $serviceCenter->id]);
        $service3 = Service::factory()->create(['service_center_id' => $otherCenter->id]);

        $result = $this->serviceRepository->getServicesByCenter($serviceCenter->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($service1));
        $this->assertTrue($result->contains($service2));
        $this->assertFalse($result->contains($service3));
    }
    #[Test]
    public function get_all_paginated_returns_paginated_results(): void
    {
        Service::factory()->count(25)->create();

        $result = $this->serviceRepository->getAllPaginated(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }
    #[Test]
    public function search_by_filters_applies_search_filter(): void
    {
        $service1 = Service::factory()->create(['description' => 'Troca de óleo']);
        $service2 = Service::factory()->create(['description' => 'Revisão completa']);
        $service3 = Service::factory()->create(['service_number' => 'SVC20240101-0001']);

        $result = $this->serviceRepository->searchByFilters(['search' => 'óleo']);
        $this->assertEquals(1, $result->total());

        $result = $this->serviceRepository->searchByFilters(['search' => 'SVC20240101']);
        $this->assertEquals(1, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_status_filter(): void
    {
        Service::factory()->count(3)->create(['status_id' => 1]); // waiting
        Service::factory()->count(2)->create(['status_id' => 2]); // in_progress
        Service::factory()->count(5)->create(['status_id' => 3]); // completed

        $result = $this->serviceRepository->searchByFilters(['status' => 1]);
        $this->assertEquals(3, $result->total());

        $result = $this->serviceRepository->searchByFilters(['status' => 3]);
        $this->assertEquals(5, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_service_center_filter(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();
        Service::factory()->count(4)->create(['service_center_id' => $serviceCenter->id]);
        Service::factory()->count(3)->create();

        $result = $this->serviceRepository->searchByFilters(['service_center_id' => $serviceCenter->id]);

        $this->assertEquals(4, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_technician_filter(): void
    {
        $technician = User::factory()->create();
        Service::factory()->count(3)->create(['technician_id' => $technician->id]);
        Service::factory()->count(2)->create();

        $result = $this->serviceRepository->searchByFilters(['technician_id' => $technician->id]);

        $this->assertEquals(3, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_date_range_filter(): void
    {
        Service::factory()->create(['created_at' => '2024-01-15']);
        Service::factory()->create(['created_at' => '2024-01-25']);
        Service::factory()->create(['created_at' => '2024-02-15']);

        $result = $this->serviceRepository->searchByFilters([
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-31'
        ]);

        $this->assertEquals(2, $result->total());
    }
    #[Test]
    public function find_includes_relationships(): void
    {
        $service = Service::factory()->create();
        ServiceItem::factory()->create(['service_id' => $service->id]);

        $result = $this->serviceRepository->find($service->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('client'));
        $this->assertTrue($result->relationLoaded('vehicle'));
        $this->assertTrue($result->relationLoaded('serviceCenter'));
        $this->assertTrue($result->relationLoaded('items'));
    }
    #[Test]
    public function create_creates_service_successfully(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'service_number' => 'SVC20240101-0001',
            'description' => 'Test service',
            'status_id' => 1
        ];

        $result = $this->serviceRepository->create($data);

        $this->assertNotNull($result);
        $this->assertEquals('Test service', $result->description);
        $this->assertDatabaseHas('services', ['service_number' => 'SVC20240101-0001']);
    }
    #[Test]
    public function update_updates_service_successfully(): void
    {
        $service = Service::factory()->create(['description' => 'Original description']);

        $result = $this->serviceRepository->update($service->id, ['description' => 'Updated description']);

        $this->assertNotNull($result);
        $this->assertEquals('Updated description', $result->description);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'description' => 'Updated description'
        ]);
    }
    #[Test]
    public function update_returns_null_when_service_not_found(): void
    {
        $result = $this->serviceRepository->update(999, ['description' => 'Updated']);

        $this->assertNull($result);
    }
    #[Test]
    public function delete_soft_deletes_service(): void
    {
        $service = Service::factory()->create();

        $result = $this->serviceRepository->delete($service->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }
    #[Test]
    public function delete_returns_false_when_service_not_found(): void
    {
        $result = $this->serviceRepository->delete(999);

        $this->assertFalse($result);
    }
    #[Test]
    public function get_by_service_center_returns_center_services(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();
        Service::factory()->count(5)->create(['service_center_id' => $serviceCenter->id]);
        Service::factory()->count(3)->create();

        $result = $this->serviceRepository->getServicesByCenter($serviceCenter->id);

        $this->assertEquals(5, $result->count());
        $this->assertTrue($result->every(fn($service) => $service->service_center_id === $serviceCenter->id));
    }
    #[Test]
    public function get_by_client_returns_client_services(): void
    {
        $client = Client::factory()->create();
        Service::factory()->count(4)->create(['client_id' => $client->id]);
        Service::factory()->count(2)->create();

        $result = $this->serviceRepository->getServicesByClient($client->id);

        $this->assertEquals(4, $result->count());
        $this->assertTrue($result->every(fn($service) => $service->client_id === $client->id));
    }
    #[Test]
    public function get_by_vehicle_returns_vehicle_services(): void
    {
        $vehicle = Vehicle::factory()->create();
        Service::factory()->count(3)->create(['vehicle_id' => $vehicle->id]);
        Service::factory()->count(2)->create();

        $result = $this->serviceRepository->getByVehicle($vehicle->id);

        $this->assertEquals(3, $result->count());
        $this->assertTrue($result->every(fn($service) => $service->vehicle_id === $vehicle->id));
    }
    #[Test]
    public function get_by_technician_returns_technician_services(): void
    {
        $technician = User::factory()->create();
        Service::factory()->count(6)->create(['technician_id' => $technician->id]);
        Service::factory()->count(2)->create();

        $result = $this->serviceRepository->getByTechnician($technician->id);

        $this->assertEquals(6, $result->count());
        $this->assertTrue($result->every(fn($service) => $service->technician_id === $technician->id));
    }
    #[Test]
    public function find_by_service_number_finds_service(): void
    {
        $service = Service::factory()->create(['service_number' => 'SVC20240101-0001']);
        Service::factory()->create(['service_number' => 'SVC20240101-0002']);

        $result = $this->serviceRepository->findByServiceNumber('SVC20240101-0001');

        $this->assertNotNull($result);
        $this->assertEquals($service->id, $result->id);
    }
    #[Test]
    public function find_by_service_number_returns_null_when_not_found(): void
    {
        $result = $this->serviceRepository->findByServiceNumber('INVALID-NUMBER');

        $this->assertNull($result);
    }
    #[Test]
    public function get_dashboard_stats_returns_aggregated_data(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();

        // Create services with different statuses
        Service::factory()->count(5)->create([
            'service_center_id' => $serviceCenter->id,
            'status_id' => 1, // waiting
            'total_amount' => 100.00
        ]);
        Service::factory()->count(3)->create([
            'service_center_id' => $serviceCenter->id,
            'status_id' => 2, // in_progress
            'total_amount' => 200.00
        ]);
        Service::factory()->count(7)->create([
            'service_center_id' => $serviceCenter->id,
            'status_id' => 3, // completed
            'total_amount' => 150.00
        ]);

        $result = $this->serviceRepository->getDashboardStats($serviceCenter->id);

        $this->assertArrayHasKey('total_services', $result);
        $this->assertArrayHasKey('pending_services', $result);
        $this->assertArrayHasKey('completed_services', $result);
        $this->assertArrayHasKey('total_revenue', $result);

        $this->assertEquals(15, $result['total_services']);
        $this->assertEquals(8, $result['pending_services']); // waiting + in_progress
        $this->assertEquals(7, $result['completed_services']);
    }
    #[Test]
    public function get_dashboard_stats_handles_null_service_center(): void
    {
        Service::factory()->count(10)->create(['status_id' => 3, 'total_amount' => 100.00]);

        $result = $this->serviceRepository->getDashboardStats(null);

        $this->assertArrayHasKey('total_services', $result);
        $this->assertEquals(10, $result['total_services']);
    }
    #[Test]
    public function repository_implements_interface(): void
    {
        $this->assertInstanceOf(
            \App\Domain\Service\Repositories\ServiceRepositoryInterface::class,
            $this->serviceRepository
        );
    }
    #[Test]
    public function database_queries_are_optimized(): void
    {
        Service::factory()->count(50)->create();

        DB::enableQueryLog();

        $this->serviceRepository->searchByFilters(['per_page' => 10]);

        $queries = DB::getQueryLog();

        // Should only have a few queries (search query + count query)
        $this->assertLessThan(5, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function bulk_operations_are_efficient(): void
    {
        $services = Service::factory()->count(100)->create();

        DB::enableQueryLog();

        $this->serviceRepository->bulkUpdateStatus(
            $services->pluck('id')->toArray(),
            'completed'
        );

        $queries = DB::getQueryLog();

        // Should use minimal queries for bulk update
        $this->assertLessThan(5, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function complex_filters_work_together(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();
        $technician = User::factory()->create();
        $client = Client::factory()->create();

        Service::factory()->create([
            'service_center_id' => $serviceCenter->id,
            'technician_id' => $technician->id,
            'client_id' => $client->id,
            'status_id' => 2,
            'description' => 'Troca de óleo',
            'created_at' => '2024-01-15'
        ]);

        // Service that shouldn't match
        Service::factory()->create([
            'service_center_id' => $serviceCenter->id,
            'status_id' => 1,
            'description' => 'Revisão',
            'created_at' => '2024-02-15'
        ]);

        $result = $this->serviceRepository->searchByFilters([
            'service_center_id' => $serviceCenter->id,
            'technician_id' => $technician->id,
            'status' => 2,
            'search' => 'óleo',
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-31'
        ]);

        $this->assertEquals(1, $result->total());
    }
}
