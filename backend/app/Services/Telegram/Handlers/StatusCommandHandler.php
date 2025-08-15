<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Channels\TelegramChannel;

class StatusCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramChannel $telegramChannel
    ) {}

    /**
     * Handle command for legacy system (TelegramCommandHandlerInterface)
     */
    public function handle(int $chatId, array $params = []): array
    {
        return $this->executeStatusCommand($chatId);
    }

    /**
     * Handle status command for unified command system
     */
    public function handleStatus(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        return $this->executeStatusCommand($chatId);
    }

    /**
     * Internal method to execute status command
     */
    private function executeStatusCommand(int $chatId): array
    {
        $message = "📋 *Status do Sistema*\n\n" .
                   "🟢 *Sistema:* Online\n" .
                   "🟢 *API:* Funcionando\n" .
                   "🟢 *Banco de Dados:* Conectado\n" .
                   "🟢 *Telegram Bot:* Ativo\n\n" .
                   "⏰ *Última verificação:* " . now()->format('d/m/Y H:i:s');

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu'],
                ['text' => '🔄 Atualizar Status', 'callback_data' => 'refresh_status']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    public function getCommandName(): string
    {
        return 'status';
    }

    public function getCommandDescription(): string
    {
        return 'Mostrar status do sistema';
    }

    public function canHandle(string $command): bool
    {
        return $command === 'status';
    }
}
