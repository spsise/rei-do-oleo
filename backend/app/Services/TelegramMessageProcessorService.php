<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\Channels\TelegramChannel;

class TelegramMessageProcessorService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramLoggingService $loggingService,
        private TelegramChannel $telegramChannel
    ) {}

    /**
     * Process webhook payload
     */
    public function processWebhookPayload(array $payload): array
    {
        try {
            // Check if it's a callback query (button click)
            if (isset($payload['callback_query'])) {
                return $this->processCallbackQuery($payload['callback_query']);
            }

            // Verify if it's a message
            if (!isset($payload['message'])) {
                $result = [
                    'success' => false,
                    'status' => 'ignored',
                    'message' => 'No message in payload'
                ];

                $this->loggingService->logWebhookProcessing($payload, $result);
                return $result;
            }

            $message = $payload['message'];

            // Check if it's a text message
            if (!isset($message['text'])) {
                $result = [
                    'success' => false,
                    'status' => 'ignored',
                    'message' => 'No text in message'
                ];

                $this->loggingService->logWebhookProcessing($payload, $result);
                return $result;
            }

            // Process the message
            $result = $this->telegramBotService->processMessage($message);
            $result['status'] = 'success';
            $result['message'] = 'Message processed';

            // Log the processing
            $this->loggingService->logWebhookProcessing($payload, $result);
            $this->loggingService->logMessageProcessing($message, $result);

            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ];

            $this->loggingService->logWebhookProcessing($payload, $result);

            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $result;
        }
    }

    /**
     * Process callback query from inline keyboard buttons
     */
    private function processCallbackQuery(array $callbackQuery): array
    {
        try {
            $callbackQueryId = $callbackQuery['id'] ?? '';
            $chatId = $callbackQuery['message']['chat']['id'] ?? '';
            $callbackData = $callbackQuery['data'] ?? '';

            // Answer the callback query to remove loading state
            $this->telegramChannel->answerCallbackQuery($callbackQueryId);

            // Process the callback query
            $result = $this->telegramBotService->processCallbackQuery($callbackQuery);
            $result['status'] = 'success';
            $result['message'] = 'Callback query processed';

            // Log the processing
            $this->loggingService->logCallbackQuery($callbackQuery, $result);

            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ];

            $this->loggingService->logCallbackQuery($callbackQuery, $result);

            Log::error('Telegram callback query error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $result;
        }
    }
}
