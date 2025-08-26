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
     * Send error message to user via Telegram
     */
    public function sendErrorMessage(int $chatId, string $errorMessage): array
    {
        try {
            // Create keyboard with helpful options
            $keyboard = [
                [
                    ['text' => '❓ Ajuda', 'callback_data' => 'help'],
                    ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
                ],
                [
                    ['text' => '📋 Comandos', 'callback_data' => 'commands_list'],
                    ['text' => '🔄 Tentar Novamente', 'callback_data' => 'retry']
                ]
            ];

            // Send error message with helpful keyboard using menuBuilder
            $result = $this->menuBuilder->buildMainMenu($chatId);

            $this->loggingService->logTelegramEvent('telegram_error_message_sent', [
                'chat_id' => $chatId,
                'error_message' => $errorMessage,
                'result' => $result
            ], 'info');

            return [
                'success' => true,
                'chat_id' => $chatId,
                'type' => 'error_message_sent',
                'message' => 'Error message sent to user',
                'data' => $result
            ];

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'send_error_message',
                'chat_id' => $chatId,
                'error_message' => $errorMessage
            ]);

            return [
                'success' => false,
                'chat_id' => $chatId,
                'type' => 'error_message_failed',
                'message' => 'Failed to send error message',
                'data' => []
            ];
        }
    }

    /**
     * Send generic error message with fallback
     */
    public function sendGenericErrorMessage(int $chatId, string $reason = 'unknown'): array
    {
        $errorMessages = [
            'validation_failed' => "❌ Sua mensagem não pôde ser processada.\n\nVerifique se está correta e tente novamente.",
            'unauthorized' => "🚫 Você não tem permissão para usar este comando.\n\nEntre em contato com o administrador.",
            'command_not_found' => "🤔 Comando não reconhecido.\n\nUse /help para ver os comandos disponíveis.",
            'timeout' => "⏰ A operação demorou muito.\n\nTente novamente em alguns instantes.",
            'internal_error' => "💥 Ocorreu um erro interno.\n\nNossa equipe foi notificada.",
            'webhook_error' => "🔌 Erro na conexão.\n\nTente novamente em alguns instantes.",
            'unknown' => "❓ Ocorreu um erro inesperado.\n\nTente novamente ou use /help para ajuda."
        ];

        $errorMessage = $errorMessages[$reason] ?? $errorMessages['unknown'];

        return $this->sendErrorMessage($chatId, $errorMessage);
    }
}
