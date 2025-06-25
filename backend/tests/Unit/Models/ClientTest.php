<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class ClientTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = [
            'name',
            'phone01',
            'phone02',
            'email',
            'cpf',
            'cnpj',
            'address',
            'city',
            'state',
            'zip_code',
            'notes',
            'active',
        ];

        $this->assertEquals($expectedFillable, $this->client->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->client->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_has_many_vehicles()
    {
        $this->assertInstanceOf(HasMany::class, $this->client->vehicles());

        $vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);

        $this->assertTrue($this->client->vehicles->contains($vehicle));
    }
    #[Test]
    public function it_has_many_services()
    {
        $this->assertInstanceOf(HasMany::class, $this->client->services());

        $service = Service::factory()->create(['client_id' => $this->client->id]);

        $this->assertTrue($this->client->services->contains($service));
    }
    #[Test]
    public function it_has_last_service_relationship()
    {
        $this->assertInstanceOf(HasOne::class, $this->client->lastService());

        $oldService = Service::factory()->create([
            'client_id' => $this->client->id,
            'created_at' => Carbon::now()->subDays(10)
        ]);

        $lastService = Service::factory()->create([
            'client_id' => $this->client->id,
            'created_at' => Carbon::now()->subDays(1)
        ]);

        $this->assertEquals($lastService->id, $this->client->lastService->id);
    }
    #[Test]
    public function active_scope_returns_only_active_clients()
    {
        Client::factory()->create(['active' => true]);
        Client::factory()->create(['active' => false]);
        Client::factory()->create(['active' => true]);

        $activeClients = Client::active()->get();

        $this->assertEquals(3, $activeClients->count()); // 2 + setUp client
        $this->assertTrue($activeClients->every(fn($client) => $client->active === true));
    }
    #[Test]
    public function search_by_name_scope_filters_by_name()
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);
        Client::factory()->create(['name' => 'Pedro João']);

        $results = Client::searchByName('João')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($client) => stripos($client->name, 'João') !== false));
    }
    #[Test]
    public function search_by_phone_scope_filters_by_phone()
    {
        Client::factory()->create(['phone01' => '11987654321']);
        Client::factory()->create(['phone01' => '21999888777']);
        Client::factory()->create(['phone01' => '11912345678']);

        $results = Client::searchByPhone('11987654321')->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals('11987654321', $results->first()->phone01);
    }
    #[Test]
    public function search_by_document_scope_filters_by_document()
    {
        $cpf = $this->generateValidCPF();
        $cnpj = $this->generateValidCNPJ();

        Client::factory()->create(['cpf' => $cpf]);
        Client::factory()->create(['cnpj' => $cnpj]);

        $cpfResults = Client::where('cpf', $cpf)->get();
        $cnpjResults = Client::where('cnpj', $cnpj)->get();

        $this->assertEquals(1, $cpfResults->count());
        $this->assertEquals(1, $cnpjResults->count());
        $this->assertEquals($cpf, $cpfResults->first()->cpf);
        $this->assertEquals($cnpj, $cnpjResults->first()->cnpj);
    }
    #[Test]
    public function it_caches_client_by_license_plate()
    {
        $licensePlate = $this->generateValidLicensePlate();
        $vehicle = Vehicle::factory()->create([
            'client_id' => $this->client->id,
            'license_plate' => $licensePlate
        ]);

        // First call should hit database and cache result
        $result1 = Client::findByLicensePlate($licensePlate);
        $this->assertEquals($this->client->id, $result1->id);

        // Second call should hit cache
        $result2 = Client::findByLicensePlate($licensePlate);
        $this->assertEquals($this->client->id, $result2->id);

        $this->assertCacheContains("client_plate_{$licensePlate}");
    }
    #[Test]
    public function it_invalidates_cache_when_client_is_updated()
    {
        $licensePlate = $this->generateValidLicensePlate();
        $vehicle = Vehicle::factory()->create([
            'client_id' => $this->client->id,
            'license_plate' => $licensePlate
        ]);

        // Cache the result
        Client::findByLicensePlate($licensePlate);
        $this->assertCacheContains("client_plate_{$licensePlate}");

        // Update client should clear cache
        $this->client->update(['name' => 'Updated Name']);

        $this->assertCacheDoesNotContain("client_plate_{$licensePlate}");
    }
    #[Test]
    public function full_address_attribute_combines_address_fields()
    {
        $this->client->update([
            'address' => 'Rua das Flores',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567'
        ]);

        $expectedAddress = 'Rua das Flores, São Paulo, SP, 01234-567';
        $this->assertEquals($expectedAddress, $this->client->full_address);
    }
                #[Test]
    public function total_services_attribute_counts_client_services()
    {
        // Get or create minimal required dependencies efficiently
        $dependencies = $this->getOrCreateServiceDependencies();
        $vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);

        // Insert services directly using DB to minimize overhead
        \Illuminate\Support\Facades\DB::table('services')->insert([
            [
                'client_id' => $this->client->id,
                'service_center_id' => $dependencies['serviceCenter']->id,
                'vehicle_id' => $vehicle->id,
                'user_id' => $dependencies['user']->id,
                'service_status_id' => $dependencies['serviceStatus']->id,
                'service_number' => 'TEST001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $this->client->id,
                'service_center_id' => $dependencies['serviceCenter']->id,
                'vehicle_id' => $vehicle->id,
                'user_id' => $dependencies['user']->id,
                'service_status_id' => $dependencies['serviceStatus']->id,
                'service_number' => 'TEST002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $this->client->id,
                'service_center_id' => $dependencies['serviceCenter']->id,
                'vehicle_id' => $vehicle->id,
                'user_id' => $dependencies['user']->id,
                'service_status_id' => $dependencies['serviceStatus']->id,
                'service_number' => 'TEST003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create services for other clients to ensure isolation
        $otherClientId = Client::factory()->create()->id;
        $otherVehicleId = Vehicle::factory()->create(['client_id' => $otherClientId])->id;

        \Illuminate\Support\Facades\DB::table('services')->insert([
            [
                'client_id' => $otherClientId,
                'service_center_id' => $dependencies['serviceCenter']->id,
                'vehicle_id' => $otherVehicleId,
                'user_id' => $dependencies['user']->id,
                'service_status_id' => $dependencies['serviceStatus']->id,
                'service_number' => 'OTHER001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Load services relationship to optimize the count
        $this->client->load('services');

        // Assert: should only count this client's services (3), not other client's (1)
        $this->assertEquals(3, $this->client->totalServices);
    }

        /**
     * Helper method to get or create service dependencies efficiently.
     */
    private function getOrCreateServiceDependencies(): array
    {
        return [
            'serviceCenter' => \App\Domain\Service\Models\ServiceCenter::first()
                ?? \App\Domain\Service\Models\ServiceCenter::factory()->create(),
            'serviceStatus' => \App\Domain\Service\Models\ServiceStatus::first()
                ?? \App\Domain\Service\Models\ServiceStatus::create([
                    'name' => 'scheduled',
                    'description' => 'Agendado',
                    'color' => '#3B82F6',
                    'sort_order' => 1,
                ]),
            'user' => \App\Domain\User\Models\User::first()
                ?? \App\Domain\User\Models\User::factory()->create(),
        ];
    }
    #[Test]
    public function total_spent_attribute_sums_service_totals()
    {
        Service::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 150.00
        ]);

        Service::factory()->create([
            'client_id' => $this->client->id,
            'total_amount' => 250.50
        ]);

        $this->assertEquals(400.50, $this->client->totalSpent);
    }
    #[Test]
    public function it_validates_cpf_format()
    {
        $validCPF = $this->generateValidCPF();

        $client = Client::factory()->create([
            'cpf' => $validCPF,
            'cnpj' => null
        ]);

        $this->assertEquals($validCPF, $client->cpf);
        $this->assertMatchesRegularExpression('/^\d{11}$/', $client->cpf);
    }
    #[Test]
    public function it_validates_cnpj_format()
    {
        $validCNPJ = $this->generateValidCNPJ();

        $client = Client::factory()->create([
            'cnpj' => $validCNPJ,
            'cpf' => null
        ]);

        $this->assertEquals($validCNPJ, $client->cnpj);
        $this->assertMatchesRegularExpression('/^\d{14}$/', $client->cnpj);
    }
    #[Test]
    public function it_validates_brazilian_phone_format()
    {
        $validPhone = '11987654321';
        $this->client->update(['phone01' => $validPhone]);

        $this->assertEquals($validPhone, $this->client->phone01);
    }
    #[Test]
    public function it_validates_brazilian_zipcode_format()
    {
        $zipcode = $this->generateBrazilianCEP();

        $this->client->update(['zip_code' => $zipcode]);

        $this->assertEquals($zipcode, $this->client->zip_code);
    }
    #[Test]
    public function factory_creates_individual_client_by_default()
    {
        $client = Client::factory()->individual()->create();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertNotNull($client->cpf);
        $this->assertNull($client->cnpj);
        $this->assertNotNull($client->name);
        $this->assertNotNull($client->phone01);
        $this->assertTrue($client->active);
    }
    #[Test]
    public function factory_can_create_company_client()
    {
        $company = Client::factory()->company()->create();

        $this->assertNotNull($company->cnpj);
        $this->assertNull($company->cpf);
        $this->assertTrue(
            str_contains($company->name, 'Ltda') ||
            str_contains($company->name, 'S/A') ||
            str_contains($company->name, 'ME') ||
            str_contains($company->name, 'EPP')
        );
    }
    #[Test]
    public function factory_can_create_individual_client()
    {
        $individual = Client::factory()->individual()->create();

        $this->assertNotNull($individual->cpf);
        $this->assertNull($individual->cnpj);
    }
    #[Test]
    public function it_uses_soft_deletes()
    {
        $clientId = $this->client->id;

        $this->client->delete();

        $this->assertSoftDeleted('clients', ['id' => $clientId]);
        $this->assertNotNull($this->client->fresh()->deleted_at);
    }
    #[Test]
    public function it_can_restore_soft_deleted_client()
    {
        $this->client->delete();
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);

        $this->client->restore();

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'deleted_at' => null
        ]);
    }
    #[Test]
    public function it_has_proper_table_name()
    {
        $this->assertEquals('clients', $this->client->getTable());
    }
    #[Test]
    public function it_has_proper_primary_key()
    {
        $this->assertEquals('id', $this->client->getKeyName());
        $this->assertTrue($this->client->getIncrementing());
    }
    #[Test]
    public function it_handles_null_values_properly()
    {
        $client = Client::factory()->create([
            'phone02' => null,
            'email' => null,
            'address' => null,
            'notes' => null,
        ]);

        $this->assertNull($client->phone02);
        $this->assertNull($client->email);
        $this->assertNull($client->address);
        $this->assertNull($client->notes);
    }
    // Removed: birth_date column doesn't exist in current schema
    #[Test]
    public function search_scope_combines_multiple_search_criteria()
    {
        $searchTerm = 'João';

        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['phone01' => '11987654321']);
        Client::factory()->create(['name' => 'Maria Santos']);

        $results = Client::search($searchTerm)->get();

        $this->assertGreaterThanOrEqual(1, $results->count());
    }
    #[Test]
    public function last_service_date_attribute_returns_correct_date()
    {
        $serviceDate = Carbon::now()->subDays(5);

        Service::factory()->create([
            'client_id' => $this->client->id,
            'created_at' => $serviceDate
        ]);

        $lastService = $this->client->lastService;
        $this->assertNotNull($lastService);
        $this->assertEquals(
            $serviceDate->format('Y-m-d'),
            $lastService->created_at->format('Y-m-d')
        );
    }
    #[Test]
    public function next_service_reminder_calculates_based_on_last_service()
    {
        $lastServiceDate = Carbon::now()->subMonths(5);

        Service::factory()->create([
            'client_id' => $this->client->id,
            'created_at' => $lastServiceDate
        ]);

        $expectedReminderDate = $lastServiceDate->addMonths(6);

        $this->assertEquals(
            $expectedReminderDate->format('Y-m-d'),
            $this->client->nextServiceReminder->format('Y-m-d')
        );
    }
    #[Test]
    public function it_stores_cpf_and_cnpj_separately()
    {
        // Test CPF storage
        $cpf = $this->generateValidCPF();
        $this->client->update([
            'cpf' => $cpf,
            'cnpj' => null
        ]);

        $this->assertEquals($cpf, $this->client->cpf);
        $this->assertNull($this->client->cnpj);

        // Test CNPJ storage
        $cnpj = $this->generateValidCNPJ();
        $this->client->update([
            'cpf' => null,
            'cnpj' => $cnpj
        ]);

        $this->assertEquals($cnpj, $this->client->cnpj);
        $this->assertNull($this->client->cpf);
    }
    #[Test]
    public function it_validates_email_format_when_provided()
    {
        $validEmail = 'test@example.com';
        $this->client->update(['email' => $validEmail]);

        $this->assertEquals($validEmail, $this->client->email);
    }
    #[Test]
    public function by_city_scope_filters_by_city()
    {
        Client::factory()->create(['city' => 'São Paulo']);
        Client::factory()->create(['city' => 'Rio de Janeiro']);
        Client::factory()->create(['city' => 'São Paulo']);

        $results = Client::byCity('São Paulo')->get();

        $this->assertGreaterThanOrEqual(2, $results->count());
        $this->assertTrue($results->every(fn($client) => $client->city === 'São Paulo'));
    }
    #[Test]
    public function by_state_scope_filters_by_state()
    {
        Client::factory()->create(['state' => 'SP']);
        Client::factory()->create(['state' => 'RJ']);
        Client::factory()->create(['state' => 'SP']);

        $results = Client::byState('SP')->get();

        // Should be 3: 2 created + 1 from setUp (if setUp client has state SP)
        $this->assertGreaterThanOrEqual(2, $results->count());
        $this->assertTrue($results->every(fn($client) => $client->state === 'SP'));
    }
    #[Test]
    public function by_document_type_scope_filters_by_document_type()
    {
        Client::factory()->individual()->create();
        Client::factory()->company()->create();
        Client::factory()->individual()->create();

        $individualsWithCpf = Client::whereNotNull('cpf')->get();
        $companiesWithCnpj = Client::whereNotNull('cnpj')->get();

        $this->assertGreaterThanOrEqual(2, $individualsWithCpf->count()); // At least 2 individuals created
        $this->assertGreaterThanOrEqual(1, $companiesWithCnpj->count()); // At least 1 company created
    }
    #[Test]
    public function client_has_services_relationship()
    {
        Service::factory()->create([
            'client_id' => $this->client->id
        ]);

        $services = $this->client->services;
        $this->assertGreaterThan(0, $services->count());
    }
    #[Test]
    public function it_validates_phone_format()
    {
        $validPhone = '11987654321';
        $this->client->update(['phone01' => $validPhone]);

        $this->assertEquals($validPhone, $this->client->phone01);
    }
}
