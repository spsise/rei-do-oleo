<?php

namespace App\Services;

use App\Repositories\TelegramRepository;
use Illuminate\Support\Facades\Log;

class TelegramLoggingService
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
}
