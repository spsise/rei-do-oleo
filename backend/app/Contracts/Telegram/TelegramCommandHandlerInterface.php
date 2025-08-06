<?php

namespace App\Contracts\Telegram;

interface TelegramCommandHandlerInterface
{
    /**
     * Handle the command
     */
    public function handle(int $chatId, array $params = []): array;

    /**
     * Get command name
     */
    public function getCommandName(): string;

    /**
     * Get command description
     */
    public function getCommandDescription(): string;

    /**
     * Check if handler can handle the command
     */
    public function canHandle(string $command): bool;
}
