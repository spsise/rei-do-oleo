<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCenterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ServiceCenter $serviceCenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceCenter = ServiceCenter::factory()->create();
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'code',
            'name',
            'slug',
            'cnpj',
            'state_registration',
            'legal_name',
            'trade_name',
            'address_line',
            'number',
            'complement',
            'neighborhood',
            'city',
            'state',
            'zip_code',
            'latitude',
            'longitude',
            'phone',
            'whatsapp',
            'email',
            'website',
            'facebook_url',
            'instagram_url',
            'google_maps_url',
            'manager_id',
            'technical_responsible',
            'opening_date',
            'operating_hours',
            'is_main_branch',
            'active',
            'observations',
        ];

        $this->assertEquals($fillable, $this->serviceCenter->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'latitude' => 'float',
            'longitude' => 'float',
            'manager_id' => 'int',
            'is_main_branch' => 'boolean',
            'active' => 'boolean',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->serviceCenter->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_has_one_manager()
    {
        $manager = User::factory()->manager()->create();
        $this->serviceCenter->update(['manager_id' => $manager->id]);

        $this->assertInstanceOf(HasOne::class, $this->serviceCenter->manager());
        $this->assertInstanceOf(User::class, $this->serviceCenter->manager);
        $this->assertEquals($manager->id, $this->serviceCenter->manager_id);
    }
    #[Test]
    public function it_has_many_users()
    {
        $this->assertInstanceOf(HasMany::class, $this->serviceCenter->users());

        $user = User::factory()->create(['service_center_id' => $this->serviceCenter->id]);

        $this->assertTrue($this->serviceCenter->users->contains($user));
    }
    #[Test]
    public function it_has_many_services()
    {
        $this->assertInstanceOf(HasMany::class, $this->serviceCenter->services());

        $service = Service::factory()->create(['service_center_id' => $this->serviceCenter->id]);

        $this->assertTrue($this->serviceCenter->services->contains($service));
    }
    #[Test]
    public function active_scope_returns_only_active_service_centers()
    {
        ServiceCenter::factory()->create(['active' => true]);
        ServiceCenter::factory()->create(['active' => false]);
        ServiceCenter::factory()->create(['active' => true]);

        $activeServiceCenters = ServiceCenter::active()->get();

        $this->assertEquals(3, $activeServiceCenters->count()); // 2 + setUp serviceCenter
        $this->assertTrue($activeServiceCenters->every(fn($sc) => $sc->active === true));
    }
    #[Test]
    public function by_state_scope_filters_by_state()
    {
        ServiceCenter::factory()->create(['state' => 'SP']);
        ServiceCenter::factory()->create(['state' => 'RJ']);
        ServiceCenter::factory()->create(['state' => 'SP']);

        $results = ServiceCenter::byState('SP')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($sc) => $sc->state === 'SP'));
    }
    #[Test]
    public function by_city_scope_filters_by_city()
    {
        ServiceCenter::factory()->create(['city' => 'São Paulo']);
        ServiceCenter::factory()->create(['city' => 'Rio de Janeiro']);
        ServiceCenter::factory()->create(['city' => 'São Paulo']);

        $results = ServiceCenter::byCity('São Paulo')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($sc) => $sc->city === 'São Paulo'));
    }
    #[Test]
    public function main_branch_scope_filters_main_branches()
    {
        ServiceCenter::factory()->create(['is_main_branch' => true]);
        ServiceCenter::factory()->create(['is_main_branch' => false]);
        ServiceCenter::factory()->create(['is_main_branch' => true]);

        $mainBranches = ServiceCenter::mainBranch()->get();

        $this->assertEquals(2, $mainBranches->count());
        $this->assertTrue($mainBranches->every(fn($sc) => $sc->is_main_branch === true));
    }
    #[Test]
    public function nearby_scope_finds_service_centers_within_radius()
    {
        // São Paulo coordinates
        $centerLat = -23.5505;
        $centerLng = -46.6333;

        // Create service centers at different distances
        ServiceCenter::factory()->create([
            'latitude' => -23.5505,
            'longitude' => -46.6333, // Same location
        ]);

        ServiceCenter::factory()->create([
            'latitude' => -23.5600,
            'longitude' => -46.6400, // Close (~1km)
        ]);

        ServiceCenter::factory()->create([
            'latitude' => -22.9068,
            'longitude' => -43.1729, // Rio de Janeiro (~350km)
        ]);

        $nearbyServiceCenters = ServiceCenter::nearby($centerLat, $centerLng, 50)->get();

        $this->assertGreaterThanOrEqual(2, $nearbyServiceCenters->count());
    }
    #[Test]
    public function calculate_distance_to_method_calculates_correct_distance()
    {
        $this->serviceCenter->update([
            'latitude' => -23.5505,
            'longitude' => -46.6333
        ]);

        // Rio de Janeiro coordinates
        $rioDeLat = -22.9068;
        $rioDeLng = -43.1729;

        $distance = $this->serviceCenter->calculateDistanceTo($rioDeLat, $rioDeLng);

        // Distance between São Paulo and Rio de Janeiro is approximately 350km
        $this->assertGreaterThan(300, $distance);
        $this->assertLessThan(400, $distance);
    }
    #[Test]
    public function full_address_attribute_combines_address_fields()
    {
        $this->serviceCenter->update([
            'address' => 'Av. Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01310-100'
        ]);

        $expectedAddress = 'Av. Paulista, 1000 - Bela Vista - São Paulo/SP - 01310-100';
        $this->assertEquals($expectedAddress, $this->serviceCenter->fullAddress);
    }
    #[Test]
    public function services_count_attribute_counts_service_center_services()
    {
        Service::factory()->count(5)->create(['service_center_id' => $this->serviceCenter->id]);
        Service::factory()->count(3)->create(); // Other service centers

        $this->assertEquals(5, $this->serviceCenter->servicesCount);
    }
    #[Test]
    public function it_validates_cnpj_format()
    {
        $validCNPJ = $this->generateValidCNPJ();

        $this->serviceCenter->update(['cnpj' => $validCNPJ]);

        $this->assertEquals($validCNPJ, $this->serviceCenter->cnpj);
    }
    #[Test]
    public function it_validates_coordinates()
    {
        $lat = -23.5505;
        $lng = -46.6333;

        $this->serviceCenter->update([
            'latitude' => $lat,
            'longitude' => $lng
        ]);

        $this->assertValidCoordinates($this->serviceCenter->latitude, $this->serviceCenter->longitude);
    }
    #[Test]
    public function it_validates_brazilian_zipcode()
    {
        $zipcode = $this->generateBrazilianCEP();

        $this->serviceCenter->update(['zipcode' => $zipcode]);

        $this->assertEquals($zipcode, $this->serviceCenter->zipcode);
    }
    #[Test]
    public function factory_creates_service_center_with_valid_data()
    {
        $serviceCenter = ServiceCenter::factory()->create();

        $this->assertInstanceOf(ServiceCenter::class, $serviceCenter);
        $this->assertNotNull($serviceCenter->name);
        $this->assertNotNull($serviceCenter->cnpj);
        $this->assertNotNull($serviceCenter->phone);
        $this->assertNotNull($serviceCenter->city);
        $this->assertNotNull($serviceCenter->state);
        $this->assertTrue($serviceCenter->active);
    }
    #[Test]
    public function it_uses_soft_deletes()
    {
        $serviceCenterId = $this->serviceCenter->id;

        $this->serviceCenter->delete();

        $this->assertSoftDeleted('service_centers', ['id' => $serviceCenterId]);
        $this->assertNotNull($this->serviceCenter->fresh()->deleted_at);
    }
    #[Test]
    public function it_can_restore_soft_deleted_service_center()
    {
        $this->serviceCenter->delete();
        $this->assertSoftDeleted('service_centers', ['id' => $this->serviceCenter->id]);

        $this->serviceCenter->restore();

        $this->assertDatabaseHas('service_centers', [
            'id' => $this->serviceCenter->id,
            'deleted_at' => null
        ]);
    }
}
