<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use App\Services\TelegramMessageProcessorService;
use Illuminate\Console\Command;

class TestSimpleWebhookCommand extends Command
{
    protected $signature = 'message-flow:test-simple';
    protected $description = 'Test simple webhook flow without timeout';

    public function handle(): int
    {
        $this->info('🧪 Testing Simple Webhook Flow...');

        try {
            $flowTracker = app(MessageFlowTrackerInterface::class);
            $messageProcessor = app(TelegramMessageProcessorService::class);

            $this->info('1. 🚀 Starting tracking...');
            $flowTracker->startTracking('simple_test_' . uniqid());

            $this->info('2. 📝 Testing processWebhookPayload...');

            $payload = [
                'update_id' => 123456789,
                'message' => [
                    'message_id' => 987,
                    'from' => [
                        'id' => 12345,
                        'first_name' => 'Test',
                        'username' => 'testuser'
                    ],
                    'chat' => [
                        'id' => 12345,
                        'type' => 'private'
                    ],
                    'text' => 'Menu'
                ]
            ];

            $this->line('   📝 Calling processWebhookPayload...');
            $result = $messageProcessor->processWebhookPayload($payload);
            $this->line('   ✅ processWebhookPayload completed');

            $this->info('3. 📊 Generating reports...');

            $flowReport = $flowTracker->generateFlowReport($payload);
            $userReport = $flowTracker->generateUserReport($payload);

            $this->info('4. 📋 Flow Report:');
            $this->line($flowReport);

            $this->info('5. 👤 User Report:');
            $this->line($userReport);

            $flowTracker->endTracking();

            $this->info('✅ Simple webhook test completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Simple webhook test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
