<?php

namespace App\Console\Commands;

use App\Contracts\MessageFlowTrackerInterface;
use Illuminate\Console\Command;

class TestTrackingStateCommand extends Command
{
    protected $signature = 'message-flow:test-state';
    protected $description = 'Test the tracking state and methods';

    public function handle(): int
    {
        $this->info('🧪 Testing Tracking State...');

        try {
            $flowTracker = app(MessageFlowTrackerInterface::class);

            $this->info('1. 🔧 Checking initial state...');
            $this->line('✅ Enabled: ' . ($flowTracker->isEnabled() ? 'YES' : 'NO'));
            $this->line('✅ Executed methods: ' . count($flowTracker->getExecutedMethods()));

            $this->info('2. 🚀 Starting tracking...');
            $flowTracker->startTracking('test_state_' . uniqid());
            $this->line('✅ Tracking started');

            $this->info('3. 📝 Testing trackMethod...');
            $flowTracker->trackMethod('TestClass', 'testMethod', ['test' => 'data']);
            $this->line('✅ trackMethod called');

            $this->info('4. 📊 Checking state after tracking...');
            $executedMethods = $flowTracker->getExecutedMethods();
            $this->line('✅ Executed methods count: ' . count($executedMethods));

            foreach ($executedMethods as $method) {
                $this->line('   • ' . $method);
            }

            $this->info('5. 🏁 Ending tracking...');
            $flowTracker->endTracking();
            $this->line('✅ Tracking ended');

            $this->info('✅ State test completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ State test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
