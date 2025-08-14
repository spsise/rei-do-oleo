<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandResult;
use Illuminate\Support\Facades\Log;

class TelegramMessageProcessorService
{
    public function __construct(
        private UnifiedCommandSystem $commandSystem,
        private TelegramAuthorizationService $authorizationService
    ) {}

    public function processMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $messageType = $this->determineMessageType($message);

            // Check authorization - temporarily disabled for development
            // TODO: Implement proper authorization logic
            // if (!$this->authorizationService->canAccess($chatId)) {
            //     return $this->createUnauthorizedResponse($chatId);
            // }

            $context = [
                'chat_id' => $chatId,
                'user_id' => $message['from']['id'] ?? null,
                'type' => $messageType,
                'timestamp' => $message['date'] ?? time(),
                'user_permissions' => $this->getUserPermissions($message)
            ];

            // Process based on message type
            switch ($messageType) {
                case 'text':
                    return $this->processTextMessage($message, $context);
                case 'voice':
                    return $this->processVoiceMessage($message, $context);
                case 'callback_query':
                    return $this->processCallbackQuery($message, $context);
                default:
                    return $this->createUnsupportedMessageResponse($chatId);
            }

        } catch (\Exception $e) {
            Log::error('Failed to process Telegram message', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResponse($chatId ?? 0, $e->getMessage());
        }
    }

    private function processTextMessage(array $message, array $context): array
    {
        $chatId = $context['chat_id'];
        $text = $message['text'] ?? '';

        if (empty($text)) {
            return $this->createEmptyMessageResponse($chatId);
        }

        // Process command through unified system
        $result = $this->commandSystem->processCommand($text, $context);

        if ($result->isSuccess()) {
            return $this->createSuccessResponse($chatId, $result);
        } else {
            return $this->createCommandNotFoundResponse($chatId, $result);
        }
    }

    private function processVoiceMessage(array $message, array $context): array
    {
        $chatId = $context['chat_id'];

        // Download and convert voice to text
        $voiceText = $this->convertVoiceToText($message);

        if (empty($voiceText)) {
            return $this->createVoiceConversionErrorResponse($chatId);
        }

        // Update context for voice processing
        $context['type'] = 'voice';
        $context['original_voice'] = $voiceText;

        // Process converted text through unified system
        $result = $this->commandSystem->processCommand($voiceText, $context);

        if ($result->isSuccess()) {
            return $this->createSuccessResponse($chatId, $result);
        } else {
            return $this->createCommandNotFoundResponse($chatId, $result);
        }
    }

    private function processCallbackQuery(array $message, array $context): array
    {
        $chatId = $context['chat_id'];
        $callbackData = $message['callback_query']['data'] ?? '';

        if (empty($callbackData)) {
            return $this->createEmptyCallbackResponse($chatId);
        }

        // Process callback through unified system
        $result = $this->commandSystem->processCommand($callbackData, $context);

        if ($result->isSuccess()) {
            return $this->createSuccessResponse($chatId, $result);
        } else {
            return $this->createCallbackErrorResponse($chatId, $result);
        }
    }

    private function determineMessageType(array $message): string
    {
        if (isset($message['callback_query'])) {
            return 'callback_query';
        }

        if (isset($message['voice'])) {
            return 'voice';
        }

        if (isset($message['text'])) {
            return 'text';
        }

        return 'unknown';
    }

    private function getUserPermissions(array $message): array
    {
        // Extract user permissions from message
        // This would integrate with your existing permission system
        $userId = $message['from']['id'] ?? null;

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

        return [
            'success' => false,
            'chat_id' => $chatId,
            'type' => 'command_not_found',
            'message' => $data['fallback_message'] ?? 'Comando não encontrado',
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
}
