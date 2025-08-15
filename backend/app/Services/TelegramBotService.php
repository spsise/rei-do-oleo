<?php

namespace App\Services;

use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Contracts\LoggingServiceInterface;

class TelegramBotService
{
    public function __construct(
        private UnifiedCommandSystem $commandSystem,
        private TelegramAuthorizationService $authorizationService,
        private TelegramMenuBuilder $menuBuilder,
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Process incoming message from Telegram webhook
     */
    public function processMessage(array $message): array
    {
        try {
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';
            $from = $message['from'] ?? [];

            // Check if user is authorized
            if (!$this->authorizationService->isAuthorizedUser($chatId)) {
                return $this->menuBuilder->buildUnauthorizedMessage($chatId);
            }

            // Process command using UnifiedCommandSystem
            $context = [
                'chat_id' => $chatId,
                'user_id' => $from['id'] ?? null,
                'type' => 'text',
                'timestamp' => time(),
                'user_permissions' => ['user'] // TODO: Get real permissions
            ];

            $result = $this->commandSystem->processCommand($text, $context);

            if ($result->isSuccess()) {
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
            } else {
                $data = $result->getData();
                $fallbackMessage = $data['fallback_message'] ?? 'Comando não encontrado';

                return [
                    'success' => false,
                    'chat_id' => $chatId,
                    'type' => 'command_not_found',
                    'message' => $fallbackMessage,
                    'suggestions' => $data['suggestions'] ?? [],
                    'data' => $data
                ];
            }

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_message_processing',
                'chat_id' => $message['chat']['id'] ?? null,
                'user_id' => $message['from']['id'] ?? null,
                'message' => $message
            ]);

            return [
                'success' => false,
                'chat_id' => $chatId,
                'type' => 'error',
                'message' => 'Erro interno do sistema',
                'data' => []
            ];
        }
    }

    /**
     * Process callback query from inline keyboard buttons
     */
    public function processCallbackQuery(array $callbackQuery): array
    {
        try {
            $chatId = $callbackQuery['message']['chat']['id'];
            $messageId = $callbackQuery['message']['message_id'] ?? null;
            $callbackData = $callbackQuery['data'] ?? '';
            $from = $callbackQuery['from'] ?? [];

            // Check if user is authorized
            if (!$this->authorizationService->isAuthorizedUser($chatId)) {
                return $this->menuBuilder->buildUnauthorizedMessage($chatId);
            }

            // Process callback using UnifiedCommandSystem
            $context = [
                'chat_id' => $chatId,
                'user_id' => $from['id'] ?? null,
                'type' => 'callback_query',
                'timestamp' => time(),
                'user_permissions' => ['user'], // TODO: Get real permissions
                'callback_data' => $callbackData
            ];

            $result = $this->commandSystem->processCommand($callbackData, $context);

            if ($result->isSuccess()) {
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
            } else {
                $data = $result->getData();
                $fallbackMessage = $data['fallback_message'] ?? 'Ação não encontrada';

                return [
                    'success' => false,
                    'chat_id' => $chatId,
                    'type' => 'callback_not_found',
                    'message' => $fallbackMessage,
                    'suggestions' => $data['suggestions'] ?? [],
                    'data' => $data
                ];
            }

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_callback_processing',
                'chat_id' => $callbackQuery['message']['chat']['id'] ?? null,
                'user_id' => $callbackQuery['from']['id'] ?? null,
                'callback_query' => $callbackQuery
            ]);

            return [
                'success' => false,
                'chat_id' => $chatId,
                'type' => 'error',
                'message' => 'Erro interno do sistema',
                'data' => []
            ];
        }
    }

    /**
     * Get available commands
     */
    public function getAvailableCommands(): array
    {
        return $this->commandHandlerManager->getAvailableCommands();
    }

    /**
     * Get available reports
     */
    public function getAvailableReports(): array
    {
        return $this->commandHandlerManager->getAvailableReports();
    }
}
