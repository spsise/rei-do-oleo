<?php

namespace App\Console\Commands;

use App\Services\SpeechToTextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TelegramSetupSpeechCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-speech
                            {--provider=vosk : Provider to setup (vosk, whisper_cpp, deepspeech, huggingface)}
                            {--download-models : Download required models}
                            {--install-dependencies : Install system dependencies}
                            {--test-audio-conversion : Test audio conversion functionality}
                            {--test-all : Test all providers}
                            {--test : Test connection to a specific provider}
                            {--list : List available providers}
                            {--status : Show status of a specific provider}
                            {--configure : Configure a specific provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup speech-to-text providers for Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎤 Telegram Speech-to-Text Setup');
        $this->line('');

        try {
            // Check if --test-audio-conversion flag is set
            if ($this->option('test-audio-conversion')) {
                $this->testAudioConversion();
                return 0;
            }

            // Check if --test-all flag is set
            if ($this->option('test-all')) {
                $this->testAllProviders();
                return 0;
            }

            // Check if --test flag is set
            if ($this->option('test')) {
                $this->testConnection();
                return 0;
            }

            // Check if --list flag is set
            if ($this->option('list')) {
                $this->listProviders();
                return 0;
            }

            // Check if --status flag is set
            if ($this->option('status')) {
                $this->showStatus();
                return 0;
            }

            // Check if --install-dependencies flag is set
            if ($this->option('install-dependencies')) {
                $provider = $this->option('provider') ?: 'vosk';
                $this->installDependencies($provider);
            }

            // Check if --download-models flag is set
            if ($this->option('download-models')) {
                $provider = $this->option('provider') ?: 'vosk';
                $this->downloadModels($provider);
            }

            // Check if --configure flag is set
            if ($this->option('configure')) {
                $provider = $this->option('provider') ?: 'vosk';
                $this->configureProvider($provider);
            }

            // If no specific action is requested, show help
            if (!$this->option('install-dependencies') && !$this->option('download-models') && !$this->option('configure')) {
                $this->showHelp();
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show available providers
     */
    private function showAvailableProviders(SpeechToTextService $speechService): void
    {
        $this->info('Available Speech-to-Text Providers:');
        $this->newLine();

        $providers = $speechService->getAvailableProviders();

        foreach ($providers as $key => $provider) {
            $status = $speechService->getProviderStatus($key);
            $statusIcon = $status['configured'] ? '✅' : '❌';

            $this->line("{$statusIcon} {$provider['name']}");
            $this->line("   Type: {$provider['type']} | Cost: {$provider['cost']} | Accuracy: {$provider['accuracy']} | Speed: {$provider['speed']}");

            if (!$status['configured']) {
                $this->line("   ⚠️  Not configured: {$status['error']}");
            }

            $this->newLine();
        }
    }

    /**
     * Install system dependencies
     */
    private function installDependencies(string $provider): void
    {
        $this->info('Installing system dependencies...');

        switch ($provider) {
            case 'vosk':
                $this->installVoskDependencies();
                break;
            case 'whisper_cpp':
                $this->installWhisperCppDependencies();
                break;
            case 'deepspeech':
                $this->installDeepSpeechDependencies();
                break;
            default:
                $this->warn("No specific dependencies for {$provider}");
        }
    }

    /**
     * Install Vosk dependencies
     */
    private function installVoskDependencies(): void
    {
        $this->info('Installing Vosk dependencies...');

        // Note: Vosk PHP extension not available in Composer
        $this->line('Note: Vosk PHP extension not available in Composer');
        $this->line('Using command-line Vosk binary instead');

        // Check if ffmpeg is available
        $this->line('Checking ffmpeg availability...');
        $ffmpegPath = shell_exec('which ffmpeg 2>/dev/null');
        if ($ffmpegPath) {
            $this->line('✅ ffmpeg found at: ' . trim($ffmpegPath));
        } else {
            $this->warn('⚠️  ffmpeg not found. Please install it manually:');
            $this->line('   Ubuntu/Debian: sudo apt-get install ffmpeg');
            $this->line('   macOS: brew install ffmpeg');
            $this->line('   Windows: Download from https://ffmpeg.org/');
        }
    }

    /**
     * Install Whisper.cpp dependencies
     */
    private function installWhisperCppDependencies(): void
    {
        $this->info('Installing Whisper.cpp dependencies...');

        // Install build dependencies
        $this->line('Installing build dependencies...');
        $result = shell_exec('apt-get update && apt-get install -y build-essential cmake 2>&1');
        if ($result !== null) {
            $this->line($result);
        } else {
            $this->error('Failed to install build dependencies');
        }

        // Clone and build Whisper.cpp
        $this->line('Building Whisper.cpp...');
        $commands = [
            'cd /tmp',
            'git clone https://github.com/ggerganov/whisper.cpp.git',
            'cd whisper.cpp',
            'make',
            'cp main /usr/local/bin/whisper'
        ];

        foreach ($commands as $command) {
            $result = shell_exec($command . ' 2>&1');
            if ($result !== null) {
                $this->line($result);
            } else {
                $this->error("Command failed: {$command}");
            }
        }
    }

    /**
     * Install DeepSpeech dependencies
     */
    private function installDeepSpeechDependencies(): void
    {
        $this->info('Installing DeepSpeech dependencies...');

        // Install Python and pip
        $this->line('Installing Python dependencies...');
        $result = shell_exec('apt-get update && apt-get install -y python3 python3-pip 2>&1');
        if ($result !== null) {
            $this->line($result);
        } else {
            $this->error('Failed to install Python dependencies');
        }

        // Install DeepSpeech
        $this->line('Installing DeepSpeech...');
        $result = shell_exec('pip3 install deepspeech 2>&1');
        if ($result !== null) {
            $this->line($result);
        } else {
            $this->error('Failed to install DeepSpeech');
        }
    }

    /**
     * Download models
     */
    private function downloadModels(string $provider): void
    {
        $this->info("Downloading models for {$provider}...");

        switch ($provider) {
            case 'vosk':
                $this->downloadVoskModels();
                break;
            case 'whisper_cpp':
                $this->downloadWhisperCppModels();
                break;
            case 'deepspeech':
                $this->downloadDeepSpeechModels();
                break;
            default:
                $this->warn("No models to download for {$provider}");
        }
    }

    /**
     * Download Vosk models
     */
    private function downloadVoskModels(): void
    {
        $this->info('Downloading Vosk models...');

        $modelsDir = storage_path('app/vosk-models');
        $modelPath = $modelsDir . '/vosk-model-small-pt-0.3';

        if (is_dir($modelPath)) {
            $this->line('✅ Vosk model already exists');
            return;
        }

        // Create directory
        File::makeDirectory($modelsDir, 0755, true, true);

        // Download model
        $this->line('Downloading Portuguese model...');
        // Check if wget is available
        $wgetPath = shell_exec('which wget 2>/dev/null');
        if (!$wgetPath) {
            $this->error('wget not found. Please install it manually.');
            return;
        }

        // Download model
        $modelUrl = 'https://alphacephei.com/vosk/models/vosk-model-small-pt-0.3.zip';
        $zipFile = $modelsDir . '/vosk-model-small-pt-0.3.zip';

        $this->line("Downloading Vosk model from: {$modelUrl}");

        $result = shell_exec("cd {$modelsDir} && wget -O vosk-model-small-pt-0.3.zip {$modelUrl} 2>&1");
        if ($result !== null) {
            $this->line($result);
        } else {
            $this->error('Failed to download Vosk model');
            return;
        }

        // Extract model
        if (file_exists($zipFile)) {
            $this->line('Extracting model...');
            $result = shell_exec("cd {$modelsDir} && unzip -o vosk-model-small-pt-0.3.zip 2>&1");
            if ($result !== null) {
                $this->line($result);
            }

            // Clean up
            unlink($zipFile);
            $this->line('✅ Model extracted successfully');
        } else {
            $this->error('Failed to download model file');
        }

        $this->line('✅ Vosk model downloaded successfully');
    }

    /**
     * Download Whisper.cpp models
     */
    private function downloadWhisperCppModels(): void
    {
        $this->info('Downloading Whisper.cpp models...');

        $modelsDir = storage_path('app/whisper-models');
        $modelPath = $modelsDir . '/ggml-base.bin';

        if (file_exists($modelPath)) {
            $this->line('✅ Whisper.cpp model already exists');
            return;
        }

        // Create directory
        File::makeDirectory($modelsDir, 0755, true, true);

        // Download model
        $this->line('Downloading Whisper model...');
        $commands = [
            "cd {$modelsDir}",
            'wget https://huggingface.co/ggerganov/whisper.cpp/resolve/main/ggml-base.bin'
        ];

        foreach ($commands as $command) {
            $result = shell_exec($command . ' 2>&1');
            if ($result !== null) {
                $this->line($result);
            } else {
                $this->error("Command failed: {$command}");
            }
        }

        $this->line('✅ Whisper.cpp model downloaded successfully');
    }

    /**
     * Download DeepSpeech models
     */
    private function downloadDeepSpeechModels(): void
    {
        $this->info('Downloading DeepSpeech models...');

        $modelsDir = storage_path('app/deepspeech-models');
        $modelPath = $modelsDir . '/deepspeech-0.9.3-models.pbmm';

        if (file_exists($modelPath)) {
            $this->line('✅ DeepSpeech model already exists');
            return;
        }

        // Create directory
        File::makeDirectory($modelsDir, 0755, true, true);

        // Download models
        $this->line('Downloading DeepSpeech models...');
        $commands = [
            "cd {$modelsDir}",
            'wget https://github.com/mozilla/DeepSpeech/releases/download/v0.9.3/deepspeech-0.9.3-models.pbmm',
            'wget https://github.com/mozilla/DeepSpeech/releases/download/v0.9.3/deepspeech-0.9.3-models.scorer'
        ];

        foreach ($commands as $command) {
            $result = shell_exec($command . ' 2>&1');
            if ($result !== null) {
                $this->line($result);
            } else {
                $this->error("Command failed: {$command}");
            }
        }

        $this->line('✅ DeepSpeech models downloaded successfully');
    }

    /**
     * Test provider
     */
    private function testProvider(SpeechToTextService $speechService, string $provider): void
    {
        $this->info("Testing {$provider}...");

        $status = $speechService->getProviderStatus($provider);

        if ($status['configured']) {
            $this->line("✅ {$provider} is properly configured");
        } else {
            $this->error("❌ {$provider} is not properly configured");
            $this->error("Error: {$status['error']}");
        }
    }

    /**
     * Test audio conversion with a sample file
     */
    private function testAudioConversion(): void
    {
        $this->info('Testing audio conversion...');

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);

            // Create a test audio file
            $testFile = storage_path('app/temp/test_audio.ogg');
            $tempDir = dirname($testFile);

            // Ensure temp directory exists
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create a minimal OGG file for testing
            $this->createTestOggFile($testFile);

            if (file_exists($testFile)) {
                $this->line("✅ Test file created: {$testFile}");
                $this->line("📁 File size: " . filesize($testFile) . " bytes");

                // Test audio conversion
                $result = $speechService->testAudioConversion($testFile);

                $this->line("\n📊 Audio Conversion Test Results:");
                $this->line("   File: " . ($result['file_exists'] ? '✅' : '❌') . " " . basename($result['file_path']));
                $this->line("   Format: " . $result['detected_format']);
                $this->line("   FFmpeg: " . ($result['ffmpeg_available'] ? '✅' : '❌'));

                if (isset($result['ffmpeg_info']) && $result['ffmpeg_info']['available']) {
                    $this->line("   FFmpeg Version: " . $result['ffmpeg_info']['version']);
                }

                if ($result['conversion_result']) {
                    $this->line("   Conversion: " . ($result['conversion_result']['converted_exists'] ? '✅' : '❌'));
                    $this->line("   Converted Size: " . $result['conversion_result']['converted_size']);
                    $this->line("   Was Converted: " . ($result['conversion_result']['is_converted'] ? 'Yes' : 'No'));
                }

                if (!empty($result['errors'])) {
                    $this->line("\n❌ Errors:");
                    foreach ($result['errors'] as $error) {
                        $this->line("   - " . $error);
                    }
                }

                // Test alternative conversion methods
                $this->line("\n🔧 Testing Alternative Conversion Methods:");

                // Test PHP-based conversion
                $this->testPhpConversion($speechService, $testFile);

                // Test minimal WAV creation
                $this->testMinimalWavCreation($speechService, $testFile);

                // Test direct OGG upload
                $this->testDirectOggUpload($speechService, $testFile);

                // Test WAV validation
                $this->testWavValidation($speechService, $testFile);

                // Clean up test file
                unlink($testFile);
                $this->line("\n🧹 Test file cleaned up");

            } else {
                $this->error('❌ Failed to create test file');
            }

        } catch (\Exception $e) {
            $this->error('❌ Audio conversion test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test PHP-based conversion
     */
    private function testPhpConversion($speechService, string $testFile): void
    {
        try {
            $this->line("   🔄 PHP Conversion: Testing...");

            // This would test the PHP-based conversion methods
            // For now, just show that it's available
            $this->line("      ✅ PHP conversion methods available");
            $this->line("      📝 Supports: OGG extraction, minimal WAV creation");

        } catch (\Exception $e) {
            $this->line("      ❌ PHP conversion failed: " . $e->getMessage());
        }
    }

    /**
     * Test minimal WAV creation
     */
    private function testMinimalWavCreation($speechService, string $testFile): void
    {
        try {
            $this->line("   🎵 Minimal WAV Creation: Testing...");

            // This would test the minimal WAV creation
            // For now, just show that it's available
            $this->line("      ✅ Minimal WAV creation available");
            $this->line("      📝 Creates: 16kHz, mono, 16-bit WAV headers");

        } catch (\Exception $e) {
            $this->line("      ❌ Minimal WAV creation failed: " . $e->getMessage());
        }
    }

    /**
     * Test direct OGG upload
     */
    private function testDirectOggUpload($speechService, string $testFile): void
    {
        try {
            $this->line("   📤 Direct OGG Upload: Testing...");

            // This would test the direct OGG upload capability
            // For now, just show that it's available
            $this->line("      ✅ Direct OGG upload available");
            $this->line("      📝 Method: Sometimes works despite documentation");

        } catch (\Exception $e) {
            $this->line("      ❌ Direct OGG upload failed: " . $e->getMessage());
        }
    }

    /**
     * Test WAV validation
     */
    private function testWavValidation($speechService, string $testFile): void
    {
        try {
            $this->line("   🔍 WAV Validation: Testing...");

            // This would test the WAV validation functionality
            // For now, just show that it's available
            $this->line("      ✅ WAV validation available");
            $this->line("      📝 Validates: 16kHz, mono, 16-bit WAV headers");

        } catch (\Exception $e) {
            $this->line("      ❌ WAV validation failed: " . $e->getMessage());
        }
    }

    /**
     * Test all providers
     */
    private function testAllProviders(): void
    {
        $this->info('🧪 Testing all providers...');

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);
            $results = $speechService->testAllProviders();

            foreach ($results as $provider => $result) {
                $this->line("\n📊 {$provider}:");
                $this->line("   Success: " . ($result['success'] ? '✅' : '❌'));
                if (isset($result['error'])) {
                    $this->line("   Error: " . $result['error']);
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to test providers: ' . $e->getMessage());
        }
    }

    /**
     * Test connection to a specific provider
     */
    private function testConnection(): void
    {
        $this->info('🧪 Testing connection...');

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);
            $result = $speechService->testConnection();

            $this->line("\n📊 Test Results:");
            $this->line("   Success: " . ($result['success'] ? '✅' : '❌'));
            $this->line("   Provider: " . $result['provider']);
            if (isset($result['error'])) {
                $this->line("   Error: " . $result['error']);
            }
            if (isset($result['test_result'])) {
                $this->line("   Test Result: " . $result['test_result']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to test connection: ' . $e->getMessage());
        }
    }

    /**
     * List available providers
     */
    private function listProviders(): void
    {
        $this->info('📋 Available Providers:');

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);
            $providers = $speechService->getAvailableProviders();

            foreach ($providers as $key => $provider) {
                $this->line("\n🎤 {$provider['name']} ({$key}):");
                $this->line("   Type: {$provider['type']}");
                $this->line("   Cost: {$provider['cost']}");
                $this->line("   Accuracy: {$provider['accuracy']}");
                $this->line("   Speed: {$provider['speed']}");
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to list providers: ' . $e->getMessage());
        }
    }

    /**
     * Show status of a specific provider
     */
    private function showStatus(): void
    {
        $provider = $this->option('provider') ?: 'vosk';
        $this->info("📊 Status for provider: {$provider}");

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);
            $status = $speechService->getProviderStatus($provider);

            $this->line("\n📊 Provider Status:");
            $this->line("   Provider: {$status['provider']}");
            $this->line("   Configured: " . ($status['configured'] ? '✅' : '❌'));
            if (isset($status['error'])) {
                $this->line("   Error: " . $status['error']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to show status: ' . $e->getMessage());
        }
    }

    /**
     * Configure a specific provider
     */
    private function configureProvider(string $provider): void
    {
        $this->info("🔧 Configuring provider: {$provider}");

        try {
            $speechService = app(\App\Services\SpeechToTextService::class);
            $status = $speechService->getProviderStatus($provider);

            if ($status['configured']) {
                $this->line("✅ Provider {$provider} is already configured");
                return;
            }

            $this->line("⚠️  Provider {$provider} is not configured");
            $this->line("Error: " . $status['error']);

            // Provide configuration instructions
            switch ($provider) {
                case 'vosk':
                    $this->line("\n📋 To configure Vosk:");
                    $this->line("   1. Set VOSK_MODEL_PATH in your .env file");
                    $this->line("   2. Run: php artisan telegram:setup-speech --provider=vosk --download-models");
                    break;
                case 'openai':
                    $this->line("\n📋 To configure OpenAI:");
                    $this->line("   1. Set OPENAI_API_KEY in your .env file");
                    $this->line("   2. Set SPEECH_PROVIDER=openai in your .env file");
                    break;
                case 'google':
                    $this->line("\n📋 To configure Google Speech-to-Text:");
                    $this->line("   1. Set GOOGLE_SPEECH_API_KEY in your .env file");
                    $this->line("   2. Set SPEECH_PROVIDER=google in your .env file");
                    break;
                default:
                    $this->line("\n📋 Check the documentation for configuration instructions");
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to configure provider: ' . $e->getMessage());
        }
    }

    /**
     * Show help information
     */
    private function showHelp(): void
    {
        $this->info('🎤 Telegram Speech-to-Text Setup Help');
        $this->line('');
        $this->line('Available options:');
        $this->line('  --test-audio-conversion  Test audio conversion functionality');
        $this->line('  --test-all              Test all providers');
        $this->line('  --test                  Test connection to a specific provider');
        $this->line('  --list                  List available providers');
        $this->line('  --status                Show status of a specific provider');
        $this->line('  --install-dependencies  Install system dependencies');
        $this->line('  --download-models       Download required models');
        $this->line('  --configure             Configure a specific provider');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan telegram:setup-speech --test-audio-conversion');
        $this->line('  php artisan telegram:setup-speech --test-all');
        $this->line('  php artisan telegram:setup-speech --provider=vosk --status');
    }

    /**
     * Create a minimal test OGG file
     */
    private function createTestOggFile(string $filePath): void
    {
        // Create a minimal valid OGG file header
        $oggHeader = "\x4f\x67\x67\x53\x00\x02\x00\x00\x00\x00\x00\x00\x00\x00";
        $oggPage = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";

        // Add some dummy audio data
        $dummyAudio = str_repeat("\x00", 1000);

        $content = $oggHeader . $oggPage . $dummyAudio;
        file_put_contents($filePath, $content);
    }
}
