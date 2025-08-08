<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class TelegramWebhookControllerTest extends BaseTestCase
{

    protected function beforeApplicationBooted(): void
    {
        // Configure Telegram services for testing before any services are instantiated
        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'test_bot_token_123456',
            'services.telegram.recipients' => ['123456789', '987654321']
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockTelegramServices();
    }

    protected function mockTelegramServices(): void
    {
        // Logger para middleware e controller
        $this->instance(\App\Contracts\LoggingServiceInterface::class, Mockery::mock(\App\Contracts\LoggingServiceInterface::class, function ($mock) {
            $mock->shouldReceive('logTelegramEvent')->andReturn(true);
            $mock->shouldReceive('logException')->andReturn(true);
            $mock->shouldReceive('logInfo')->andReturn(true);
            $mock->shouldReceive('logError')->andReturn(true);
            $mock->shouldReceive('logWarning')->andReturn(true);
            $mock->shouldReceive('logDebug')->andReturn(true);
            $mock->shouldReceive('logApiRequest')->andReturn(true);
            $mock->shouldReceive('logApiResponse')->andReturn(true);
            $mock->shouldReceive('logBusinessOperation')->andReturn(true);
            $mock->shouldReceive('logSecurityEvent')->andReturn(true);
            $mock->shouldReceive('logPerformance')->andReturn(true);
            $mock->shouldReceive('logAudit')->andReturn(true);
            $mock->shouldReceive('logWhatsAppEvent')->andReturn(true);
            $mock->shouldReceive('getLogStats')->andReturn([]);
        }));

        // Canal Telegram (não será chamado diretamente aqui, mas garantimos que não quebre)
        $this->mock(\App\Services\Channels\TelegramChannel::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->andReturn(['ok' => true]);
            $mock->shouldReceive('sendKeyboard')->andReturn(['ok' => true]);
            $mock->shouldReceive('sendTextMessage')->andReturn(['ok' => true]);
            $mock->shouldReceive('sendMessageWithKeyboard')->andReturn(['ok' => true]);
            $mock->shouldReceive('answerCallbackQuery')->andReturn(['ok' => true]);
            $mock->shouldReceive('getFile')->andReturn(['ok' => true, 'result' => ['file_path' => 'test']]);
            $mock->shouldReceive('getMe')->andReturn(['ok' => true, 'result' => ['username' => 'test_bot']]);
            $mock->shouldReceive('testConnection')->andReturn(['success' => true]);
            $mock->shouldReceive('sendNotification')->andReturn(['success' => true]);
            $mock->shouldReceive('sendTypingIndicator')->andReturn(['success' => true]);
            $mock->shouldReceive('sendUploadDocumentIndicator')->andReturn(['success' => true]);
            $mock->shouldReceive('isEnabled')->andReturn(true);
        });

        // Serviços de alto nível
        $this->mock(\App\Services\TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->andReturn(['ok' => true]);
            $mock->shouldReceive('sendKeyboard')->andReturn(['ok' => true]);
            $mock->shouldReceive('answerCallbackQuery')->andReturn(['ok' => true]);
            $mock->shouldReceive('getMe')->andReturn(['ok' => true, 'result' => ['username' => 'test_bot']]);
        });

        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('validatePayload')->andReturn(['valid' => true, 'message' => 'Valid payload']);
            $mock->shouldReceive('setWebhook')->andReturn(['success' => true, 'message' => 'Webhook set successfully']);
            $mock->shouldReceive('getWebhookInfo')->andReturn(['success' => true, 'data' => ['url' => 'https://example.com']]);
            $mock->shouldReceive('deleteWebhook')->andReturn(['success' => true, 'message' => 'Webhook deleted']);
            $mock->shouldReceive('testBot')->andReturn(['success' => true, 'message' => 'Bot test successful']);
        });

        $this->mock(\App\Services\TelegramMessageProcessorService::class, function ($mock) {
            $mock->shouldReceive('processWebhookPayload')->andReturn([
                'success' => true,
                'message' => 'Message processed successfully',
                'status' => 'success'
            ]);
        });
    }

    private function sampleTextPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Joao',
                    'username' => 'joao_user'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Joao',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => 'Ola, como posso ajudar?'
            ]
        ], $overrides);
    }

    private function sampleCallbackPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'update_id' => 123456789,
            'callback_query' => [
                'id' => 'cb-1',
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Joao',
                    'username' => 'joao_user'
                ],
                'message' => [
                    'message_id' => 1,
                    'from' => [
                        'id' => 123456789,
                        'is_bot' => true,
                        'first_name' => 'Bot',
                        'username' => 'test_bot'
                    ],
                    'chat' => [
                        'id' => 987654321,
                        'first_name' => 'Joao',
                        'type' => 'private'
                    ],
                    'date' => time()
                ],
                'chat_instance' => 'inst-1',
                'data' => 'menu_option_1'
            ]
        ], $overrides);
    }

    // ========== Webhook: fluxo feliz (texto) ==========
    #[Test]
    public function handle_processes_text_message_successfully(): void
    {
        $response = $this->postJson('/api/telegram/webhook', $this->sampleTextPayload());
        $response->assertStatus(200)
                 ->assertJsonStructure(['status','message','data','timestamp']);
    }

    // ========== Webhook: validações ==========
    #[Test]
    public function handle_validates_required_update_id(): void
    {
        $payload = $this->sampleTextPayload(['update_id' => null]);
        unset($payload['update_id']);
        $response = $this->postJson('/api/telegram/webhook', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['update_id']);
    }

    #[Test]
    public function handle_validates_message_text_required(): void
    {
        $payload = $this->sampleTextPayload();
        unset($payload['message']['text']);
        $response = $this->postJson('/api/telegram/webhook', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['message.text']);
    }

    #[Test]
    public function handle_validates_callback_query_data_required(): void
    {
        $payload = $this->sampleCallbackPayload();
        unset($payload['callback_query']['data']);
        $response = $this->postJson('/api/telegram/webhook', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['callback_query.data']);
    }

    // ========== Webhook: callback ==========
    #[Test]
    public function handle_processes_callback_query_successfully(): void
    {
        $response = $this->postJson('/api/telegram/webhook', $this->sampleCallbackPayload());
        $response->assertStatus(200)->assertJsonStructure(['status','message','data','timestamp']);
    }

    // ========== Webhook: cenários especiais via mocks ==========
    #[Test]
    public function handle_returns_ignored_when_payload_invalid(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('validatePayload')->andReturn(['valid' => false, 'message' => 'Payload ignored']);
        });
        $response = $this->postJson('/api/telegram/webhook', $this->sampleTextPayload());
        $response->assertStatus(200)->assertJson(['status' => 'ignored']);
    }

    #[Test]
    public function handle_returns_ignored_when_processor_ignored(): void
    {
        $this->mock(\App\Services\TelegramMessageProcessorService::class, function ($mock) {
            $mock->shouldReceive('processWebhookPayload')->andReturn([
                'success' => true,
                'message' => 'Ignored update',
                'status' => 'ignored'
            ]);
        });
        $response = $this->postJson('/api/telegram/webhook', $this->sampleTextPayload());
        $response->assertStatus(200)->assertJson(['status' => 'ignored']);
    }

    #[Test]
    public function handle_returns_error_when_processor_fails(): void
    {
        $this->mock(\App\Services\TelegramMessageProcessorService::class, function ($mock) {
            $mock->shouldReceive('processWebhookPayload')->andReturn([
                'success' => false,
                'message' => 'Processing failed',
                'status' => 'error'
            ]);
        });
        $response = $this->postJson('/api/telegram/webhook', $this->sampleTextPayload());
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    #[Test]
    public function handle_returns_500_on_exception(): void
    {
        $this->mock(\App\Services\TelegramMessageProcessorService::class, function ($mock) {
            $mock->shouldReceive('processWebhookPayload')->andThrow(new \Exception('Unexpected error'));
        });
        $response = $this->postJson('/api/telegram/webhook', $this->sampleTextPayload());
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    // ========== setWebhook ==========
    #[Test]
    public function set_webhook_success(): void
    {
        $response = $this->postJson('/api/telegram/set-webhook', ['webhook_url' => 'https://example.com/hook']);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }

    #[Test]
    public function set_webhook_failure_returns_400(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('setWebhook')->andReturn(['success' => false, 'message' => 'Invalid URL']);
        });
        $response = $this->postJson('/api/telegram/set-webhook', ['webhook_url' => 'https://bad.example/hook']);
        $response->assertStatus(400)->assertJson(['status' => 'error']);
    }

    #[Test]
    public function set_webhook_exception_returns_500(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('setWebhook')->andThrow(new \Exception('boom'));
        });
        $response = $this->postJson('/api/telegram/set-webhook', ['webhook_url' => 'https://example.com/hook']);
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    // ========== getWebhookInfo ==========
    #[Test]
    public function get_webhook_info_success(): void
    {
        $response = $this->getJson('/api/telegram/webhook-info');
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }

    #[Test]
    public function get_webhook_info_failure_returns_400(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('getWebhookInfo')->andReturn(['success' => false, 'message' => 'Unavailable']);
        });
        $response = $this->getJson('/api/telegram/webhook-info');
        $response->assertStatus(400)->assertJson(['status' => 'error']);
    }

    #[Test]
    public function get_webhook_info_exception_returns_500(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('getWebhookInfo')->andThrow(new \Exception('fail'));
        });
        $response = $this->getJson('/api/telegram/webhook-info');
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    // ========== deleteWebhook ==========
    #[Test]
    public function delete_webhook_success(): void
    {
        $response = $this->deleteJson('/api/telegram/webhook');
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }

    #[Test]
    public function delete_webhook_failure_returns_400(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('deleteWebhook')->andReturn(['success' => false, 'message' => 'Cannot delete']);
        });
        $response = $this->deleteJson('/api/telegram/webhook');
        $response->assertStatus(400)->assertJson(['status' => 'error']);
    }

    #[Test]
    public function delete_webhook_exception_returns_500(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('deleteWebhook')->andThrow(new \Exception('oops'));
        });
        $response = $this->deleteJson('/api/telegram/webhook');
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    // ========== test (bot) ==========
    #[Test]
    public function test_bot_success(): void
    {
        $response = $this->postJson('/api/telegram/test');
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }

    #[Test]
    public function test_bot_failure_returns_400(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('testBot')->andReturn(['success' => false, 'message' => 'Down']);
        });
        $response = $this->postJson('/api/telegram/test');
        $response->assertStatus(400)->assertJson(['status' => 'error']);
    }

    #[Test]
    public function test_bot_exception_returns_500(): void
    {
        $this->mock(\App\Services\TelegramWebhookService::class, function ($mock) {
            $mock->shouldReceive('testBot')->andThrow(new \Exception('panic'));
        });
        $response = $this->postJson('/api/telegram/test');
        $response->assertStatus(500)->assertJson(['status' => 'error']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
