<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use Illuminate\Console\Command;

class TestMessageFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message-flow:test {--payload= : JSON payload to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the message flow tracking system';

    /**
     * Execute the console command.
     */
    public function handle(MessageFlowTrackerInterface $flowTracker): int
    {
        $this->info('🧪 Testing Message Flow Tracking System...');

        // Test payload padrão
        $payload = $this->option('payload') ? json_decode($this->option('payload'), true) : [
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
                'text' => 'Test message'
            ]
        ];

        $this->info('📝 Test Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        // Inicia tracking
        $flowTracker->startTracking('test_' . uniqid());

        // Simula execução de métodos
        $this->info('🔄 Simulating method execution...');

        $flowTracker->trackMethod('TelegramMessageProcessorService', 'processWebhookPayload', ['payload' => $payload]);
        $this->line('✅ processWebhookPayload executed');

        $flowTracker->trackMethod('TelegramMessageProcessorService', 'processTextMessage', ['message' => $payload['message']]);
        $this->line('✅ processTextMessage executed');

        $flowTracker->trackMethod('TelegramBotService', 'processMessage', ['message' => $payload['message']]);
        $this->line('✅ processMessage executed');

        // Finaliza métodos
        $flowTracker->endMethod('TelegramMessageProcessorService', 'processWebhookPayload', ['success' => true]);
        $flowTracker->endMethod('TelegramMessageProcessorService', 'processTextMessage', ['success' => true]);
        $flowTracker->endMethod('TelegramBotService', 'processMessage', ['success' => true]);

        // Gera relatórios
        $this->info('📊 Generating reports...');

        $flowReport = $flowTracker->generateFlowReport($payload);
        $userReport = $flowTracker->generateUserReport($payload);

        $this->info('📋 Flow Report:');
        $this->line($flowReport);

        $this->info('👤 User Report:');
        $this->line($userReport);

        // Finaliza tracking
        $flowTracker->endTracking();

        $this->info('✅ Message Flow Tracking test completed successfully!');

        return Command::SUCCESS;
    }
}
