<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use App\Domain\User\Models\User;
use App\Domain\Client\Models\Client;
use App\Domain\Service\Models\ServiceCenter;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear all caches before each test
        Cache::flush();

        // Only flush Redis if it's configured for testing
        if (config('cache.default') === 'redis') {
            try {
                Redis::flushdb();
            } catch (\Exception $e) {
                // Redis might not be available in testing
            }
        }

        // Enable query logging for performance monitoring
        if (env('QUERY_LOG', false)) {
            DB::enableQueryLog();
        }

        // Seed basic data required for tests (optimized)
        $this->seedBasicDataOptimized();

        // Set Brazilian locale for faker
        $this->faker = \Faker\Factory::create('pt_BR');
    }

    /**
     * Static cache to avoid recreating data across tests
     */
    protected static $basicDataSeeded = false;

    /**
     * Optimized seed basic data needed for tests
     */
    protected function seedBasicDataOptimized(): void
    {
        // Only seed once per test session
        if (static::$basicDataSeeded) {
            return;
        }

        // Create roles and permissions optimized
        $this->seedRolesAndPermissionsOptimized();

        // Seed service statuses optimized
        $this->seedServiceStatusesOptimized();

        // Seed payment methods optimized
        $this->seedPaymentMethodsOptimized();

        static::$basicDataSeeded = true;
    }

    protected function tearDown(): void
    {
        // Clean up caches and logs
        Cache::flush();

        // Only flush Redis if it's configured for testing
        if (config('cache.default') === 'redis') {
            try {
                Redis::flushdb();
            } catch (\Exception $e) {
                // Redis might not be available in testing
            }
        }

        if (env('QUERY_LOG', false)) {
            $queries = DB::getQueryLog();
            if (count($queries) > 50) {
                echo "\nWarning: Test executed " . count($queries) . " queries - consider optimization\n";
            }
        }

        parent::tearDown();
    }

    /**
     * Create authenticated user for API testing
     */
    protected function actingAsUser($role = 'admin', $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if ($role) {
            $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $user->assignRole($roleModel);
        }

        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    /**
     * Create authenticated API request
     */
    protected function authenticatedJson($method, $uri, $data = [], $headers = [], $role = 'admin')
    {
        $user = $this->actingAsUser($role);

        return $this->json($method, $uri, $data, array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken,
        ], $headers));
    }

    /**
     * Assert JSON response structure for API responses
     */
    protected function assertApiResponse($response, $structure = null)
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        if ($structure) {
            $response->assertJsonStructure($structure);
        }
    }

    /**
     * Assert successful API response
     */
    protected function assertSuccessResponse($response, $message = null, $statusCode = 200)
    {
        $response->assertStatus($statusCode)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ])
                ->assertJson(['success' => true]);

        if ($message) {
            $response->assertJson(['message' => $message]);
        }
    }

    /**
     * Assert error API response
     */
    protected function assertErrorResponse($response, $status = 422, $message = null)
    {
        $response->assertStatus($status)
                ->assertJsonStructure([
                    'success',
                    'message'
                ])
                ->assertJson(['success' => false]);

        if ($message) {
            $response->assertJson(['message' => $message]);
        }
    }

    /**
     * Assert validation errors in API response
     */
    protected function assertValidationErrors($response, $fields = [])
    {
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ])
                ->assertJson(['success' => false]);

        if (!empty($fields)) {
            $response->assertJsonValidationErrors($fields);
        }
    }

    /**
     * Assert paginated response structure
     */
    protected function assertPaginatedResponse($response)
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]
        ]);
    }

    /**
     * Optimized seed roles and permissions
     */
    protected function seedRolesAndPermissionsOptimized(): void
    {
        // Only create if they don't exist
        if (Role::count() > 0) {
            return;
        }

        // Use single bulk insert for better performance
        $roles = ['admin', 'manager', 'technician', 'attendant'];
        $permissions = [
            'view-dashboard', 'manage-services', 'manage-clients', 'view-reports',
            'view-services', 'update-service-status', 'add-service-items',
            'view-clients', 'create-services'
        ];

        // Bulk insert roles
        $roleData = [];
        foreach ($roles as $role) {
            $roleData[] = [
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('roles')->insert($roleData);

        // Bulk insert permissions
        $permissionData = [];
        foreach ($permissions as $permission) {
            $permissionData[] = [
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('permissions')->insert($permissionData);
    }

    /**
     * Optimized seed service statuses
     */
    protected function seedServiceStatusesOptimized(): void
    {
        // Use upsert for better performance
        DB::table('service_statuses')->upsert([
            ['id' => 1, 'name' => 'scheduled', 'description' => 'Serviço agendado', 'color' => '#fbbf24', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'in_progress', 'description' => 'Serviço em execução', 'color' => '#3b82f6', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'completed', 'description' => 'Serviço finalizado', 'color' => '#10b981', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'cancelled', 'description' => 'Serviço cancelado', 'color' => '#ef4444', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['name', 'description', 'color', 'sort_order', 'updated_at']);
    }

    /**
     * Optimized seed payment methods
     */
    protected function seedPaymentMethodsOptimized(): void
    {
        // Use upsert for better performance
        DB::table('payment_methods')->upsert([
            ['id' => 1, 'name' => 'Dinheiro', 'description' => 'Pagamento em dinheiro', 'active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Cartão de Débito', 'description' => 'Pagamento com cartão de débito', 'active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Cartão de Crédito', 'description' => 'Pagamento com cartão de crédito', 'active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'PIX', 'description' => 'Pagamento via PIX', 'active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['name', 'description', 'active', 'sort_order', 'updated_at']);
    }

    /**
     * Generate valid Brazilian CPF
     */
    protected function generateValidCPF(): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= rand(0, 9);
        }

        // Calculate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        // Calculate second check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $sum += $digit1 * 2;
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return $cpf . $digit1 . $digit2;
    }

    /**
     * Generate valid Brazilian CNPJ
     */
    protected function generateValidCNPJ(): string
    {
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= rand(0, 9);
        }

        // Calculate first check digit
        $multipliers = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $multipliers[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        // Calculate second check digit
        $multipliers = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $multipliers[$i];
        }
        $sum += $digit1 * $multipliers[12];
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return $cnpj . $digit1 . $digit2;
    }

    /**
     * Generate valid Brazilian license plate
     */
    protected function generateValidLicensePlate($mercosul = false): string
    {
        if ($mercosul) {
            // Mercosul format: ABC1D23
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return $letters[rand(0, 25)] . $letters[rand(0, 25)] . $letters[rand(0, 25)] .
                   rand(0, 9) . $letters[rand(0, 25)] . rand(0, 9) . rand(0, 9);
        } else {
            // Old format: ABC-1234
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return $letters[rand(0, 25)] . $letters[rand(0, 25)] . $letters[rand(0, 25)] . '-' .
                   rand(1000, 9999);
        }
    }

    /**
     * Generate Brazilian phone number
     */
    protected function generateBrazilianPhone($mobile = true): string
    {
        $ddd = rand(11, 99);

        if ($mobile) {
            // Mobile: 9XXXX-XXXX
            return $ddd . '9' . rand(10000000, 99999999);
        } else {
            // Landline: XXXX-XXXX
            return $ddd . rand(20000000, 59999999);
        }
    }

    /**
     * Generate Brazilian postal code (CEP)
     */
    protected function generateBrazilianCEP(): string
    {
        return rand(10000, 99999) . '-' . rand(100, 999);
    }

    /**
     * Assert that cache was cleared for a specific pattern
     */
    protected function assertCacheWasCleared($pattern): void
    {
        $keys = Redis::keys("*{$pattern}*");
        $this->assertEmpty($keys, "Cache keys matching pattern '{$pattern}' should be cleared");
    }

    /**
     * Assert that cache contains a specific key
     */
    protected function assertCacheContains($key): void
    {
        $this->assertTrue(Cache::has($key), "Cache should contain key '{$key}'");
    }

    /**
     * Assert that cache does not contain a specific key
     */
    protected function assertCacheDoesNotContain($key): void
    {
        $this->assertFalse(Cache::has($key), "Cache should not contain key '{$key}'");
    }

    /**
     * Create a service center for testing
     */
    protected function createServiceCenter($attributes = []): ServiceCenter
    {
        return ServiceCenter::factory()->create($attributes);
    }

    /**
     * Create a client for testing
     */
    protected function createClient($attributes = []): Client
    {
        return Client::factory()->create($attributes);
    }

    /**
     * Assert geolocation coordinates are valid
     */
    protected function assertValidCoordinates($lat, $lng): void
    {
        $this->assertIsFloat($lat);
        $this->assertIsFloat($lng);
        $this->assertGreaterThanOrEqual(-90, $lat);
        $this->assertLessThanOrEqual(90, $lat);
        $this->assertGreaterThanOrEqual(-180, $lng);
        $this->assertLessThanOrEqual(180, $lng);
    }

    /**
     * Calculate distance between two coordinates (for testing geolocation)
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Assert performance is within acceptable limits
     */
    protected function assertPerformance($maxQueries = 50, $maxMemory = 32): void
    {
        if (env('QUERY_LOG', false)) {
            $queries = DB::getQueryLog();
            $this->assertLessThanOrEqual(
                $maxQueries,
                count($queries),
                "Test executed too many queries: " . count($queries)
            );
        }

        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
        $this->assertLessThanOrEqual(
            $maxMemory,
            $memoryUsage,
            "Test used too much memory: {$memoryUsage}MB"
        );
    }

    /**
     * Mock external services for testing
     */
    protected function mockExternalServices(): void
    {
        // Mock payment gateway
        Http::fake([
            'api.mercadopago.com/*' => Http::response(['status' => 'approved'], 200),
            'sandbox.mercadopago.com/*' => Http::response(['status' => 'approved'], 200),
        ]);

        // Mock geolocation services
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'results' => [
                    [
                        'geometry' => [
                            'location' => [
                                'lat' => -23.5505,
                                'lng' => -46.6333
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);
    }
}
