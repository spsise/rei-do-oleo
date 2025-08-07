<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramMessageProcessorService;

class TestTelegramBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-bot {message=Menu} {chat_id=7024642701}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram bot with a simulated message';

    /**
     * Execute the console command.
     */
    public function handle(TelegramMessageProcessorService $messageProcessor)
    {
        $message = $this->argument('message');
        $chatId = (int) $this->argument('chat_id');

        $this->info("🤖 Testing Telegram Bot");
        $this->info("====================");
        $this->info("Message: {$message}");
        $this->info("Chat ID: {$chatId}");
        $this->info("");

        // Simulate webhook payload
        $payload = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => $chatId,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => $chatId,
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => $message
            ]
        ];

        $this->info("📤 Processing message...");

        try {
            $result = $messageProcessor->processWebhookPayload($payload);

            $this->info("📥 Result:");
            $this->info("Status: " . ($result['status'] ?? 'unknown'));
            $this->info("Success: " . ($result['success'] ? 'true' : 'false'));
            $this->info("Message: " . ($result['message'] ?? 'No message'));

            if (isset($result['error'])) {
                $this->error("Error: " . $result['error']);
            }

            if (isset($result['data'])) {
                $this->info("Data: " . json_encode($result['data'], JSON_PRETTY_PRINT));
            }

            // Show full result for debugging
            $this->info("Full Result: " . json_encode($result, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
