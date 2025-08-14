<?php

namespace App\Services\Telegram\Handlers;

use App\Contracts\Telegram\TelegramCommandHandlerInterface;
use App\Services\Telegram\Reports\ProductsReportGenerator;

class ProductsCommandHandler implements TelegramCommandHandlerInterface
{
    public function __construct(
        private ProductsReportGenerator $productsReportGenerator
    ) {}

    public function handle(int $chatId, array $params = []): array
    {
        $period = $params['period'] ?? 'today';
        return $this->productsReportGenerator->generate($chatId, ['period' => $period]);
    }

    public function getCommandName(): string
    {
        return 'products';
    }

    public function getCommandDescription(): string
    {
        return 'Gerar relatório de produtos';
    }

    public function canHandle(string $command): bool
    {
        return $command === 'products';
    }
}
