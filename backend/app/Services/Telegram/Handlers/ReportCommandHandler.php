<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\TelegramMenuBuilder;

class ReportCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        return $this->menuBuilder->buildReportMenu($chatId);
    }

    public function getCommandName(): string
    {
        return 'report';
    }

    public function getCommandDescription(): string
    {
        return 'Mostrar menu de relatórios';
    }

    public function canHandle(string $command): bool
    {
        return $command === 'report';
    }
}
