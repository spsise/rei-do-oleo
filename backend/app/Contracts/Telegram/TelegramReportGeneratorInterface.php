<?php

namespace App\Contracts\Telegram;

interface TelegramReportGeneratorInterface
{
    /**
     * Generate report
     */
    public function generate(int $chatId, array $params = []): array;

    /**
     * Get report type
     */
    public function getReportType(): string;

    /**
     * Get report name
     */
    public function getReportName(): string;

    /**
     * Get available periods
     */
    public function getAvailablePeriods(): array;
}
