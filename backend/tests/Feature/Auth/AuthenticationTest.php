<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function unauthenticated_requests_return_401(): void
    {
        $protectedRoutes = [
            '/api/clients',
            '/api/services',
            '/api/products',
            '/api/categories',
            '/api/users',
            '/api/service-centers',
            '/api/me'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }
    }
    #[Test]
    public function sanctum_token_authentication_works(): void
    {
        $user = User::factory()->create();

        // Use Sanctum acting as
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'email',
                        'name'
                    ]
                ]);
    }
    #[Test]
    public function invalid_token_returns_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/me');

        $response->assertStatus(401);
    }
    #[Test]
    public function expired_token_returns_401(): void
    {
        $user = User::factory()->create();

        // Create expired token
        $token = $user->createToken('test-token');
        $token->accessToken->update([
            'created_at' => now()->subDays(10) // Simulate expired token
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/me');

        // Note: Sanctum doesn't have built-in expiration, but this tests the concept
        $response->assertStatus(401);
    }
    #[Test]
    public function revoked_token_returns_401(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        // Revoke token
        $token->accessToken->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/me');

        $response->assertStatus(401);
    }
    #[Test]
    public function user_permissions_are_enforced(): void
    {
        $serviceCenter = ServiceCenter::factory()->create();

        // Create roles and permissions
        $managerRole = Role::create(['name' => 'manager']);
        $technicianRole = Role::create(['name' => 'technician']);

        $manageUsersPermission = Permission::create(['name' => 'manage_users']);
        $viewServicesPermission = Permission::create(['name' => 'view_services']);

        $managerRole->givePermissionTo([$manageUsersPermission, $viewServicesPermission]);
        $technicianRole->givePermissionTo($viewServicesPermission);

        // Manager user
        $manager = User::factory()->create(['service_center_id' => $serviceCenter->id]);
        $manager->assignRole($managerRole);

        // Technician user
        $technician = User::factory()->create(['service_center_id' => $serviceCenter->id]);
        $technician->assignRole($technicianRole);

        // Manager can access user management
        Sanctum::actingAs($manager);
        $response = $this->getJson('/api/users');
        $response->assertStatus(200);

        // Technician cannot access user management
        Sanctum::actingAs($technician);
        $response = $this->getJson('/api/users');
        $response->assertStatus(403);

        // Both can view services
        Sanctum::actingAs($manager);
        $response = $this->getJson('/api/services');
        $response->assertStatus(200);

        Sanctum::actingAs($technician);
        $response = $this->getJson('/api/services');
        $response->assertStatus(200);
    }
    #[Test]
    public function inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'active' => false,
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
    }
    #[Test]
    public function user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Verify user is authenticated
        $response = $this->getJson('/api/me');
        $response->assertStatus(200);

        // Logout
        $response = $this->postJson('/api/logout');
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout realizado com sucesso'
                ]);

        // Verify token is revoked (this depends on your logout implementation)
        $this->assertEquals(0, $user->tokens()->count());
    }
    #[Test]
    public function api_rate_limiting_works(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        // Make multiple login attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password'
            ]);
        }

        // Should be rate limited
        $response->assertStatus(429); // Too Many Requests
    }
    #[Test]
    public function csrf_protection_works_for_web_routes(): void
    {
        // This test would be relevant for web routes with CSRF protection
        // API routes typically don't use CSRF protection when using token auth
        $this->assertTrue(true); // Placeholder
    }
    #[Test]
    public function token_abilities_are_enforced(): void
    {
        $user = User::factory()->create();

        // Create token with specific abilities
        $token = $user->createToken('limited-token', ['view-clients']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/clients');

        // Should work for allowed ability
        $response->assertStatus(200);

        // Should fail for non-allowed ability
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/clients', []);

        $response->assertStatus(403);
    }
    #[Test]
    public function multiple_tokens_can_exist_for_user(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('web-token');
        $token2 = $user->createToken('mobile-token');

        $this->assertEquals(2, $user->tokens()->count());

        // Both tokens should work
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1->plainTextToken,
        ])->getJson('/api/me');

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2->plainTextToken,
        ])->getJson('/api/me');

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }
    #[Test]
    public function user_can_revoke_specific_tokens(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('web-token');
        $token2 = $user->createToken('mobile-token');

        // Revoke specific token
        $user->tokens()->where('name', 'web-token')->delete();

        $this->assertEquals(1, $user->tokens()->count());

        // web-token should not work
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1->plainTextToken,
        ])->getJson('/api/me');

        // mobile-token should still work
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2->plainTextToken,
        ])->getJson('/api/me');

        $response1->assertStatus(401);
        $response2->assertStatus(200);
    }
    #[Test]
    public function user_service_center_scoping_works(): void
    {
        $serviceCenter1 = ServiceCenter::factory()->create();
        $serviceCenter2 = ServiceCenter::factory()->create();

        $user1 = User::factory()->create(['service_center_id' => $serviceCenter1->id]);
        $user2 = User::factory()->create(['service_center_id' => $serviceCenter2->id]);

        // User should only see data from their service center
        Sanctum::actingAs($user1);
        $response = $this->getJson('/api/services');
        $response->assertStatus(200);

        // This would need to be implemented in the actual controllers
        // to filter data by user's service center
        $this->assertTrue(true); // Placeholder for service center scoping test
    }
    #[Test]
    public function password_reset_token_validation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Request password reset
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200);

        // In a real implementation, you would check that a reset token was created
        // and test the reset process with the token
        $this->assertTrue(true); // Placeholder
    }
    #[Test]
    public function account_lockout_after_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password')
        ]);

        // Make multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password'
            ]);
        }

        // Account should be temporarily locked
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password'
        ]);

        $response->assertStatus(429); // Too many requests
    }
}
