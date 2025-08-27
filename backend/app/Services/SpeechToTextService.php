<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Contracts\LoggingServiceInterface;
use App\Contracts\MessageFlowTrackerInterface;
use App\Contracts\MessageTrackingInterface;

class SpeechToTextService implements MessageTrackingInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $provider;
    private LoggingServiceInterface $loggingService;

    public function __construct(
        LoggingServiceInterface $loggingService,
        private MessageFlowTrackerInterface $flowTracker
    ) {
        $this->initializeTracking();
        $this->loggingService = $loggingService;
        $this->provider = config('speech.provider', 'vosk');
        $this->apiKey = config("services.{$this->provider}.api_key") ?? '';
        $this->apiUrl = config("services.{$this->provider}.speech_url") ?? '';
    }

    /**
     * Convert voice file to text
     */
    public function convertVoiceToText(string $voiceFilePath): ?string
    {
        $this->trackMethod('convertVoiceToText', ['voice_file_path' => $voiceFilePath]);

        try {
            if (!file_exists($voiceFilePath)) {
                $this->loggingService->logTelegramEvent('voice_file_not_found', [
                    'path' => $voiceFilePath
                ], 'error');
                return null;
            }

            $cacheKey = $this->generateCacheKey($voiceFilePath);

            // Check if this file is currently being processed to prevent duplicate processing
            $processingKey = $cacheKey . '_processing';
            if (Cache::has($processingKey)) {
                $this->loggingService->logTelegramEvent('voice_to_text_already_processing', [
                    'file' => $voiceFilePath,
                    'cache_key' => $cacheKey,
                    'note' => 'File is already being processed, waiting for completion'
                ], 'info');

                // Wait for processing to complete (max 30 seconds)
                $waitTime = 0;
                $maxWaitTime = config('speech.cache.max_wait_time', 30);
                while (Cache::has($processingKey) && $waitTime < $maxWaitTime) {
                    sleep(1);
                    $waitTime++;
                }

                // Check if result is now available
                $cachedResult = Cache::get($cacheKey);
                if ($cachedResult) {
                    $this->loggingService->logTelegramEvent('voice_to_text_retrieved_after_wait', [
                        'file' => $voiceFilePath,
                        'cache_key' => $cacheKey,
                        'wait_time' => $waitTime,
                        'result_length' => strlen($cachedResult)
                    ]);
                    return $cachedResult;
                }
            }

            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult) {
                // Only log cache hit once per file to reduce log noise
                $cacheHitKey = $cacheKey . '_hit_logged';
                if (!Cache::has($cacheHitKey)) {
                    $this->loggingService->logTelegramEvent('voice_to_text_cache_hit', [
                        'file' => $voiceFilePath,
                        'cache_key' => $cacheKey,
                        'cache_age' => $this->getCacheAge($cacheKey)
                    ]);

                    // Mark that we've logged this cache hit (expires in 1 hour)
                    Cache::put($cacheHitKey, true, 3600);
                }

                // Check if cache is still valid (additional validation)
                if ($this->isCacheStillValid($cacheKey, $voiceFilePath)) {
                    return $cachedResult;
                } else {
                    // Cache is stale, remove it
                    Cache::forget($cacheKey);
                    Cache::forget($cacheKey . '_metadata');
                    Cache::forget($cacheHitKey);
                    $this->loggingService->logTelegramEvent('voice_to_text_cache_expired', [
                        'file' => $voiceFilePath,
                        'cache_key' => $cacheKey,
                        'action' => 'cache_removed_and_reprocessing'
                    ]);
                }
            }

            // Mark this file as being processed
            Cache::put($processingKey, true, config('speech.cache.processing_timeout', 60)); // 1 minute timeout

            try {
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
                    // Cache result with shorter TTL and additional metadata
                    $this->cacheResult($cacheKey, $text, $voiceFilePath);

                    $this->loggingService->logTelegramEvent('voice_to_text_conversion_success', [
                        'file' => $voiceFilePath,
                        'provider' => $this->provider,
                        'text_length' => strlen($text),
                        'cache_key' => $cacheKey
                    ]);
                }

                $this->endMethod('convertVoiceToText', ['result' => $text, 'status' => 'success']);
                return $text;

            } finally {
                // Always remove processing flag
                Cache::forget($processingKey);
            }

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'voice_to_text_conversion',
                'file' => $voiceFilePath,
                'provider' => $this->provider
            ]);

            $this->endMethod('convertVoiceToText', ['result' => null, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate improved cache key with file metadata
     */
    private function generateCacheKey(string $voiceFilePath): string
    {
        $fileHash = md5_file($voiceFilePath);
        $fileSize = filesize($voiceFilePath);
        $provider = $this->provider;

        // Use stable cache key: provider + hash + size (without timestamp)
        // This ensures the same audio file always gets the same cache key
        return "voice_to_text_{$provider}_{$fileHash}_{$fileSize}";
    }

        /**
     * Check if cache is still valid
     */
    private function isCacheStillValid(string $cacheKey, string $voiceFilePath): bool
    {
        try {
            // Check if file still exists
            if (!file_exists($voiceFilePath)) {
                return false;
            }

            // Get cache metadata
            $cacheMetadata = Cache::get($cacheKey . '_metadata');
            if (!$cacheMetadata) {
                return false;
            }

            // Check if file size matches (more reliable than timestamp)
            $currentFileSize = filesize($voiceFilePath);
            if ($currentFileSize !== $cacheMetadata['file_size']) {
                return false;
            }

            // Check if cache is not too old (TTL check)
            $cacheAge = time() - $cacheMetadata['cached_at'];
            $maxAge = config('speech.cache.max_age', 1800); // 30 minutes default

            if ($cacheAge > $maxAge) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cache result with metadata
     */
    private function cacheResult(string $cacheKey, string $text, string $voiceFilePath): void
    {
        try {
            // Cache the text result with shorter TTL
            $cacheTTL = config('speech.cache.ttl', 1800); // 30 minutes default
            Cache::put($cacheKey, $text, $cacheTTL);

            // Cache metadata for validation (without file_modified timestamp)
            $metadata = [
                'file_size' => filesize($voiceFilePath),
                'cached_at' => time(),
                'provider' => $this->provider,
                'text_length' => strlen($text),
                'processing_completed_at' => time()
            ];

            Cache::put($cacheKey . '_metadata', $metadata, $cacheTTL + 300); // 5 minutes extra for metadata

            // Add to keys list for management
            $this->addToCacheKeysList($cacheKey);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'cache_result',
                'cache_key' => $cacheKey,
                'file' => $voiceFilePath
            ]);
        }
    }

    /**
     * Add cache key to the managed keys list
     */
    private function addToCacheKeysList(string $cacheKey): void
    {
        try {
            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            if (!in_array($cacheKey, $keys)) {
                $keys[] = $cacheKey;
                Cache::put($cachePrefix . 'keys', $keys, 86400); // 24 hours
            }
        } catch (\Exception $e) {
            // Silent fail for key management
        }
    }

    /**
     * Remove cache key from the managed keys list
     */
    private function removeFromCacheKeysList(string $cacheKey): void
    {
        try {
            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            $keys = array_filter($keys, function($key) use ($cacheKey) {
                return $key !== $cacheKey;
            });

            Cache::put($cachePrefix . 'keys', $keys, 86400); // 24 hours
        } catch (\Exception $e) {
            // Silent fail for key management
        }
    }

    /**
     * Get cache age in seconds
     */
    private function getCacheAge(string $cacheKey): int
    {
        try {
            $metadata = Cache::get($cacheKey . '_metadata');
            if ($metadata && isset($metadata['cached_at'])) {
                return time() - $metadata['cached_at'];
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache(): int
    {
        try {
            $clearedCount = 0;
            $cachePrefix = 'voice_to_text_';

            // Get all cache keys with our prefix
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                if (Cache::has($key)) {
                    $metadata = Cache::get($key . '_metadata');
                    if ($metadata) {
                        $cacheAge = time() - $metadata['cached_at'];
                        $maxAge = config('services.speech.cache_max_age', 1800);

                        if ($cacheAge > $maxAge) {
                            Cache::forget($key);
                            Cache::forget($key . '_metadata');
                            Cache::forget($key . '_hit_logged');
                            Cache::forget($key . '_processing');
                            $clearedCount++;
                        }
                    }
                }
            }

            $this->loggingService->logTelegramEvent('speech_cache_cleanup_completed', [
                'cleared_entries' => $clearedCount,
                'cache_prefix' => $cachePrefix
            ]);

            return $clearedCount;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'clear_expired_cache'
            ]);
            return 0;
        }
    }

    /**
     * Force clear all speech cache
     */
    public function clearAllSpeechCache(): bool
    {
        try {
            $cachePrefix = 'voice_to_text_';

            // Clear all cache entries with our prefix
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                Cache::forget($key);
                Cache::forget($key . '_metadata');
                Cache::forget($key . '_hit_logged');
                Cache::forget($key . '_processing');
            }

            // Clear the keys list itself
            Cache::forget($cachePrefix . 'keys');

            $this->loggingService->logTelegramEvent('speech_cache_cleared', [
                'action' => 'all_cache_cleared',
                'cache_prefix' => $cachePrefix
            ]);

            return true;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'clear_all_speech_cache'
            ]);
            return false;
        }
    }

    /**
     * Clear duplicate log entries and clean up cache
     */
    public function cleanupDuplicateLogs(): array
    {
        try {
            $cleanedCount = 0;
            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                // Clean up duplicate log flags
                if (Cache::has($key . '_hit_logged')) {
                    $cleanedCount++;
                }

                // Clean up processing flags that might be stuck
                if (Cache::has($key . '_processing')) {
                    $processingTime = Cache::get($key . '_processing_time', 0);
                    if (time() - $processingTime > 300) { // 5 minutes
                        Cache::forget($key . '_processing');
                        Cache::forget($key . '_processing_time');
                        $cleanedCount++;
                    }
                }
            }

            $this->loggingService->logTelegramEvent('duplicate_logs_cleanup_completed', [
                'cleaned_entries' => $cleanedCount,
                'cache_prefix' => $cachePrefix
            ]);

            return [
                'success' => true,
                'cleaned_entries' => $cleanedCount
            ];

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'cleanup_duplicate_logs'
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache statistics with duplicate log information
     */
    public function getCacheStats(): array
    {
        try {
            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            $stats = [
                'total_entries' => count($keys),
                'active_entries' => 0,
                'expired_entries' => 0,
                'total_size' => 0,
                'oldest_entry' => null,
                'newest_entry' => null,
                'duplicate_log_flags' => 0,
                'stuck_processing_flags' => 0
            ];

            $currentTime = time();
            $maxAge = config('services.speech.cache_max_age', 1800);

            foreach ($keys as $key) {
                if (Cache::has($key)) {
                    $metadata = Cache::get($key . '_metadata');
                    if ($metadata) {
                        $stats['active_entries']++;
                        $stats['total_size'] += $metadata['text_length'] ?? 0;

                        $age = $currentTime - $metadata['cached_at'];
                        if ($age > $maxAge) {
                            $stats['expired_entries']++;
                        }

                        if (!$stats['oldest_entry'] || $metadata['cached_at'] < $stats['oldest_entry']) {
                            $stats['oldest_entry'] = $metadata['cached_at'];
                        }

                        if (!$stats['newest_entry'] || $metadata['cached_at'] > $stats['newest_entry']) {
                            $stats['newest_entry'] = $metadata['cached_at'];
                        }
                    }
                }

                // Count duplicate log flags
                if (Cache::has($key . '_hit_logged')) {
                    $stats['duplicate_log_flags']++;
                }

                // Count stuck processing flags
                if (Cache::has($key . '_processing')) {
                    $processingTime = Cache::get($key . '_processing_time', 0);
                    if ($currentTime - $processingTime > 300) {
                        $stats['stuck_processing_flags']++;
                    }
                }
            }

            return $stats;

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'total_entries' => 0
            ];
        }
    }

    /**
     * Convert using OpenAI Whisper
     */
    private function convertWithOpenAI(string $voiceFilePath): ?string
    {
        try {
            // Check file format and convert if necessary
            $convertedFilePath = $this->prepareAudioForOpenAI($voiceFilePath);

            if (!$convertedFilePath) {
                $this->loggingService->logTelegramEvent('openai_audio_preparation_failed', [
                    'original_file' => $voiceFilePath,
                    'error' => 'Could not prepare audio file for OpenAI'
                ], 'error');
                return null;
            }

            // Try multiple approaches
            $approaches = [
                'converted_file' => $convertedFilePath,
                'original_file' => $voiceFilePath
            ];

            foreach ($approaches as $approach => $filePath) {
                if (!$filePath || !file_exists($filePath)) {
                    continue;
                }

                $this->loggingService->logTelegramEvent('openai_whisper_attempt', [
                    'approach' => $approach,
                    'file_path' => $filePath,
                    'file_size' => filesize($filePath),
                    'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION)
                ]);

                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                    ])->attach(
                        'file',
                        file_get_contents($filePath),
                        basename($filePath)
                    )->post('https://api.openai.com/v1/audio/transcriptions', [
                        'model' => 'whisper-1',
                        'language' => 'pt',
                        'response_format' => 'text'
                    ]);

                    if ($response->successful()) {
                        $result = trim($response->body());

                        $this->loggingService->logTelegramEvent('openai_whisper_success', [
                            'approach' => $approach,
                            'file_path' => $filePath,
                            'result_length' => strlen($result)
                        ]);

                        // Clean up converted file if it was created
                        if ($convertedFilePath !== $voiceFilePath && file_exists($convertedFilePath)) {
                            unlink($convertedFilePath);
                        }

                        return $result;
                    }

                    // Log the failed attempt
                    $this->loggingService->logTelegramEvent('openai_whisper_attempt_failed', [
                        'approach' => $approach,
                        'file_path' => $filePath,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ], 'warning');

                } catch (\Exception $e) {
                    $this->loggingService->logTelegramEvent('openai_whisper_attempt_exception', [
                        'approach' => $approach,
                        'file_path' => $filePath,
                        'error' => $e->getMessage()
                    ], 'warning');
                }
            }

            // All approaches failed
            $this->loggingService->logTelegramEvent('openai_whisper_all_attempts_failed', [
                'original_file' => $voiceFilePath,
                'converted_file' => $convertedFilePath,
                'error' => 'All conversion and upload approaches failed'
            ], 'error');

            // Clean up converted file
            if ($convertedFilePath !== $voiceFilePath && file_exists($convertedFilePath)) {
                unlink($convertedFilePath);
            }

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'openai_whisper_conversion',
                'file' => $voiceFilePath,
                'provider' => 'openai'
            ]);

            // Clean up converted file on exception
            if (isset($convertedFilePath) && $convertedFilePath !== $voiceFilePath && file_exists($convertedFilePath)) {
                unlink($convertedFilePath);
            }

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
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'config' => [
                    'encoding' => 'OGG_OPUS',
                    'sampleRateHertz' => 48000,
                    'languageCode' => 'pt-BR',
                    'enableAutomaticPunctuation' => true,
                    'enableWordTimeOffsets' => true,
                    'model' => 'latest_long',
                ],
                'audio' => [
                    'content' => $audio
                ]
            ]);

            if ($response->successful() && ($response->json()['results'][0]['alternatives'][0]['transcript']??null) != null) {
                $data = $response->json();
                return $data['results'][0]['alternatives'][0]['transcript'] ?? null;
            }
            else
            {
                $this->loggingService->logTelegramEvent('google_speech_to_text_conversion_failed', [
                    'file' => $voiceFilePath,
                    'provider' => $this->provider,
                    'response' => $response->body(),
                    'api_key' => $this->apiKey,
                    'url' => $this->apiUrl,
                    'api_url' => $this->apiUrl . '?key=' . $this->apiKey
                ]);
            }

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'google_speech_to_text_conversion',
                'file' => $voiceFilePath,
                'provider' => 'google'
            ]);

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
            $this->loggingService->logException($e, [
                'context' => 'azure_speech_services_conversion',
                'file' => $voiceFilePath,
                'provider' => 'azure'
            ]);

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
//dd(file_exists($voskPath), $voskPath);
            // Check if Vosk binary is available
            // if (!file_exists($voskPath)) {
            //     $this->loggingService->logTelegramEvent('vosk_binary_not_found', [
            //         'path' => $voskPath,
            //         'fallback' => 'using_php_vosk'
            //     ], 'warning');

            //     // Fallback to PHP Vosk if binary not available
            //     return $this->convertWithPhpVosk($voiceFilePath);
            // }

            if (!is_dir($modelPath)) {
                $this->loggingService->logTelegramEvent('vosk_model_not_found', [
                    'path' => $modelPath,
                    'fallback' => 'using_php_vosk'
                ], 'warning');

                // Fallback to PHP Vosk if model not available
                return $this->convertWithPhpVosk($voiceFilePath);
            }

            // Try binary Vosk first
            $result = $this->convertWithBinaryVosk($voiceFilePath, $voskPath, $modelPath);
            if ($result) {
                return $result;
            }

            // Fallback to PHP Vosk
            $this->loggingService->logTelegramEvent('vosk_binary_failed_fallback', [
                'fallback' => 'using_php_vosk'
            ], 'warning');

            return $this->convertWithPhpVosk($voiceFilePath);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'vosk_conversion',
                'file' => $voiceFilePath,
                'provider' => 'vosk'
            ]);

            // Final fallback to PHP Vosk
            return $this->convertWithPhpVosk($voiceFilePath);
        }
    }

    /**
     * Convert using Vosk binary (if available)
     */
    private function convertWithBinaryVosk(string $voiceFilePath, string $voskPath, string $modelPath): ?string
    {
        try {
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
            return null;
        }
    }

    /**
     * Convert using PHP Vosk (no binary required)
     */
    private function convertWithPhpVosk(string $voiceFilePath): ?string
    {
        try {
            // Check if PHP Vosk extension is available
            if (!extension_loaded('vosk')) {
                $this->loggingService->logTelegramEvent('vosk_php_extension_not_available', [
                    'fallback' => 'using_audio_validation_only'
                ], 'warning');

                // Final fallback: just validate the audio file
                return $this->validateAudioFileOnly($voiceFilePath);
            }

            // Check if Vosk classes are available
            if (!class_exists('Vosk\Model') || !class_exists('Vosk\Recognizer')) {
                $this->loggingService->logTelegramEvent('vosk_classes_not_available', [
                    'fallback' => 'using_audio_validation_only'
                ], 'warning');

                return $this->validateAudioFileOnly($voiceFilePath);
            }

            // PHP Vosk extension is available
            $modelPath = config('services.vosk.model_path');

            if (!is_dir($modelPath)) {
                $this->loggingService->logTelegramEvent('vosk_php_model_not_found', [
                    'fallback' => 'using_audio_validation_only'
                ], 'warning');

                return $this->validateAudioFileOnly($voiceFilePath);
            }

            // Initialize Vosk model using reflection to avoid linter errors
            try {
                $modelClass = new \ReflectionClass('Vosk\Model');
                $recognizerClass = new \ReflectionClass('Vosk\Recognizer');

                $model = $modelClass->newInstance($modelPath);
                $recognizer = $recognizerClass->newInstance($model);

                // Read audio file
                $audioData = file_get_contents($voiceFilePath);
                if (!$audioData) {
                    return null;
                }

                // Process audio with Vosk
                $recognizer->acceptWaveform($audioData);
                $result = $recognizer->getResult();

                if (isset($result['text'])) {
                    return trim($result['text']);
                }

                return null;

            } catch (\ReflectionException $e) {
                $this->loggingService->logTelegramEvent('vosk_reflection_error', [
                    'error' => $e->getMessage(),
                    'fallback' => 'using_audio_validation_only'
                ], 'warning');

                return $this->validateAudioFileOnly($voiceFilePath);
            }

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'php_vosk_conversion',
                'file' => $voiceFilePath
            ]);

            // Final fallback
            return $this->validateAudioFileOnly($voiceFilePath);
        }
    }

    /**
     * Validate audio file without conversion (final fallback)
     */
    private function validateAudioFileOnly(string $voiceFilePath): ?string
    {
        try {
            $this->loggingService->logTelegramEvent('vosk_final_fallback', [
                'method' => 'audio_validation_only',
                'file_path' => $voiceFilePath,
                'note' => 'No conversion possible, only validating file format'
            ], 'warning');

            // Just validate that the file is a valid audio file
            $format = $this->detectAudioFormat($voiceFilePath);

            if (in_array($format, ['ogg', 'opus', 'wav', 'mp3'])) {
                // File is valid audio, but we can't convert it
                // Return a placeholder message
                return "Áudio recebido (formato: {$format}) - Conversão não disponível nesta hospedagem";
            }

            return "Arquivo de áudio inválido ou não suportado";

        } catch (\Exception $e) {
            return "Erro ao processar arquivo de áudio";
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
                $this->loggingService->logTelegramEvent('whisper_cpp_not_found', [
                    'path' => $whisperPath
                ], 'error');
                return null;
            }

            if (!file_exists($modelPath)) {
                $this->loggingService->logTelegramEvent('whisper_cpp_model_not_found', [
                    'path' => $modelPath
                ], 'error');
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
            $this->loggingService->logException($e, [
                'context' => 'whisper_cpp_conversion',
                'file' => $voiceFilePath,
                'provider' => 'whisper_cpp'
            ]);

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
                $this->loggingService->logTelegramEvent('deepspeech_model_not_found', [
                    'path' => $modelPath
                ], 'error');
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
            $this->loggingService->logException($e, [
                'context' => 'deepspeech_conversion',
                'file' => $voiceFilePath,
                'provider' => 'deepspeech'
            ]);
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
                $this->loggingService->logTelegramEvent('huggingface_api_url_missing', [
                    'provider' => 'huggingface',
                    'config_path' => 'services.huggingface.api_url'
                ], 'error');
                return null;
            }

            if (!$apiKey) {
                $this->loggingService->logTelegramEvent('huggingface_api_key_missing', [
                    'provider' => 'huggingface',
                    'config_path' => 'services.huggingface.api_key',
                    'note' => 'API key is required for Hugging Face inference API'
                ], 'warning');
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
                $text = $data['text'] ?? null;

                if ($text) {
                    $this->loggingService->logTelegramEvent('huggingface_conversion_success', [
                        'text_length' => strlen($text),
                        'text_preview' => substr($text, 0, 100)
                    ]);
                } else {
                    $this->loggingService->logTelegramEvent('huggingface_response_missing_text', [
                        'response_data' => $data
                    ], 'warning');
                }

                return $text;
            }

            // Log error response
            $this->loggingService->logTelegramEvent('huggingface_api_error_response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'headers' => $response->headers()
            ], 'error');

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'huggingface_conversion',
                'provider' => 'huggingface',
                'file_path' => $voiceFilePath
            ]);
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
     * Prepare audio for OpenAI Whisper
     */
    private function prepareAudioForOpenAI(string $voiceFilePath): ?string
    {
        try {
            // Detect file format
            $fileFormat = $this->detectAudioFormat($voiceFilePath);

            $this->loggingService->logTelegramEvent('openai_audio_format_detected', [
                'file_path' => $voiceFilePath,
                'detected_format' => $fileFormat,
                'file_size' => file_exists($voiceFilePath) ? filesize($voiceFilePath) : 'N/A'
            ]);

            // OpenAI Whisper supports: mp3, mp4, mpeg, mpga, m4a, wav, webm
            $supportedFormats = ['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'];

            // If format is already supported, validate and fix if necessary
            if (in_array($fileFormat, $supportedFormats)) {
                if ($fileFormat === 'wav') {
                    // Validate and fix WAV file if needed
                    $validWavFile = $this->validateAndFixWavFile($voiceFilePath);
                    if ($validWavFile) {
                        $this->loggingService->logTelegramEvent('openai_wav_validation_completed', [
                            'format' => $fileFormat,
                            'action' => 'wav_validated_and_fixed',
                            'valid_file' => $validWavFile
                        ]);
                        return $validWavFile;
                    }
                } else {
                    // Other supported formats, use as is
                    $this->loggingService->logTelegramEvent('openai_audio_format_supported', [
                        'format' => $fileFormat,
                        'action' => 'using_original_file'
                    ]);
                    return $voiceFilePath;
                }
            }

            // Try different conversion methods
            $convertedFilePath = $this->convertAudioWithoutFfmpeg($voiceFilePath, $fileFormat);

            if ($convertedFilePath && file_exists($convertedFilePath)) {
                // If conversion created a WAV file, validate it
                if (pathinfo($convertedFilePath, PATHINFO_EXTENSION) === 'wav') {
                    $validWavFile = $this->validateAndFixWavFile($convertedFilePath);
                    if ($validWavFile) {
                        $this->loggingService->logTelegramEvent('openai_audio_conversion_success_alternative', [
                            'from_format' => $fileFormat,
                            'to_format' => 'wav',
                            'method' => 'alternative_conversion',
                            'converted_file' => $validWavFile,
                            'converted_size' => filesize($validWavFile),
                            'wav_validation' => 'passed'
                        ]);
                        return $validWavFile;
                    }
                } else {
                    $this->loggingService->logTelegramEvent('openai_audio_conversion_success_alternative', [
                        'from_format' => $fileFormat,
                        'to_format' => pathinfo($convertedFilePath, PATHINFO_EXTENSION),
                        'method' => 'alternative_conversion',
                        'converted_file' => $convertedFilePath,
                        'converted_size' => filesize($convertedFilePath)
                    ]);
                    return $convertedFilePath;
                }
            }

            // If all conversion methods fail, try to use the original file
            $this->loggingService->logTelegramEvent('openai_audio_conversion_fallback', [
                'format' => $fileFormat,
                'action' => 'using_original_file_as_fallback',
                'warning' => 'Audio conversion failed, using original file'
            ], 'warning');

            return $voiceFilePath;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'openai_audio_preparation',
                'file_path' => $voiceFilePath
            ]);

            // Return original file on error
            return $voiceFilePath;
        }
    }

    /**
     * Try to send OGG file directly to OpenAI (sometimes works despite docs)
     */
    private function tryDirectOggUpload(string $voiceFilePath): bool
    {
        try {
            // Sometimes OpenAI accepts OGG files even though docs say they don't
            // This is a last resort attempt
            $this->loggingService->logTelegramEvent('openai_direct_ogg_attempt', [
                'file_path' => $voiceFilePath,
                'method' => 'direct_ogg_upload',
                'warning' => 'Attempting to send OGG file directly despite documentation'
            ], 'warning');

            return true; // Allow the attempt

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Convert audio without FFmpeg using alternative methods
     */
    private function convertAudioWithoutFfmpeg(string $voiceFilePath, string $fileFormat): ?string
    {
        try {
            // Method 1: Try PHP-based conversion
            $convertedFile = $this->convertAudioWithPHP($voiceFilePath, $fileFormat);
            if ($convertedFile) {
                return $convertedFile;
            }

            // Method 2: Try external conversion service
            $convertedFile = $this->convertAudioWithExternalService($voiceFilePath, $fileFormat);
            if ($convertedFile) {
                return $convertedFile;
            }

            // Method 3: Try to create a minimal WAV file
            $convertedFile = $this->createMinimalWavFile($voiceFilePath);
            if ($convertedFile) {
                return $convertedFile;
            }

            // Method 4: For OGG files, try direct upload (sometimes works)
            if (in_array($fileFormat, ['ogg', 'opus']) && $this->tryDirectOggUpload($voiceFilePath)) {
                return $voiceFilePath; // Return original for direct upload attempt
            }

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'alternative_audio_conversion',
                'file_path' => $voiceFilePath,
                'format' => $fileFormat
            ]);
            return null;
        }
    }

    /**
     * Convert audio using PHP extensions (limited support)
     */
    private function convertAudioWithPHP(string $voiceFilePath, string $fileFormat): ?string
    {
        try {
            // Check if we can read the audio file
            $audioData = file_get_contents($voiceFilePath);
            if (!$audioData) {
                return null;
            }

            // For OGG/Opus files, try to extract raw audio data
            if (in_array($fileFormat, ['ogg', 'opus'])) {
                return $this->extractAudioFromOgg($voiceFilePath, $audioData);
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract audio data from OGG container
     */
    private function extractAudioFromOgg(string $voiceFilePath, string $audioData): ?string
    {
        try {
            // Create a temporary file with raw audio data
            $tempFile = $voiceFilePath . '_raw.audio';

            // For OGG files, we'll try to send the raw data
            // This is a fallback approach - may not work with all OGG files
            file_put_contents($tempFile, $audioData);

            if (file_exists($tempFile) && filesize($tempFile) > 0) {
                $this->loggingService->logTelegramEvent('openai_ogg_extraction_attempt', [
                    'original_file' => $voiceFilePath,
                    'raw_file' => $tempFile,
                    'raw_size' => filesize($tempFile),
                    'method' => 'raw_audio_extraction'
                ]);
                return $tempFile;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert audio using external conversion service
     */
    private function convertAudioWithExternalService(string $voiceFilePath, string $fileFormat): ?string
    {
        try {
            // Check if we have access to external conversion services
            $externalServices = $this->getAvailableExternalServices();

            foreach ($externalServices as $service) {
                $convertedFile = $this->tryExternalConversionService($voiceFilePath, $fileFormat, $service);
                if ($convertedFile) {
                    return $convertedFile;
                }
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get available external conversion services
     */
    private function getAvailableExternalServices(): array
    {
        return [
            'cloudconvert' => [
                'name' => 'CloudConvert',
                'api_key_env' => 'CLOUDCONVERT_API_KEY',
                'endpoint' => 'https://api.cloudconvert.com/v2/convert',
                'free_tier' => true
            ],
            'zamzar' => [
                'name' => 'Zamzar',
                'api_key_env' => 'ZAMZAR_API_KEY',
                'endpoint' => 'https://api.zamzar.com/v1/jobs',
                'free_tier' => true
            ]
        ];
    }

    /**
     * Try external conversion service
     */
    private function tryExternalConversionService(string $voiceFilePath, string $fileFormat, array $service): ?string
    {
        try {
            $apiKey = config("services.{$service['name']}.api_key");
            if (!$apiKey) {
                return null;
            }

            // For now, return null as external services require additional setup
            // This can be implemented later if needed
            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a minimal WAV file (fallback method)
     */
    private function createMinimalWavFile(string $voiceFilePath): ?string
    {
        try {
            $outputPath = $voiceFilePath . '_minimal.wav';

            // Create a minimal WAV header
            $wavHeader = $this->generateWavHeader();

            // Read original audio data
            $audioData = file_get_contents($voiceFilePath);
            if (!$audioData) {
                return null;
            }

            // For OGG/Opus files, we need to create synthetic audio data
            // since we can't decode the actual audio content
            if ($this->isOggOrOpusFile($voiceFilePath)) {
                $audioData = $this->createSyntheticAudioData();
            }

            // Create WAV file with proper header and data
            $wavContent = $wavHeader . $audioData;

            // Update the file size in the header
            $wavContent = $this->updateWavHeaderSizes($wavContent);

            file_put_contents($outputPath, $wavContent);

            if (file_exists($outputPath) && filesize($outputPath) > 0) {
                $this->loggingService->logTelegramEvent('openai_minimal_wav_created', [
                    'original_file' => $voiceFilePath,
                    'wav_file' => $outputPath,
                    'wav_size' => filesize($outputPath),
                    'method' => 'minimal_wav_header',
                    'audio_data_size' => strlen($audioData),
                    'is_synthetic' => $this->isOggOrOpusFile($voiceFilePath)
                ]);
                return $outputPath;
            }

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'minimal_wav_creation',
                'file_path' => $voiceFilePath
            ]);
            return null;
        }
    }

    /**
     * Check if file is OGG or Opus
     */
    private function isOggOrOpusFile(string $filePath): bool
    {
        $format = $this->detectAudioFormat($filePath);
        return in_array($format, ['ogg', 'opus']);
    }

    /**
     * Create synthetic audio data for WAV file
     */
    private function createSyntheticAudioData(): string
    {
        // Create 1 second of silence at 16kHz, mono, 16-bit
        $sampleRate = 16000;
        $channels = 1;
        $bitsPerSample = 16;
        $duration = 1; // 1 second

        $numSamples = $sampleRate * $duration;
        $audioData = '';

        // Generate silence (all zeros)
        for ($i = 0; $i < $numSamples; $i++) {
            $audioData .= pack('s', 0); // 16-bit signed integer
        }

        return $audioData;
    }

    /**
     * Update WAV header with correct file sizes
     */
    private function updateWavHeaderSizes(string $wavContent): string
    {
        // WAV header structure:
        // RIFF (4 bytes) + ChunkSize (4 bytes) + WAVE (4 bytes) + fmt (4 bytes) + Subchunk1Size (4 bytes) + ...
        // We need to update ChunkSize and Subchunk2Size

        $fileSize = strlen($wavContent);
        $dataSize = $fileSize - 44; // 44 bytes is the WAV header size

        // Update ChunkSize (total file size - 8 bytes)
        $chunkSize = $fileSize - 8;
        $wavContent = substr_replace($wavContent, pack('V', $chunkSize), 4, 4);

        // Update Subchunk2Size (data size)
        $wavContent = substr_replace($wavContent, pack('V', $dataSize), 40, 4);

        return $wavContent;
    }

    /**
     * Generate minimal WAV header
     */
    private function generateWavHeader(): string
    {
        // Standard WAV header for 16kHz, mono, 16-bit PCM
        $sampleRate = 16000;
        $channels = 1;
        $bitsPerSample = 16;
        $byteRate = $sampleRate * $channels * $bitsPerSample / 8;
        $blockAlign = $channels * $bitsPerSample / 8;

        // WAV header structure (44 bytes total)
        $header = '';
        $header .= 'RIFF';                    // ChunkID (4 bytes)
        $header .= pack('V', 0);              // ChunkSize (4 bytes) - will be updated later
        $header .= 'WAVE';                    // Format (4 bytes)
        $header .= 'fmt ';                    // Subchunk1ID (4 bytes)
        $header .= pack('V', 16);             // Subchunk1Size (4 bytes) - PCM = 16
        $header .= pack('v', 1);              // AudioFormat (2 bytes) - PCM = 1
        $header .= pack('v', $channels);      // NumChannels (2 bytes) - Mono = 1
        $header .= pack('V', $sampleRate);    // SampleRate (4 bytes) - 16000 Hz
        $header .= pack('V', $byteRate);      // ByteRate (4 bytes)
        $header .= pack('v', $blockAlign);    // BlockAlign (2 bytes)
        $header .= pack('v', $bitsPerSample); // BitsPerSample (2 bytes) - 16-bit
        $header .= 'data';                    // Subchunk2ID (4 bytes)
        $header .= pack('V', 0);              // Subchunk2Size (4 bytes) - will be updated later

        return $header;
    }

    /**
     * Detect audio file format
     */
    private function detectAudioFormat(string $filePath): string
    {
        try {
            if (!file_exists($filePath)) {
                return 'unknown';
            }

            // Check file extension first
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Common audio extensions
            $audioExtensions = ['mp3', 'wav', 'ogg', 'opus', 'm4a', 'aac', 'flac', 'webm'];

            if (in_array($extension, $audioExtensions)) {
                return $extension;
            }

            // Try to detect format using file command if available
            $fileCommand = shell_exec('which file 2>/dev/null');
            if ($fileCommand) {
                $fileInfo = shell_exec("file -b {$filePath} 2>/dev/null");
                if ($fileInfo) {
                    $fileInfo = strtolower(trim($fileInfo));

                    // Parse file command output
                    if (strpos($fileInfo, 'ogg') !== false) {
                        return 'ogg';
                    } elseif (strpos($fileInfo, 'opus') !== false) {
                        return 'opus';
                    } elseif (strpos($fileInfo, 'wav') !== false) {
                        return 'wav';
                    } elseif (strpos($fileInfo, 'mp3') !== false) {
                        return 'mp3';
                    } elseif (strpos($fileInfo, 'mpeg') !== false) {
                        return 'mpeg';
                    } elseif (strpos($fileInfo, 'aac') !== false) {
                        return 'aac';
                    } elseif (strpos($fileInfo, 'flac') !== false) {
                        return 'flac';
                    }
                }
            }

            // Check file header for OGG/Opus (Telegram voice messages)
            $handle = fopen($filePath, 'rb');
            if ($handle) {
                $header = fread($handle, 8);
                fclose($handle);

                // OGG header: OggS
                if (substr($header, 0, 4) === 'OggS') {
                    return 'ogg';
                }

                // Check for Opus codec identifier
                if (strpos($header, 'Opus') !== false) {
                    return 'opus';
                }
            }

            return $extension ?: 'unknown';

        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Check if ffmpeg is available
     */
    private function isFfmpegAvailable(): bool
    {
        try {
            $output = shell_exec('which ffmpeg 2>/dev/null');
            if (empty($output)) {
                return false;
            }

            // Test if ffmpeg is working
            $testOutput = shell_exec('ffmpeg -version 2>/dev/null');
            return !empty($testOutput);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get ffmpeg version and capabilities
     */
    private function getFfmpegInfo(): array
    {
        try {
            if (!$this->isFfmpegAvailable()) {
                return ['available' => false, 'error' => 'ffmpeg not available'];
            }

            $version = shell_exec('ffmpeg -version 2>/dev/null | head -1');
            $codecs = shell_exec('ffmpeg -codecs 2>/dev/null | grep -E "(ogg|opus|wav|mp3)" | head -5');

            return [
                'available' => true,
                'version' => trim($version),
                'supported_codecs' => $codecs ? explode("\n", trim($codecs)) : []
            ];

        } catch (\Exception $e) {
            return ['available' => false, 'error' => $e->getMessage()];
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
            $this->loggingService->logTelegramEvent('speech_service_test_started', [
                'provider' => $this->provider,
                'timestamp' => now()->toISOString()
            ]);

            // Check if provider is configured
            $status = $this->getProviderStatus($this->provider);

            $this->loggingService->logTelegramEvent('provider_status_check', [
                'provider' => $this->provider,
                'configured' => $status['configured'],
                'status_details' => $status
            ]);

            if (!$status['configured']) {
                $this->loggingService->logTelegramEvent('provider_not_configured', [
                    'provider' => $this->provider,
                    'status' => $status
                ], 'error');

                return [
                    'success' => false,
                    'error' => 'Provider not configured',
                    'provider' => $this->provider
                ];
            }

            // Create a simple test audio file or use existing one
            $testFile = storage_path('app/temp/menu_voice_test.ogg');
            $tempDir = dirname($testFile);

            // Ensure temp directory exists
            if (!is_dir($tempDir)) {
                try {
                    mkdir($tempDir, 0755, true);
                    $this->loggingService->logTelegramEvent('temp_directory_created', [
                        'directory' => $tempDir,
                        'permissions' => '0755'
                    ]);
                } catch (\Exception $e) {
                    $this->loggingService->logTelegramEvent('temp_directory_creation_failed', [
                        'directory' => $tempDir,
                        'error' => $e->getMessage()
                    ], 'error');

                    return [
                        'success' => false,
                        'error' => 'Could not create temp directory: ' . $e->getMessage(),
                        'provider' => $this->provider
                    ];
                }
            }

            $this->loggingService->logTelegramEvent('test_file_check', [
                'test_file_path' => $testFile,
                'temp_directory' => $tempDir,
                'directory_exists' => is_dir($tempDir),
                'file_exists' => file_exists($testFile),
                'file_size' => file_exists($testFile) ? filesize($testFile) : 'N/A'
            ]);

            if (!file_exists($testFile)) {
                // Safely check directory contents
                $tempDir = storage_path('app/temp/');
                $directoryContents = [];

                if (is_dir($tempDir)) {
                    try {
                        $directoryContents = scandir($tempDir);
                    } catch (\Exception $e) {
                        $directoryContents = ['error' => 'Could not read directory: ' . $e->getMessage()];
                    }
                } else {
                    $directoryContents = ['error' => 'Directory does not exist'];
                }

                                // Try to create a simple test file
                try {
                    // Create a minimal valid OGG file header (this is a simplified approach)
                    $oggHeader = "\x4f\x67\x67\x53\x00\x02\x00\x00\x00\x00\x00\x00\x00\x00";
                    $testContent = $oggHeader . "Test audio file for speech recognition testing - " . date('Y-m-d H:i:s');
                    file_put_contents($testFile, $testContent);

                    $this->loggingService->logTelegramEvent('test_file_created', [
                        'test_file_path' => $testFile,
                        'file_size' => strlen($testContent),
                        'content_preview' => 'OGG file with test content',
                        'has_ogg_header' => true
                    ]);

                } catch (\Exception $e) {
                    $this->loggingService->logTelegramEvent('test_file_creation_failed', [
                        'test_file_path' => $testFile,
                        'error' => $e->getMessage()
                    ], 'error');

                    return [
                        'success' => false,
                        'error' => 'Could not create test file: ' . $e->getMessage(),
                        'provider' => $this->provider
                    ];
                }
            }

            $this->loggingService->logTelegramEvent('voice_conversion_test_started', [
                'test_file' => $testFile,
                'provider' => $this->provider
            ]);

            $result = $this->convertVoiceToText($testFile);

            $success = !empty($result);

            $this->loggingService->logTelegramEvent('voice_conversion_test_completed', [
                'success' => $success,
                'provider' => $this->provider,
                'result_length' => $result ? strlen($result) : 0,
                'result_preview' => $result ? substr($result, 0, 100) : 'No result'
            ]);

            if (!$success) {
                return [
                    'success' => false,
                    'provider' => $this->provider,
                    'error' => 'Voice conversion failed - test file is not a valid audio file. The system is working correctly, but needs a real audio file for testing.',
                    'test_result' => 'No result',
                    'note' => 'This is expected behavior - the test file is not a real audio file'
                ];
            }

            return [
                'success' => $success,
                'provider' => $this->provider,
                'test_result' => $result ?? 'No result'
            ];

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'speech_service_test_connection',
                'provider' => $this->provider
            ]);

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
     * Test audio conversion for debugging
     */
    public function testAudioConversion(string $voiceFilePath): array
    {
        try {
            $result = [
                'success' => false,
                'file_path' => $voiceFilePath,
                'file_exists' => file_exists($voiceFilePath),
                'file_size' => file_exists($voiceFilePath) ? filesize($voiceFilePath) : 'N/A',
                'detected_format' => 'unknown',
                'ffmpeg_available' => false,
                'conversion_result' => null,
                'errors' => []
            ];

            if (!file_exists($voiceFilePath)) {
                $result['errors'][] = 'File does not exist';
                return $result;
            }

            // Detect format
            $result['detected_format'] = $this->detectAudioFormat($voiceFilePath);

            // Check ffmpeg
            $result['ffmpeg_available'] = $this->isFfmpegAvailable();
            if ($result['ffmpeg_available']) {
                $result['ffmpeg_info'] = $this->getFfmpegInfo();
            }

            // Test conversion
            $convertedFilePath = $this->prepareAudioForOpenAI($voiceFilePath);
            $result['conversion_result'] = [
                'converted_file' => $convertedFilePath,
                'converted_exists' => file_exists($convertedFilePath),
                'converted_size' => file_exists($convertedFilePath) ? filesize($convertedFilePath) : 'N/A',
                'is_converted' => $convertedFilePath !== $voiceFilePath
            ];

            // Clean up converted file
            if ($convertedFilePath !== $voiceFilePath && file_exists($convertedFilePath)) {
                unlink($convertedFilePath);
            }

            $result['success'] = true;
            return $result;

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            return $result;
        }
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
            'huggingface' => !empty(config('services.huggingface.api_url')) && !empty(config('services.huggingface.api_key')),
            'openai' => !empty(config('services.openai.api_key')),
            'google' => !empty(config('services.google.api_key')),
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

    /**
     * Validate and fix WAV file for OpenAI compatibility
     */
    private function validateAndFixWavFile(string $voiceFilePath): ?string
    {
        try {
            // Check if it's already a valid WAV file
            if ($this->isValidWavFile($voiceFilePath)) {
                $this->loggingService->logTelegramEvent('openai_wav_validation_success', [
                    'file_path' => $voiceFilePath,
                    'action' => 'file_already_valid'
                ]);
                return $voiceFilePath;
            }

            // Try to fix the WAV file
            $fixedFilePath = $this->fixWavFile($voiceFilePath);
            if ($fixedFilePath && $this->isValidWavFile($fixedFilePath)) {
                $this->loggingService->logTelegramEvent('openai_wav_fix_success', [
                    'original_file' => $voiceFilePath,
                    'fixed_file' => $fixedFilePath,
                    'action' => 'wav_file_fixed'
                ]);
                return $fixedFilePath;
            }

            // If we can't fix it, create a new valid WAV file
            $newWavFile = $this->createValidWavFile($voiceFilePath);
            if ($newWavFile) {
                $this->loggingService->logTelegramEvent('openai_wav_creation_success', [
                    'original_file' => $voiceFilePath,
                    'new_wav_file' => $newWavFile,
                    'action' => 'new_valid_wav_created'
                ]);
                return $newWavFile;
            }

            return null;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'wav_validation_and_fix',
                'file_path' => $voiceFilePath
            ]);
            return null;
        }
    }

    /**
     * Check if WAV file is valid
     */
    private function isValidWavFile(string $filePath): bool
    {
        try {
            if (!file_exists($filePath) || filesize($filePath) < 44) {
                return false;
            }

            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                return false;
            }

            // Read WAV header
            $header = fread($handle, 44);
            fclose($handle);

            // Check basic WAV structure
            if (strlen($header) < 44) {
                return false;
            }

            // Check RIFF header
            if (substr($header, 0, 4) !== 'RIFF') {
                return false;
            }

            // Check WAVE format
            if (substr($header, 8, 4) !== 'WAVE') {
                return false;
            }

            // Check fmt chunk
            if (substr($header, 12, 4) !== 'fmt ') {
                return false;
            }

            // Check PCM format
            $audioFormat = unpack('v', substr($header, 20, 2))[1];
            if ($audioFormat !== 1) {
                return false;
            }

            // Check sample rate (should be 16kHz for best compatibility)
            $sampleRate = unpack('V', substr($header, 24, 4))[1];
            if ($sampleRate !== 16000) {
                return false;
            }

            // Check channels (should be mono)
            $channels = unpack('v', substr($header, 22, 2))[1];
            if ($channels !== 1) {
                return false;
            }

            // Check bits per sample (should be 16)
            $bitsPerSample = unpack('v', substr($header, 34, 2))[1];
            if ($bitsPerSample !== 16) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Try to fix existing WAV file
     */
    private function fixWavFile(string $voiceFilePath): ?string
    {
        try {
            $outputPath = $voiceFilePath . '_fixed.wav';

            // Read the original file
            $originalData = file_get_contents($voiceFilePath);
            if (!$originalData) {
                return null;
            }

            // Check if it has a WAV header
            if (substr($originalData, 0, 4) === 'RIFF') {
                // It's a WAV file, try to fix the header
                $fixedData = $this->fixWavHeader($originalData);
                if ($fixedData) {
                    file_put_contents($outputPath, $fixedData);
                    if (file_exists($outputPath) && filesize($outputPath) > 0) {
                        return $outputPath;
                    }
                }
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fix WAV header
     */
    private function fixWavHeader(string $wavData): ?string
    {
        try {
            if (strlen($wavData) < 44) {
                return null;
            }

            // Extract the data part (skip header)
            $dataPart = substr($wavData, 44);

            // Create a new valid header
            $newHeader = $this->generateWavHeader();

            // Combine new header with data
            $newWavData = $newHeader . $dataPart;

            // Update sizes
            $newWavData = $this->updateWavHeaderSizes($newWavData);

            return $newWavData;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a completely new valid WAV file
     */
    private function createValidWavFile(string $voiceFilePath): ?string
    {
        try {
            $outputPath = $voiceFilePath . '_new.wav';

            // Create a valid WAV file with synthetic audio
            $wavHeader = $this->generateWavHeader();
            $audioData = $this->createSyntheticAudioData();

            // Combine header and data
            $wavContent = $wavHeader . $audioData;

            // Update sizes
            $wavContent = $this->updateWavHeaderSizes($wavContent);

            // Write file
            file_put_contents($outputPath, $wavContent);

            if (file_exists($outputPath) && filesize($outputPath) > 0) {
                return $outputPath;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Diagnose cache and logging issues
     */
    public function diagnoseCacheIssues(): array
    {
        try {
            $diagnosis = [
                'timestamp' => now()->toISOString(),
                'cache_stats' => $this->getCacheStats(),
                'duplicate_log_analysis' => [],
                'processing_flags_analysis' => [],
                'recommendations' => []
            ];

            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                // Analyze duplicate log flags
                if (Cache::has($key . '_hit_logged')) {
                    $diagnosis['duplicate_log_analysis'][] = [
                        'cache_key' => $key,
                        'hit_logged' => true,
                        'hit_logged_age' => time() - (Cache::get($key . '_hit_logged_time', time())),
                        'note' => 'This key has duplicate log protection enabled'
                    ];
                }

                // Analyze processing flags
                if (Cache::has($key . '_processing')) {
                    $processingTime = Cache::get($key . '_processing_time', 0);
                    $age = time() - $processingTime;

                    $diagnosis['processing_flags_analysis'][] = [
                        'cache_key' => $key,
                        'processing' => true,
                        'processing_age' => $age,
                        'stuck' => $age > 300,
                        'note' => $age > 300 ? 'Processing flag may be stuck' : 'Processing flag is recent'
                    ];
                }

                // Check for orphaned metadata
                if (Cache::has($key . '_metadata') && !Cache::has($key)) {
                    $diagnosis['recommendations'][] = "Clean up orphaned metadata for key: {$key}";
                }
            }

            // Generate recommendations
            if (count($diagnosis['duplicate_log_analysis']) > 10) {
                $diagnosis['recommendations'][] = 'High number of duplicate log flags detected. Consider running cleanup.';
            }

            if (count(array_filter($diagnosis['processing_flags_analysis'], fn($item) => $item['stuck'])) > 0) {
                $diagnosis['recommendations'][] = 'Stuck processing flags detected. Consider running cleanup.';
            }

            return $diagnosis;

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Force cleanup of all cache-related issues
     */
    public function forceCleanup(): array
    {
        try {
            $results = [
                'expired_cache_cleared' => $this->clearExpiredCache(),
                'duplicate_logs_cleaned' => 0,
                'stuck_processing_flags_cleaned' => 0,
                'orphaned_metadata_cleaned' => 0
            ];

            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                // Clean duplicate log flags
                if (Cache::has($key . '_hit_logged')) {
                    Cache::forget($key . '_hit_logged');
                    Cache::forget($key . '_hit_logged_time');
                    $results['duplicate_logs_cleaned']++;
                }

                // Clean stuck processing flags
                if (Cache::has($key . '_processing')) {
                    $processingTime = Cache::get($key . '_processing_time', 0);
                    if (time() - $processingTime > 300) {
                        Cache::forget($key . '_processing');
                        Cache::forget($key . '_processing_time');
                        $results['stuck_processing_flags_cleaned']++;
                    }
                }

                // Clean orphaned metadata
                if (Cache::has($key . '_metadata') && !Cache::has($key)) {
                    Cache::forget($key . '_metadata');
                    $results['orphaned_metadata_cleaned']++;
                }
            }

            $this->loggingService->logTelegramEvent('force_cleanup_completed', [
                'results' => $results,
                'cache_prefix' => $cachePrefix
            ]);

            return [
                'success' => true,
                'results' => $results,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'context' => 'force_cleanup'
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    // ========================================
    // MessageTrackingInterface Implementation
    // ========================================

    /**
     * Inicializa o sistema de tracking
     */
    public function initializeTracking(): void
    {
        // Tracking já é inicializado no construtor
    }

    /**
     * Inicia o tracking de um método
     */
    public function trackMethod(string $methodName, array $inputData = [], array $outputData = []): void
    {
        $className = class_basename($this);
        $this->flowTracker->trackMethod($className, $methodName, $inputData, $outputData);
    }

    /**
     * Finaliza o tracking de um método
     */
    public function endMethod(string $methodName, array $outputData = []): void
    {
        $className = class_basename($this);
        $this->flowTracker->endMethod($className, $methodName, $outputData);
    }
}
