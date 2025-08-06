<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SpeechToTextService
{
    private string $apiKey;
    private string $apiUrl;
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.speech.provider', 'vosk');
        $this->apiKey = config("services.{$this->provider}.api_key") ?? '';
        $this->apiUrl = config("services.{$this->provider}.speech_url") ?? '';
    }

    /**
     * Convert voice file to text
     */
    public function convertVoiceToText(string $voiceFilePath): ?string
    {
        try {
            if (!file_exists($voiceFilePath)) {
                Log::error('Voice file not found', ['path' => $voiceFilePath]);
                return null;
            }

            // Check cache first
            $cacheKey = 'voice_to_text_' . md5_file($voiceFilePath);
            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult) {
                Log::info('Voice to text result from cache', ['file' => $voiceFilePath]);
                return $cachedResult;
            }

            // Convert based on provider
            $text = match($this->provider) {
                'vosk' => $this->convertWithVosk($voiceFilePath),
                'whisper_cpp' => $this->convertWithWhisperCpp($voiceFilePath),
                'deepspeech' => $this->convertWithDeepSpeech($voiceFilePath),
                'huggingface' => $this->convertWithHuggingFace($voiceFilePath),
                'openai' => $this->convertWithOpenAI($voiceFilePath),
                'google' => $this->convertWithGoogle($voiceFilePath),
                'azure' => $this->convertWithAzure($voiceFilePath),
                default => $this->convertWithVosk($voiceFilePath)
            };

            if ($text) {
                // Cache result for 1 hour
                Cache::put($cacheKey, $text, 3600);

                Log::info('Voice to text conversion successful', [
                    'file' => $voiceFilePath,
                    'provider' => $this->provider,
                    'text_length' => strlen($text)
                ]);
            }

            return $text;

        } catch (\Exception $e) {
            Log::error('Voice to text conversion error', [
                'error' => $e->getMessage(),
                'file' => $voiceFilePath,
                'provider' => $this->provider
            ]);

            return null;
        }
    }

    /**
     * Convert using OpenAI Whisper
     */
    private function convertWithOpenAI(string $voiceFilePath): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->attach(
                'file',
                file_get_contents($voiceFilePath),
                basename($voiceFilePath)
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'pt',
                'response_format' => 'text'
            ]);

            if ($response->successful()) {
                return trim($response->body());
            }

            Log::error('OpenAI Whisper API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI Whisper error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using Google Speech-to-Text
     */
    private function convertWithGoogle(string $voiceFilePath): ?string
    {
        try {
            $audioContent = file_get_contents($voiceFilePath);
            $audio = base64_encode($audioContent);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://speech.googleapis.com/v1/speech:recognize', [
                'config' => [
                    'encoding' => 'OGG_OPUS',
                    'sampleRateHertz' => 48000,
                    'languageCode' => 'pt-BR',
                    'enableAutomaticPunctuation' => true
                ],
                'audio' => [
                    'content' => $audio
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'][0]['alternatives'][0]['transcript'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Google Speech-to-Text error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using Azure Speech Services
     */
    private function convertWithAzure(string $voiceFilePath): ?string
    {
        try {
            $region = config('services.azure.speech_region');
            $url = "https://{$region}.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1";

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
                'Content-Type' => 'audio/ogg;codecs=opus'
            ])->post($url, file_get_contents($voiceFilePath));

            if ($response->successful()) {
                $data = $response->json();
                return $data['DisplayText'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Azure Speech Services error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using Vosk (Offline - Free)
     */
    private function convertWithVosk(string $voiceFilePath): ?string
    {
        try {
            $modelPath = config('services.vosk.model_path');
            $voskPath = config('services.vosk.path', '/usr/local/bin/vosk');

            // Check if Vosk binary is available
            if (!file_exists($voskPath)) {
                Log::error('Vosk binary not found at: ' . $voskPath);
                return null;
            }

            if (!is_dir($modelPath)) {
                Log::error('Vosk model not found at: ' . $modelPath);
                return null;
            }

            // Execute Vosk command
            $command = "{$voskPath} -m {$modelPath} -f {$voiceFilePath} -l pt";
            $output = shell_exec($command . ' 2>&1');

            // Parse output to extract text
            if (preg_match('/Transcription:\s*(.+)/', $output, $matches)) {
                return trim($matches[1]);
            }

            // If no pattern match, return the output as is
            return trim($output);

        } catch (\Exception $e) {
            Log::error('Vosk conversion error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using Whisper.cpp (Offline - Free)
     */
    private function convertWithWhisperCpp(string $voiceFilePath): ?string
    {
        try {
            $whisperPath = config('services.whisper_cpp.path');
            $modelPath = config('services.whisper_cpp.model_path');

            if (!file_exists($whisperPath)) {
                Log::error('Whisper.cpp not found at: ' . $whisperPath);
                return null;
            }

            if (!file_exists($modelPath)) {
                Log::error('Whisper.cpp model not found at: ' . $modelPath);
                return null;
            }

            // Execute whisper.cpp command
            $command = "{$whisperPath} -m {$modelPath} -f {$voiceFilePath} -l pt -otxt";
            $output = shell_exec($command . ' 2>&1');

            // Read the output file
            $outputFile = $voiceFilePath . '.txt';
            if (file_exists($outputFile)) {
                $text = file_get_contents($outputFile);
                unlink($outputFile); // Clean up
                return trim($text);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Whisper.cpp conversion error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using DeepSpeech (Offline - Free)
     */
    private function convertWithDeepSpeech(string $voiceFilePath): ?string
    {
        try {
            $modelPath = config('services.deepspeech.model_path');
            $scorerPath = config('services.deepspeech.scorer_path');

            if (!file_exists($modelPath)) {
                Log::error('DeepSpeech model not found at: ' . $modelPath);
                return null;
            }

            // Execute DeepSpeech command
            $command = "deepspeech --model {$modelPath}";
            if (file_exists($scorerPath)) {
                $command .= " --scorer {$scorerPath}";
            }
            $command .= " --audio {$voiceFilePath}";

            $output = shell_exec($command . ' 2>&1');

            // Parse output to extract text
            if (preg_match('/Transcription:\s*(.+)/', $output, $matches)) {
                return trim($matches[1]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('DeepSpeech conversion error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert using Hugging Face (Online - Free)
     */
    private function convertWithHuggingFace(string $voiceFilePath): ?string
    {
        try {
            $apiUrl = config('services.huggingface.api_url');
            $apiKey = config('services.huggingface.api_key');

            if (!$apiUrl) {
                Log::error('Hugging Face API URL not configured');
                return null;
            }

            $headers = ['Content-Type' => 'audio/wav'];
            if ($apiKey) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }

            $response = Http::withHeaders($headers)
                ->attach('file', file_get_contents($voiceFilePath), basename($voiceFilePath))
                ->post($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                return $data['text'] ?? null;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Hugging Face conversion error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Prepare audio for Vosk processing
     */
    private function prepareAudioForVosk(string $voiceFilePath): string
    {
        // Vosk expects 16kHz mono WAV format
        $outputPath = $voiceFilePath . '_vosk.wav';

        // Use ffmpeg to convert if available
        if ($this->isFfmpegAvailable()) {
            $command = "ffmpeg -i {$voiceFilePath} -ar 16000 -ac 1 -c:a pcm_s16le {$outputPath} -y 2>/dev/null";
            shell_exec($command);

            if (file_exists($outputPath)) {
                $audioData = file_get_contents($outputPath);
                unlink($outputPath); // Clean up
                return $audioData;
            }
        }

        // Fallback: return original file
        return file_get_contents($voiceFilePath);
    }

    /**
     * Check if ffmpeg is available
     */
    private function isFfmpegAvailable(): bool
    {
        $output = shell_exec('which ffmpeg 2>/dev/null');
        return !empty($output);
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return [
            'pt-BR' => 'Português (Brasil)',
            'pt-PT' => 'Português (Portugal)',
            'en-US' => 'English (US)',
            'es-ES' => 'Español'
        ];
    }

    /**
     * Get available providers
     */
    public function getAvailableProviders(): array
    {
        return [
            'vosk' => [
                'name' => 'Vosk (Offline - Free)',
                'type' => 'offline',
                'cost' => 'free',
                'accuracy' => '90%',
                'speed' => 'medium'
            ],
            'whisper_cpp' => [
                'name' => 'Whisper.cpp (Offline - Free)',
                'type' => 'offline',
                'cost' => 'free',
                'accuracy' => '95%',
                'speed' => 'medium'
            ],
            'deepspeech' => [
                'name' => 'DeepSpeech (Offline - Free)',
                'type' => 'offline',
                'cost' => 'free',
                'accuracy' => '88%',
                'speed' => 'slow'
            ],
            'huggingface' => [
                'name' => 'Hugging Face (Online - Free)',
                'type' => 'online',
                'cost' => 'free',
                'accuracy' => '92%',
                'speed' => 'fast'
            ],
            'openai' => [
                'name' => 'OpenAI Whisper (Online - Paid)',
                'type' => 'online',
                'cost' => 'paid',
                'accuracy' => '95%',
                'speed' => 'fast'
            ],
            'google' => [
                'name' => 'Google Speech-to-Text (Online - Paid)',
                'type' => 'online',
                'cost' => 'paid',
                'accuracy' => '94%',
                'speed' => 'fast'
            ],
            'azure' => [
                'name' => 'Azure Speech Services (Online - Paid)',
                'type' => 'online',
                'cost' => 'paid',
                'accuracy' => '93%',
                'speed' => 'fast'
            ]
        ];
    }

        /**
     * Test connection to speech service
     */
    public function testConnection(): array
    {
        try {
            // Check if provider is configured
            $status = $this->getProviderStatus($this->provider);

            if (!$status['configured']) {
                return [
                    'success' => false,
                    'error' => 'Provider not configured',
                    'provider' => $this->provider
                ];
            }

            // Create a simple test audio file or use existing one
            $testFile = storage_path('app/temp/test_voice.ogg');

            if (!file_exists($testFile)) {
                return [
                    'success' => false,
                    'error' => 'Test file not found',
                    'provider' => $this->provider
                ];
            }

            $result = $this->convertVoiceToText($testFile);

            return [
                'success' => !empty($result),
                'provider' => $this->provider,
                'test_result' => $result ?? 'No result'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->provider
            ];
        }
    }

    /**
     * Test all providers
     */
    public function testAllProviders(): array
    {
        $results = [];
        $providers = array_keys($this->getAvailableProviders());

        foreach ($providers as $provider) {
            try {
                // Temporarily change provider
                $originalProvider = $this->provider;
                $this->provider = $provider;

                $result = $this->testConnection();
                $results[$provider] = $result;

                // Restore original provider
                $this->provider = $originalProvider;

            } catch (\Exception $e) {
                $results[$provider] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'provider' => $provider
                ];
            }
        }

        return $results;
    }

    /**
     * Get provider status
     */
    public function getProviderStatus(string $provider): array
    {
        $providers = $this->getAvailableProviders();

        if (!isset($providers[$provider])) {
            return [
                'success' => false,
                'error' => 'Provider not found'
            ];
        }

        $providerInfo = $providers[$provider];

        // Check if provider is properly configured
        $isConfigured = match($provider) {
            'vosk' => is_dir(config('services.vosk.model_path')),
            'whisper_cpp' => file_exists(config('services.whisper_cpp.path')) && file_exists(config('services.whisper_cpp.model_path')),
            'deepspeech' => file_exists(config('services.deepspeech.model_path')),
            'huggingface' => !empty(config('services.huggingface.api_url')),
            'openai' => !empty(config('services.openai.api_key')),
            'google' => !empty(config('services.google.speech_api_key')),
            'azure' => !empty(config('services.azure.speech_key')),
            default => false
        };

        return [
            'success' => $isConfigured,
            'provider' => $provider,
            'info' => $providerInfo,
            'configured' => $isConfigured,
            'error' => $isConfigured ? null : 'Provider not properly configured'
        ];
    }
}
