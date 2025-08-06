<?php

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\Activitylog\ActivityLogger;

class ActivityLoggingService implements LoggingServiceInterface
{
    /**
     * Check if a specific log type should be recorded
     */
    private function shouldLog(string $type, string $operation = ''): bool
    {
        $filters = config('unified-logging.filters', []);

        return $filters[$type] ?? false;
    }

    /**
     * Log API requests and responses
     */
    public function logApiRequest(Request $request, array $context = []): void
    {
        if (!$this->shouldLog('api_requests')) {
            return;
        }

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'request_id' => uniqid('api_', true),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'body' => $request->method() !== 'GET' ? $this->sanitizeBody($request->all()) : null,
                'context' => $context,
                'log_type' => 'api_requests',
            ])
            ->log('API Request: ' . $request->method() . ' ' . $request->path());
    }

    /**
     * Log API responses
     */
    public function logApiResponse(int $statusCode, array $response, float $duration, array $context = []): void
    {
        if (!$this->shouldLog('api_responses')) {
            return;
        }

        $logData = [
            'status_code' => $statusCode,
            'duration_ms' => round($duration, 2),
            'response_size' => strlen(json_encode($response)),
            'context' => $context,
        ];

        if ($statusCode >= 400) {
            $logData['error_response'] = $response;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(array_merge($logData, ['log_type' => 'api_responses']))
            ->log('API Response: ' . $statusCode . ' (' . round($duration, 2) . 'ms)');
    }

    /**
     * Log business operations
     */
    public function logBusinessOperation(string $operation, array $data, string $status = 'success', array $context = []): void
    {
        if (!$this->shouldLog('business_operations')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'operation' => $operation,
                'status' => $status,
                'data' => $this->sanitizeBusinessData($data),
                'context' => $context,
                'log_type' => 'business_operations',
            ])
            ->log('Business Operation: ' . $operation . ' (' . $status . ')');
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void
    {
        if (!$this->shouldLog('security_events')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'event' => $event,
                'level' => $level,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => $this->sanitizeSecurityData($data),
                'context' => $context,
                'log_type' => 'security_events',
            ])
            ->log('Security Event: ' . $event . ' (' . $level . ')');
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $metrics = [], array $context = []): void
    {
        if (!$this->shouldLog('performance')) {
            return;
        }

        $logData = [
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'metrics' => $metrics,
            'context' => $context,
        ];

        $description = 'Performance: ' . $operation . ' (' . round($duration, 2) . 'ms)';

        $slowThreshold = config('unified-logging.performance.slow_operation_threshold', 1000);
        $criticalThreshold = config('unified-logging.performance.critical_operation_threshold', 5000);

        if ($duration > $criticalThreshold) {
            $description .= ' [CRITICAL]';
        } elseif ($duration > $slowThreshold) {
            $description .= ' [SLOW]';
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(array_merge($logData, ['log_type' => 'performance']))
            ->log($description);
    }

    /**
     * Log audit trail
     */
    public function logAudit(string $action, string $model, int $modelId, array $changes = [], array $context = []): void
    {
        if (!$this->shouldLog('audit')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => $action,
                'model' => $model,
                'model_id' => $modelId,
                'changes' => $changes,
                'context' => $context,
                'log_type' => 'audit_trail',
            ])
            ->log('Audit: ' . $action . ' on ' . $model . ' (ID: ' . $modelId . ')');
    }

    /**
     * Log Telegram bot events
     */
    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        if (!$this->shouldLog('telegram')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'event' => $event,
                'level' => $level,
                'chat_id' => $data['chat_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'message_type' => $data['message_type'] ?? null,
                'data' => $this->sanitizeTelegramData($data),
                'context' => $context,
                'log_type' => 'telegram_events',
            ])
            ->log('Telegram Event: ' . $event . ' (' . $level . ')');
    }

    /**
     * Log WhatsApp events
     */
    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        if (!$this->shouldLog('whatsapp')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'event' => $event,
                'level' => $level,
                'phone' => $data['phone'] ?? null,
                'message_type' => $data['message_type'] ?? null,
                'data' => $this->sanitizeWhatsAppData($data),
                'context' => $context,
                'log_type' => 'whatsapp_events',
            ])
            ->log('WhatsApp Event: ' . $event . ' (' . $level . ')');
    }

    /**
     * Log exceptions with context
     */
    public function logException(\Throwable $exception, array $context = []): void
    {
        if (!$this->shouldLog('exceptions')) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'request_url' => request()->fullUrl(),
                'request_method' => request()->method(),
                'context' => $context,
                'log_type' => 'exceptions',
            ])
            ->log('Exception: ' . get_class($exception) . ' - ' . $exception->getMessage());
    }

    /**
     * Get log statistics from Activity Log
     */
    public function getLogStats(): array
    {
        $activityModel = \Spatie\Activitylog\Models\Activity::class;

        $stats = [
            'total_logs' => $activityModel::count(),
            'logs_by_type' => $activityModel::select('log_name', DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'log_name')
                ->toArray(),
            'recent_activity' => $activityModel::latest()
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'log_name' => $activity->log_name,
                        'log_type' => $activity->properties['log_type'] ?? 'default',
                        'created_at' => $activity->created_at->toISOString(),
                    ];
                })
                ->toArray(),
        ];

        return $stats;
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = config('unified-logging.sanitization.sensitive_headers', [
            'authorization', 'cookie', 'x-csrf-token', 'x-api-key'
        ]);

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
        $sensitiveFields = config('unified-logging.sanitization.sensitive_fields', [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'secret', 'credit_card', 'ssn'
        ]);

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
        $sensitiveFields = config('unified-logging.sanitization.sensitive_integration_fields.telegram', [
            'token', 'webhook_secret'
        ]);

        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * Sanitize WhatsApp data
     */
    private function sanitizeWhatsAppData(array $data): array
    {
        $sensitiveFields = config('unified-logging.sanitization.sensitive_integration_fields.whatsapp', [
            'token', 'webhook_secret'
        ]);

        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
