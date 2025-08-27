<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use App\Services\TelegramMessageProcessorService;
use App\Services\TelegramBotService;
use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Channels\TelegramChannel;
use App\Services\SpeechToTextService;
use Illuminate\Console\Command;

class TestCompleteMessageFlowCommand extends Command
{
    protected $signature = 'message-flow:test-complete {--type=text : Message type (text, voice, callback_query)}';
    protected $description = 'Test the complete message flow tracking system';

    public function handle(): int
    {
        $this->info('🧪 Testing Complete Message Flow Tracking System...');

        $messageType = $this->option('type');

        // Test payload baseado no tipo
        $payload = $this->createTestPayload($messageType);

        $this->info("📝 Testing with {$messageType} message type");
        $this->line("Payload: " . json_encode($payload, JSON_PRETTY_PRINT));

        // Inicia tracking
        $flowTracker = app(MessageFlowTrackerInterface::class);
        $flowTracker->startTracking('test_complete_' . uniqid());

        try {
            // Simula o fluxo completo
            $this->info('🔄 Simulating complete message flow...');

            // 1. ProcessWebhookPayload
            $this->line('1. 📥 Processing webhook payload...');
            $flowTracker->trackMethod('TelegramMessageProcessorService', 'processWebhookPayload', ['payload' => $payload]);

            // 2. ProcessTextMessage (se for texto)
            if ($messageType === 'text') {
                $this->line('2. 📝 Processing text message...');
                $flowTracker->trackMethod('TelegramMessageProcessorService', 'processTextMessage', ['message' => $payload['message']]);
            }

            // 3. ProcessMessage (TelegramBotService)
            $this->line('3. 🤖 Processing message in bot service...');
            $flowTracker->trackMethod('TelegramBotService', 'processMessage', ['message' => $payload['message']]);

            // 4. ParseCommand (se aplicável)
            if ($messageType === 'text') {
                $this->line('4. 🔍 Parsing command...');
                $flowTracker->trackMethod('TelegramCommandParser', 'parseCommand', ['text' => $payload['message']['text']]);
            }

            // 5. HandleCommand (se aplicável)
            if ($messageType === 'text') {
                $this->line('5. ⚙️ Handling command...');
                $flowTracker->trackMethod('TelegramCommandHandlerManager', 'handleCommand', [
                    'command' => 'start',
                    'chat_id' => $payload['message']['chat']['id'],
                    'params' => []
                ]);
            }

            // 6. SendTextMessage
            $this->line('6. 📤 Sending text message...');
            $flowTracker->trackMethod('TelegramChannel', 'sendTextMessage', [
                'message' => 'Test response',
                'recipient' => (string) $payload['message']['chat']['id']
            ]);

            // Finaliza métodos
            $this->info('✅ Finalizing method tracking...');
            $flowTracker->endMethod('TelegramMessageProcessorService', 'processWebhookPayload', ['success' => true]);
            $flowTracker->endMethod('TelegramBotService', 'processMessage', ['success' => true]);
            $flowTracker->endMethod('TelegramChannel', 'sendTextMessage', ['success' => true]);

            if ($messageType === 'text') {
                $flowTracker->endMethod('TelegramMessageProcessorService', 'processTextMessage', ['success' => true]);
                $flowTracker->endMethod('TelegramCommandParser', 'parseCommand', ['success' => true]);
                $flowTracker->endMethod('TelegramCommandHandlerManager', 'handleCommand', ['success' => true]);
            }

            // Gera relatórios
            $this->info('📊 Generating reports...');

            $flowReport = $flowTracker->generateFlowReport($payload);
            $userReport = $flowTracker->generateUserReport($payload);

            $this->info('📋 Complete Flow Report:');
            $this->line($flowReport);

            $this->info('👤 User Report:');
            $this->line($userReport);

            // Finaliza o tracking
            $flowTracker->endTracking();

            $this->info('✅ Complete Message Flow Tracking test completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during testing: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    private function createTestPayload(string $type): array
    {
        $basePayload = [
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
                ]
            ]
        ];

        switch ($type) {
            case 'text':
                $basePayload['message']['text'] = '/start';
                break;

            case 'voice':
                $basePayload['message']['voice'] = [
                    'file_id' => 'test_voice_file',
                    'duration' => 10,
                    'mime_type' => 'audio/ogg'
                ];
                break;

            case 'callback_query':
                $basePayload['callback_query'] = [
                    'id' => 'test_callback_id',
                    'from' => [
                        'id' => 12345,
                        'first_name' => 'Test',
                        'username' => 'testuser'
                    ],
                    'message' => [
                        'message_id' => 987,
                        'chat' => [
                            'id' => 12345,
                            'type' => 'private'
                        ]
                    ],
                    'data' => 'test_callback_data'
                ];
                unset($basePayload['message']);
                break;

            default:
                $basePayload['message']['text'] = 'Test message';
        }

        return $basePayload;
    }
}
