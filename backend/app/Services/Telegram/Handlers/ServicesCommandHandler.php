<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\Reports\ServicesReportGenerator;

class ServicesCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private ServicesReportGenerator $servicesReportGenerator
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        $period = $params['period'] ?? 'today';
        return $this->servicesReportGenerator->generate($chatId, ['period' => $period]);
    }

    public function getCommandName(): string
    {
        return 'services';
    }

    public function getCommandDescription(): string
    {
        return 'Gerar relatório de serviços';
    }

    public function canHandle(string $command): bool
    {
        return $command === 'services';
    }
}
