<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramDebugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:debug
                            {--get-updates : Get recent updates from bot}
                            {--send-test : Send test message to configured recipients}
                            {--validate-token : Validate bot token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Telegram bot configuration and get chat IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botToken = config('services.telegram.bot_token');
        $recipients = config('services.telegram.recipients', []);
        $apiUrl = "https://api.telegram.org/bot{$botToken}";

        $this->info('🤖 Telegram Bot Debug');
        $this->info('==================');
        $this->info("Bot Token: " . substr($botToken, 0, 10) . '...');
        $this->info("Recipients: " . implode(', ', $recipients));
        $this->info('');

        // Validate token
        if ($this->option('validate-token') || $this->option('get-updates') || $this->option('send-test')) {
            $this->validateToken($apiUrl);
        }

        // Get updates
        if ($this->option('get-updates')) {
            $this->getUpdates($apiUrl);
        }

        // Send test message
        if ($this->option('send-test')) {
            $this->sendTestMessage($apiUrl, $recipients);
        }

        // Show instructions
        if (!$this->option('get-updates') && !$this->option('send-test') && !$this->option('validate-token')) {
            $this->showInstructions();
        }
    }

    /**
     * Validate bot token
     */
    private function validateToken(string $apiUrl): void
    {
        $this->info('🔍 Validating bot token...');

        try {
            $response = Http::get("{$apiUrl}/getMe");

            if ($response->successful()) {
                $data = $response->json();
                $bot = $data['result'];

                $this->info('✅ Bot token is valid!');
                $this->info("Bot Name: {$bot['first_name']}");
                $this->info("Bot Username: @{$bot['username']}");
                $this->info("Bot ID: {$bot['id']}");
                $this->info('');
            } else {
                $this->error('❌ Bot token is invalid!');
                $this->error("Error: " . ($response->json()['description'] ?? 'Unknown error'));
                $this->error("Status: " . $response->status());
                $this->info('');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error validating token: ' . $e->getMessage());
            $this->info('');
        }
    }

    /**
     * Get recent updates from bot
     */
    private function getUpdates(string $apiUrl): void
    {
        $this->info('📥 Getting recent updates...');

        try {
            $response = Http::get("{$apiUrl}/getUpdates");

            if ($response->successful()) {
                $data = $response->json();
                $updates = $data['result'] ?? [];

                if (empty($updates)) {
                    $this->warn('⚠️  No recent updates found.');
                    $this->info('To get chat IDs, you need to:');
                    $this->info('1. Start a conversation with your bot (@your_bot_username)');
                    $this->info('2. Send a message to the bot');
                    $this->info('3. Run this command again');
                    $this->info('');
                } else {
                    $this->info('📋 Recent updates:');
                    $this->info('');

                    foreach ($updates as $update) {
                        if (isset($update['message'])) {
                            $message = $update['message'];
                            $chat = $message['chat'];
                            $from = $message['from'];

                            $username = $from['username'] ?? 'no_username';
                            $this->info("📨 Message from: {$from['first_name']} (@{$username})");
                            $this->info("💬 Chat ID: {$chat['id']}");
                            $this->info("📝 Chat Type: {$chat['type']}");
                            $this->info("💭 Text: " . ($message['text'] ?? 'No text'));
                            $this->info('---');
                        }
                    }

                    $this->info('');
                    $this->info('💡 To use these chat IDs, update your .env file:');
                    $this->info('TELEGRAM_RECIPIENTS=CHAT_ID_1,CHAT_ID_2,CHAT_ID_3');
                    $this->info('');
                }
            } else {
                $this->error('❌ Error getting updates: ' . ($response->json()['description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error('❌ Error getting updates: ' . $e->getMessage());
        }
    }

    /**
     * Send test message to configured recipients
     */
    private function sendTestMessage(string $apiUrl, array $recipients): void
    {
        if (empty($recipients)) {
            $this->error('❌ No recipients configured!');
            return;
        }

        $this->info('📤 Sending test message...');
        $testMessage = "🧪 *Test Message*\n\nThis is a test message from your Telegram bot.\n\nTimestamp: " . now()->format('d/m/Y H:i:s');

        foreach ($recipients as $recipient) {
            $this->info("📱 Sending to: {$recipient}");

            try {
                $response = Http::post("{$apiUrl}/sendMessage", [
                    'chat_id' => $recipient,
                    'text' => $testMessage,
                    'parse_mode' => 'Markdown'
                ]);

                if ($response->successful()) {
                    $this->info("✅ Message sent successfully to {$recipient}");
                } else {
                    $error = $response->json()['description'] ?? 'Unknown error';
                    $this->error("❌ Failed to send to {$recipient}: {$error}");

                    if (str_contains($error, 'chat not found')) {
                        $this->warn("💡 This usually means the chat_id is incorrect.");
                        $this->warn("   Make sure to use the chat_id from getUpdates, not a phone number.");
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ Error sending to {$recipient}: " . $e->getMessage());
            }
        }

        $this->info('');
    }

    /**
     * Show instructions
     */
    private function showInstructions(): void
    {
        $this->info('📖 How to use this command:');
        $this->info('');
        $this->info('1. Validate bot token:');
        $this->info('   php artisan telegram:debug --validate-token');
        $this->info('');
        $this->info('2. Get chat IDs (after starting conversation with bot):');
        $this->info('   php artisan telegram:debug --get-updates');
        $this->info('');
        $this->info('3. Send test message:');
        $this->info('   php artisan telegram:debug --send-test');
        $this->info('');
        $this->info('4. All operations:');
        $this->info('   php artisan telegram:debug --validate-token --get-updates --send-test');
        $this->info('');
        $this->info('🔧 To get chat IDs:');
        $this->info('1. Start a conversation with your bot (@your_bot_username)');
        $this->info('2. Send any message to the bot');
        $this->info('3. Run: php artisan telegram:debug --get-updates');
        $this->info('4. Copy the chat_id and update your .env file');
        $this->info('');
    }
}
