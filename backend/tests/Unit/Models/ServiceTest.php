<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Models\ServiceStatus;
use App\Domain\Service\Models\PaymentMethod;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Service $service;
    protected Client $client;
    protected Vehicle $vehicle;
    protected ServiceCenter $serviceCenter;
    protected User $technician;
    protected User $attendant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);
        $this->serviceCenter = ServiceCenter::factory()->create();
        $this->technician = User::factory()->technician()->create();
        $this->attendant = User::factory()->attendant()->create();

        $this->service = Service::factory()->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_center_id' => $this->serviceCenter->id,
            'technician_id' => $this->technician->id,
            'attendant_id' => $this->attendant->id,
        ]);
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'service_number',
            'client_id',
            'vehicle_id',
            'service_center_id',
            'technician_id',
            'attendant_id',
            'status_id',
            'description',
            'diagnosis',
            'solution',
            'vehicle_mileage',
            'start_date',
            'end_date',
            'estimated_completion',
            'labor_amount',
            'parts_amount',
            'discount_amount',
            'total_amount',
            'payment_method_id',
            'payment_status',
            'notes',
            'warranty_months'
        ];

        $this->assertEquals($fillable, $this->service->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'client_id' => 'int',
            'vehicle_id' => 'int',
            'service_center_id' => 'int',
            'technician_id' => 'int',
            'attendant_id' => 'int',
            'status_id' => 'int',
            'vehicle_mileage' => 'int',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'estimated_completion' => 'datetime',
            'labor_amount' => 'decimal:2',
            'parts_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_method_id' => 'int',
            'warranty_months' => 'int',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->service->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_client()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->service->client());
        $this->assertInstanceOf(Client::class, $this->service->client);
        $this->assertEquals($this->client->id, $this->service->client_id);
    }
    #[Test]
    public function it_belongs_to_vehicle()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->service->vehicle());
        $this->assertInstanceOf(Vehicle::class, $this->service->vehicle);
        $this->assertEquals($this->vehicle->id, $this->service->vehicle_id);
    }
    #[Test]
    public function it_belongs_to_service_center()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->service->serviceCenter());
        $this->assertInstanceOf(ServiceCenter::class, $this->service->serviceCenter);
        $this->assertEquals($this->serviceCenter->id, $this->service->service_center_id);
    }
    #[Test]
    public function it_belongs_to_technician()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->service->technician());
        $this->assertInstanceOf(User::class, $this->service->technician);
        $this->assertEquals($this->technician->id, $this->service->technician_id);
    }
    #[Test]
    public function it_belongs_to_attendant()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->service->attendant());
        $this->assertInstanceOf(User::class, $this->service->attendant);
        $this->assertEquals($this->attendant->id, $this->service->attendant_id);
    }
    #[Test]
    public function it_has_many_items()
    {
        $this->assertInstanceOf(HasMany::class, $this->service->items());

        $item = ServiceItem::factory()->create(['service_id' => $this->service->id]);

        $this->assertTrue($this->service->items->contains($item));
    }
    #[Test]
    public function by_status_scope_filters_by_status()
    {
        Service::factory()->create(['status_id' => 1]); // waiting
        Service::factory()->create(['status_id' => 2]); // in_progress
        Service::factory()->create(['status_id' => 1]); // waiting

        $waitingServices = Service::byStatus(1)->get();

        $this->assertEquals(2, $waitingServices->count());
        $this->assertTrue($waitingServices->every(fn($service) => $service->status_id === 1));
    }
    #[Test]
    public function by_service_center_scope_filters_by_service_center()
    {
        $anotherServiceCenter = ServiceCenter::factory()->create();

        Service::factory()->create(['service_center_id' => $this->serviceCenter->id]);
        Service::factory()->create(['service_center_id' => $anotherServiceCenter->id]);

        $services = Service::byServiceCenter($this->serviceCenter->id)->get();

        $this->assertEquals(2, $services->count()); // 1 + setUp service
        $this->assertTrue($services->every(fn($service) => $service->service_center_id === $this->serviceCenter->id));
    }
    #[Test]
    public function by_technician_scope_filters_by_technician()
    {
        $anotherTechnician = User::factory()->technician()->create();

        Service::factory()->create(['technician_id' => $this->technician->id]);
        Service::factory()->create(['technician_id' => $anotherTechnician->id]);

        $services = Service::byTechnician($this->technician->id)->get();

        $this->assertEquals(2, $services->count()); // 1 + setUp service
        $this->assertTrue($services->every(fn($service) => $service->technician_id === $this->technician->id));
    }
    #[Test]
    public function by_period_scope_filters_by_date_range()
    {
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now()->subDays(5);

        Service::factory()->create(['start_date' => Carbon::now()->subDays(7)]);
        Service::factory()->create(['start_date' => Carbon::now()->subDays(15)]);
        Service::factory()->create(['start_date' => Carbon::now()->subDays(6)]);

        $services = Service::byPeriod($startDate, $endDate)->get();

        $this->assertEquals(2, $services->count());
    }
    #[Test]
    public function pending_scope_returns_pending_services()
    {
        Service::factory()->create(['status_id' => 1]); // waiting
        Service::factory()->create(['status_id' => 3]); // completed

        $pendingServices = Service::pending()->get();

        $this->assertTrue($pendingServices->every(fn($service) => $service->status_id === 1));
    }
    #[Test]
    public function in_progress_scope_returns_in_progress_services()
    {
        Service::factory()->create(['status_id' => 2]); // in_progress
        Service::factory()->create(['status_id' => 3]); // completed

        $inProgressServices = Service::inProgress()->get();

        $this->assertTrue($inProgressServices->every(fn($service) => $service->status_id === 2));
    }
    #[Test]
    public function completed_scope_returns_completed_services()
    {
        Service::factory()->create(['status_id' => 3]); // completed
        Service::factory()->create(['status_id' => 1]); // waiting

        $completedServices = Service::completed()->get();

        $this->assertTrue($completedServices->every(fn($service) => $service->status_id === 3));
    }
    #[Test]
    public function generate_service_number_creates_unique_number()
    {
        $serviceNumber = Service::generateServiceNumber($this->serviceCenter->id);

        $this->assertNotNull($serviceNumber);
        $this->assertStringContainsString(date('Y'), $serviceNumber);
        $this->assertStringContainsString(str_pad($this->serviceCenter->id, 3, '0', STR_PAD_LEFT), $serviceNumber);
    }
    #[Test]
    public function calculate_final_amount_method_calculates_correct_total()
    {
        $this->service->update([
            'labor_amount' => 100.00,
            'parts_amount' => 200.00,
            'discount_amount' => 30.00
        ]);

        $finalAmount = $this->service->calculateFinalAmount();

        $this->assertEquals(270.00, $finalAmount);
    }
    #[Test]
    public function update_amounts_method_updates_service_totals()
    {
        ServiceItem::factory()->create([
            'service_id' => $this->service->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'discount_percentage' => 10
        ]);

        $this->service->updateAmounts();

        $this->assertEquals(90.00, (float) $this->service->fresh()->parts_amount);
    }
    #[Test]
    public function is_pending_method_checks_status_correctly()
    {
        $this->service->update(['status_id' => 1]);
        $this->assertTrue($this->service->isPending());

        $this->service->update(['status_id' => 2]);
        $this->assertFalse($this->service->isPending());
    }
    #[Test]
    public function is_in_progress_method_checks_status_correctly()
    {
        $this->service->update(['status_id' => 2]);
        $this->assertTrue($this->service->isInProgress());

        $this->service->update(['status_id' => 1]);
        $this->assertFalse($this->service->isInProgress());
    }
    #[Test]
    public function is_completed_method_checks_status_correctly()
    {
        $this->service->update(['status_id' => 3]);
        $this->assertTrue($this->service->isCompleted());

        $this->service->update(['status_id' => 2]);
        $this->assertFalse($this->service->isCompleted());
    }
    #[Test]
    public function factory_creates_service_with_valid_data()
    {
        $service = Service::factory()->create();

        $this->assertInstanceOf(Service::class, $service);
        $this->assertNotNull($service->service_number);
        $this->assertNotNull($service->client_id);
        $this->assertNotNull($service->vehicle_id);
        $this->assertNotNull($service->service_center_id);
        $this->assertNotNull($service->status_id);
    }
    #[Test]
    public function it_calculates_service_duration()
    {
        $startDate = Carbon::now()->subDays(3);
        $endDate = Carbon::now();

        $this->service->update([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $duration = $this->service->start_date->diffInDays($this->service->end_date);
        $this->assertEquals(3, $duration);
    }
    #[Test]
    public function it_handles_warranty_calculation()
    {
        $this->service->update([
            'end_date' => Carbon::now(),
            'warranty_months' => 6
        ]);

        $warrantyExpiry = $this->service->end_date->addMonths($this->service->warranty_months);
        $this->assertTrue($warrantyExpiry->isFuture());
    }
}
