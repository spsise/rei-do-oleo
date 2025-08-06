<?php

namespace App\Services;

use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Contracts\LoggingServiceInterface;

class TelegramBotService
{
    public function __construct(
        private TelegramCommandParser $commandParser,
        private TelegramCommandHandlerManager $commandHandlerManager,
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

            // Parse command
            $command = $this->commandParser->parseCommand($text);

            // Handle command using the manager
            return $this->commandHandlerManager->handleCommand($command['type'], $chatId, $command['params']);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_message_processing',
                'chat_id' => $message['chat']['id'] ?? null,
                'user_id' => $message['from']['id'] ?? null,
                'message' => $message
            ]);

            return $this->menuBuilder->buildErrorMessage($chatId);
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

            // Parse callback data
            $callback = $this->commandParser->parseCallbackData($callbackData);

            // Handle callback using the manager
            return $this->commandHandlerManager->handleCallbackQuery($callback['action'], $chatId, $callback);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_callback_processing',
                'chat_id' => $callbackQuery['message']['chat']['id'] ?? null,
                'user_id' => $callbackQuery['from']['id'] ?? null,
                'callback_query' => $callbackQuery
            ]);

            return $this->menuBuilder->buildErrorMessage($chatId);
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
