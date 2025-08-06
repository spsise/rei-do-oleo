<?php

namespace App\Traits;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Log;

trait HasSafeLogging
{
    /**
     * Get the logging service instance
     */
    protected function getLogger(): LoggingServiceInterface
    {
        return app(LoggingServiceInterface::class);
    }

    /**
     * Log info message with fallback
     */
    protected function logInfo(string $message, array $context = []): void
    {
        try {
            $this->getLogger()->logBusinessOperation('info', ['message' => $message], 'success', $context);
        } catch (\Throwable $e) {
            Log::info($message, $context);
        }
    }

    /**
     * Log error message with fallback
     */
    protected function logError(string $message, array $context = []): void
    {
        try {
            $this->getLogger()->logBusinessOperation('error', ['message' => $message], 'failed', $context);
        } catch (\Throwable $e) {
            Log::error($message, $context);
        }
    }

    /**
     * Log warning message with fallback
     */
    protected function logWarning(string $message, array $context = []): void
    {
        try {
            $this->getLogger()->logBusinessOperation('warning', ['message' => $message], 'warning', $context);
        } catch (\Throwable $e) {
            Log::warning($message, $context);
        }
    }

    /**
     * Log debug message with fallback
     */
    protected function logDebug(string $message, array $context = []): void
    {
        try {
            $this->getLogger()->logBusinessOperation('debug', ['message' => $message], 'success', $context);
        } catch (\Throwable $e) {
            Log::debug($message, $context);
        }
    }

    /**
     * Log exception with fallback
     */
    protected function logException(\Throwable $exception, array $context = []): void
    {
        try {
            $this->getLogger()->logException($exception, $context);
        } catch (\Throwable $e) {
            Log::error('Exception: ' . $exception->getMessage(), array_merge($context, [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]));
        }
    }

    /**
     * Log security event with fallback
     */
    protected function logSecurityEvent(string $event, array $data, string $level = 'warning'): void
    {
        try {
            $this->getLogger()->logSecurityEvent($event, $data, $level);
        } catch (\Throwable $e) {
            Log::channel('security')->$level('Security Event: ' . $event, $data);
        }
    }

    /**
     * Log performance metric with fallback
     */
    protected function logPerformance(string $operation, float $duration, array $metrics = []): void
    {
        try {
            $this->getLogger()->logPerformance($operation, $duration, $metrics);
        } catch (\Throwable $e) {
            Log::channel('performance')->info('Performance: ' . $operation, [
                'duration_ms' => round($duration, 2),
                'metrics' => $metrics,
            ]);
        }
    }

    /**
     * Log Telegram event with fallback
     */
    protected function logTelegramEvent(string $event, array $data, string $level = 'info'): void
    {
        try {
            $this->getLogger()->logTelegramEvent($event, $data, $level);
        } catch (\Throwable $e) {
            Log::channel('telegram')->$level('Telegram Event: ' . $event, $data);
        }
    }

    /**
     * Log WhatsApp event with fallback
     */
    protected function logWhatsAppEvent(string $event, array $data, string $level = 'info'): void
    {
        try {
            $this->getLogger()->logWhatsAppEvent($event, $data, $level);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->$level('WhatsApp Event: ' . $event, $data);
        }
    }
}
