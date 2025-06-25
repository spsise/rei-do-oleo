<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class VehicleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Vehicle $vehicle;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'client_id',
            'license_plate',
            'brand',
            'model',
            'year',
            'color',
            'fuel_type',
            'mileage',
            'last_service',
        ];

        $this->assertEquals($fillable, $this->vehicle->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'client_id' => 'int',
            'year' => 'int',
            'mileage' => 'int',
            'last_service' => 'date',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->vehicle->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_client()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->vehicle->client());
        $this->assertInstanceOf(Client::class, $this->vehicle->client);
        $this->assertEquals($this->client->id, $this->vehicle->client_id);
    }
    #[Test]
    public function it_has_many_services()
    {
        $this->assertInstanceOf(HasMany::class, $this->vehicle->services());

        $service = Service::factory()->create(['vehicle_id' => $this->vehicle->id]);

        $this->assertTrue($this->vehicle->services->contains($service));
    }
    #[Test]
    public function it_has_one_last_service(): void
    {
        $oldService = Service::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'created_at' => now()->subDays(5)
        ]);
        $latestService = Service::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'created_at' => now()
        ]);

        $this->assertInstanceOf(Service::class, $this->vehicle->lastService);
        $this->assertEquals($latestService->id, $this->vehicle->lastService->id);
    }
    #[Test]
    public function search_by_plate_scope_filters_by_license_plate()
    {
        Vehicle::factory()->create(['license_plate' => 'ABC-1234']);
        Vehicle::factory()->create(['license_plate' => 'XYZ-5678']);
        Vehicle::factory()->create(['license_plate' => 'ABC-9999']);

        $results = Vehicle::searchByPlate('ABC')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($vehicle) => str_contains($vehicle->license_plate, 'ABC')));
    }
    #[Test]
    public function search_by_brand_scope_filters_by_brand()
    {
        Vehicle::factory()->create(['brand' => 'Toyota']);
        Vehicle::factory()->create(['brand' => 'Honda']);
        Vehicle::factory()->create(['brand' => 'Toyota']);

        $results = Vehicle::searchByBrand('Toyota')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($vehicle) => $vehicle->brand === 'Toyota'));
    }
    #[Test]
    public function formatted_license_plate_attribute_formats_plate_correctly()
    {
        // Test old format
        $this->vehicle->update(['license_plate' => 'ABC1234']);
        $this->assertEquals('ABC-1234', $this->vehicle->formattedLicensePlate);

        // Test Mercosul format
        $this->vehicle->update(['license_plate' => 'ABC1D23']);
        $this->assertEquals('ABC1D23', $this->vehicle->formattedLicensePlate);
    }
    #[Test]
    public function full_description_attribute_combines_vehicle_info()
    {
        $this->vehicle->update([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'color' => 'Prata'
        ]);

        $expected = 'Toyota Corolla 2020 Prata';
        $this->assertEquals($expected, $this->vehicle->fullDescription);
    }
    #[Test]
    public function update_service_info_method_updates_mileage()
    {
        $newMileage = 50000;

        $this->vehicle->updateServiceInfo($newMileage);

        $this->assertEquals($newMileage, $this->vehicle->fresh()->mileage);
    }
    #[Test]
    public function factory_creates_vehicle_with_valid_data()
    {
        $vehicle = Vehicle::factory()->create();

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertNotNull($vehicle->license_plate);
        $this->assertNotNull($vehicle->brand);
        $this->assertNotNull($vehicle->model);
        $this->assertNotNull($vehicle->year);
    }
    #[Test]
    public function it_clears_cache_on_update(): void
    {
        $licensePlate = $this->vehicle->license_plate;

        // Populate cache
        Cache::put("client_plate_{$licensePlate}", 'test_data', 3600);
        $this->assertCacheContains("client_plate_{$licensePlate}");

        // Update vehicle
        $this->vehicle->update(['mileage' => 50000]);

        // Cache should be cleared
        $this->assertCacheWasCleared("client_plate_{$licensePlate}");
    }
    #[Test]
    public function it_clears_cache_on_delete(): void
    {
        $licensePlate = $this->vehicle->license_plate;

        // Populate cache
        Cache::put("client_plate_{$licensePlate}", 'test_data', 3600);
        $this->assertCacheContains("client_plate_{$licensePlate}");

        // Delete vehicle
        $this->vehicle->delete();

        // Cache should be cleared
        $this->assertCacheWasCleared("client_plate_{$licensePlate}");
    }
    #[Test]
    public function it_validates_year_range(): void
    {
        $currentYear = (int) date('Y');
        $vehicle = Vehicle::factory()->create(['year' => $currentYear]);

        $this->assertEquals($currentYear, $vehicle->year);
        $this->assertIsInt($vehicle->year);
    }
    #[Test]
    public function it_validates_fuel_type_options(): void
    {
        $validFuelTypes = ['gasoline', 'ethanol', 'diesel', 'flex', 'electric', 'hybrid'];

        foreach ($validFuelTypes as $fuelType) {
            $vehicle = Vehicle::factory()->create(['fuel_type' => $fuelType]);
            $this->assertEquals($fuelType, $vehicle->fuel_type);
        }
    }
    #[Test]
    public function it_validates_mileage_is_numeric(): void
    {
        $vehicle = Vehicle::factory()->create(['mileage' => 50000]);

        $this->assertEquals(50000, $vehicle->mileage);
        $this->assertIsInt($vehicle->mileage);
    }
    #[Test]
    public function it_uses_factory_correctly(): void
    {
        $vehicle = Vehicle::factory()->create([
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla'
        ]);

        $this->assertEquals('ABC-1234', $vehicle->license_plate);
        $this->assertEquals('Toyota', $vehicle->brand);
        $this->assertEquals('Corolla', $vehicle->model);
    }
    #[Test]
    public function it_uses_old_format_factory_state(): void
    {
        $vehicle = Vehicle::factory()->oldFormat()->create();

        $this->assertMatchesRegularExpression('/^[A-Z]{3}-\d{4}$/', $vehicle->license_plate);
    }
    #[Test]
    public function it_uses_mercosul_format_factory_state(): void
    {
        $vehicle = Vehicle::factory()->mercosulFormat()->create();

        $this->assertMatchesRegularExpression('/^[A-Z]{3}\d[A-Z]\d{2}$/', $vehicle->license_plate);
    }
    #[Test]
    public function it_uses_new_vehicle_factory_state(): void
    {
        $vehicle = Vehicle::factory()->newVehicle()->create();

        $currentYear = (int) date('Y');
        $this->assertGreaterThanOrEqual($currentYear - 2, $vehicle->year);
        $this->assertLessThanOrEqual(50000, $vehicle->mileage);
    }
    #[Test]
    public function it_uses_old_vehicle_factory_state(): void
    {
        $vehicle = Vehicle::factory()->oldVehicle()->create();

        $this->assertLessThanOrEqual(2010, $vehicle->year);
        $this->assertGreaterThanOrEqual(100000, $vehicle->mileage);
    }
    #[Test]
    public function it_uses_electric_factory_state(): void
    {
        $vehicle = Vehicle::factory()->electric()->create();

        $this->assertEquals('electric', $vehicle->fuel_type);
    }
    #[Test]
    public function it_uses_flex_factory_state(): void
    {
        $vehicle = Vehicle::factory()->flex()->create();

        $this->assertEquals('flex', $vehicle->fuel_type);
    }
    #[Test]
    public function it_validates_old_format_license_plate()
    {
        $oldPlate = $this->generateValidLicensePlate(false); // ABC-1234

        $vehicle = Vehicle::factory()->create(['license_plate' => $oldPlate]);

        $this->assertEquals($oldPlate, $vehicle->license_plate);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}-[0-9]{4}$/', $vehicle->license_plate);
    }
    #[Test]
    public function it_validates_mercosul_format_license_plate()
    {
        $mercosulPlate = $this->generateValidLicensePlate(true); // ABC1D23

        $vehicle = Vehicle::factory()->create(['license_plate' => $mercosulPlate]);

        $this->assertEquals($mercosulPlate, $vehicle->license_plate);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $vehicle->license_plate);
    }
}
