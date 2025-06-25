<?php

namespace Tests\Feature\Cache;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

class CacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
    #[Test]
    public function client_by_license_plate_cache_works(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'client_id' => $client->id,
            'license_plate' => 'ABC-1234'
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First request should cache the result
        $this->assertNull(Cache::get('client_plate_ABC-1234'));

        $response = $this->getJson('/api/clients/search/license-plate/ABC-1234');

        $response->assertStatus(200);
        $this->assertEquals($client->id, $response->json('data.id'));
    }
    #[Test]
    public function cache_operations_work(): void
    {
        // Test basic cache operations
        Cache::put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_key'));

        Cache::forget('test_key');
        $this->assertNull(Cache::get('test_key'));
    }
}
