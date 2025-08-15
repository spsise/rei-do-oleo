<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandResult;
use App\Services\Channels\TelegramChannel;
use App\Services\SpeechToTextService;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\Log;

class TelegramMessageProcessorService
{
    public function __construct(
        private UnifiedCommandSystem $commandSystem,
        private TelegramAuthorizationService $authorizationService,
        private ?SpeechToTextService $speechService = null,
        private ?TelegramChannel $telegramChannel = null,
        private ?LoggingServiceInterface $loggingService = null
    ) {}

    public function processMessage(array $payload): array
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

            if ($this->loggingService) {
                $this->loggingService->logException($e, [
                    'operation' => 'telegram_webhook_processing',
                    'payload' => $payload
                ]);
            }

            return $result;
        }
    }

    private function processTextMessage(array $message): array
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if (empty($text)) {
            return $this->createEmptyMessageResponse($chatId);
        }

        // Process command through unified system
        $result = $this->commandSystem->processCommand($text, $this->getContextFromMessage($message));

        if ($result->isSuccess()) {
            return $this->createSuccessResponse($chatId, $result);
        } else {
            return $this->createCommandNotFoundResponse($chatId, $result);
        }
    }

    private function processVoiceMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $voice = $message['voice'];

            // Check if speech service is available
            if (!$this->speechService) {
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Serviço de reconhecimento de voz não está disponível. Envie uma mensagem de texto.",
                        (string) $chatId
                    );
                }

                return $this->createVoiceConversionErrorResponse($chatId);
            }

            // Send processing message
            if ($this->telegramChannel) {
                $this->telegramChannel->sendTextMessage(
                    "🎤 Processando mensagem de voz...",
                    (string) $chatId
                );
            }

            // Download voice file
            $voiceFilePath = $this->downloadVoiceFile($voice['file_id']);

            if (!$voiceFilePath) {
                return $this->createVoiceConversionErrorResponse($chatId);
            }

            // Convert voice to text with error handling
            try {
                $text = $this->speechService->convertVoiceToText($voiceFilePath);
            } catch (\Exception $speechException) {
                if ($this->loggingService) {
                    $this->loggingService->logException($speechException, [
                        'operation' => 'speech_to_text_conversion',
                        'chat_id' => $chatId,
                        'file_path' => $voiceFilePath
                    ]);
                }

                // Send error message to user
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Erro ao processar mensagem de voz. Tente novamente ou envie uma mensagem de texto.",
                        (string) $chatId
                    );
                }

                return $this->createVoiceConversionErrorResponse($chatId);
            }

            if (!$text) {
                if ($this->loggingService) {
                    $this->loggingService->logException(new \Exception('Failed to convert voice to text'), [
                        'chat_id' => $chatId,
                        'file_path' => $voiceFilePath
                    ]);
                }

                // Send error message to user
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Não foi possível reconhecer o texto da mensagem de voz. Tente novamente.",
                        (string) $chatId
                    );
                }

                return $this->createVoiceConversionErrorResponse($chatId);
            }

            // Clean up voice file
            if (file_exists($voiceFilePath)) {
                unlink($voiceFilePath);
            }

            // Send recognized text to user
            if ($this->telegramChannel) {
                $this->telegramChannel->sendTextMessage(
                    "🎯 Texto reconhecido: *{$text}*",
                    (string) $chatId
                );
            }

            // Update context for voice processing
            $context = $this->getContextFromMessage($message);
            $context['type'] = 'voice';
            $context['original_voice'] = $text;

            // Process converted text through unified system
            $result = $this->commandSystem->processCommand($text, $context);

            if ($result->isSuccess()) {
                return $this->createSuccessResponse($chatId, $result);
            } else {
                return $this->createCommandNotFoundResponse($chatId, $result);
            }

        } catch (\Exception $e) {
            if ($this->loggingService) {
                $this->loggingService->logException($e, [
                    'operation' => 'voice_message_processing',
                    'chat_id' => $message['chat']['id'] ?? null,
                    'message' => $message
                ]);
            }

            return $this->createVoiceConversionErrorResponse($chatId);
        }
    }

    private function processCallbackQuery(array $callbackQuery): array
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? 0;
        $callbackData = $callbackQuery['data'] ?? '';

        if (empty($callbackData)) {
            return $this->createEmptyCallbackResponse($chatId);
        }

        // Process callback through unified system
        $result = $this->commandSystem->processCommand($callbackData, $this->getContextFromCallbackQuery($callbackQuery));

        if ($result->isSuccess()) {
            return $this->createSuccessResponse($chatId, $result);
        } else {
            return $this->createCallbackErrorResponse($chatId, $result);
        }
    }

    private function getContextFromMessage(array $message): array
    {
        // Check if this is a callback query
        if (isset($message['callback_query'])) {
            return $this->getContextFromCallbackQuery($message['callback_query']);
        }

        // Regular message
        return [
            'chat_id' => $message['chat']['id'],
            'user_id' => $message['from']['id'] ?? null,
            'type' => $this->determineMessageType($message),
            'timestamp' => $message['date'] ?? time(),
            'user_permissions' => $this->getUserPermissions($message)
        ];
    }

    private function getContextFromCallbackQuery(array $callbackQuery): array
    {
        return [
            'chat_id' => $callbackQuery['message']['chat']['id'] ?? 0,
            'user_id' => $callbackQuery['from']['id'] ?? null,
            'type' => 'callback_query',
            'timestamp' => $callbackQuery['date'] ?? time(),
            'user_permissions' => $this->getUserPermissions($callbackQuery)
        ];
    }

    private function determineMessageType(array $message): string
    {
        if (isset($message['callback_query'])) {
            return 'callback_query';
        }

        if (isset($message['voice'])) {
            return 'voice';
        }

        if (isset($message['audio'])) {
            return 'audio';
        }

        if (isset($message['text'])) {
            return 'text';
        }

        return 'unknown';
    }

    private function getUserPermissions(array $message): array
    {
        // Extract user permissions from message or callback query
        $userId = null;

        if (isset($message['from']['id'])) {
            // Regular message
            $userId = $message['from']['id'];
        } elseif (isset($message['callback_query']['from']['id'])) {
            // Callback query wrapped in payload
            $userId = $message['callback_query']['from']['id'];
        }

        if (!$userId) {
            return ['user'];
        }

        // TODO: Implement actual permission checking
        // For now, return default permissions
        return ['user'];
    }

    private function convertVoiceToText(array $message): string
    {
        // TODO: Implement voice-to-text conversion
        // This would use your existing SpeechToTextService
        return '';
    }

    private function createSuccessResponse(int $chatId, CommandResult $result): array
    {
        $data = $result->getData();

        return [
            'success' => true,
            'chat_id' => $chatId,
            'type' => $result->getType(),
            'data' => $data,
            'command_info' => $result->getCommandMatch() ? [
                'command_id' => $result->getCommandMatch()->getCommand()->getId(),
                'confidence' => $result->getCommandMatch()->getConfidence()
            ] : null
        ];
    }

    private function createCommandNotFoundResponse(int $chatId, CommandResult $result): array
    {
        $data = $result->getData();
        $fallbackMessage = $data['fallback_message'] ?? 'Comando não encontrado';

        // Send the fallback message to the user in Telegram chat
        if ($this->telegramChannel) {
            $this->telegramChannel->sendTextMessage(
                $fallbackMessage,
                (string) $chatId
            );
        }

        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'command_not_found',
            'message' => $fallbackMessage,
            'suggestions' => $data['suggestions'] ?? [],
            'data' => $data
        ];
    }

    private function createUnauthorizedResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'unauthorized',
            'message' => 'Você não está autorizado a usar este bot.',
            'data' => []
        ];
    }

    private function createUnsupportedMessageResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'unsupported',
            'message' => 'Tipo de mensagem não suportado.',
            'data' => []
        ];
    }

    private function createEmptyMessageResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'empty_message',
            'message' => 'Mensagem vazia recebida.',
            'data' => []
        ];
    }

    private function createVoiceConversionErrorResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'voice_conversion_error',
            'message' => 'Erro ao converter mensagem de voz.',
            'data' => []
        ];
    }

    private function createEmptyCallbackResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'empty_callback',
            'message' => 'Dados de callback vazios.',
            'data' => []
        ];
    }

    private function createCallbackErrorResponse(int $chatId, CommandResult $result): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'callback_error',
            'message' => 'Erro ao processar callback.',
            'data' => $result->getData()
        ];
    }

    private function createErrorResponse(int $chatId, string $error): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'error',
            'message' => 'Erro interno do sistema: ' . $error,
            'data' => []
        ];
    }

    private function createAudioConversionErrorResponse(int $chatId): array
    {
        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'audio_conversion_error',
            'message' => 'Erro ao converter mensagem de áudio.',
            'data' => []
        ];
    }

    /**
     * Process audio message
     */
    private function processAudioMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $audio = $message['audio'];

            // Check if speech service is available
            if (!$this->speechService) {
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Serviço de reconhecimento de áudio não está disponível. Envie uma mensagem de texto.",
                        (string) $chatId
                    );
                }

                return $this->createAudioConversionErrorResponse($chatId);
            }

            // Send processing message
            if ($this->telegramChannel) {
                $this->telegramChannel->sendTextMessage(
                    "🎵 Processando mensagem de áudio...",
                    (string) $chatId
                );
            }

            // Download audio file
            $audioFilePath = $this->downloadAudioFile($audio['file_id']);

            if (!$audioFilePath) {
                return $this->createAudioConversionErrorResponse($chatId);
            }

            // Convert audio to text with error handling
            try {
                $text = $this->speechService->convertVoiceToText($audioFilePath);
            } catch (\Exception $speechException) {
                if ($this->loggingService) {
                    $this->loggingService->logException($speechException, [
                        'operation' => 'speech_to_text_conversion',
                        'chat_id' => $chatId,
                        'file_path' => $audioFilePath,
                        'audio_info' => $audio
                    ]);
                }

                // Send error message to user
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Erro ao processar mensagem de áudio. Tente novamente ou envie uma mensagem de texto.",
                        (string) $chatId
                    );
                }

                return $this->createAudioConversionErrorResponse($chatId);
            }

            if (!$text) {
                if ($this->loggingService) {
                    $this->loggingService->logException(new \Exception('Failed to convert audio to text'), [
                        'chat_id' => $chatId,
                        'file_path' => $audioFilePath,
                        'audio_info' => $audio
                    ]);
                }

                // Send error message to user
                if ($this->telegramChannel) {
                    $this->telegramChannel->sendTextMessage(
                        "❌ Não foi possível reconhecer o texto da mensagem de áudio. Tente novamente.",
                        (string) $chatId
                    );
                }

                return $this->createAudioConversionErrorResponse($chatId);
            }

            // Clean up audio file
            if (file_exists($audioFilePath)) {
                unlink($audioFilePath);
            }

            // Send recognized text to user
            if ($this->telegramChannel) {
                $this->telegramChannel->sendTextMessage(
                    "🎯 Texto reconhecido: *{$text}*",
                    (string) $chatId
                );
            }

            // Update context for audio processing
            $context = $this->getContextFromMessage($message);
            $context['type'] = 'audio';
            $context['original_audio'] = $text;

            // Process converted text through unified system
            $result = $this->commandSystem->processCommand($text, $context);

            if ($result->isSuccess()) {
                return $this->createSuccessResponse($chatId, $result);
            } else {
                return $this->createCommandNotFoundResponse($chatId, $result);
            }

        } catch (\Exception $e) {
            if ($this->loggingService) {
                $this->loggingService->logException($e, [
                    'operation' => 'audio_message_processing',
                    'chat_id' => $message['chat']['id'] ?? null,
                    'message' => $message
                ]);
            }

            return $this->createAudioConversionErrorResponse($chatId);
        }
    }

    /**
     * Download voice file from Telegram
     */
    private function downloadVoiceFile(string $fileId): ?string
    {
        try {
            if (!$this->telegramChannel) {
                return null;
            }

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
                if ($this->loggingService) {
                    $this->loggingService->logException(new \Exception('Failed to download voice file'), [
                        'file_id' => $fileId,
                        'file_url' => $fileUrl
                    ]);
                }

                return null;
            }

            file_put_contents($localPath, $fileContent);

            return $localPath;

        } catch (\Exception $e) {
            if ($this->loggingService) {
                $this->loggingService->logException($e, [
                    'operation' => 'voice_file_download',
                    'file_id' => $fileId
                ]);
            }

            return null;
        }
    }

    /**
     * Download audio file from Telegram
     */
    private function downloadAudioFile(string $fileId): ?string
    {
        try {
            if (!$this->telegramChannel) {
                return null;
            }

            $fileInfo = $this->telegramChannel->getFile($fileId);

            if (!$fileInfo['success']) {
                return null;
            }

            $filePath = $fileInfo['file_path'];
            $fileName = basename($filePath);
            $localPath = storage_path("app/temp/audio_{$fileName}");

            // Ensure temp directory exists
            if (!is_dir(dirname($localPath))) {
                mkdir(dirname($localPath), 0755, true);
            }

            // Download file
            $fileUrl = "https://api.telegram.org/file/bot" . config('services.telegram.bot_token') . "/{$filePath}";
            $fileContent = file_get_contents($fileUrl);

            if ($fileContent === false) {
                if ($this->loggingService) {
                    $this->loggingService->logException(new \Exception('Failed to download audio file'), [
                        'file_id' => $fileId,
                        'file_url' => $fileUrl
                    ]);
                }

                return null;
            }

            file_put_contents($localPath, $fileContent);

            return $localPath;

        } catch (\Exception $e) {
            if ($this->loggingService) {
                $this->loggingService->logException($e, [
                    'operation' => 'audio_file_download',
                    'file_id' => $fileId
                ]);
            }

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
}
