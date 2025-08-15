<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Services\Channels\TelegramChannel;

class StartCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder,
        private TelegramChannel $telegramChannel
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        return $this->menuBuilder->buildMainMenu($chatId);
    }

    /**
     * Show help (called by command system)
     */
    public function showHelp(array $context): array
    {
        $chatId = $context['chat_id'] ?? 0;

        if (!$chatId) {
            return [
                'success' => false,
                'message' => 'Chat ID não encontrado no contexto'
            ];
        }

        $message = "🤖 *Rei do Óleo - Ajuda*\n\n" .
                   "📋 *Comandos Disponíveis:*\n\n" .
                   "🏠 *Navegação:*\n" .
                   "• Menu - Menu principal\n" .
                   "• Serviços - Menu de serviços\n" .
                   "• Relatórios - Menu de relatórios\n\n" .
                   "📊 *Relatórios:*\n" .
                   "• Hoje - Relatório do dia\n" .
                   "• Semana - Relatório da semana\n" .
                   "• Mês - Relatório do mês\n\n" .
                   "🔧 *Sistema:*\n" .
                   "• Status - Status do sistema\n" .
                   "• Ajuda - Esta mensagem\n\n" .
                   "💡 *Dica:* Você também pode usar comandos de voz!";

        $keyboard = [
            [
                ['text' => '🏠 Menu Principal', 'callback_data' => 'main_menu'],
                ['text' => '📊 Relatórios', 'callback_data' => 'report_menu']
            ]
        ];

        return $this->telegramChannel->sendMessageWithKeyboard($message, $chatId, $keyboard);
    }

    public function getCommandName(): string
    {
        return 'start';
    }

    public function getCommandDescription(): string
    {
        return 'Iniciar o bot e mostrar menu principal';
    }

    public function canHandle(string $command): bool
    {
        // Only handle basic start commands, not specific menu navigation
        $startCommands = [
            'start', 'help', 'ajuda', 'comandos', 'opções',
            'iniciar', 'begin', 'home', 'principal'
        ];

        return in_array(strtolower($command), $startCommands);
    }
}
