<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\TelegramMenuBuilder;

class StartCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        return $this->menuBuilder->buildMainMenu($chatId);
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
        $menuCommands = [
            'start', 'help', 'menu', 'ajuda', 'comandos',
            'opções', 'iniciar', 'begin', 'home', 'principal'
        ];

        return in_array(strtolower($command), $menuCommands);
    }
}
