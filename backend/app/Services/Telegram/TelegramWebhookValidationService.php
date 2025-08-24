<?php

namespace App\Services\Telegram;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookValidationService
{
    private const CACHE_PREFIX = 'telegram_update_processed_';
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Check if webhook is duplicate and mark as processing
     */
    public function validateAndMarkProcessing(?int $updateId, bool $skipDuplicateCheck = false): bool
    {
        if (!$updateId) {
            return true; // No update_id, allow processing
        }

        // Skip duplicate check if requested (useful for testing)
        if ($skipDuplicateCheck) {
            $this->markRequestAsProcessing($updateId);
            return true;
        }

        // Check if already processed
        if ($this->isDuplicateRequest($updateId)) {
            $this->logDuplicateWebhook($updateId);
            return false;
        }

        // Mark as processing to prevent race conditions
        $this->markRequestAsProcessing($updateId);
        return true;
    }

    /**
     * Check if request is duplicate (quick cache check)
     */
    private function isDuplicateRequest(int $updateId): bool
    {
        try {
            $cacheKey = $this->getCacheKey($updateId);
            return Cache::has($cacheKey);
        } catch (\Exception $e) {
            // If cache fails, log but don't block processing
            $this->loggingService->logException($e, [
                'operation' => 'check_duplicate_update_id',
                'update_id' => $updateId
            ]);
            return false;
        }
    }

    /**
     * Mark request as being processed (to prevent race conditions)
     */
    private function markRequestAsProcessing(int $updateId): void
    {
        try {
            $cacheKey = $this->getCacheKey($updateId);
            Cache::put($cacheKey, true, self::CACHE_TTL);
        } catch (\Exception $e) {
            // If cache fails, log but don't block processing
            $this->loggingService->logException($e, [
                'operation' => 'mark_update_id_processed',
                'update_id' => $updateId
            ]);
        }
    }

    /**
     * Get cache key for update_id
     */
    private function getCacheKey(int $updateId): string
    {
        return self::CACHE_PREFIX . $updateId;
    }

    /**
     * Log duplicate webhook detection
     */
    private function logDuplicateWebhook(int $updateId): void
    {
        $this->loggingService->logTelegramEvent('duplicate_webhook_ignored_early', [
            'update_id' => $updateId,
            'message' => 'Duplicate webhook detected and ignored before processing'
        ], 'info');
    }

    /**
     * Clean up processed update_id (useful for testing or manual cleanup)
     */
    public function cleanupProcessedUpdate(int $updateId): bool
    {
        try {
            $cacheKey = $this->getCacheKey($updateId);
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'cleanup_processed_update_id',
                'update_id' => $updateId
            ]);
            return false;
        }
    }

    /**
     * Get cache statistics for monitoring
     */
    public function getCacheStats(): array
    {
        try {
            // This is a simplified approach - in production you might want more sophisticated stats
            return [
                'cache_driver' => config('cache.default'),
                'cache_prefix' => self::CACHE_PREFIX,
                'cache_ttl' => self::CACHE_TTL,
                'note' => 'Use Redis or Memcached for better performance and monitoring'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to get cache stats: ' . $e->getMessage()
            ];
        }
    }
}
