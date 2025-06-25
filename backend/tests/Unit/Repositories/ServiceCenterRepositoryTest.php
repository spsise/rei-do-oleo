<?php

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Repositories\ServiceCenterRepository;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ServiceCenterRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceCenterRepository $serviceCenterRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceCenterRepository = new ServiceCenterRepository();
    }
    #[Test]
    public function get_all_active_returns_only_active_service_centers(): void
    {
        ServiceCenter::factory()->count(5)->create(['active' => true]);
        ServiceCenter::factory()->count(3)->create(['active' => false]);

        $result = $this->serviceCenterRepository->getAllActive();

        $this->assertCount(5, $result);
        $this->assertTrue($result->every(fn($center) => $center->active));
    }
    #[Test]
    public function get_all_active_orders_by_name(): void
    {
        ServiceCenter::factory()->create(['name' => 'Centro B', 'active' => true]);
        ServiceCenter::factory()->create(['name' => 'Centro A', 'active' => true]);
        ServiceCenter::factory()->create(['name' => 'Centro C', 'active' => true]);

        $result = $this->serviceCenterRepository->getAllActive();

        $this->assertEquals('Centro A', $result->first()->name);
        $this->assertEquals('Centro C', $result->last()->name);
    }
    #[Test]
    public function find_by_code_returns_service_center(): void
    {
        $serviceCenter = ServiceCenter::factory()->create(['code' => 'SC001']);
        ServiceCenter::factory()->create(['code' => 'SC002']);

        $result = $this->serviceCenterRepository->findByCode('SC001');

        $this->assertNotNull($result);
        $this->assertEquals($serviceCenter->id, $result->id);
        $this->assertEquals('SC001', $result->code);
    }
    #[Test]
    public function find_by_code_returns_null_when_not_found(): void
    {
        $result = $this->serviceCenterRepository->findByCode('INVALID');

        $this->assertNull($result);
    }
    #[Test]
    public function get_by_region_filters_by_state(): void
    {
        ServiceCenter::factory()->create(['state' => 'SP', 'city' => 'São Paulo']);
        ServiceCenter::factory()->create(['state' => 'SP', 'city' => 'Campinas']);
        ServiceCenter::factory()->create(['state' => 'RJ', 'city' => 'Rio de Janeiro']);

        $result = $this->serviceCenterRepository->getByRegion('SP');

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($center) => $center->state === 'SP'));
    }
    #[Test]
    public function get_by_region_filters_by_state_and_city(): void
    {
        ServiceCenter::factory()->create(['state' => 'SP', 'city' => 'São Paulo']);
        ServiceCenter::factory()->create(['state' => 'SP', 'city' => 'Campinas']);
        ServiceCenter::factory()->create(['state' => 'RJ', 'city' => 'Rio de Janeiro']);

        $result = $this->serviceCenterRepository->getByRegion('SP', 'São Paulo');

        $this->assertCount(1, $result);
        $this->assertEquals('São Paulo', $result->first()->city);
    }
    #[Test]
    public function get_main_branch_returns_main_branch(): void
    {
        $mainBranch = ServiceCenter::factory()->create(['is_main_branch' => true]);
        ServiceCenter::factory()->count(3)->create(['is_main_branch' => false]);

        $result = $this->serviceCenterRepository->getMainBranch();

        $this->assertNotNull($result);
        $this->assertEquals($mainBranch->id, $result->id);
        $this->assertTrue($result->is_main_branch);
    }
    #[Test]
    public function get_main_branch_returns_null_when_no_main_branch(): void
    {
        ServiceCenter::factory()->count(3)->create(['is_main_branch' => false]);

        $result = $this->serviceCenterRepository->getMainBranch();

        $this->assertNull($result);
    }
    #[Test]
    public function find_nearby_returns_service_centers_within_radius(): void
    {
        // São Paulo coordinates
        $centerLat = -23.5505;
        $centerLng = -46.6333;

        // Create service centers at different distances
        $nearbyCenter = ServiceCenter::factory()->create([
            'latitude' => -23.5510, // Very close
            'longitude' => -46.6340
        ]);

        $mediumDistanceCenter = ServiceCenter::factory()->create([
            'latitude' => -23.5600, // About 1km away
            'longitude' => -46.6400
        ]);

        $farCenter = ServiceCenter::factory()->create([
            'latitude' => -22.9068, // Rio de Janeiro (far)
            'longitude' => -43.1729
        ]);

        $result = $this->serviceCenterRepository->findNearby($centerLat, $centerLng, 5); // 5km radius

        $this->assertGreaterThanOrEqual(1, $result->count());
        $this->assertTrue($result->contains($nearbyCenter));
        $this->assertTrue($result->contains($mediumDistanceCenter));
        $this->assertFalse($result->contains($farCenter));
    }
    #[Test]
    public function find_nearby_orders_by_distance(): void
    {
        $centerLat = -23.5505;
        $centerLng = -46.6333;

        $closestCenter = ServiceCenter::factory()->create([
            'latitude' => -23.5510,
            'longitude' => -46.6340
        ]);

        $fartherCenter = ServiceCenter::factory()->create([
            'latitude' => -23.5600,
            'longitude' => -46.6500
        ]);

        $result = $this->serviceCenterRepository->findNearby($centerLat, $centerLng, 10);

        $this->assertEquals($closestCenter->id, $result->first()->id);
    }
    #[Test]
    public function find_nearby_excludes_centers_without_coordinates(): void
    {
        $centerLat = -23.5505;
        $centerLng = -46.6333;

        ServiceCenter::factory()->create([
            'latitude' => null,
            'longitude' => null
        ]);

        ServiceCenter::factory()->create([
            'latitude' => -23.5510,
            'longitude' => -46.6340
        ]);

        $result = $this->serviceCenterRepository->findNearby($centerLat, $centerLng, 10);

        $this->assertCount(1, $result);
    }
    #[Test]
    public function get_with_manager_info_includes_manager_relationship(): void
    {
        $manager = User::factory()->create();
        $serviceCenter = ServiceCenter::factory()->create(['manager_id' => $manager->id]);
        ServiceCenter::factory()->create(['manager_id' => null]);

        $result = $this->serviceCenterRepository->getWithManagerInfo();

        $managerCenter = $result->where('id', $serviceCenter->id)->first();
        $this->assertTrue($managerCenter->relationLoaded('manager'));
        $this->assertEquals($manager->id, $managerCenter->manager->id);
    }
    #[Test]
    public function get_all_paginated_returns_paginated_results(): void
    {
        ServiceCenter::factory()->count(25)->create();

        $result = $this->serviceCenterRepository->getAllPaginated(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }
    #[Test]
    public function search_by_filters_applies_search_filter(): void
    {
        ServiceCenter::factory()->create(['name' => 'Centro Automotivo São Paulo']);
        ServiceCenter::factory()->create(['name' => 'Oficina Rio de Janeiro']);
        ServiceCenter::factory()->create(['code' => 'SP001']);

        $result = $this->serviceCenterRepository->searchByFilters(['search' => 'São Paulo']);
        $this->assertEquals(1, $result->total());

        $result = $this->serviceCenterRepository->searchByFilters(['search' => 'SP001']);
        $this->assertEquals(1, $result->total());
    }
    #[Test]
    public function search_by_filters_applies_state_filter(): void
    {
        ServiceCenter::factory()->count(3)->create(['state' => 'SP']);
        ServiceCenter::factory()->count(2)->create(['state' => 'RJ']);
        ServiceCenter::factory()->count(1)->create(['state' => 'MG']);

        $result = $this->serviceCenterRepository->searchByFilters(['state' => 'SP']);

        $this->assertEquals(3, $result->total());
        $this->assertTrue($result->every(fn($center) => $center->state === 'SP'));
    }
    #[Test]
    public function search_by_filters_applies_city_filter(): void
    {
        ServiceCenter::factory()->count(2)->create(['city' => 'São Paulo']);
        ServiceCenter::factory()->count(1)->create(['city' => 'Campinas']);
        ServiceCenter::factory()->count(1)->create(['city' => 'Santos']);

        $result = $this->serviceCenterRepository->searchByFilters(['city' => 'São Paulo']);

        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->every(fn($center) => $center->city === 'São Paulo'));
    }
    #[Test]
    public function search_by_filters_applies_active_filter(): void
    {
        ServiceCenter::factory()->count(4)->create(['active' => true]);
        ServiceCenter::factory()->count(2)->create(['active' => false]);

        $result = $this->serviceCenterRepository->searchByFilters(['active' => true]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue($result->every(fn($center) => $center->active));
    }
    #[Test]
    public function find_includes_basic_relationships(): void
    {
        $manager = User::factory()->create();
        $serviceCenter = ServiceCenter::factory()->create(['manager_id' => $manager->id]);

        $result = $this->serviceCenterRepository->find($serviceCenter->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('manager'));
    }
    #[Test]
    public function find_returns_null_when_not_found(): void
    {
        $result = $this->serviceCenterRepository->find(999);

        $this->assertNull($result);
    }
    #[Test]
    public function create_creates_service_center_successfully(): void
    {
        $data = [
            'code' => 'SC001',
            'name' => 'Centro Automotivo',
            'state' => 'SP',
            'city' => 'São Paulo',
            'active' => true
        ];

        $result = $this->serviceCenterRepository->create($data);

        $this->assertNotNull($result);
        $this->assertEquals('Centro Automotivo', $result->name);
        $this->assertDatabaseHas('service_centers', ['code' => 'SC001']);
    }
    #[Test]
    public function update_updates_service_center_successfully(): void
    {
        $serviceCenter = ServiceCenter::factory()->create(['name' => 'Original Name']);

        $result = $this->serviceCenterRepository->update($serviceCenter->id, ['name' => 'Updated Name']);

        $this->assertNotNull($result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertDatabaseHas('service_centers', [
            'id' => $serviceCenter->id,
            'name' => 'Updated Name'
        ]);
    }
    #[Test]
    public function update_returns_null_when_service_center_not_found(): void
    {
        $result = $this->serviceCenterRepository->update(999, ['name' => 'Updated Name']);

        $this->assertNull($result);
    }
    #[Test]
    public function delete_soft_deletes_service_center(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();

        $result = $this->serviceCenterRepository->delete($serviceCenter->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('service_centers', ['id' => $serviceCenter->id]);
    }
    #[Test]
    public function delete_returns_false_when_service_center_not_found(): void
    {
        $result = $this->serviceCenterRepository->delete(999);

        $this->assertFalse($result);
    }
    #[Test]
    public function get_statistics_returns_aggregated_data(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();

        // Create related data
        User::factory()->count(5)->create(['service_center_id' => $serviceCenter->id]);
        \App\Domain\Service\Models\Service::factory()->count(10)->create([
            'service_center_id' => $serviceCenter->id,
            'status_id' => 3, // completed
            'total_amount' => 100.00
        ]);

        $result = $this->serviceCenterRepository->getStatistics($serviceCenter->id);

        $this->assertArrayHasKey('total_users', $result);
        $this->assertArrayHasKey('total_services', $result);
        $this->assertArrayHasKey('total_revenue', $result);
        $this->assertEquals(5, $result['total_users']);
        $this->assertEquals(10, $result['total_services']);
    }
    #[Test]
    public function repository_implements_interface(): void
    {
        $this->assertInstanceOf(
            \App\Domain\Service\Repositories\ServiceCenterRepositoryInterface::class,
            $this->serviceCenterRepository
        );
    }
    #[Test]
    public function geolocation_queries_are_optimized(): void
    {
        ServiceCenter::factory()->count(50)->create([
            'latitude' => fake()->latitude(-30, -20),
            'longitude' => fake()->longitude(-50, -40)
        ]);

        DB::enableQueryLog();

        $this->serviceCenterRepository->findNearby(-23.5505, -46.6333, 10);

        $queries = DB::getQueryLog();

        // Should only have one optimized query
        $this->assertLessThan(3, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function search_with_multiple_filters_works_correctly(): void
    {
        ServiceCenter::factory()->create([
            'name' => 'Centro Automotivo SP',
            'state' => 'SP',
            'city' => 'São Paulo',
            'active' => true
        ]);

        ServiceCenter::factory()->create([
            'name' => 'Oficina SP',
            'state' => 'SP',
            'city' => 'Campinas',
            'active' => true
        ]);

        ServiceCenter::factory()->create([
            'name' => 'Centro RJ',
            'state' => 'RJ',
            'city' => 'Rio de Janeiro',
            'active' => false
        ]);

        $result = $this->serviceCenterRepository->searchByFilters([
            'search' => 'Centro',
            'state' => 'SP',
            'active' => true
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Centro Automotivo SP', $result->first()->name);
    }
    #[Test]
    public function distance_calculation_is_accurate(): void
    {
        // São Paulo coordinates
        $spLat = -23.5505;
        $spLng = -46.6333;

        // Create a service center in Rio de Janeiro (known distance ~357km)
        $rjCenter = ServiceCenter::factory()->create([
            'latitude' => -22.9068,
            'longitude' => -43.1729
        ]);

        $result = $this->serviceCenterRepository->findNearby($spLat, $spLng, 400); // 400km radius

        $this->assertTrue($result->contains($rjCenter));

        $result = $this->serviceCenterRepository->findNearby($spLat, $spLng, 300); // 300km radius

        $this->assertFalse($result->contains($rjCenter));
    }
    #[Test]
    public function bulk_operations_are_efficient(): void
    {
        $serviceCenters = ServiceCenter::factory()->count(20)->create();

        DB::enableQueryLog();

        $this->serviceCenterRepository->bulkUpdate(
            $serviceCenters->pluck('id')->toArray(),
            ['active' => false]
        );

        $queries = DB::getQueryLog();

        // Should use minimal queries for bulk update
        $this->assertLessThan(3, count($queries));

        DB::disableQueryLog();
    }
    #[Test]
    public function coordinates_validation_works(): void
    {
        // Valid coordinates
        $validCenter = ServiceCenter::factory()->create([
            'latitude' => -23.5505,
            'longitude' => -46.6333
        ]);

        $this->assertGreaterThanOrEqual(-90, $validCenter->latitude);
        $this->assertLessThanOrEqual(90, $validCenter->latitude);
        $this->assertGreaterThanOrEqual(-180, $validCenter->longitude);
        $this->assertLessThanOrEqual(180, $validCenter->longitude);
    }
    #[Test]
    public function brazilian_specific_data_is_handled(): void
    {
        $cnpj = $this->generateValidCNPJ();
        $serviceCenter = ServiceCenter::factory()->create([
            'cnpj' => $cnpj,
            'zip_code' => '01234-567',
            'state' => 'SP'
        ]);

        $this->assertEquals($cnpj, $serviceCenter->cnpj);
        $this->assertEquals('01234-567', $serviceCenter->zip_code);
        $this->assertEquals(2, strlen($serviceCenter->state));
    }
}
