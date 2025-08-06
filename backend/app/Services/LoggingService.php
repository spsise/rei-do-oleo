<?php

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Spatie\Activitylog\ActivityLogger;

class LoggingService implements LoggingServiceInterface
{
    /**
     * Log API requests and responses
     */
    public function logApiRequest(Request $request, array $context = []): void
    {
        $logData = array_merge([
            'request_id' => uniqid('api_', true),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'timestamp' => now()->toISOString(),
        ], $context);

        if ($request->method() !== 'GET') {
            $logData['body'] = $this->sanitizeBody($request->all());
        }

        Log::channel('api')->info('API Request', $logData);
    }

    /**
     * Log API responses
     */
    public function logApiResponse(int $statusCode, array $response, float $duration, array $context = []): void
    {
        $logData = array_merge([
            'status_code' => $statusCode,
            'duration_ms' => round($duration, 2),
            'response_size' => strlen(json_encode($response)),
            'timestamp' => now()->toISOString(),
        ], $context);

        if ($statusCode >= 400) {
            $logData['error_response'] = $response;
            Log::channel('api')->error('API Error Response', $logData);
        } else {
            Log::channel('api')->info('API Response', $logData);
        }
    }

    /**
     * Log business operations
     */
    public function logBusinessOperation(string $operation, array $data, string $status = 'success', array $context = []): void
    {
        $logData = array_merge([
            'operation' => $operation,
            'status' => $status,
            'user_id' => Auth::id(),
            'data' => $this->sanitizeBusinessData($data),
            'timestamp' => now()->toISOString(),
        ], $context);

        Log::channel('business')->info('Business Operation', $logData);
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $this->sanitizeSecurityData($data),
            'timestamp' => now()->toISOString(),
        ], $context);

        Log::channel('security')->$level('Security Event', $logData);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metrics = [], array $context = []): void
    {
        $logData = array_merge([
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'metrics' => $metrics,
            'timestamp' => now()->toISOString(),
        ], $context);

        if ($duration > 1000) { // Log slow operations as warnings
            Log::channel('performance')->warning('Slow Operation', $logData);
        } else {
            Log::channel('performance')->info('Performance Metric', $logData);
        }
    }

    /**
     * Log audit trail
     */
    public function logAudit(string $action, string $model, int $modelId, array $changes = [], array $context = []): void
    {
        $logData = array_merge([
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'user_id' => Auth::id(),
            'changes' => $changes,
            'timestamp' => now()->toISOString(),
        ], $context);

        Log::channel('audit')->info('Audit Trail', $logData);
    }

    /**
     * Log Telegram bot events
     */
    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'chat_id' => $data['chat_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $this->sanitizeTelegramData($data),
            'timestamp' => now()->toISOString(),
        ], $context);

        Log::channel('telegram')->$level('Telegram Event', $logData);
    }

    /**
     * Log WhatsApp events
     */
    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'phone' => $data['phone'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $this->sanitizeWhatsAppData($data),
            'timestamp' => now()->toISOString(),
        ], $context);

        Log::channel('whatsapp')->$level('WhatsApp Event', $logData);
    }

    /**
     * Log exceptions with context
     */
    public function logException(\Throwable $exception, array $context = []): void
    {
        $logData = array_merge([
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

        Log::channel('api')->error('Exception', $logData);
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token', 'x-api-key'];

        return collect($headers)->map(function ($value, $key) use ($sensitiveHeaders) {
            $key = strtolower($key);
            if (in_array($key, $sensitiveHeaders)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }

    /**
     * Sanitize request body to remove sensitive information
     */
    private function sanitizeBody(array $body): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'secret', 'credit_card', 'ssn'
        ];

        return collect($body)->map(function ($value, $key) use ($sensitiveFields) {
            if (in_array($key, $sensitiveFields)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }

    /**
     * Sanitize business data
     */
    private function sanitizeBusinessData(array $data): array
    {
        // Remove sensitive business data if needed
        return $data;
    }

    /**
     * Sanitize security data
     */
    private function sanitizeSecurityData(array $data): array
    {
        // Remove sensitive security data if needed
        return $data;
    }

    /**
     * Sanitize Telegram data
     */
    private function sanitizeTelegramData(array $data): array
    {
        // Remove sensitive Telegram data if needed
        unset($data['token'], $data['webhook_secret']);
        return $data;
    }

    /**
     * Sanitize WhatsApp data
     */
    private function sanitizeWhatsAppData(array $data): array
    {
        // Remove sensitive WhatsApp data if needed
        unset($data['token'], $data['webhook_secret']);
        return $data;
    }

    /**
     * Get log statistics
     */
    public function getLogStats(): array
    {
        $logFiles = [
            'api' => storage_path('logs/api.log'),
            'business' => storage_path('logs/business.log'),
            'security' => storage_path('logs/security.log'),
            'performance' => storage_path('logs/performance.log'),
            'telegram' => storage_path('logs/telegram.log'),
            'whatsapp' => storage_path('logs/whatsapp.log'),
            'audit' => storage_path('logs/audit.log'),
        ];

        $stats = [];
        foreach ($logFiles as $channel => $file) {
            if (file_exists($file)) {
                $stats[$channel] = [
                    'size' => filesize($file),
                    'last_modified' => filemtime($file),
                    'lines' => count(file($file)),
                ];
            }
        }

        return $stats;
    }
}
