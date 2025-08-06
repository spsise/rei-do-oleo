<?php

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileLoggingService implements LoggingServiceInterface
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/custom');

        // Create log directory if it doesn't exist
        if (!File::exists($this->logPath)) {
            File::makeDirectory($this->logPath, 0755, true);
        }
    }

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
            'timestamp' => now()->toISOString(),
        ], $context);

        $this->writeToFile('api_requests.log', $logData);
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

        $this->writeToFile('api_responses.log', $logData);
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
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);

        $this->writeToFile('business_operations.log', $logData);
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'level' => $level,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);

        $this->writeToFile('security_events.log', $logData);
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

        $this->writeToFile('performance_metrics.log', $logData);
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

        $this->writeToFile('audit_trail.log', $logData);
    }

    /**
     * Log Telegram bot events
     */
    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'level' => $level,
            'chat_id' => $data['chat_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);

        $this->writeToFile('telegram_events.log', $logData);
    }

    /**
     * Log WhatsApp events
     */
    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'level' => $level,
            'phone' => $data['phone'] ?? null,
            'message_type' => $data['message_type'] ?? null,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $context);

        $this->writeToFile('whatsapp_events.log', $logData);
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

        $this->writeToFile('exceptions.log', $logData);
    }

    /**
     * Get log statistics
     */
    public function getLogStats(): array
    {
        $logFiles = [
            'api_requests' => $this->logPath . '/api_requests.log',
            'api_responses' => $this->logPath . '/api_responses.log',
            'business_operations' => $this->logPath . '/business_operations.log',
            'security_events' => $this->logPath . '/security_events.log',
            'performance_metrics' => $this->logPath . '/performance_metrics.log',
            'audit_trail' => $this->logPath . '/audit_trail.log',
            'telegram_events' => $this->logPath . '/telegram_events.log',
            'whatsapp_events' => $this->logPath . '/whatsapp_events.log',
            'exceptions' => $this->logPath . '/exceptions.log',
        ];

        $stats = [];
        foreach ($logFiles as $channel => $file) {
            if (File::exists($file)) {
                $stats[$channel] = [
                    'size' => File::size($file),
                    'last_modified' => File::lastModified($file),
                    'lines' => count(File::lines($file)),
                ];
            }
        }

        return $stats;
    }

    /**
     * Write log data to file
     */
    private function writeToFile(string $filename, array $data): void
    {
        $filePath = $this->logPath . '/' . $filename;
        $logEntry = json_encode($data) . "\n";

        File::append($filePath, $logEntry);
    }
}
