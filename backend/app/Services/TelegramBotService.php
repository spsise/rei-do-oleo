<?php

namespace App\Services;

use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Telegram\TelegramMenuBuilder;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function __construct(
        private TelegramCommandParser $commandParser,
        private TelegramCommandHandlerManager $commandHandlerManager,
        private TelegramAuthorizationService $authorizationService,
        private TelegramMenuBuilder $menuBuilder
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

            Log::info('Telegram message received', [
                'chat_id' => $chatId,
                'text' => $text,
                'from' => $from
            ]);

            // Check if user is authorized
            if (!$this->authorizationService->isAuthorizedUser($chatId)) {
                return $this->menuBuilder->buildUnauthorizedMessage($chatId);
            }

            // Parse command
            $command = $this->commandParser->parseCommand($text);

            // Handle command using the manager
            return $this->commandHandlerManager->handleCommand($command['type'], $chatId, $command['params']);

        } catch (\Exception $e) {
            Log::error('Error processing Telegram message', [
                'error' => $e->getMessage(),
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

            Log::info('Telegram callback query received', [
                'chat_id' => $chatId,
                'callback_data' => $callbackData,
                'from' => $from
            ]);

            // Check if user is authorized
            if (!$this->authorizationService->isAuthorizedUser($chatId)) {
                return $this->menuBuilder->buildUnauthorizedMessage($chatId);
            }

            // Parse callback data
            $callback = $this->commandParser->parseCallbackData($callbackData);

            // Handle callback using the manager
            return $this->commandHandlerManager->handleCallbackQuery($callback['action'], $chatId, $callback);

        } catch (\Exception $e) {
            Log::error('Error processing Telegram callback query', [
                'error' => $e->getMessage(),
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
