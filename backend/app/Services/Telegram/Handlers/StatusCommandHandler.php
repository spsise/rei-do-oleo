<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Channels\TelegramChannel;

class StatusCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramChannel $telegramChannel
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        $message = "📋 *Status do Sistema*\n\n" .
                   "🟢 *Sistema:* Online\n" .
                   "🟢 *API:* Funcionando\n" .
                   "🟢 *Banco de Dados:* Conectado\n" .
                   "🟢 *Telegram Bot:* Ativo\n\n" .
                   "⏰ *Última verificação:* " . now()->format('d/m/Y H:i:s');

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu']
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
