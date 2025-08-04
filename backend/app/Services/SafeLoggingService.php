<?php

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SafeLoggingService implements LoggingServiceInterface
{
    private LoggingServiceInterface $primaryLogger;
    private bool $useFallback = false;

    public function __construct(LoggingServiceInterface $primaryLogger)
    {
        $this->primaryLogger = $primaryLogger;
    }

    /**
     * Execute logging with fallback to Laravel Log facade
     */
    private function safeExecute(callable $primaryOperation, callable $fallbackOperation, string $fallbackMessage, array $fallbackContext = []): void
    {
        if ($this->useFallback) {
            $fallbackOperation();
            return;
        }

        try {
            $primaryOperation();
        } catch (\Throwable $e) {
            // Log the error with Laravel Log facade
            Log::error('LoggingService failed, using fallback', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'original_message' => $fallbackMessage,
                'original_context' => $fallbackContext,
            ]);

            // Execute fallback
            $fallbackOperation();

            // Mark as using fallback for subsequent calls
            $this->useFallback = true;
        }
    }

    /**
     * Log API requests and responses
     */
    public function logApiRequest(Request $request, array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logApiRequest($request, $context),
            fn() => Log::channel('api')->info('API Request', $this->prepareApiRequestData($request, $context)),
            'API Request',
            $context
        );
    }

    /**
     * Log API responses
     */
    public function logApiResponse(int $statusCode, array $response, float $duration, array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logApiResponse($statusCode, $response, $duration, $context),
            fn() => Log::channel('api')->info('API Response', $this->prepareApiResponseData($statusCode, $response, $duration, $context)),
            'API Response',
            $context
        );
    }

    /**
     * Log business operations
     */
    public function logBusinessOperation(string $operation, array $data, string $status = 'success', array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logBusinessOperation($operation, $data, $status, $context),
            fn() => Log::channel('business')->info('Business Operation', $this->prepareBusinessData($operation, $data, $status, $context)),
            'Business Operation',
            $context
        );
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logSecurityEvent($event, $data, $level, $context),
            fn() => Log::channel('security')->$level('Security Event', $this->prepareSecurityData($event, $data, $level, $context)),
            'Security Event',
            $context
        );
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metrics = [], array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logPerformance($operation, $duration, $metrics, $context),
            fn() => Log::channel('performance')->info('Performance Metric', $this->preparePerformanceData($operation, $duration, $metrics, $context)),
            'Performance Metric',
            $context
        );
    }

    /**
     * Log audit trail
     */
    public function logAudit(string $action, string $model, int $modelId, array $changes = [], array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logAudit($action, $model, $modelId, $changes, $context),
            fn() => Log::channel('audit')->info('Audit Trail', $this->prepareAuditData($action, $model, $modelId, $changes, $context)),
            'Audit Trail',
            $context
        );
    }

    /**
     * Log Telegram bot events
     */
    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logTelegramEvent($event, $data, $level, $context),
            fn() => Log::channel('telegram')->$level('Telegram Event', $this->prepareTelegramData($event, $data, $level, $context)),
            'Telegram Event',
            $context
        );
    }

    /**
     * Log WhatsApp events
     */
    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logWhatsAppEvent($event, $data, $level, $context),
            fn() => Log::channel('whatsapp')->$level('WhatsApp Event', $this->prepareWhatsAppData($event, $data, $level, $context)),
            'WhatsApp Event',
            $context
        );
    }

    /**
     * Log exceptions with context
     */
    public function logException(\Throwable $exception, array $context = []): void
    {
        $this->safeExecute(
            fn() => $this->primaryLogger->logException($exception, $context),
            fn() => Log::channel('api')->error('Exception', $this->prepareExceptionData($exception, $context)),
            'Exception',
            $context
        );
    }

    /**
     * Get log statistics
     */
    public function getLogStats(): array
    {
        try {
            return $this->primaryLogger->getLogStats();
        } catch (\Throwable $e) {
            Log::error('Failed to get log stats', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }

    // Helper methods to prepare data for fallback logging

    private function prepareApiRequestData(Request $request, array $context): array
    {
        return array_merge([
            'request_id' => uniqid('api_', true),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareApiResponseData(int $statusCode, array $response, float $duration, array $context): array
    {
        return array_merge([
            'status_code' => $statusCode,
            'duration_ms' => round($duration, 2),
            'response_size' => strlen(json_encode($response)),
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareBusinessData(string $operation, array $data, string $status, array $context): array
    {
        return array_merge([
            'operation' => $operation,
            'status' => $status,
            'user_id' => Auth::id(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareSecurityData(string $event, array $data, string $level, array $context): array
    {
        return array_merge([
            'event' => $event,
            'level' => $level,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function preparePerformanceData(string $operation, float $duration, array $metrics, array $context): array
    {
        return array_merge([
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'metrics' => $metrics,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareAuditData(string $action, string $model, int $modelId, array $changes, array $context): array
    {
        return array_merge([
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'user_id' => Auth::id(),
            'changes' => $changes,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareTelegramData(string $event, array $data, string $level, array $context): array
    {
        return array_merge([
            'event' => $event,
            'level' => $level,
            'chat_id' => $data['chat_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareWhatsAppData(string $event, array $data, string $level, array $context): array
    {
        return array_merge([
            'event' => $event,
            'level' => $level,
            'phone' => $data['phone'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);
    }

    private function prepareExceptionData(\Throwable $exception, array $context): array
    {
        return array_merge([
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => Auth::id(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ], $context);
    }
}
