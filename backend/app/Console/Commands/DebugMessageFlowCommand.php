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
use Illuminate\Container\Container;

class DebugMessageFlowCommand extends Command
{
    protected $signature = 'message-flow:debug';
    protected $description = 'Debug the message flow tracking system to identify issues';

    public function handle(): int
    {
        $this->info('🔍 Debugging Message Flow Tracking System...');

        try {
            // 1. Verificar se o container está funcionando
            $this->info('1. 🔧 Checking Laravel Container...');
            $container = app();
            $this->line('✅ Container: ' . get_class($container));

            // 2. Verificar se o MessageFlowTrackerInterface está resolvendo
            $this->info('2. 📡 Checking MessageFlowTrackerInterface resolution...');
            $flowTracker = app(MessageFlowTrackerInterface::class);
            $this->line('✅ FlowTracker: ' . get_class($flowTracker));
            $this->line('✅ Enabled: ' . ($flowTracker->isEnabled() ? 'YES' : 'NO'));

            // 3. Verificar se as classes estão sendo resolvidas
            $this->info('3. 🏗️ Checking service resolution...');

            $this->line('📝 Checking TelegramMessageProcessorService...');
            try {
                $messageProcessor = app(TelegramMessageProcessorService::class);
                $this->line('✅ TelegramMessageProcessorService: ' . get_class($messageProcessor));

                // Verificar se implementa a interface
                $reflection = new \ReflectionClass($messageProcessor);
                $interfaces = $reflection->getInterfaceNames();
                $this->line('✅ Interfaces: ' . implode(', ', $interfaces));

                // Verificar se tem o método trackMethod
                if (method_exists($messageProcessor, 'trackMethod')) {
                    $this->line('✅ trackMethod method exists');
                } else {
                    $this->error('❌ trackMethod method NOT found!');
                }

            } catch (\Exception $e) {
                $this->error('❌ Error resolving TelegramMessageProcessorService: ' . $e->getMessage());
            }

            $this->line('🤖 Checking TelegramBotService...');
            try {
                $botService = app(TelegramBotService::class);
                $this->line('✅ TelegramBotService: ' . get_class($botService));

                if (method_exists($botService, 'trackMethod')) {
                    $this->line('✅ trackMethod method exists');
                } else {
                    $this->error('❌ trackMethod method NOT found!');
                }

            } catch (\Exception $e) {
                $this->error('❌ Error resolving TelegramBotService: ' . $e->getMessage());
            }

            // 4. Testar tracking manualmente
            $this->info('4. 🧪 Testing manual tracking...');

            $flowTracker->startTracking('debug_test_' . uniqid());

            // Simular tracking de métodos
            $this->line('📝 Testing trackMethod...');
            $flowTracker->trackMethodInternal('TestClass', 'testMethod', ['test' => 'data']);

            $this->line('📝 Testing endMethod...');
            $flowTracker->endMethodInternal('TestClass', 'testMethod', ['result' => 'success']);

            $executedMethods = $flowTracker->getExecutedMethods();
            $this->line('✅ Executed methods: ' . count($executedMethods));

            foreach ($executedMethods as $method) {
                $this->line('   • ' . $method);
            }

            $flowTracker->endTracking();

            // 5. Verificar se há problemas de dependência circular
            $this->info('5. 🔄 Checking for circular dependencies...');

            try {
                $this->line('📝 Testing TelegramMessageProcessorService instantiation...');
                $testPayload = [
                    'update_id' => 123,
                    'message' => [
                        'chat' => ['id' => 123],
                        'from' => ['id' => 123],
                        'text' => 'test'
                    ]
                ];

                $messageProcessor = app(TelegramMessageProcessorService::class);
                $this->line('✅ Service instantiated successfully');

                // Testar se o tracking funciona
                $this->line('📝 Testing tracking in real service...');
                $messageProcessor->trackMethod('testMethod', ['test' => 'data']);
                $messageProcessor->endMethod('testMethod', ['result' => 'success']);

                $this->line('✅ Tracking methods called successfully');

            } catch (\Exception $e) {
                $this->error('❌ Error testing service: ' . $e->getMessage());
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }

            $this->info('✅ Debug completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Debug failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
