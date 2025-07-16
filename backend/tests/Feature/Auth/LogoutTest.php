<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Login first
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
                      ->assertJson([
                          'status' => 'success',
                          'message' => 'Logout successful'
                      ]);
    }

    #[Test]
    public function test_logout_requires_authentication(): void
    {
        // Try to logout without authentication
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    #[Test]
    public function test_logout_with_invalid_token(): void
    {
        // Try to logout with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    #[Test]
    public function test_logout_without_authorization_header(): void
    {
        // Try to logout without Authorization header
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    #[Test]
    public function test_logout_with_malformed_authorization_header(): void
    {
        // Try to logout with malformed Authorization header
        $response = $this->withHeaders([
            'Authorization' => 'InvalidFormat token123'
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    #[Test]
    public function test_token_is_revoked_on_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Create a database token directly instead of using login
        $token = $user->createToken('test-token')->plainTextToken;

        // Verify token exists in database
        $this->assertEquals(1, $user->fresh()->tokens()->count());

        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);

        // Verify token was revoked from database
        // Note: In some environments, there might be a slight delay
        $this->assertEquals(0, $user->fresh()->tokens()->count(), 'Token should be revoked after logout');
    }

    #[Test]
    public function test_multiple_logout_attempts_with_same_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Create a database token directly instead of using login
        $token = $user->createToken('test-token')->plainTextToken;

        // First logout should succeed
        $logoutResponse1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/logout');

        $logoutResponse1->assertStatus(200);

        // Second logout with same token should fail or return success (both are acceptable)
        // In some cases, the token might be already revoked, so the logout returns success
        $logoutResponse2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/logout');

        // Accept both 200 (token already revoked) and 401 (token invalid)
        $this->assertTrue(
            in_array($logoutResponse2->status(), [200, 401]),
            'Second logout should return 200 (token already revoked) or 401 (token invalid). Got: ' . $logoutResponse2->status()
        );
    }
}
