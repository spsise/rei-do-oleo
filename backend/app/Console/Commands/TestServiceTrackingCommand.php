<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use App\Services\TelegramMessageProcessorService;
use Illuminate\Console\Command;

class TestServiceTrackingCommand extends Command
{
    protected $signature = 'message-flow:test-service';
    protected $description = 'Test tracking directly in the service';

    public function handle(): int
    {
        $this->info('🧪 Testing Service Tracking Directly...');

        try {
            $flowTracker = app(MessageFlowTrackerInterface::class);
            $messageProcessor = app(TelegramMessageProcessorService::class);

            $this->info('1. 🔧 Checking flowTracker state...');
            $debug = $messageProcessor->debugFlowTracker();
            $this->line('✅ FlowTracker class: ' . $debug['flow_tracker_class']);
            $this->line('✅ FlowTracker enabled: ' . ($debug['flow_tracker_enabled'] ? 'YES' : 'NO'));
            $this->line('✅ Service class: ' . $debug['this_class']);

            $this->info('2. 🚀 Starting tracking...');
            $flowTracker->startTracking('test_service_' . uniqid());
            $this->line('✅ Tracking started');

            $this->info('3. 📝 Testing tracking in service...');
            $this->line('   📝 Calling trackMethod...');
            $messageProcessor->trackMethod('testMethod', ['test' => 'data']);
            $this->line('   ✅ trackMethod called');

            $this->info('4. 📊 Checking executed methods...');
            $executedMethods = $flowTracker->getExecutedMethods();
            $this->line('✅ Executed methods count: ' . count($executedMethods));

            foreach ($executedMethods as $method) {
                $this->line('   • ' . $method);
            }

            $this->info('5. 🏁 Ending tracking...');
            $flowTracker->endTracking();
            $this->line('✅ Tracking ended');

            $this->info('✅ Service tracking test completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Service tracking test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
