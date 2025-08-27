<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use App\Services\TelegramMessageProcessorService;
use Illuminate\Console\Command;

class TestWebhookFlowCommand extends Command
{
    protected $signature = 'message-flow:test-webhook';
    protected $description = 'Test the webhook flow exactly as it happens in production';

    public function handle(): int
    {
        $this->info('🧪 Testing Webhook Flow (Production Simulation)...');

        try {
            // 1. Simular exatamente o que acontece no controller
            $this->info('1. 📥 Simulating webhook controller...');

            $flowTracker = app(MessageFlowTrackerInterface::class);
            $messageProcessor = app(TelegramMessageProcessorService::class);

            // 2. Iniciar tracking como no controller
            $this->info('2. 🚀 Starting tracking (like in controller)...');
            $flowTracker->startTracking(uniqid('webhook_', true));

            // 3. Simular o processWithTimeout
            $this->info('3. ⏱️ Simulating processWithTimeout...');

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

            // Simular o callback do processWithTimeout
            $result = $this->simulateProcessWithTimeout(function () use ($messageProcessor, $payload) {
                $this->info('   📝 Inside processWithTimeout callback...');
                $this->info('   📝 Calling processWebhookPayload...');

                // Aqui é onde o tracking deveria acontecer
                $result = $messageProcessor->processWebhookPayload($payload);

                $this->info('   ✅ processWebhookPayload completed');
                return $result;
            }, 25);

            $this->info('4. 📊 Generating reports...');

            // Gerar relatórios como no controller
            $flowReport = $flowTracker->generateFlowReport($payload);
            $userReport = $flowTracker->generateUserReport($payload);

            $this->info('5. 📋 Flow Report:');
            $this->line($flowReport);

            $this->info('6. 👤 User Report:');
            $this->line($userReport);

            // Finalizar tracking
            $flowTracker->endTracking();

            $this->info('✅ Webhook flow test completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Webhook flow test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Simula exatamente o processWithTimeout do controller
     */
    private function simulateProcessWithTimeout(callable $callback, int $timeoutSeconds): array
    {
        $result = null;
        $exception = null;

        try {
            $result = $callback();
        } catch (\Exception $e) {
            $exception = $e;
        }

        if ($exception) {
            throw $exception;
        }

        // Ensure we always return an array
        if (!is_array($result)) {
            return [
                'success' => false,
                'type' => 'invalid_result',
                'message' => 'Invalid result from message processor',
                'data' => []
            ];
        }

        return $result;
    }
}
