<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Services\Telegram\Reports\GeneralReportGenerator;

class ReportCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private TelegramMenuBuilder $menuBuilder,
        private GeneralReportGenerator $generalReportGenerator
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        // If we have period parameters, generate a specific report
        if (isset($params['period'])) {
            return $this->generalReportGenerator->generate($chatId, $params);
        }

        // Otherwise, show the report menu
        return $this->menuBuilder->buildReportMenu($chatId);
    }

    public function getCommandName(): string
    {
        return 'report';
    }

    public function getCommandDescription(): string
    {
        return 'Mostrar menu de relatórios ou gerar relatório específico';
    }

    public function canHandle(string $command): bool
    {
        return $command === 'report';
    }
}
