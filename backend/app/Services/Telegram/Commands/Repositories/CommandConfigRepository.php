<?php

namespace App\Services\Telegram\Commands\Repositories;

use App\Models\Telegram\TelegramCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CommandConfigRepository
{
    private const CACHE_KEY = 'telegram_commands';
    private const CACHE_TTL = 3600; // 1 hour

    public function getAllCommands(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return TelegramCommand::active()
                ->orderBy('priority', 'desc')
                ->orderBy('command_id')
                ->get();
        });
    }

    public function getCommandsByCategory(string $category): Collection
    {
        return $this->getAllCommands()->filter(function ($command) use ($category) {
            return $command->getCategory() === $category;
        });
    }

    public function findCommandById(string $commandId): ?TelegramCommand
    {
        return $this->getAllCommands()->first(function ($command) use ($commandId) {
            return $command->getId() === $commandId;
        });
    }

    public function findCommandsByAlias(string $alias): Collection
    {
        $alias = strtolower(trim($alias));

        return $this->getAllCommands()->filter(function ($command) use ($alias) {
            return in_array($alias, array_map('strtolower', $command->getAliases()));
        });
    }

    public function addCommand(array $commandConfig): TelegramCommand
    {
        $command = TelegramCommand::create($commandConfig);
        $this->clearCache();
        return $command;
    }

    public function updateCommand(string $commandId, array $commandConfig): bool
    {
        $command = TelegramCommand::where('command_id', $commandId)->first();

        if (!$command) {
            return false;
        }

        $command->update($commandConfig);
        $this->clearCache();
        return true;
    }

    public function removeCommand(string $commandId): bool
    {
        $command = TelegramCommand::where('command_id', $commandId)->first();

        if (!$command) {
            return false;
        }

        $command->delete();
        $this->clearCache();
        return true;
    }

    public function activateCommand(string $commandId): bool
    {
        $command = TelegramCommand::where('command_id', $commandId)->first();

        if (!$command) {
            return false;
        }

        $command->update(['is_active' => true]);
        $this->clearCache();
        return true;
    }

    public function deactivateCommand(string $commandId): bool
    {
        $command = TelegramCommand::where('command_id', $commandId)->first();

        if (!$command) {
            return false;
        }

        $command->update(['is_active' => false]);
        $this->clearCache();
        return true;
    }

    public function getCommandsByPermission(array $userPermissions): Collection
    {
        return $this->getAllCommands()->filter(function ($command) use ($userPermissions) {
            $commandPermissions = $command->getPermissions();

            // Check if command allows all users
            if (in_array('all', $commandPermissions)) {
                return true;
            }

            // Check if user has any of the required permissions
            return !empty(array_intersect($userPermissions, $commandPermissions));
        });
    }

    public function searchCommands(string $query): Collection
    {
        $query = strtolower(trim($query));

        return $this->getAllCommands()->filter(function ($command) use ($query) {
            // Search in command ID
            if (str_contains(strtolower($command->getId()), $query)) {
                return true;
            }

            // Search in aliases
            foreach ($command->getAliases() as $alias) {
                if (str_contains(strtolower($alias), $query)) {
                    return true;
                }
            }

            // Search in description
            if (str_contains(strtolower($command->getDescription()), $query)) {
                return true;
            }

            // Search in natural language patterns
            foreach ($command->getNaturalLanguage() as $pattern) {
                if (str_contains(strtolower($pattern), $query)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function getCommandCategories(): array
    {
        return $this->getAllCommands()
            ->pluck('category')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getCommandsStats(): array
    {
        $commands = $this->getAllCommands();

        return [
            'total' => $commands->count(),
            'active' => $commands->where('is_active', true)->count(),
            'inactive' => $commands->where('is_active', false)->count(),
            'by_category' => $commands->groupBy('category')->map->count(),
            'by_permission' => $commands->groupBy('permissions')->map->count(),
        ];
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function warmCache(): void
    {
        $this->clearCache();
        $this->getAllCommands();
    }
}
