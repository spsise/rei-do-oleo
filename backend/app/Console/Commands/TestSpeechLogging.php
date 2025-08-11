<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpeechToTextService;
use App\Contracts\LoggingServiceInterface;

class TestSpeechLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'speech:test-logging {--provider=huggingface}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test speech service logging functionality';

    /**
     * Execute the console command.
     */
    public function handle(LoggingServiceInterface $loggingService): int
    {
        $this->info('🧪 Testing Speech Service Logging...');

        try {
            // Create speech service instance
            $speechService = new SpeechToTextService($loggingService);

            $this->info('✅ Speech service created successfully');

            // Test connection
            $this->info('🔍 Testing connection...');
            $result = $speechService->testConnection();

            if ($result['success']) {
                $this->info('✅ Connection test successful');
                $this->info("Provider: {$result['provider']}");
                $this->info("Result: {$result['test_result']}");
            } else {
                $this->warn('⚠️ Connection test failed');
                $this->warn("Provider: {$result['provider']}");
                $this->warn("Error: " . ($result['error'] ?? 'Unknown error'));
            }

            $this->info('📝 Check the logs for detailed information');
            $this->info('Log file: storage/logs/telegram.log');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with exception: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}
