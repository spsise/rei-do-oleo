<?php

namespace App\Contracts\Telegram\Commands;

interface CommandRegistryInterface
{
    public function findCommand(string $input, array $context = []): ?CommandMatch;
    public function getAllCommands(): array;
    public function getCommandsByCategory(string $category): array;
    public function reloadCommands(): void;
    public function addCommand(array $commandConfig): void;
    public function removeCommand(string $commandId): void;
}
