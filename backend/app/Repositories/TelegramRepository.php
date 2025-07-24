<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class TelegramRepository
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'telegram:';

    /**
     * Get authorized users
     */
    public function getAuthorizedUsers(): array
    {
        return Config::get('services.telegram.recipients', []);
    }

    /**
     * Check if user is authorized
     */
    public function isUserAuthorized(int $chatId): bool
    {
        $authorizedUsers = $this->getAuthorizedUsers();
        return in_array($chatId, $authorizedUsers);
    }

    /**
     * Get bot configuration
     */
    public function getBotConfig(): array
    {
        return [
            'enabled' => Config::get('services.telegram.enabled', false),
            'bot_token' => Config::get('services.telegram.bot_token'),
            'recipients' => $this->getAuthorizedUsers(),
        ];
    }

    /**
     * Cache webhook info
     */
    public function cacheWebhookInfo(array $webhookInfo): void
    {
        Cache::put(
            self::CACHE_PREFIX . 'webhook_info',
            $webhookInfo,
            self::CACHE_TTL
        );
    }

    /**
     * Get cached webhook info
     */
    public function getCachedWebhookInfo(): ?array
    {
        return Cache::get(self::CACHE_PREFIX . 'webhook_info');
    }

    /**
     * Clear webhook cache
     */
    public function clearWebhookCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'webhook_info');
    }

    /**
     * Cache message processing result
     */
    public function cacheMessageResult(int $chatId, array $result): void
    {
        $key = self::CACHE_PREFIX . "message_result:{$chatId}";
        Cache::put($key, $result, self::CACHE_TTL);
    }

    /**
     * Get cached message result
     */
    public function getCachedMessageResult(int $chatId): ?array
    {
        $key = self::CACHE_PREFIX . "message_result:{$chatId}";
        return Cache::get($key);
    }

    /**
     * Store webhook log
     */
    public function storeWebhookLog(array $payload, array $result): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'payload' => $payload,
            'result' => $result,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in cache for recent logs
        $logs = Cache::get(self::CACHE_PREFIX . 'recent_logs', []);
        $logs[] = $logData;

        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }

        Cache::put(self::CACHE_PREFIX . 'recent_logs', $logs, self::CACHE_TTL);
    }

    /**
     * Get recent webhook logs
     */
    public function getRecentWebhookLogs(int $limit = 50): array
    {
        $logs = Cache::get(self::CACHE_PREFIX . 'recent_logs', []);
        return array_slice($logs, -$limit);
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(): array
    {
        $logs = $this->getRecentWebhookLogs(1000);

        $stats = [
            'total_requests' => count($logs),
            'successful_requests' => 0,
            'failed_requests' => 0,
            'ignored_requests' => 0,
            'message_requests' => 0,
            'callback_requests' => 0,
        ];

        foreach ($logs as $log) {
            $result = $log['result'] ?? [];
            $status = $result['status'] ?? 'unknown';

            switch ($status) {
                case 'success':
                    $stats['successful_requests']++;
                    break;
                case 'error':
                    $stats['failed_requests']++;
                    break;
                case 'ignored':
                    $stats['ignored_requests']++;
                    break;
            }

            // Count request types
            if (isset($log['payload']['message'])) {
                $stats['message_requests']++;
            }
            if (isset($log['payload']['callback_query'])) {
                $stats['callback_requests']++;
            }
        }

        return $stats;
    }
}
