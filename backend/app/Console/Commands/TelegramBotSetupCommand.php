<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Contracts\LoggingServiceInterface;

class TelegramBotSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:bot-setup
                            {--set-webhook : Set webhook URL}
                            {--delete-webhook : Delete webhook}
                            {--webhook-info : Get webhook info}
                            {--test : Test bot functionality}
                            {--webhook-url= : Webhook URL to set}
                            {--with-audio-support : Set webhook with full audio support}
                            {--all : Run all setup operations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and manage Telegram bot webhook';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(LoggingServiceInterface $loggingService)
    {
        // Debug: Test if LoggingService is injected correctly
        $loggingService->logTelegramEvent('command_started', [
            'command' => 'telegram:bot-setup',
            'options' => $this->options()
        ], 'info');

        $botToken = config('services.telegram.bot_token');
        $recipients = config('services.telegram.recipients', []);

        if (!$botToken) {
            $this->error('❌ Telegram bot token not configured');
            $this->info('Please set TELEGRAM_BOT_TOKEN in your .env file');
            return 1;
        }

        $apiUrl = "https://api.telegram.org/bot{$botToken}";

        $this->info('🤖 Telegram Bot Setup');
        $this->info('==================');
        $this->info("Bot Token: " . substr($botToken, 0, 10) . '...');
        $this->info("Recipients: " . implode(', ', $recipients));
        $this->info('');

        // Validate token first
        $this->validateToken($apiUrl, $loggingService);

        // Handle different options
        if ($this->option('set-webhook') || $this->option('all')) {
            $this->setWebhook($apiUrl, $loggingService);
        }

        if ($this->option('with-audio-support')) {
            $this->setWebhookWithAudioSupport($apiUrl, $loggingService);
        }

        if ($this->option('delete-webhook') || $this->option('all')) {
            $this->deleteWebhook($apiUrl, $loggingService);
        }

        if ($this->option('webhook-info') || $this->option('all')) {
            $this->getWebhookInfo($apiUrl, $loggingService);
        }

        if ($this->option('test')) {
            $this->testBot($apiUrl, $recipients, $loggingService);
        } else {
            $this->showInstructions();
        }

        return 0;
    }

    /**
     * Validate bot token
     */
    private function validateToken(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $this->info('🔍 Validating bot token...');

        try {
            $response = Http::get("{$apiUrl}/getMe");

            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Bot validated successfully");
                $this->info("Bot Name: {$data['result']['first_name']}");
                $this->info("Bot Username: @{$data['result']['username']}");
                $this->info("Bot ID: {$data['result']['id']}");
                $this->info('');

                // Log successful validation
                $loggingService->logTelegramEvent('bot_validated', [
                    'bot_name' => $data['result']['first_name'],
                    'bot_username' => $data['result']['username'],
                    'bot_id' => $data['result']['id']
                ], 'info', [
                    'command' => 'telegram:bot-setup',
                    'operation' => 'validate_token'
                ]);
            } else {
                $this->error("❌ Invalid bot token");
                $this->error("Error: " . ($response->json()['description'] ?? 'Unknown error'));

                // Log validation error
                $loggingService->logTelegramEvent('bot_validation_failed', [
                    'error' => $response->json()['description'] ?? 'Unknown error'
                ], 'error', [
                    'command' => 'telegram:bot-setup',
                    'operation' => 'validate_token'
                ]);

                exit(1);
            }

        } catch (\Exception $e) {
            $this->error("❌ Error validating token: " . $e->getMessage());
            exit(1);
        }
    }

    /**
     * Set webhook
     */
    private function setWebhook(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $webhookUrl = $this->option('webhook-url');

        if (!$webhookUrl) {
            $webhookUrl = $this->ask('Enter webhook URL (e.g., https://api-hom.virtualt.com.br/api/telegram/webhook)');
        }

        if (!$webhookUrl) {
            $this->error('❌ Webhook URL is required');
            return;
        }

        $this->info("🔗 Setting webhook to: {$webhookUrl}");

        try {
            $response = Http::post("{$apiUrl}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'channel_post',
                    'edited_message',
                    'edited_channel_post',
                    'inline_query',
                    'chosen_inline_result',
                    'shipping_query',
                    'pre_checkout_query',
                    'poll',
                    'poll_answer',
                    'my_chat_member',
                    'chat_member',
                    'chat_join_request'
                ],
                'drop_pending_updates' => true,
                'secret_token' => config('services.telegram.webhook_secret', ''),
                'max_connections' => 40
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok']) {
                    $this->info("✅ Webhook set successfully");

                    // Verificar se há informações adicionais na resposta
                    if (isset($data['result']) && is_array($data['result'])) {
                        if (isset($data['result']['url'])) {
                            $this->info("Webhook URL: {$data['result']['url']}");
                        }
                        if (isset($data['result']['pending_update_count'])) {
                            $this->info("Pending updates: {$data['result']['pending_update_count']}");
                        }
                    }

                                        // Log successful webhook setup
                    $loggingService->logTelegramEvent('webhook_set', [
                        'webhook_url' => $webhookUrl,
                        'result' => $data['result'] ?? []
                    ], 'info', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'set_webhook'
                    ]);
                } else {
                    $this->error("❌ Failed to set webhook");
                    $this->error("Error: " . ($data['description'] ?? 'Unknown error'));

                    // Log webhook setup error
                    $loggingService->logTelegramEvent('webhook_set_failed', [
                        'webhook_url' => $webhookUrl,
                        'error' => $data['description'] ?? 'Unknown error'
                    ], 'error', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'set_webhook'
                    ]);
                }
            } else {
                $this->error("❌ HTTP error: " . $response->status());
                $this->error("Response: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error setting webhook: " . $e->getMessage());
        }
    }

    /**
     * Delete webhook
     */
    private function deleteWebhook(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $this->info('🗑️ Deleting webhook...');

        try {
            $response = Http::post("{$apiUrl}/deleteWebhook");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok']) {
                    $this->info("✅ Webhook deleted successfully");

                                        // Log successful webhook deletion
                    $loggingService->logTelegramEvent('webhook_deleted', [
                        'result' => $data['result'] ?? []
                    ], 'info', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'delete_webhook'
                    ]);
                } else {
                    $this->error("❌ Failed to delete webhook");
                    $this->error("Error: " . ($data['description'] ?? 'Unknown error'));

                    // Log webhook deletion error
                    $loggingService->logTelegramEvent('webhook_delete_failed', [
                        'error' => $data['description'] ?? 'Unknown error'
                    ], 'error', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'delete_webhook'
                    ]);
                }
            } else {
                $this->error("❌ HTTP error: " . $response->status());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error deleting webhook: " . $e->getMessage());
        }
    }

    /**
     * Get webhook info
     */
    private function getWebhookInfo(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $this->info('📋 Getting webhook info...');

        try {
            $response = Http::get("{$apiUrl}/getWebhookInfo");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok']) {
                    $webhookInfo = $data['result'];

                    // Debug: Log the complete webhook info for troubleshooting
                    $loggingService->logTelegramEvent('webhook_info_retrieved', [
                        'webhook_info' => $webhookInfo
                    ], 'info', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'get_webhook_info'
                    ]);

                    $this->info("✅ Webhook info retrieved");
                    $this->info("URL: " . ($webhookInfo['url'] ?? 'Not set'));
                    $this->info("Pending updates: " . ($webhookInfo['pending_update_count'] ?? '0'));
                    $this->info("Last error date: " . ($webhookInfo['last_error_date'] ?? 'None'));
                    $this->info("Last error message: " . ($webhookInfo['last_error_message'] ?? 'None'));
                    $this->info("Max connections: " . ($webhookInfo['max_connections'] ?? 'Not set'));
                } else {
                    $this->error("❌ Failed to get webhook info");
                    $this->error("Error: " . ($data['description'] ?? 'Unknown error'));
                }
            } else {
                $this->error("❌ HTTP error: " . $response->status());
            }

                } catch (\Exception $e) {
            $this->error("❌ Error getting webhook info: " . $e->getMessage());

                            // Log additional debug information
                $loggingService->logTelegramEvent('webhook_info_error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error', [
                    'command' => 'telegram:bot-setup',
                    'operation' => 'get_webhook_info'
                ]);
        }
    }

    /**
     * Test bot functionality
     */
    private function testBot(string $apiUrl, array $recipients, LoggingServiceInterface $loggingService): void
    {
        if (empty($recipients)) {
            $this->error('❌ No recipients configured');
            $this->info('Please set TELEGRAM_RECIPIENTS in your .env file');
            return;
        }

        $this->info('🧪 Testing bot functionality...');

        $testMessage = "🧪 *Teste do Bot*\n\n" .
                      "Este é um teste do bot de relatórios do Rei do Óleo.\n" .
                      "Se você recebeu esta mensagem, o bot está funcionando!\n\n" .
                      "Use `/help` para ver os comandos disponíveis.\n\n" .
                      "⏰ Teste realizado em: " . now()->format('d/m/Y H:i:s');

        $results = [];
        foreach ($recipients as $recipient) {
            $this->info("📤 Sending test message to: {$recipient}");

            try {
                $response = Http::post("{$apiUrl}/sendMessage", [
                    'chat_id' => $recipient,
                    'text' => $testMessage,
                    'parse_mode' => 'Markdown'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['ok']) {
                        $this->info("✅ Message sent successfully to {$recipient}");
                        $results[$recipient] = ['success' => true];

                                                // Log successful message send
                        $loggingService->logTelegramEvent('test_message_sent', [
                            'recipient' => $recipient,
                            'message_id' => $data['result']['message_id'] ?? null
                        ], 'info', [
                            'command' => 'telegram:bot-setup',
                            'operation' => 'test_bot'
                        ]);
                    } else {
                        $this->error("❌ Failed to send message to {$recipient}");
                        $this->error("Error: " . ($data['description'] ?? 'Unknown error'));
                        $results[$recipient] = ['success' => false, 'error' => $data['description'] ?? 'Unknown error'];

                        // Log failed message send
                        $loggingService->logTelegramEvent('test_message_failed', [
                            'recipient' => $recipient,
                            'error' => $data['description'] ?? 'Unknown error'
                        ], 'error', [
                            'command' => 'telegram:bot-setup',
                            'operation' => 'test_bot'
                        ]);
                    }
                } else {
                    $this->error("❌ HTTP error sending to {$recipient}: " . $response->status());
                    $results[$recipient] = ['success' => false, 'error' => 'HTTP error'];
                }

            } catch (\Exception $e) {
                $this->error("❌ Exception sending to {$recipient}: " . $e->getMessage());
                $results[$recipient] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $this->info('');
        $this->info("📊 Test Results:");
        $this->info("✅ Successful: {$successCount}");
        $this->info("❌ Failed: " . (count($results) - $successCount));
        $this->info("📋 Total: " . count($results));
    }

    /**
     * Set webhook with audio support
     */
    private function setWebhookWithAudioSupport(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $webhookUrl = $this->option('webhook-url');

        if (!$webhookUrl) {
            $webhookUrl = $this->ask('Enter webhook URL (e.g., https://api-hom.virtualt.com.br/api/telegram/webhook)');
        }

        if (!$webhookUrl) {
            $this->error('❌ Webhook URL is required');
            return;
        }

        $this->info("🔗 Setting webhook with audio support to: {$webhookUrl}");

        try {
            // First, delete existing webhook
            $this->info("🗑️ Deleting existing webhook...");
            $deleteResponse = Http::post("{$apiUrl}/deleteWebhook");

            if ($deleteResponse->successful()) {
                $this->info("✅ Existing webhook deleted");
            }

            // Set new webhook with audio support
            $response = Http::post("{$apiUrl}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'channel_post',
                    'edited_message',
                    'edited_channel_post',
                    'inline_query',
                    'chosen_inline_result',
                    'shipping_query',
                    'pre_checkout_query',
                    'poll',
                    'poll_answer',
                    'my_chat_member',
                    'chat_member',
                    'chat_join_request'
                ],
                'drop_pending_updates' => true,
                'secret_token' => config('services.telegram.webhook_secret', ''),
                'max_connections' => 40
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok']) {
                    $this->info("✅ Webhook with audio support set successfully");

                    // Verificar se há informações adicionais na resposta
                    if (isset($data['result']) && is_array($data['result'])) {
                        if (isset($data['result']['url'])) {
                            $this->info("Webhook URL: {$data['result']['url']}");
                        }
                        if (isset($data['result']['pending_update_count'])) {
                            $this->info("Pending updates: {$data['result']['pending_update_count']}");
                        }
                        if (isset($data['result']['allowed_updates'])) {
                            $this->info("Allowed updates: " . implode(', ', $data['result']['allowed_updates']));
                        }
                    }

                    // Log successful webhook setup
                    $loggingService->logTelegramEvent('webhook_set_with_audio_support', [
                        'webhook_url' => $webhookUrl,
                        'result' => $data['result'] ?? []
                    ], 'info', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'set_webhook_with_audio'
                    ]);

                    // Test webhook with different message types
                    $this->info("🧪 Testing webhook with different message types...");
                    $this->testWebhookMessageTypes($apiUrl, $loggingService);

                } else {
                    $this->error("❌ Failed to set webhook with audio support");
                    $this->error("Error: " . ($data['description'] ?? 'Unknown error'));

                    // Log webhook setup error
                    $loggingService->logTelegramEvent('webhook_set_with_audio_failed', [
                        'webhook_url' => $webhookUrl,
                        'error' => $data['description'] ?? 'Unknown error'
                    ], 'error', [
                        'command' => 'telegram:bot-setup',
                        'operation' => 'set_webhook_with_audio'
                    ]);
                }
            } else {
                $this->error("❌ HTTP error: " . $response->status());
                $this->error("Response: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error setting webhook with audio support: " . $e->getMessage());
        }
    }

    /**
     * Test webhook with different message types
     */
    private function testWebhookMessageTypes(string $apiUrl, LoggingServiceInterface $loggingService): void
    {
        $this->info("📝 Testing text message support...");

        // Get bot info to test basic functionality
        try {
            $botInfoResponse = Http::get("{$apiUrl}/getMe");
            if ($botInfoResponse->successful()) {
                $botInfo = $botInfoResponse->json();
                if ($botInfo['ok']) {
                    $this->info("✅ Bot info retrieved successfully");
                    $this->info("Bot: @{$botInfo['result']['username']} ({$botInfo['result']['first_name']})");
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Could not retrieve bot info: " . $e->getMessage());
        }

        // Test webhook info
        try {
            $webhookInfoResponse = Http::get("{$apiUrl}/getWebhookInfo");
            if ($webhookInfoResponse->successful()) {
                $webhookInfo = $webhookInfoResponse->json();
                if ($webhookInfo['ok']) {
                    $this->info("✅ Webhook info retrieved successfully");
                    $result = $webhookInfo['result'];

                    if (isset($result['url'])) {
                        $this->info("Webhook URL: {$result['url']}");
                    }
                    if (isset($result['allowed_updates']) && is_array($result['allowed_updates'])) {
                        $this->info("Allowed updates: " . implode(', ', $result['allowed_updates']));
                    }
                    if (isset($result['max_connections'])) {
                        $this->info("Max connections: {$result['max_connections']}");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Could not retrieve webhook info: " . $e->getMessage());
        }
    }

    /**
     * Show instructions
     */
    private function showInstructions(): void
    {
        $this->info('📖 How to use this command:');
        $this->info('');
        $this->info('1. Set webhook:');
        $this->info('   php artisan telegram:bot-setup --set-webhook --webhook-url=https://api-hom.virtualt.com.br/api/telegram/webhook');
        $this->info('');
        $this->info('2. Get webhook info:');
        $this->info('   php artisan telegram:bot-setup --webhook-info');
        $this->info('');
        $this->info('3. Delete webhook:');
        $this->info('   php artisan telegram:bot-setup --delete-webhook');
        $this->info('');
        $this->info('4. Test bot:');
        $this->info('   php artisan telegram:bot-setup --test');
        $this->info('');
        $this->info('🔧 Configuration required:');
        $this->info('• TELEGRAM_BOT_TOKEN in .env file');
        $this->info('• TELEGRAM_RECIPIENTS in .env file (comma-separated chat IDs)');
        $this->info('');
        $this->info('💡 To get chat IDs:');
        $this->info('1. Start a conversation with your bot');
        $this->info('2. Send a message to the bot');
        $this->info('3. Run: php artisan telegram:debug --get-updates');
        $this->info('4. Copy the chat_id and update your .env file');
        $this->info('');
    }
}
