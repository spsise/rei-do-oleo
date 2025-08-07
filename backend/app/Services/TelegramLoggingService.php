<?php

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use App\Repositories\TelegramRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramLoggingService implements LoggingServiceInterface
{
    public function __construct(
        private TelegramRepository $telegramRepository
    ) {}

    /**
     * Log webhook processing
     */
    public function logWebhookProcessing(array $payload, array $result): void
    {
        // Store in repository for statistics
        $this->telegramRepository->storeWebhookLog($payload, $result);

        // Log to Laravel logs
        $this->logToLaravelLogs($payload, $result);
    }

    /**
     * Log to Laravel logs
     */
    private function logToLaravelLogs(array $payload, array $result): void
    {
        $status = $result['status'] ?? 'unknown';
        $chatId = $this->extractChatId($payload);
        $messageType = $this->getMessageType($payload);

        $logContext = [
            'chat_id' => $chatId,
            'message_type' => $messageType,
            'status' => $status,
            'success' => $result['success'] ?? false,
        ];

        if ($status === 'success' && ($result['success'] ?? false)) {
            Log::info('Telegram webhook processed successfully', $logContext);
        } elseif ($status === 'ignored') {
            Log::info('Telegram webhook ignored', $logContext);
        } else {
            Log::error('Telegram webhook processing failed', array_merge($logContext, [
                'error' => $result['error'] ?? 'Unknown error',
                'message' => $result['message'] ?? 'No error message'
            ]));
        }
    }

    /**
     * Log callback query processing
     */
    public function logCallbackQuery(array $callbackQuery, array $result): void
    {
        $callbackQueryId = $callbackQuery['id'] ?? '';
        $chatId = $callbackQuery['message']['chat']['id'] ?? '';
        $callbackData = $callbackQuery['data'] ?? '';

        $logContext = [
            'callback_query_id' => $callbackQueryId,
            'chat_id' => $chatId,
            'callback_data' => $callbackData,
            'success' => $result['success'] ?? false,
        ];

        if ($result['success'] ?? false) {
            Log::info('Telegram callback query processed successfully', $logContext);
        } else {
            Log::error('Telegram callback query processing failed', array_merge($logContext, [
                'error' => $result['error'] ?? 'Unknown error'
            ]));
        }
    }

    /**
     * Log message processing
     */
    public function logMessageProcessing(array $message, array $result): void
    {
        $chatId = $message['chat']['id'] ?? '';
        $text = $message['text'] ?? '';

        $logContext = [
            'chat_id' => $chatId,
            'text' => $text,
            'success' => $result['success'] ?? false,
        ];

        if ($result['success'] ?? false) {
            Log::info('Telegram message processed successfully', $logContext);
        } else {
            Log::error('Telegram message processing failed', array_merge($logContext, [
                'error' => $result['error'] ?? 'Unknown error'
            ]));
        }
    }

    /**
     * Log webhook setup operations
     */
    public function logWebhookSetup(string $operation, array $data, bool $success): void
    {
        $logContext = [
            'operation' => $operation,
            'success' => $success,
            'data' => $data,
        ];

        if ($success) {
            Log::info("Telegram webhook {$operation} successful", $logContext);
        } else {
            Log::error("Telegram webhook {$operation} failed", $logContext);
        }
    }

    /**
     * Log bot test operations
     */
    public function logBotTest(array $result): void
    {
        $logContext = [
            'success' => $result['success'] ?? false,
            'sent_to' => $result['sent_to'] ?? 0,
            'total_recipients' => $result['total_recipients'] ?? 0,
        ];

        if ($result['success'] ?? false) {
            Log::info('Telegram bot test completed successfully', $logContext);
        } else {
            Log::error('Telegram bot test failed', array_merge($logContext, [
                'error' => $result['message'] ?? 'Unknown error'
            ]));
        }
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(): array
    {
        return $this->telegramRepository->getWebhookStats();
    }

    /**
     * Get recent webhook logs
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->telegramRepository->getRecentWebhookLogs($limit);
    }

    /**
     * Extract chat ID from payload
     */
    private function extractChatId(array $payload): ?string
    {
        if (isset($payload['message']['chat']['id'])) {
            return (string) $payload['message']['chat']['id'];
        }

        if (isset($payload['callback_query']['message']['chat']['id'])) {
            return (string) $payload['callback_query']['message']['chat']['id'];
        }

        return null;
    }

    /**
     * Get message type from payload
     */
    private function getMessageType(array $payload): string
    {
        if (isset($payload['callback_query'])) {
            return 'callback_query';
        }

        if (isset($payload['message']['text'])) {
            return 'text';
        }

        if (isset($payload['message']['voice'])) {
            return 'voice';
        }

        if (isset($payload['message']['audio'])) {
            return 'audio';
        }

        if (isset($payload['message'])) {
            return 'message';
        }

        return 'unknown';
    }

    /**
     * Log voice processing
     */
    public function logVoiceProcessing(array $message, array $result): void
    {
        $chatId = $message['chat']['id'] ?? null;
        $voice = $message['voice'] ?? null;
        $recognizedText = $result['recognized_text'] ?? null;

        $logContext = [
            'chat_id' => $chatId,
            'message_type' => 'voice',
            'voice_duration' => $voice['duration'] ?? null,
            'voice_file_id' => $voice['file_id'] ?? null,
            'recognized_text' => $recognizedText,
            'text_length' => strlen($recognizedText ?? ''),
            'success' => $result['success'] ?? false,
            'processing_time' => $result['processing_time'] ?? null
        ];

        if ($result['success'] ?? false) {
            Log::info('Voice message processed successfully', $logContext);
        } else {
            Log::error('Voice message processing failed', array_merge($logContext, [
                'error' => $result['error'] ?? 'Unknown error'
            ]));
        }

        // Store in repository for analytics
        // $this->telegramRepository->storeVoiceProcessingLog($message, $result); // TODO: Implement in repository
    }

    // Interface LoggingServiceInterface methods

    public function logApiRequest(Request $request, array $context = []): void
    {
        Log::info('API Request', array_merge([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $context));
    }

    public function logApiResponse(int $statusCode, array $response, float $duration, array $context = []): void
    {
        Log::info('API Response', array_merge([
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'response_size' => strlen(json_encode($response)),
        ], $context));
    }

    public function logBusinessOperation(string $operation, array $data, string $status = 'success', array $context = []): void
    {
        $logMethod = $status === 'success' ? 'info' : 'warning';
        Log::$logMethod('Business Operation: ' . $operation, array_merge($data, $context));
    }

    public function logSecurityEvent(string $event, array $data, string $level = 'warning', array $context = []): void
    {
        Log::warning('Security Event: ' . $event, array_merge($data, $context));
    }

    public function logPerformance(string $operation, float $duration, array $metrics = [], array $context = []): void
    {
        Log::info('Performance: ' . $operation, array_merge([
            'duration_ms' => round($duration * 1000, 2),
        ], $metrics, $context));
    }

    public function logAudit(string $action, string $model, int $modelId, array $changes = [], array $context = []): void
    {
        Log::info('Audit: ' . $action, array_merge([
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
        ], $context));
    }

    public function logTelegramEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        Log::$level('Telegram Event: ' . $event, array_merge($data, $context));
    }

    public function logWhatsAppEvent(string $event, array $data, string $level = 'info', array $context = []): void
    {
        Log::$level('WhatsApp Event: ' . $event, array_merge($data, $context));
    }

    public function logException(\Throwable $exception, array $context = []): void
    {
        Log::error('Exception: ' . $exception->getMessage(), array_merge([
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $context));
    }

    public function getLogStats(): array
    {
        return $this->getWebhookStats();
    }
}
