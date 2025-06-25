<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    #[Test]
    public function login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'cpf',
                            'phone'
                        ],
                        'token',
                        'expires_at'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('data.token'));
    }
    #[Test]
    public function login_with_invalid_credentials_returns_error(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ]);
    }
    #[Test]
    public function login_with_inactive_user_returns_error(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => false
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Usuário inativo'
                ]);
    }
    #[Test]
    public function login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }
    #[Test]
    public function login_validates_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
    #[Test]
    public function logout_with_valid_token_revokes_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout realizado com sucesso'
                ]);
    }
    #[Test]
    public function logout_without_token_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
    #[Test]
    public function me_returns_authenticated_user_data(): void
    {
        $user = User::factory()->create([
            'cpf' => $this->generateValidCpf(),
            'phone' => '(11) 99999-9999'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'cpf',
                        'phone',
                        'active',
                        'service_center',
                        'roles',
                        'permissions'
                    ]
                ]);

        $this->assertEquals($user->id, $response->json('data.id'));
        $this->assertEquals($user->email, $response->json('data.email'));
    }
    #[Test]
    public function me_without_token_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
    #[Test]
    public function register_creates_new_user_successfully(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => $this->generateValidCpf(),
            'phone' => '(11) 99999-9999',
            'service_center_id' => 1
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'cpf',
                            'phone'
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'name' => 'João Silva'
        ]);
    }
    #[Test]
    public function register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'email',
                    'password',
                    'cpf'
                ]);
    }
    #[Test]
    public function register_validates_unique_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => $this->generateValidCpf()
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
    #[Test]
    public function register_validates_unique_cpf(): void
    {
        $cpf = $this->generateValidCpf();

        $existingUser = User::factory()->create(['cpf' => $cpf]);

        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => $cpf
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }
    #[Test]
    public function register_validates_cpf_format(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '123.456.789-00' // Invalid CPF
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }
    #[Test]
    public function register_validates_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'cpf' => $this->generateValidCpf()
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
    #[Test]
    public function refresh_token_with_valid_token_returns_new_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'token',
                        'expires_at'
                    ]
                ]);

        $this->assertNotEmpty($response->json('data.token'));
    }
    #[Test]
    public function refresh_token_without_token_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401);
    }
    #[Test]
    public function change_password_with_valid_data_updates_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Senha alterada com sucesso'
                ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
    #[Test]
    public function change_password_with_wrong_current_password_returns_error(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_password']);
    }
    #[Test]
    public function forgot_password_with_valid_email_sends_reset_link(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com'
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'user@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Link de recuperação enviado para o e-mail'
                ]);
    }
    #[Test]
    public function forgot_password_with_nonexistent_email_returns_error(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
    #[Test]
    public function login_rate_limiting_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }
    #[Test]
    public function token_expiration_is_set_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);

        $expiresAt = $response->json('data.expires_at');
        $this->assertNotNull($expiresAt);

        // Check if expiration is in the future
        $this->assertGreaterThan(now()->timestamp, strtotime($expiresAt));
    }
}
