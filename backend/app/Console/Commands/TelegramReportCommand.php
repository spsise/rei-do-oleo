<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:report
                            {type=general : Type of report (general, services, products, dashboard)}
                            {--period=today : Period (today, week, month)}
                            {--chat-id= : Specific chat ID to send to}
                            {--test : Test mode (simulate message)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send reports via Telegram bot';

    private TelegramBotService $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        parent::__construct();
        $this->telegramBotService = $telegramBotService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $period = $this->option('period');
        $chatId = $this->option('chat-id');
        $isTest = $this->option('test');

        $this->info('📊 Telegram Report Generator');
        $this->info('==========================');
        $this->info("Type: {$type}");
        $this->info("Period: {$period}");
        $this->info("Chat ID: " . ($chatId ?: 'All recipients'));
        $this->info("Test Mode: " . ($isTest ? 'Yes' : 'No'));
        $this->info('');

        if ($isTest) {
            $this->runTestMode($type, $period, $chatId);
        } else {
            $this->runProductionMode($type, $period, $chatId);
        }

        return 0;
    }

    /**
     * Run in test mode (simulate message)
     */
    private function runTestMode(string $type, string $period, ?string $chatId): void
    {
        $this->info('🧪 Running in TEST mode...');

        // Simulate message processing
        $message = [
            'chat' => ['id' => $chatId ?: '123456789'],
            'text' => $this->generateTestCommand($type, $period),
            'from' => [
                'id' => $chatId ?: '123456789',
                'first_name' => 'Test User',
                'username' => 'testuser'
            ]
        ];

        $this->info("📝 Simulating command: {$message['text']}");
        $this->info('');

        try {
            $result = $this->telegramBotService->processMessage($message);

            if ($result['success']) {
                $this->info('✅ Report generated successfully');
                $this->info("Sent to: {$result['sent_to']} recipients");
                $this->info("Total recipients: {$result['total_recipients']}");
            } else {
                $this->error('❌ Failed to generate report');
                $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
        }
    }

    /**
     * Run in production mode (send actual messages)
     */
    private function runProductionMode(string $type, string $period, ?string $chatId): void
    {
        $this->info('🚀 Running in PRODUCTION mode...');

        if ($chatId) {
            $this->sendToSpecificChat($type, $period, $chatId);
        } else {
            $this->sendToAllRecipients($type, $period);
        }
    }

    /**
     * Send report to specific chat
     */
    private function sendToSpecificChat(string $type, string $period, string $chatId): void
    {
        $this->info("📤 Sending {$type} report to chat ID: {$chatId}");

        $message = [
            'chat' => ['id' => $chatId],
            'text' => $this->generateTestCommand($type, $period),
            'from' => [
                'id' => $chatId,
                'first_name' => 'CLI User',
                'username' => 'cli'
            ]
        ];

        try {
            $result = $this->telegramBotService->processMessage($message);

            if ($result['success']) {
                $this->info('✅ Report sent successfully');
            } else {
                $this->error('❌ Failed to send report');
                $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
        }
    }

    /**
     * Send report to all configured recipients
     */
    private function sendToAllRecipients(string $type, string $period): void
    {
        $recipients = config('services.telegram.recipients', []);

        if (empty($recipients)) {
            $this->error('❌ No recipients configured');
            $this->info('Please set TELEGRAM_RECIPIENTS in your .env file');
            return;
        }

        $this->info("📤 Sending {$type} report to " . count($recipients) . " recipients");

        $results = [];
        foreach ($recipients as $recipient) {
            $this->info("📤 Sending to: {$recipient}");

            $message = [
                'chat' => ['id' => $recipient],
                'text' => $this->generateTestCommand($type, $period),
                'from' => [
                    'id' => $recipient,
                    'first_name' => 'CLI User',
                    'username' => 'cli'
                ]
            ];

            try {
                $result = $this->telegramBotService->processMessage($message);
                $results[$recipient] = $result;

                if ($result['success']) {
                    $this->info("✅ Sent successfully to {$recipient}");
                } else {
                    $this->error("❌ Failed to send to {$recipient}");
                    $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
                }

            } catch (\Exception $e) {
                $this->error("❌ Exception sending to {$recipient}: " . $e->getMessage());
                $results[$recipient] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $this->info('');
        $this->info("📊 Results Summary:");
        $this->info("✅ Successful: {$successCount}");
        $this->info("❌ Failed: " . (count($results) - $successCount));
        $this->info("📋 Total: " . count($results));
    }

    /**
     * Generate test command based on type and period
     */
    private function generateTestCommand(string $type, string $period): string
    {
        return match($type) {
            'general' => "/report {$period}",
            'services' => "/services {$period}",
            'products' => "/products",
            'dashboard' => "/dashboard",
            default => "/report {$period}"
        };
    }
}
