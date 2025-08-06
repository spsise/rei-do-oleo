<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Services\ServiceService;
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\Service\Models\Service;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

class ServiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceService $serviceService;
    protected $serviceRepositoryMock;
    protected $clientRepositoryMock;
    protected $vehicleRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceRepositoryMock = Mockery::mock(ServiceRepositoryInterface::class);
        $this->clientRepositoryMock = Mockery::mock(ClientRepositoryInterface::class);
        $this->vehicleRepositoryMock = Mockery::mock(VehicleRepositoryInterface::class);

        $this->serviceService = new ServiceService(
            $this->serviceRepositoryMock,
            $this->clientRepositoryMock,
            $this->vehicleRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    #[Test]
    public function create_service_validates_client_exists(): void
    {
        $data = [
            'client_id' => 999,
            'vehicle_id' => 1,
            'service_center_id' => 1,
            'description' => 'Troca de óleo'
        ];

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cliente não encontrado');

        $this->serviceService->createService($data);
    }
    #[Test]
    public function create_service_validates_vehicle_exists(): void
    {
        $data = [
            'client_id' => 1,
            'vehicle_id' => 999,
            'service_center_id' => 1,
            'description' => 'Troca de óleo'
        ];

        $client = Client::factory()->make(['id' => 1]);

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($client);

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Veículo não encontrado');

        $this->serviceService->createService($data);
    }
    #[Test]
    public function create_service_validates_vehicle_belongs_to_client(): void
    {
        $data = [
            'client_id' => 1,
            'vehicle_id' => 2,
            'service_center_id' => 1,
            'description' => 'Troca de óleo'
        ];

        $client = Client::factory()->make(['id' => 1]);
        $vehicle = Vehicle::factory()->make(['id' => 2, 'client_id' => 999]);

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($client);

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with(2)
            ->andReturn($vehicle);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Veículo não pertence ao cliente informado');

        $this->serviceService->createService($data);
    }
    #[Test]
    public function create_service_creates_successfully(): void
    {
        $data = [
            'client_id' => 1,
            'vehicle_id' => 2,
            'service_center_id' => 1,
            'description' => 'Troca de óleo'
        ];

        $client = Client::factory()->make(['id' => 1]);
        $vehicle = Vehicle::factory()->make(['id' => 2, 'client_id' => 1]);
        $service = Service::factory()->make(['id' => 1]);

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($client);

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with(2)
            ->andReturn($vehicle);

        $this->serviceRepositoryMock
            ->shouldReceive('createService')
            ->with($data)
            ->andReturn($service);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->serviceService->createService($data);

        $this->assertEquals($service, $result);
    }
    #[Test]
    public function update_service_status_updates_and_clears_cache(): void
    {
        $serviceId = 1;
        $status = 'in_progress';
        $service = Service::factory()->make(['id' => $serviceId]);

        $this->serviceRepositoryMock
            ->shouldReceive('updateServiceStatus')
            ->with($serviceId, $status, null)
            ->andReturn(true);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn($service);

        $result = $this->serviceService->updateServiceStatus($serviceId, $status);

        $this->assertTrue($result);
    }
    #[Test]
    public function start_service_updates_status_to_in_progress(): void
    {
        $serviceId = 1;

        $this->serviceRepositoryMock
            ->shouldReceive('updateServiceStatus')
            ->with($serviceId, 'in_progress', null)
            ->andReturn(true);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn(Service::factory()->make());

        $result = $this->serviceService->startService($serviceId);

        $this->assertTrue($result);
    }
    #[Test]
    public function complete_service_updates_status_to_completed(): void
    {
        $serviceId = 1;

        $this->serviceRepositoryMock
            ->shouldReceive('updateServiceStatus')
            ->with($serviceId, 'completed', null)
            ->andReturn(true);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn(Service::factory()->make());

        $result = $this->serviceService->completeService($serviceId);

        $this->assertTrue($result);
    }
    #[Test]
    public function cancel_service_updates_status_to_cancelled(): void
    {
        $serviceId = 1;

        $this->serviceRepositoryMock
            ->shouldReceive('updateServiceStatus')
            ->with($serviceId, 'cancelled', null)
            ->andReturn(true);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn(Service::factory()->make());

        $result = $this->serviceService->cancelService($serviceId);

        $this->assertTrue($result);
    }
    #[Test]
    public function add_service_items_validates_service_exists(): void
    {
        $serviceId = 999;
        $items = [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 50.00]
        ];

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Serviço não encontrado');

        $this->serviceService->addServiceItems($serviceId, $items);
    }
    #[Test]
    public function add_service_items_adds_items_successfully(): void
    {
        $serviceId = 1;
        $items = [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 50.00]
        ];
        $service = Service::factory()->make(['id' => $serviceId]);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn($service);

        $this->serviceRepositoryMock
            ->shouldReceive('addServiceItems')
            ->with($service, $items)
            ->once();

        $this->serviceService->addServiceItems($serviceId, $items);
    }
    #[Test]
    public function get_services_by_date_range_uses_cache(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';
        $expectedServices = new Collection();

        Cache::shouldReceive('remember')
            ->once()
            ->with("services_range_{$startDate}_{$endDate}", 1800, Mockery::type('Closure'))
            ->andReturn($expectedServices);

        $this->serviceRepositoryMock
            ->shouldReceive('getServicesByDateRange')
            ->with($startDate, $endDate)
            ->andReturn($expectedServices);

        $result = $this->serviceService->getServicesByDateRange($startDate, $endDate);

        $this->assertEquals($expectedServices, $result);
    }
    #[Test]
    public function get_services_by_client_returns_client_services(): void
    {
        $clientId = 1;
        $expectedServices = new Collection();

        $this->serviceRepositoryMock
            ->shouldReceive('getServicesByClient')
            ->with($clientId)
            ->andReturn($expectedServices);

        $result = $this->serviceService->getServicesByClient($clientId);

        $this->assertEquals($expectedServices, $result);
    }
    #[Test]
    public function get_services_by_center_uses_cache(): void
    {
        $serviceCenterId = 1;
        $expectedServices = new Collection();

        Cache::shouldReceive('remember')
            ->once()
            ->with("services_center_{$serviceCenterId}", 1800, Mockery::type('Closure'))
            ->andReturn($expectedServices);

        $this->serviceRepositoryMock
            ->shouldReceive('getServicesByCenter')
            ->with($serviceCenterId)
            ->andReturn($expectedServices);

        $result = $this->serviceService->getServicesByCenter($serviceCenterId);

        $this->assertEquals($expectedServices, $result);
    }
    #[Test]
    public function search_services_returns_paginated_results(): void
    {
        $filters = [
            'search' => 'troca de óleo',
            'status' => 'completed'
        ];

        $expectedResults = new LengthAwarePaginator([], 0, 15);

        $this->serviceRepositoryMock
            ->shouldReceive('searchByFilters')
            ->with($filters)
            ->andReturn($expectedResults);

        $result = $this->serviceService->searchServices($filters);

        $this->assertEquals($expectedResults, $result);
    }
    #[Test]
    public function generate_service_number_creates_unique_number(): void
    {
        $serviceNumber = $this->serviceService->generateServiceNumber();

        $this->assertMatchesRegularExpression('/^SVC\d{8}-\d{4}$/', $serviceNumber);
        $this->assertStringStartsWith('SVC' . date('Ymd'), $serviceNumber);
    }
    #[Test]
    public function calculate_service_total_sums_items_and_labor(): void
    {
        $items = [
            ['quantity' => 2, 'unit_price' => 50.00, 'discount' => 0],
            ['quantity' => 1, 'unit_price' => 100.00, 'discount' => 10.00]
        ];
        $laborCost = 150.00;

        $result = $this->serviceService->calculateServiceTotal($items, $laborCost);

        // (2 * 50) + (100 - 10) + 150 = 100 + 90 + 150 = 340
        $this->assertEquals(340.00, $result);
    }
    #[Test]
    public function calculate_service_total_handles_empty_items(): void
    {
        $items = [];
        $laborCost = 100.00;

        $result = $this->serviceService->calculateServiceTotal($items, $laborCost);

        $this->assertEquals(100.00, $result);
    }
    #[Test]
    public function apply_discount_calculates_final_amount(): void
    {
        $totalAmount = 1000.00;
        $discountPercentage = 15.00;

        $result = $this->serviceService->applyDiscount($totalAmount, $discountPercentage);

        $this->assertEquals(850.00, $result);
    }
    #[Test]
    public function apply_discount_handles_zero_discount(): void
    {
        $totalAmount = 500.00;
        $discountPercentage = 0;

        $result = $this->serviceService->applyDiscount($totalAmount, $discountPercentage);

        $this->assertEquals(500.00, $result);
    }
    #[Test]
    public function get_service_statistics_returns_aggregated_data(): void
    {
        $filters = ['status' => 'completed'];
        $expectedStats = [
            'total_services' => 100,
            'total_revenue' => 25000.00,
            'avg_service_value' => 250.00,
            'completion_rate' => 85.5
        ];

        $this->serviceRepositoryMock
            ->shouldReceive('getStatistics')
            ->with($filters)
            ->andReturn($expectedStats);

        $result = $this->serviceService->getServiceStatistics($filters);

        $this->assertEquals($expectedStats, $result);
    }
    #[Test]
    public function get_technician_workload_returns_workload_data(): void
    {
        $technicianId = 1;
        $expectedWorkload = [
            'active_services' => 5,
            'completed_today' => 3,
            'avg_completion_time' => 2.5
        ];

        $this->serviceRepositoryMock
            ->shouldReceive('getTechnicianWorkload')
            ->with($technicianId)
            ->andReturn($expectedWorkload);

        $result = $this->serviceService->getTechnicianWorkload($technicianId);

        $this->assertEquals($expectedWorkload, $result);
    }
    #[Test]
    public function schedule_service_validates_future_date(): void
    {
        $serviceId = 1;
        $scheduledDate = now()->subDay(); // Past date

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data de agendamento deve ser futura');

        $this->serviceService->scheduleService($serviceId, $scheduledDate);
    }
    #[Test]
    public function schedule_service_schedules_successfully(): void
    {
        $serviceId = 1;
        $scheduledDate = now()->addDay();
        $service = Service::factory()->make(['id' => $serviceId]);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn($service);

        $this->serviceRepositoryMock
            ->shouldReceive('update')
            ->with($serviceId, ['scheduled_at' => $scheduledDate])
            ->andReturn($service);

        $result = $this->serviceService->scheduleService($serviceId, $scheduledDate);

        $this->assertTrue($result);
    }
    #[Test]
    public function get_pending_services_returns_pending_services(): void
    {
        $serviceCenterId = 1;
        $expectedServices = new Collection();

        $this->serviceRepositoryMock
            ->shouldReceive('getPendingServices')
            ->with($serviceCenterId)
            ->andReturn($expectedServices);

        $result = $this->serviceService->getPendingServices($serviceCenterId);

        $this->assertEquals($expectedServices, $result);
    }
    #[Test]
    public function get_overdue_services_returns_overdue_services(): void
    {
        $expectedServices = new Collection();

        $this->serviceRepositoryMock
            ->shouldReceive('getOverdueServices')
            ->andReturn($expectedServices);

        $result = $this->serviceService->getOverdueServices();

        $this->assertEquals($expectedServices, $result);
    }
    #[Test]
    public function update_service_amounts_recalculates_totals(): void
    {
        $serviceId = 1;
        $service = Service::factory()->make(['id' => $serviceId]);

        $this->serviceRepositoryMock
            ->shouldReceive('find')
            ->with($serviceId)
            ->andReturn($service);

        $this->serviceRepositoryMock
            ->shouldReceive('updateAmounts')
            ->with($service)
            ->andReturn(true);

        $result = $this->serviceService->updateServiceAmounts($serviceId);

        $this->assertTrue($result);
    }
    #[Test]
    public function clear_service_caches_removes_related_cache_entries(): void
    {
        $service = Service::factory()->make([
            'id' => 1,
            'service_center_id' => 2,
            'client_id' => 3
        ]);

        Cache::shouldReceive('forget')
            ->with("services_center_2")
            ->once();

        Cache::shouldReceive('forget')
            ->with("client_services_3")
            ->once();

        Cache::shouldReceive('tags')
            ->with(['services'])
            ->andReturnSelf();

        Cache::shouldReceive('flush')
            ->once();

        $this->serviceService->clearServiceCaches($service);
    }
    #[Test]
    public function bulk_update_service_status_processes_multiple_services(): void
    {
        $serviceIds = [1, 2, 3];
        $newStatus = 'completed';

        $this->serviceRepositoryMock
            ->shouldReceive('bulkUpdateStatus')
            ->with($serviceIds, $newStatus)
            ->andReturn(3);

        $result = $this->serviceService->bulkUpdateServiceStatus($serviceIds, $newStatus);

        $this->assertEquals(3, $result);
    }
    #[Test]
    public function export_services_data_returns_formatted_data(): void
    {
        $filters = ['status' => 'completed'];
        $services = new Collection([
            Service::factory()->make(['service_number' => 'SVC20240101-0001']),
            Service::factory()->make(['service_number' => 'SVC20240101-0002'])
        ]);

        $this->serviceRepositoryMock
            ->shouldReceive('getForExport')
            ->with($filters)
            ->andReturn($services);

        $result = $this->serviceService->exportServicesData($filters);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('service_number', $result[0]);
    }
}
