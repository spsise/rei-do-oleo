<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Models\Telegram\TelegramCommand;

class TelegramCommandManager extends Command
{
    protected $signature = 'telegram:unified-commands {action : Action to perform (list, add, update, remove, stats, train, warmup)} {--command-id= : Command ID for specific actions} {--config= : JSON configuration for add/update actions} {--category= : Filter by category for list action}';

    protected $description = 'Manage Telegram bot commands through the unified system';

    public function handle(): int
    {
        $action = $this->argument('action');
        $commandSystem = app(UnifiedCommandSystem::class);

        try {
            switch ($action) {
                case 'list':
                    return $this->listCommands($commandSystem);
                case 'add':
                    return $this->addCommand($commandSystem);
                case 'update':
                    return $this->updateCommand($commandSystem);
                case 'remove':
                    return $this->removeCommand($commandSystem);
                case 'stats':
                    return $this->showStats($commandSystem);
                case 'train':
                    return $this->trainModel($commandSystem);
                case 'warmup':
                    return $this->warmUpCache($commandSystem);
                default:
                    $this->error("Unknown action: {$action}");
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function listCommands(UnifiedCommandSystem $commandSystem): int
    {
        $category = $this->option('category');

        if ($category) {
            $commands = $commandSystem->getCommandsByCategory($category);
            $this->info("Commands in category '{$category}':");
        } else {
            $commands = $commandSystem->searchCommands('');
            $this->info('All available commands:');
        }

        if (empty($commands)) {
            $this->warn('No commands found.');
            return 0;
        }

        $table = [];
        foreach ($commands as $command) {
            $table[] = [
                $command->getId(),
                implode(', ', $command->getAliases()),
                $command->getDescription(),
                $command->getCategory(),
                implode(', ', $command->getPermissions()),
                $command->is_active ? 'Yes' : 'No'
            ];
        }

        $this->table([
            'ID', 'Aliases', 'Description', 'Category', 'Permissions', 'Active'
        ], $table);

        return 0;
    }

    private function addCommand(UnifiedCommandSystem $commandSystem): int
    {
        $config = $this->option('config');

        if (!$config) {
            $this->error('--config option is required for add action');
            return 1;
        }

        try {
            $commandData = json_decode($config, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error('Invalid JSON configuration: ' . $e->getMessage());
            return 1;
        }

        $result = $commandSystem->addCommand($commandData);

        if ($result->isSuccess()) {
            $this->info('Command added successfully!');
            $this->info("Command ID: {$result->getData()['command_id']}");
            return 0;
        } else {
            $this->error('Failed to add command: ' . $result->message);
            return 1;
        }
    }

    private function updateCommand(UnifiedCommandSystem $commandSystem): int
    {
        $commandId = $this->option('command-id');
        $config = $this->option('config');

        if (!$commandId || !$config) {
            $this->error('Both --command-id and --config options are required for update action');
            return 1;
        }

        try {
            $commandData = json_decode($config, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error('Invalid JSON configuration: ' . $e->getMessage());
            return 1;
        }

        $result = $commandSystem->updateCommand($commandId, $commandData);

        if ($result->isSuccess()) {
            $this->info('Command updated successfully!');
            return 0;
        } else {
            $this->error('Failed to update command: ' . $result->message);
            return 1;
        }
    }

    private function removeCommand(UnifiedCommandSystem $commandSystem): int
    {
        $commandId = $this->option('command-id');

        if (!$commandId) {
            $this->error('--command-id option is required for remove action');
            return 1;
        }

        if (!$this->confirm("Are you sure you want to remove command '{$commandId}'?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $result = $commandSystem->removeCommand($commandId);

        if ($result->isSuccess()) {
            $this->info('Command removed successfully!');
            return 0;
        } else {
            $this->error('Failed to remove command: ' . $result->message);
            return 1;
        }
    }

    private function showStats(UnifiedCommandSystem $commandSystem): int
    {
        $stats = $commandSystem->getCommandStats();

        $this->info('Command System Statistics:');
        $this->newLine();

        // Cache stats
        $this->info('Cache Statistics:');
        $this->table(['Metric', 'Value'], [
            ['Total Processed', $stats['total_processed'] ?? 0],
            ['Cache Hits', $stats['cache']['hits'] ?? 0],
            ['Cache Misses', $stats['cache']['misses'] ?? 0],
            ['Cache Hit Rate', ($stats['cache_efficiency'] ?? 0) . '%'],
            ['Cache Size', $stats['cache']['size'] ?? 0]
        ]);

        $this->newLine();

        // Learning stats
        $this->info('Learning Statistics:');
        $this->table(['Metric', 'Value'], [
            ['Total Usage', $stats['learning']['total_usage'] ?? 0],
            ['Successful Usage', $stats['learning']['successful_usage'] ?? 0],
            ['Failed Usage', $stats['learning']['failed_usage'] ?? 0]
        ]);

        $this->newLine();

        // Command stats
        $this->info('Command Statistics:');
        $this->table(['Metric', 'Value'], [
            ['Total Commands', $stats['commands']['total'] ?? 0],
            ['Active Commands', $stats['commands']['active'] ?? 0],
            ['Inactive Commands', $stats['commands']['inactive'] ?? 0]
        ]);

        return 0;
    }

    private function trainModel(UnifiedCommandSystem $commandSystem): int
    {
        $this->info('Training command learning model...');

        $result = $commandSystem->trainModel();

        if ($result->isSuccess()) {
            $this->info('Model trained successfully!');
            return 0;
        } else {
            $this->error('Failed to train model: ' . $result->message);
            return 1;
        }
    }

    private function warmUpCache(UnifiedCommandSystem $commandSystem): int
    {
        $this->info('Warming up command cache...');

        $result = $commandSystem->warmUpCache();

        if ($result->isSuccess()) {
            $data = $result->getData();
            $this->info("Cache warmed up successfully! ({$data['commands_count']} commands)");
            return 0;
        } else {
            $this->error('Failed to warm up cache: ' . $result->message);
            return 1;
        }
    }
}
