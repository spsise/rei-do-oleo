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
                            {--install-dependencies : Install system dependencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup speech-to-text providers for Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(SpeechToTextService $speechService): int
    {
        $provider = $this->option('provider');
        $downloadModels = $this->option('download-models');
        $installDependencies = $this->option('install-dependencies');

        $this->info("🎤 Setting up Speech-to-Text Provider: {$provider}");
        $this->newLine();

        // Show available providers
        $this->showAvailableProviders($speechService);

        // Install dependencies if requested
        if ($installDependencies) {
            $this->installDependencies($provider);
        }

        // Download models if requested
        if ($downloadModels) {
            $this->downloadModels($provider);
        }

        // Test the provider
        $this->testProvider($speechService, $provider);

        $this->newLine();
        $this->info('🎉 Speech-to-Text setup completed!');

        return 0;
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
}
