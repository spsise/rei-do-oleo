<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpeechToTextService;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ManageSpeechCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'speech:cache
                            {action : Action to perform (clear, stats, cleanup, reset)}
                            {--force : Force the action without confirmation}
                            {--older-than= : Clear cache entries older than X minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage speech-to-text cache to prevent audio processing issues';

    /**
     * Execute the console command.
     */
    public function handle(SpeechToTextService $speechService, LoggingServiceInterface $loggingService): int
    {
        $action = $this->argument('action');
        $force = $this->option('force');
        $olderThan = $this->option('older-than');

        $this->info("🎤 Speech Cache Management - Action: {$action}");
        $this->newLine();

        try {
            switch ($action) {
                case 'clear':
                    return $this->clearAllCache($speechService, $force);

                case 'stats':
                    return $this->showCacheStats($speechService);

                case 'cleanup':
                    return $this->cleanupExpiredCache($speechService, $olderThan);

                case 'reset':
                    return $this->resetCache($speechService, $force);

                default:
                    $this->error("❌ Invalid action: {$action}");
                    $this->info("Available actions: clear, stats, cleanup, reset");
                    return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());

            $loggingService->logException($e, [
                'context' => 'speech_cache_management_command',
                'action' => $action,
                'command' => 'speech:cache'
            ]);

            return 1;
        }
    }

    /**
     * Clear all speech cache
     */
    private function clearAllCache(SpeechToTextService $speechService, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('⚠️  This will clear ALL speech cache. Are you sure?')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        $this->info('🧹 Clearing all speech cache...');

        $result = $speechService->clearAllSpeechCache();

        if ($result) {
            $this->info('✅ All speech cache cleared successfully.');

            // Also clear Laravel cache entries that might be related
            $this->clearLaravelCache();

            return 0;
        } else {
            $this->error('❌ Failed to clear speech cache.');
            return 1;
        }
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(SpeechToTextService $speechService): int
    {
        $this->info('📊 Speech Cache Statistics');
        $this->newLine();

        $stats = $speechService->getCacheStats();

        if (isset($stats['error'])) {
            $this->error("❌ Error getting stats: " . $stats['error']);
            return 1;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', $stats['total_entries']],
                ['Active Entries', $stats['active_entries']],
                ['Expired Entries', $stats['expired_entries']],
                ['Total Text Size', $stats['total_size'] . ' characters'],
                ['Oldest Entry', $stats['oldest_entry'] ? date('Y-m-d H:i:s', $stats['oldest_entry']) : 'N/A'],
                ['Newest Entry', $stats['newest_entry'] ? date('Y-m-d H:i:s', $stats['newest_entry']) : 'N/A'],
            ]
        );

        // Show cache configuration
        $this->newLine();
        $this->info('⚙️  Cache Configuration');

        $config = [
            ['Setting', 'Value'],
            ['Cache TTL', config('services.speech.cache_ttl', 1800) . ' seconds'],
            ['Max Age', config('services.speech.cache_max_age', 1800) . ' seconds'],
            ['Provider', config('services.speech.provider', 'vosk')],
        ];

        $this->table(['Setting', 'Value'], $config);

        return 0;
    }

    /**
     * Cleanup expired cache entries
     */
    private function cleanupExpiredCache(SpeechToTextService $speechService, ?string $olderThan): int
    {
        $this->info('🧹 Cleaning up expired speech cache...');

        if ($olderThan) {
            $minutes = (int) $olderThan;
            $this->info("🗑️  Clearing entries older than {$minutes} minutes...");

            // Custom cleanup for older entries
            $clearedCount = $this->clearOlderCacheEntries($minutes);
        } else {
            // Use service method
            $clearedCount = $speechService->clearExpiredCache();
        }

        $this->info("✅ Cleanup completed. Cleared {$clearedCount} expired entries.");

        return 0;
    }

    /**
     * Reset cache completely
     */
    private function resetCache(SpeechToTextService $speechService, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('⚠️  This will reset ALL speech cache and clear temp files. Are you sure?')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔄 Resetting speech cache and cleaning temp files...');

        // Clear speech cache
        $speechService->clearAllSpeechCache();

        // Clear Laravel cache
        $this->clearLaravelCache();

        // Clean temp files
        $tempFilesCleared = $this->cleanTempFiles();

        $this->info("✅ Reset completed successfully.");
        $this->info("🗑️  Temp files cleared: {$tempFilesCleared}");

        return 0;
    }

    /**
     * Clear Laravel cache entries
     */
    private function clearLaravelCache(): void
    {
        try {
            // Clear specific cache tags if they exist
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['speech', 'voice', 'audio'])->flush();
            }

            // Clear cache entries with specific prefixes
            $this->clearCacheByPrefix('voice_to_text_');
            $this->clearCacheByPrefix('speech_');
            $this->clearCacheByPrefix('audio_');

        } catch (\Exception $e) {
            $this->warn("⚠️  Warning: Could not clear some Laravel cache entries: " . $e->getMessage());
        }
    }

    /**
     * Clear cache entries by prefix
     */
    private function clearCacheByPrefix(string $prefix): void
    {
        try {
            // This is a simplified approach - in production you might want to use Redis SCAN or similar
            $keys = Cache::get($prefix . 'keys', []);

            foreach ($keys as $key) {
                if (str_starts_with($key, $prefix)) {
                    Cache::forget($key);
                }
            }

            Cache::forget($prefix . 'keys');

        } catch (\Exception $e) {
            // Ignore errors for individual cache operations
        }
    }

    /**
     * Clear cache entries older than specified minutes
     */
    private function clearOlderCacheEntries(int $minutes): int
    {
        $clearedCount = 0;
        $cutoffTime = time() - ($minutes * 60);

        try {
            $cachePrefix = 'voice_to_text_';
            $keys = Cache::get($cachePrefix . 'keys', []);

            foreach ($keys as $key) {
                if (Cache::has($key)) {
                    $metadata = Cache::get($key . '_metadata');
                    if ($metadata && isset($metadata['cached_at'])) {
                        if ($metadata['cached_at'] < $cutoffTime) {
                            Cache::forget($key);
                            Cache::forget($key . '_metadata');
                            $clearedCount++;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $this->warn("⚠️  Warning: Error during custom cleanup: " . $e->getMessage());
        }

        return $clearedCount;
    }

    /**
     * Clean temporary audio files
     */
    private function cleanTempFiles(): int
    {
        $clearedCount = 0;
        $tempPath = storage_path('app/temp');

        try {
            if (!is_dir($tempPath)) {
                return 0;
            }

            $files = glob($tempPath . '/*.{ogg,wav,mp3,audio}', GLOB_BRACE);

            foreach ($files as $file) {
                if (is_file($file)) {
                    // Check if file is older than 1 hour
                    if (filemtime($file) < (time() - 3600)) {
                        unlink($file);
                        $clearedCount++;
                    }
                }
            }

        } catch (\Exception $e) {
            $this->warn("⚠️  Warning: Could not clean all temp files: " . $e->getMessage());
        }

        return $clearedCount;
    }
}
