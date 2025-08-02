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
        $this->provider = config('services.speech.provider', 'openai');
        $this->apiKey = config("services.{$this->provider}.api_key");
        $this->apiUrl = config("services.{$this->provider}.speech_url");
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
                'openai' => $this->convertWithOpenAI($voiceFilePath),
                'google' => $this->convertWithGoogle($voiceFilePath),
                'azure' => $this->convertWithAzure($voiceFilePath),
                default => $this->convertWithOpenAI($voiceFilePath)
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
     * Test connection to speech service
     */
    public function testConnection(): array
    {
        try {
            // Create a simple test audio file or use existing one
            $testFile = storage_path('app/temp/test_voice.ogg');

            if (!file_exists($testFile)) {
                return [
                    'success' => false,
                    'error' => 'Test file not found'
                ];
            }

            $result = $this->convertVoiceToText($testFile);

            return [
                'success' => !empty($result),
                'provider' => $this->provider,
                'test_result' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->provider
            ];
        }
    }
}
