<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\Client\Services\ClientService;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ClientService $clientService;
    protected $clientRepositoryMock;
    protected $vehicleRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepositoryMock = Mockery::mock(ClientRepositoryInterface::class);
        $this->vehicleRepositoryMock = Mockery::mock(VehicleRepositoryInterface::class);

        $this->clientService = new ClientService(
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
    public function find_by_license_plate_returns_cached_client(): void
    {
        $licensePlate = 'ABC-1234';
        $client = Client::factory()->make(['id' => 1]);

        Cache::shouldReceive('remember')
            ->once()
            ->with("client_plate_{$licensePlate}", 3600, Mockery::type('Closure'))
            ->andReturn($client);

        $this->clientRepositoryMock
            ->shouldReceive('findByLicensePlate')
            ->with($licensePlate)
            ->andReturn($client);

        $result = $this->clientService->findByLicensePlate($licensePlate);

        $this->assertEquals($client, $result);
    }
    #[Test]
    public function find_by_license_plate_returns_null_when_not_found(): void
    {
        $licensePlate = 'XYZ-9999';

        Cache::shouldReceive('remember')
            ->once()
            ->with("client_plate_{$licensePlate}", 3600, Mockery::type('Closure'))
            ->andReturn(null);

        $this->clientRepositoryMock
            ->shouldReceive('findByLicensePlate')
            ->with($licensePlate)
            ->andReturn(null);

        $result = $this->clientService->findByLicensePlate($licensePlate);

        $this->assertNull($result);
    }
    #[Test]
    public function create_with_vehicle_validates_license_plate_format(): void
    {
        $data = [
            'client' => [
                'name' => 'João Silva',
                'phone01' => '11999887766'
            ],
            'vehicle' => [
                'license_plate' => 'INVALID-PLATE',
                'brand' => 'Toyota',
                'model' => 'Corolla'
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato de placa inválido');

        $this->clientService->createWithVehicle($data);
    }
    #[Test]
    public function create_with_vehicle_checks_for_existing_license_plate(): void
    {
        $data = [
            'client' => [
                'name' => 'João Silva',
                'phone01' => '11999887766'
            ],
            'vehicle' => [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla'
            ]
        ];

        $existingVehicle = Vehicle::factory()->make(['license_plate' => 'ABC-1234']);

        $this->vehicleRepositoryMock
            ->shouldReceive('findByLicensePlate')
            ->with('ABC-1234')
            ->andReturn($existingVehicle);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Placa já cadastrada');

        $this->clientService->createWithVehicle($data);
    }
    #[Test]
    public function create_with_vehicle_creates_client_and_vehicle_successfully(): void
    {
        $data = [
            'client' => [
                'name' => 'João Silva',
                'phone01' => '11999887766'
            ],
            'vehicle' => [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla'
            ]
        ];

        $createdClient = Client::factory()->make(['id' => 1]);

        $this->vehicleRepositoryMock
            ->shouldReceive('findByLicensePlate')
            ->with('ABC-1234')
            ->andReturn(null);

        $this->clientRepositoryMock
            ->shouldReceive('createWithVehicle')
            ->with($data['client'], $data['vehicle'])
            ->andReturn($createdClient);

        Cache::shouldReceive('forget')
            ->with("client_plate_ABC-1234")
            ->once();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->clientService->createWithVehicle($data);

        $this->assertEquals($createdClient, $result);
    }
    #[Test]
    public function update_vehicle_mileage_validates_mileage_increase(): void
    {
        $vehicleId = 1;
        $currentMileage = 50000;
        $newMileage = 40000; // Lower than current

        $vehicle = Vehicle::factory()->make([
            'id' => $vehicleId,
            'mileage' => $currentMileage
        ]);

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with($vehicleId)
            ->andReturn($vehicle);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nova quilometragem deve ser maior que a atual');

        $this->clientService->updateVehicleMileage($vehicleId, $newMileage);
    }
    #[Test]
    public function update_vehicle_mileage_updates_successfully(): void
    {
        $vehicleId = 1;
        $currentMileage = 50000;
        $newMileage = 60000;

        $vehicle = Vehicle::factory()->make([
            'id' => $vehicleId,
            'mileage' => $currentMileage
        ]);

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with($vehicleId)
            ->andReturn($vehicle);

        $this->vehicleRepositoryMock
            ->shouldReceive('update')
            ->with($vehicleId, ['mileage' => $newMileage])
            ->andReturn(true);

        $result = $this->clientService->updateVehicleMileage($vehicleId, $newMileage);

        $this->assertTrue($result);
    }
    #[Test]
    public function update_vehicle_mileage_returns_false_when_vehicle_not_found(): void
    {
        $vehicleId = 999;
        $newMileage = 60000;

        $this->vehicleRepositoryMock
            ->shouldReceive('find')
            ->with($vehicleId)
            ->andReturn(null);

        $result = $this->clientService->updateVehicleMileage($vehicleId, $newMileage);

        $this->assertFalse($result);
    }
    #[Test]
    public function search_clients_uses_repository_with_filters(): void
    {
        $filters = [
            'search' => 'João',
            'active' => true,
            'per_page' => 15
        ];

        $expectedResult = collect([]);

        $this->clientRepositoryMock
            ->shouldReceive('searchByFilters')
            ->with($filters)
            ->andReturn($expectedResult);

        $result = $this->clientService->searchClients($filters);

        $this->assertEquals($expectedResult, $result);
    }
    #[Test]
    public function get_client_by_document_validates_cpf(): void
    {
        $cpf = '12345678901';
        $client = Client::factory()->make(['cpf' => $cpf]);

        $this->clientRepositoryMock
            ->shouldReceive('findByDocument')
            ->with($cpf)
            ->andReturn($client);

        $result = $this->clientService->getClientByDocument($cpf);

        $this->assertEquals($client, $result);
    }
    #[Test]
    public function get_client_by_document_validates_cnpj(): void
    {
        $cnpj = '12345678000123';
        $client = Client::factory()->make(['cnpj' => $cnpj]);

        $this->clientRepositoryMock
            ->shouldReceive('findByDocument')
            ->with($cnpj)
            ->andReturn($client);

        $result = $this->clientService->getClientByDocument($cnpj);

        $this->assertEquals($client, $result);
    }
    #[Test]
    public function get_client_statistics_returns_aggregated_data(): void
    {
        $clientId = 1;
        $expectedStats = [
            'total_services' => 10,
            'total_spent' => 2500.00,
            'avg_service_value' => 250.00,
            'last_service_date' => '2024-01-15'
        ];

        $this->clientRepositoryMock
            ->shouldReceive('getClientStatistics')
            ->with($clientId)
            ->andReturn($expectedStats);

        $result = $this->clientService->getClientStatistics($clientId);

        $this->assertEquals($expectedStats, $result);
    }
    #[Test]
    public function get_clients_with_recent_services_uses_cache(): void
    {
        $days = 30;
        $expectedClients = collect([]);

        Cache::shouldReceive('remember')
            ->once()
            ->with("clients_recent_services_{$days}", 1800, Mockery::type('Closure'))
            ->andReturn($expectedClients);

        $this->clientRepositoryMock
            ->shouldReceive('getClientsWithRecentServices')
            ->with($days)
            ->andReturn($expectedClients);

        $result = $this->clientService->getClientsWithRecentServices($days);

        $this->assertEquals($expectedClients, $result);
    }
    #[Test]
    public function activate_client_updates_status(): void
    {
        $clientId = 1;
        $client = Client::factory()->make(['id' => $clientId, 'active' => false]);

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with($clientId)
            ->andReturn($client);

        $this->clientRepositoryMock
            ->shouldReceive('update')
            ->with($clientId, ['active' => true])
            ->andReturn($client);

        $result = $this->clientService->activateClient($clientId);

        $this->assertTrue($result);
    }
    #[Test]
    public function deactivate_client_updates_status(): void
    {
        $clientId = 1;
        $client = Client::factory()->make(['id' => $clientId, 'active' => true]);

        $this->clientRepositoryMock
            ->shouldReceive('find')
            ->with($clientId)
            ->andReturn($client);

        $this->clientRepositoryMock
            ->shouldReceive('update')
            ->with($clientId, ['active' => false])
            ->andReturn($client);

        $result = $this->clientService->deactivateClient($clientId);

        $this->assertTrue($result);
    }
    #[Test]
    public function validate_cpf_format_returns_true_for_valid_cpf(): void
    {
        $validCPF = $this->generateValidCPF();

        $result = $this->clientService->validateCPF($validCPF);

        $this->assertTrue($result);
    }
    #[Test]
    public function validate_cpf_format_returns_false_for_invalid_cpf(): void
    {
        $invalidCPF = '12345678901';

        $result = $this->clientService->validateCPF($invalidCPF);

        $this->assertFalse($result);
    }
    #[Test]
    public function validate_cnpj_format_returns_true_for_valid_cnpj(): void
    {
        $validCNPJ = $this->generateValidCNPJ();

        $result = $this->clientService->validateCNPJ($validCNPJ);

        $this->assertTrue($result);
    }
    #[Test]
    public function validate_cnpj_format_returns_false_for_invalid_cnpj(): void
    {
        $invalidCNPJ = '12345678000123';

        $result = $this->clientService->validateCNPJ($invalidCNPJ);

        $this->assertFalse($result);
    }
    #[Test]
    public function format_phone_number_formats_brazilian_phone(): void
    {
        $phone = '11999887766';
        $expectedFormat = '(11) 99988-7766';

        $result = $this->clientService->formatPhoneNumber($phone);

        $this->assertEquals($expectedFormat, $result);
    }
    #[Test]
    public function format_phone_number_handles_landline(): void
    {
        $phone = '1133334444';
        $expectedFormat = '(11) 3333-4444';

        $result = $this->clientService->formatPhoneNumber($phone);

        $this->assertEquals($expectedFormat, $result);
    }
    #[Test]
    public function is_valid_license_plate_validates_old_format(): void
    {
        $this->assertTrue($this->clientService->isValidLicensePlate('ABC-1234'));
        $this->assertTrue($this->clientService->isValidLicensePlate('ABC1234'));
    }
    #[Test]
    public function is_valid_license_plate_validates_mercosul_format(): void
    {
        $this->assertTrue($this->clientService->isValidLicensePlate('ABC1D23'));
        $this->assertTrue($this->clientService->isValidLicensePlate('abc1d23'));
    }
    #[Test]
    public function is_valid_license_plate_rejects_invalid_format(): void
    {
        $this->assertFalse($this->clientService->isValidLicensePlate('ABC-123'));
        $this->assertFalse($this->clientService->isValidLicensePlate('12345'));
        $this->assertFalse($this->clientService->isValidLicensePlate('ABCD-1234'));
    }
    #[Test]
    public function clear_client_cache_removes_related_cache_entries(): void
    {
        $client = Client::factory()->make(['id' => 1]);
        $vehicle = Vehicle::factory()->make(['license_plate' => 'ABC-1234']);

        $client->setRelation('vehicles', collect([$vehicle]));

        Cache::shouldReceive('forget')
            ->with("client_plate_ABC-1234")
            ->once();

        Cache::shouldReceive('forget')
            ->with("client_stats_1")
            ->once();

        $this->clientService->clearClientCache($client);
    }
    #[Test]
    public function bulk_update_clients_processes_multiple_clients(): void
    {
        $clientIds = [1, 2, 3];
        $updateData = ['active' => false];

        $this->clientRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with($clientIds, $updateData)
            ->andReturn(3);

        $result = $this->clientService->bulkUpdateClients($clientIds, $updateData);

        $this->assertEquals(3, $result);
    }
    #[Test]
    public function export_clients_data_returns_formatted_data(): void
    {
        $filters = ['active' => true];
        $clients = collect([
            Client::factory()->make(['name' => 'João Silva']),
            Client::factory()->make(['name' => 'Maria Santos'])
        ]);

        $this->clientRepositoryMock
            ->shouldReceive('getForExport')
            ->with($filters)
            ->andReturn($clients);

        $result = $this->clientService->exportClientsData($filters);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('name', $result[0]);
    }
    #[Test]
    public function it_creates_client_with_valid_data()
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $this->generateValidCPF(),
            'document_type' => 'cpf',
            'email' => 'joao@example.com'
        ];

        $client = Client::factory()->make($clientData);

        $this->clientRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($clientData)
            ->andReturn($client);

        $result = $this->clientService->create($clientData);

        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($clientData['name'], $result->name);
        $this->assertEquals($clientData['phone'], $result->phone);
    }
    #[Test]
    public function it_validates_cpf_format_when_creating_client()
    {
        $invalidCPF = '12345678901'; // Invalid CPF

        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $invalidCPF,
            'document_type' => 'cpf'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF inválido');

        $this->clientService->create($clientData);
    }
    #[Test]
    public function it_validates_cnpj_format_when_creating_client()
    {
        $invalidCNPJ = '12345678901234'; // Invalid CNPJ

        $clientData = [
            'name' => 'Empresa Teste Ltda',
            'phone' => '11987654321',
            'document' => $invalidCNPJ,
            'document_type' => 'cnpj'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ inválido');

        $this->clientService->create($clientData);
    }
    #[Test]
    public function it_validates_license_plate_format()
    {
        $invalidPlate = 'INVALID';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Placa inválida');

        $this->clientService->validateLicensePlate($invalidPlate);
    }
    #[Test]
    public function it_validates_old_format_license_plate()
    {
        $validOldPlate = $this->generateValidLicensePlate(false); // ABC-1234

        $result = $this->clientService->validateLicensePlate($validOldPlate);

        $this->assertTrue($result);
    }
    #[Test]
    public function it_validates_mercosul_format_license_plate()
    {
        $validMercosulPlate = $this->generateValidLicensePlate(true); // ABC1D23

        $result = $this->clientService->validateLicensePlate($validMercosulPlate);

        $this->assertTrue($result);
    }
    #[Test]
    public function it_creates_client_with_vehicle_in_transaction()
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $this->generateValidCPF(),
            'document_type' => 'cpf'
        ];

        $vehicleData = [
            'license_plate' => $this->generateValidLicensePlate(),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020
        ];

        $client = Client::factory()->make($clientData);
        $vehicle = Vehicle::factory()->make($vehicleData);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->clientRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($clientData)
            ->andReturn($client);

        $this->vehicleRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(array_merge($vehicleData, ['client_id' => $client->id]))
            ->andReturn($vehicle);

        $result = $this->clientService->createWithVehicle($clientData, $vehicleData);

        $this->assertInstanceOf(Client::class, $result);
    }
    #[Test]
    public function it_rolls_back_transaction_on_error()
    {
        $clientData = [
            'name' => 'João Silva',
            'phone' => '11987654321',
            'document' => $this->generateValidCPF(),
            'document_type' => 'cpf'
        ];

        $vehicleData = [
            'license_plate' => $this->generateValidLicensePlate(),
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldReceive('commit')->never();

        $this->clientRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($clientData)
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->clientService->createWithVehicle($clientData, $vehicleData);
    }
    #[Test]
    public function it_updates_client_and_clears_cache()
    {
        $clientId = 1;
        $updateData = ['name' => 'João Silva Updated'];

        $client = Client::factory()->make(['id' => $clientId]);

        $this->clientRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($clientId, $updateData)
            ->andReturn($client);

        Cache::shouldReceive('forget')
            ->once()
            ->with("client_{$clientId}");

        $result = $this->clientService->update($clientId, $updateData);

        $this->assertInstanceOf(Client::class, $result);
    }
    #[Test]
    public function it_validates_vehicle_mileage()
    {
        $invalidMileage = -1000;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quilometragem deve ser um valor positivo');

        $this->clientService->validateMileage($invalidMileage);
    }
    #[Test]
    public function it_formats_brazilian_phone_number()
    {
        $phone = '11987654321';

        $formattedPhone = $this->clientService->formatPhoneNumber($phone);

        $this->assertEquals('(11) 98765-4321', $formattedPhone);
    }
    #[Test]
    public function it_searches_clients_with_filters()
    {
        $filters = [
            'name' => 'João',
            'phone' => '11987',
            'active' => true
        ];

        $clients = collect([
            Client::factory()->make(['name' => 'João Silva']),
            Client::factory()->make(['name' => 'João Santos'])
        ]);

        $this->clientRepositoryMock
            ->shouldReceive('findWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($clients);

        $result = $this->clientService->searchClients($filters);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn($client) => str_contains($client->name, 'João')));
    }
    #[Test]
    public function it_finds_client_by_license_plate_with_cache()
    {
        $licensePlate = $this->generateValidLicensePlate();
        $client = Client::factory()->make();

        Cache::shouldReceive('remember')
            ->once()
            ->with("client_by_plate_{$licensePlate}", 3600, Mockery::type('Closure'))
            ->andReturn($client);

        $result = $this->clientService->findByLicensePlate($licensePlate);

        $this->assertInstanceOf(Client::class, $result);
    }
    #[Test]
    public function it_validates_document_uniqueness()
    {
        $document = $this->generateValidCPF();

        $this->clientRepositoryMock
            ->shouldReceive('findByDocument')
            ->once()
            ->with($document)
            ->andReturn(Client::factory()->make());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Documento já cadastrado');

        $this->clientService->validateDocumentUniqueness($document);
    }
    #[Test]
    public function it_allows_unique_document()
    {
        $document = $this->generateValidCPF();

        $this->clientRepositoryMock
            ->shouldReceive('findByDocument')
            ->once()
            ->with($document)
            ->andReturn(null);

        $result = $this->clientService->validateDocumentUniqueness($document);

        $this->assertTrue($result);
    }
    #[Test]
    public function it_deletes_client_and_clears_cache()
    {
        $clientId = 1;

        $this->clientRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($clientId)
            ->andReturn(true);

        Cache::shouldReceive('forget')
            ->once()
            ->with("client_{$clientId}");

        $result = $this->clientService->delete($clientId);

        $this->assertTrue($result);
    }
}
