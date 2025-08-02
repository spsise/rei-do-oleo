<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\Channels\TelegramChannel;

class TelegramMessageProcessorService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramLoggingService $loggingService,
        private TelegramChannel $telegramChannel,
        private SpeechToTextService $speechService
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

            // Process different message types
            if (isset($message['text'])) {
                return $this->processTextMessage($message);
            }

            if (isset($message['voice'])) {
                return $this->processVoiceMessage($message);
            }

            if (isset($message['audio'])) {
                return $this->processAudioMessage($message);
            }

            return $this->createIgnoredResult('Unsupported message type');

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
     * Process text message
     */
    private function processTextMessage(array $message): array
    {
        $result = $this->telegramBotService->processMessage($message);
        $result['status'] = 'success';
        $result['message'] = 'Text message processed';
        $result['input_type'] = 'text';

        $this->loggingService->logWebhookProcessing(['message' => $message], $result);
        $this->loggingService->logMessageProcessing($message, $result);

        return $result;
    }

    /**
     * Process voice message
     */
    private function processVoiceMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $voice = $message['voice'];

            // Send processing message
            $this->telegramChannel->sendTextMessage(
                "🎤 Processando mensagem de voz...",
                (string) $chatId
            );

            // Download voice file
            $voiceFilePath = $this->downloadVoiceFile($voice['file_id']);

            if (!$voiceFilePath) {
                return $this->createErrorResult('Failed to download voice file');
            }

            // Convert voice to text
            $text = $this->speechService->convertVoiceToText($voiceFilePath);

            if (!$text) {
                return $this->createErrorResult('Failed to convert voice to text');
            }

            // Clean up voice file
            if (file_exists($voiceFilePath)) {
                unlink($voiceFilePath);
            }

            // Send recognized text to user
            $this->telegramChannel->sendTextMessage(
                "🎯 Texto reconhecido: *{$text}*",
                (string) $chatId
            );

            // Create synthetic message with recognized text
            $syntheticMessage = [
                'chat' => $message['chat'],
                'from' => $message['from'],
                'text' => $text,
                'voice_original' => $voice,
                'is_voice_converted' => true
            ];

            // Process as text message
            $result = $this->telegramBotService->processMessage($syntheticMessage);
            $result['status'] = 'success';
            $result['message'] = 'Voice message processed';
            $result['input_type'] = 'voice';
            $result['original_voice'] = $voice;
            $result['recognized_text'] = $text;

            $this->loggingService->logWebhookProcessing(['message' => $message], $result);
            // $this->loggingService->logVoiceProcessing($message, $result); // TODO: Implement in repository

            return $result;

        } catch (\Exception $e) {
            return $this->createErrorResult('Voice processing error', $e->getMessage());
        }
    }

    /**
     * Process audio message
     */
    private function processAudioMessage(array $message): array
    {
        // Similar to voice processing but for audio files
        return $this->processVoiceMessage($message);
    }

    /**
     * Download voice file from Telegram
     */
    private function downloadVoiceFile(string $fileId): ?string
    {
        try {
            // Get file info from Telegram
            $fileInfo = $this->telegramChannel->getFile($fileId);

            if (!$fileInfo['success']) {
                return null;
            }

            $filePath = $fileInfo['file_path'];
            $fileName = basename($filePath);
            $localPath = storage_path("app/temp/voice_{$fileName}");

            // Ensure temp directory exists
            if (!is_dir(dirname($localPath))) {
                mkdir(dirname($localPath), 0755, true);
            }

            // Download file
            $fileUrl = "https://api.telegram.org/file/bot" . config('services.telegram.bot_token') . "/{$filePath}";
            $fileContent = file_get_contents($fileUrl);

            if ($fileContent === false) {
                return null;
            }

            file_put_contents($localPath, $fileContent);

            return $localPath;

        } catch (\Exception $e) {
            Log::error('Voice file download error', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);

            return null;
        }
    }

    private function createIgnoredResult(string $message): array
    {
        return [
            'success' => false,
            'status' => 'ignored',
            'message' => $message
        ];
    }

    private function createErrorResult(string $message, string $error = ''): array
    {
        return [
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'error' => $error
        ];
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
