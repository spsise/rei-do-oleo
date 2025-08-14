<?php

namespace App\Services\Telegram\Commands\Cache;

use Illuminate\Support\Facades\Cache;
use App\Contracts\Telegram\Commands\CommandMatch;
use Illuminate\Support\Facades\Log;

class CommandCache
{
    private const CACHE_PREFIX = 'telegram_command_cache';
    private const DEFAULT_TTL = 1800; // 30 minutes
    private const MAX_CACHE_SIZE = 1000;

    public function get(string $input, array $context = []): ?CommandMatch
    {
        $cacheKey = $this->generateCacheKey($input, $context);

        try {
            $cached = Cache::get($cacheKey);

            if ($cached) {
                $this->recordCacheHit($input);
                return $cached;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve command from cache', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    public function put(string $input, array $context, CommandMatch $result, ?int $ttl = null): void
    {
        $cacheKey = $this->generateCacheKey($input, $context);
        $ttl = $ttl ?? $this->calculateTTL($result);

        try {
            // Check cache size before adding
            $this->manageCacheSize();

            Cache::put($cacheKey, $result, $ttl);

            $this->recordCacheMiss($input);

            // Store metadata for analytics
            $this->storeCacheMetadata($input, $context, $result, $ttl);

        } catch (\Exception $e) {
            Log::warning('Failed to store command in cache', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function clear(): void
    {
        try {
            $keys = Cache::get(self::CACHE_PREFIX . '_keys', []);

            foreach ($keys as $key) {
                Cache::forget($key);
            }

            Cache::forget(self::CACHE_PREFIX . '_keys');
            Cache::forget(self::CACHE_PREFIX . '_metadata');
            Cache::forget(self::CACHE_PREFIX . '_stats');

        } catch (\Exception $e) {
            Log::warning('Failed to clear command cache', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function clearByPattern(string $pattern): void
    {
        try {
            $keys = Cache::get(self::CACHE_PREFIX . '_keys', []);
            $filteredKeys = array_filter($keys, function ($key) use ($pattern) {
                return str_contains($key, $pattern);
            });

            foreach ($filteredKeys as $key) {
                Cache::forget($key);
            }

            // Update keys list
            $remainingKeys = array_diff($keys, $filteredKeys);
            Cache::put(self::CACHE_PREFIX . '_keys', $remainingKeys, 86400);

        } catch (\Exception $e) {
            Log::warning('Failed to clear command cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getStats(): array
    {
        try {
            $stats = Cache::get(self::CACHE_PREFIX . '_stats', [
                'hits' => 0,
                'misses' => 0,
                'size' => 0,
                'hit_rate' => 0.0
            ]);

            $stats['size'] = $this->getCacheSize();
            $stats['hit_rate'] = $stats['hits'] + $stats['misses'] > 0
                ? round(($stats['hits'] / ($stats['hits'] + $stats['misses'])) * 100, 2)
                : 0.0;

            return $stats;

        } catch (\Exception $e) {
            Log::warning('Failed to get cache stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'hits' => 0,
                'misses' => 0,
                'size' => 0,
                'hit_rate' => 0.0,
                'error' => $e->getMessage()
            ];
        }
    }

    public function warmUp(array $commands): void
    {
        try {
            foreach ($commands as $command) {
                $aliases = $command->getAliases();
                $naturalLanguage = $command->getNaturalLanguage();

                // Cache common inputs
                foreach ($aliases as $alias) {
                    $this->warmUpInput($alias, $command);
                }

                foreach ($naturalLanguage as $pattern) {
                    $this->warmUpInput($pattern, $command);
                }
            }

            Log::info('Command cache warmed up successfully', [
                'commands_count' => count($commands)
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to warm up command cache', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generateCacheKey(string $input, array $context): string
    {
        $contextHash = md5(serialize($context));
        $inputHash = md5(strtolower(trim($input)));

        return self::CACHE_PREFIX . ':' . $inputHash . ':' . $contextHash;
    }

    private function calculateTTL(CommandMatch $result): int
    {
        $confidence = $result->getConfidence();

        // Higher confidence = longer cache time
        if ($confidence >= 0.9) {
            return 3600; // 1 hour
        } elseif ($confidence >= 0.7) {
            return 1800; // 30 minutes
        } else {
            return 900; // 15 minutes
        }
    }

    private function manageCacheSize(): void
    {
        $currentSize = $this->getCacheSize();

        if ($currentSize >= self::MAX_CACHE_SIZE) {
            $this->evictOldestEntries();
        }
    }

    private function getCacheSize(): int
    {
        try {
            $keys = Cache::get(self::CACHE_PREFIX . '_keys', []);
            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function evictOldestEntries(): void
    {
        try {
            $metadata = Cache::get(self::CACHE_PREFIX . '_metadata', []);

            // Sort by last accessed time
            uasort($metadata, function ($a, $b) {
                return $a['last_accessed'] <=> $b['last_accessed'];
            });

            // Remove oldest 20% of entries
            $removeCount = (int) (count($metadata) * 0.2);
            $keysToRemove = array_slice(array_keys($metadata), 0, $removeCount);

            foreach ($keysToRemove as $key) {
                Cache::forget($key);
                unset($metadata[$key]);
            }

            Cache::put(self::CACHE_PREFIX . '_metadata', $metadata, 86400);

        } catch (\Exception $e) {
            Log::warning('Failed to evict cache entries', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function recordCacheHit(string $input): void
    {
        $this->updateStats('hits');
        $this->updateLastAccessed($input);
    }

    private function recordCacheMiss(string $input): void
    {
        $this->updateStats('misses');
    }

    private function updateStats(string $type): void
    {
        try {
            $stats = Cache::get(self::CACHE_PREFIX . '_stats', [
                'hits' => 0,
                'misses' => 0
            ]);

            $stats[$type]++;
            Cache::put(self::CACHE_PREFIX . '_stats', $stats, 86400);

        } catch (\Exception $e) {
            // Silently fail for stats updates
        }
    }

    private function updateLastAccessed(string $input): void
    {
        try {
            $metadata = Cache::get(self::CACHE_PREFIX . '_metadata', []);
            $inputHash = md5(strtolower(trim($input)));

            if (isset($metadata[$inputHash])) {
                $metadata[$inputHash]['last_accessed'] = time();
                $metadata[$inputHash]['access_count']++;
            }

            Cache::put(self::CACHE_PREFIX . '_metadata', $metadata, 86400);

        } catch (\Exception $e) {
            // Silently fail for metadata updates
        }
    }

    private function storeCacheMetadata(string $input, array $context, CommandMatch $result, int $ttl): void
    {
        try {
            $metadata = Cache::get(self::CACHE_PREFIX . '_metadata', []);
            $inputHash = md5(strtolower(trim($input)));

            $metadata[$inputHash] = [
                'input' => $input,
                'context' => $context,
                'command_id' => $result->getCommand()->getId(),
                'confidence' => $result->getConfidence(),
                'ttl' => $ttl,
                'created_at' => time(),
                'last_accessed' => time(),
                'access_count' => 1
            ];

            // Store keys list for management
            $keys = Cache::get(self::CACHE_PREFIX . '_keys', []);
            $keys[] = $inputHash;
            $keys = array_unique($keys);

            Cache::put(self::CACHE_PREFIX . '_keys', $keys, 86400);
            Cache::put(self::CACHE_PREFIX . '_metadata', $metadata, 86400);

        } catch (\Exception $e) {
            Log::warning('Failed to store cache metadata', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function warmUpInput(string $input, $command): void
    {
        // This would create a mock CommandMatch for warming up
        // Implementation depends on how you want to handle this
    }
}
