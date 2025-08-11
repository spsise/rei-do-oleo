<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Services\Channels\TelegramChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class TelegramWebhookExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the TelegramChannel to avoid actual API calls
        $this->mock(TelegramChannel::class, function ($mock) {
            $mock->shouldReceive('sendTextMessage')
                ->andReturn(['success' => true]);
        });
    }

    public function test_validation_error_sends_message_to_telegram_chat()
    {
        // Test payload with missing required fields
        $payload = [
            'update_id' => 'invalid', // Should be integer
            // Missing message.chat.id and message.text
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        // Should return 200 (not 422) to avoid Telegram retries
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Validation failed'
        ]);
        $response->assertJsonStructure([
            'status',
            'message',
            'errors'
        ]);
    }

    public function test_callback_query_validation_error_sends_message_to_telegram()
    {
        // Test callback query payload with missing required fields
        $payload = [
            'update_id' => 123456,
            'callback_query' => [
                'id' => 'callback_123',
                // Missing callback_query.data and callback_query.message.chat.id
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Validation failed'
        ]);
    }

    public function test_malformed_payload_returns_200_with_error()
    {
        // Test completely malformed payload
        $payload = [
            'invalid' => 'data',
            'structure' => 'wrong'
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Validation failed'
        ]);
    }

    public function test_valid_payload_returns_success()
    {
        // Test valid payload
        $payload = [
            'update_id' => 123456,
            'message' => [
                'chat' => [
                    'id' => 987654321
                ],
                'text' => '/start',
                'from' => [
                    'id' => 123456789
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        // Should not return validation error
        $response->assertStatus(200);
        $response->assertJsonMissing([
            'status' => 'error',
            'message' => 'Validation failed'
        ]);
    }

    public function test_middleware_logs_validation_errors()
    {
        $payload = [
            'update_id' => 'invalid',
        ];

        $this->postJson('/api/telegram/webhook', $payload);

        // Check if validation error was logged
        $this->assertDatabaseHas('logs', [
            'level' => 'warning',
            'message' => 'Telegram webhook validation failed'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
