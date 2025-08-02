<?php

namespace App\Console\Commands;

use App\Services\SpeechToTextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramTestSpeechCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-speech
                            {--file= : Path to test audio file}
                            {--all-providers : Test all available providers}
                            {--provider= : Specific provider to test (openai, google, azure)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test speech-to-text functionality for Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(SpeechToTextService $speechService): int
    {
        $this->info('🎤 Testing Speech-to-Text System');
        $this->newLine();

        // Test connection
        $this->info('Testing connection...');
        $connectionResult = $speechService->testConnection();

        if ($connectionResult['success']) {
            $this->info('✅ Connection successful');
            $this->info("Provider: {$connectionResult['provider']}");

            if (isset($connectionResult['test_result'])) {
                $this->info("Test result: {$connectionResult['test_result']}");
            }
        } else {
            $this->error('❌ Connection failed');
            $this->error("Error: {$connectionResult['error']}");
            return 1;
        }

        $this->newLine();

        // Test with specific file if provided
        if ($filePath = $this->option('file')) {
            $this->info("Testing with file: {$filePath}");

            if (!file_exists($filePath)) {
                $this->error("❌ File not found: {$filePath}");
                return 1;
            }

            $result = $speechService->convertVoiceToText($filePath);

            if ($result) {
                $this->info('✅ Conversion successful');
                $this->info("Recognized text: {$result}");
            } else {
                $this->error('❌ Conversion failed');
                return 1;
            }
        }

        // Test all providers if requested
        if ($this->option('all-providers')) {
            $this->testAllProviders($speechService);
        }

        // Test specific provider
        if ($provider = $this->option('provider')) {
            $this->testSpecificProvider($speechService, $provider);
        }

        $this->newLine();
        $this->info('🎉 Speech-to-Text testing completed!');

        return 0;
    }

    /**
     * Test all available providers
     */
    private function testAllProviders(SpeechToTextService $speechService): void
    {
        $this->info('Testing all available providers...');
        $this->newLine();

        $providers = ['openai', 'google', 'azure'];
        $results = [];

        foreach ($providers as $provider) {
            $this->info("Testing {$provider}...");

            try {
                // Temporarily change provider
                config(["services.speech.provider" => $provider]);

                $result = $speechService->testConnection();
                $results[$provider] = $result;

                if ($result['success']) {
                    $this->info("✅ {$provider}: OK");
                } else {
                    $this->warn("⚠️ {$provider}: {$result['error']}");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$provider}: {$e->getMessage()}");
                $results[$provider] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        $this->newLine();
        $this->info('Provider Test Summary:');

        foreach ($results as $provider => $result) {
            $status = $result['success'] ? '✅' : '❌';
            $this->line("{$status} {$provider}: " . ($result['success'] ? 'OK' : $result['error']));
        }
    }

    /**
     * Test specific provider
     */
    private function testSpecificProvider(SpeechToTextService $speechService, string $provider): void
    {
        $this->info("Testing specific provider: {$provider}");

        if (!in_array($provider, ['openai', 'google', 'azure'])) {
            $this->error("❌ Invalid provider: {$provider}");
            $this->error("Valid providers: openai, google, azure");
            return;
        }

        try {
            // Temporarily change provider
            config(["services.speech.provider" => $provider]);

            $result = $speechService->testConnection();

            if ($result['success']) {
                $this->info("✅ {$provider} is working correctly");
                $this->info("Provider: {$result['provider']}");

                if (isset($result['test_result'])) {
                    $this->info("Test result: {$result['test_result']}");
                }
            } else {
                $this->error("❌ {$provider} test failed");
                $this->error("Error: {$result['error']}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error testing {$provider}: {$e->getMessage()}");
        }
    }
}
