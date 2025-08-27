<?php

namespace App\Services\Telegram\Commands;

use App\Contracts\Telegram\Commands\CommandMatch;
use App\Services\Telegram\Commands\Repositories\CommandConfigRepository;
use App\Services\Telegram\Commands\Cache\CommandCache;
use App\Services\Telegram\Commands\Learning\CommandLearning;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class UnifiedCommandSystem
{
    public function __construct(
        private CommandRegistry $registry,
        private CommandCache $cache,
        private CommandLearning $learning,
        private CommandConfigRepository $configRepo
    ) {}

    public function processCommand(string $input, array $context = []): CommandResult
    {
        try {
            $cacheKey = $this->generateCacheKey($input, $context);

            // 1. Check cache first
            if ($cached = $this->cache->get($input, $context)) {
                $this->learning->recordHit($input, $cached);
                return $this->createResult($cached, 'cache_hit');
            }

            // 2. Process command through registry
            $result = $this->registry->findCommand($input, $context);

            if (!$result) {
                return $this->createFallbackResult($input, $context);
            }

            // 3. Cache result
            $this->cache->put($input, $context, $result);

            // 4. Learn from usage
            $this->learning->recordUsage($input, $result);

            // 5. Execute command
            $executionResult = $this->executeCommand($result, $context);

            return $this->createResult($result, 'success', $executionResult);

        } catch (\Exception $e) {
            Log::error('Failed to process command', [
                'input' => $input,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult($input, $e->getMessage());
        }
    }

    public function addCommand(array $commandConfig): CommandResult
    {
        try {
            $command = $this->configRepo->addCommand($commandConfig);
            $this->registry->reloadCommands();
            $this->cache->clear();

            Log::info('Command added successfully', [
                'command_id' => $command->getId(),
                'aliases' => $command->getAliases()
            ]);

            return $this->createResult(null, 'command_added', [
                'command_id' => $command->getId(),
                'message' => 'Command added successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to add command', [
                'config' => $commandConfig,
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult('add_command', $e->getMessage());
        }
    }

    public function updateCommand(string $commandId, array $commandConfig): CommandResult
    {
        try {
            $success = $this->configRepo->updateCommand($commandId, $commandConfig);

            if ($success) {
                $this->registry->reloadCommands();
                $this->cache->clearByPattern($commandId);

                Log::info('Command updated successfully', [
                    'command_id' => $commandId
                ]);

                return $this->createResult(null, 'command_updated', [
                    'command_id' => $commandId,
                    'message' => 'Command updated successfully'
                ]);
            } else {
                return $this->createErrorResult('update_command', 'Command not found');
            }

        } catch (\Exception $e) {
            Log::error('Failed to update command', [
                'command_id' => $commandId,
                'config' => $commandConfig,
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult('update_command', $e->getMessage());
        }
    }

    public function removeCommand(string $commandId): CommandResult
    {
        try {
            $success = $this->configRepo->removeCommand($commandId);

            if ($success) {
                $this->registry->reloadCommands();
                $this->cache->clearByPattern($commandId);

                Log::info('Command removed successfully', [
                    'command_id' => $commandId
                ]);

                return $this->createResult(null, 'command_removed', [
                    'command_id' => $commandId,
                    'message' => 'Command removed successfully'
                ]);
            } else {
                return $this->createErrorResult('remove_command', 'Command not found');
            }

        } catch (\Exception $e) {
            Log::error('Failed to remove command', [
                'command_id' => $commandId,
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult('remove_command', $e->getMessage());
        }
    }

    public function getCommandStats(): array
    {
        try {
            $cacheStats = $this->cache->getStats();
            $learningStats = $this->learning->getLearningStats();
            $commandStats = $this->configRepo->getCommandsStats();

            return [
                'cache' => $cacheStats,
                'learning' => $learningStats,
                'commands' => $commandStats,
                'total_processed' => $cacheStats['hits'] + $cacheStats['misses'],
                'cache_efficiency' => $cacheStats['hit_rate'] ?? 0.0
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get command stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function getCommandInsights(string $commandId): array
    {
        try {
            $insights = $this->learning->getCommandInsights($commandId);
            $command = $this->configRepo->findCommandById($commandId);

            if ($command) {
                $insights['command_info'] = [
                    'id' => $command->getId(),
                    'aliases' => $command->getAliases(),
                    'description' => $command->getDescription(),
                    'category' => $command->getCategory(),
                    'permissions' => $command->getPermissions()
                ];
            }

            return $insights;

        } catch (\Exception $e) {
            Log::error('Failed to get command insights', [
                'command_id' => $commandId,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function suggestImprovements(): array
    {
        try {
            return $this->learning->suggestImprovements();
        } catch (\Exception $e) {
            Log::error('Failed to get improvement suggestions', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function trainModel(): CommandResult
    {
        try {
            $this->learning->trainModel();

            Log::info('Command learning model trained successfully');

            return $this->createResult(null, 'model_trained', [
                'message' => 'Learning model trained successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to train learning model', [
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult('train_model', $e->getMessage());
        }
    }

    public function warmUpCache(): CommandResult
    {
        try {
            $commands = $this->registry->getAllCommands();
            $this->cache->warmUp($commands);

            Log::info('Command cache warmed up successfully', [
                'commands_count' => count($commands)
            ]);

            return $this->createResult(null, 'cache_warmed', [
                'message' => 'Cache warmed up successfully',
                'commands_count' => count($commands)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to warm up cache', [
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResult('warm_cache', $e->getMessage());
        }
    }

    public function searchCommands(string $query): array
    {
        try {
            return $this->registry->searchCommands($query);
        } catch (\Exception $e) {
            Log::error('Failed to search commands', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function getCommandsByCategory(string $category): array
    {
        try {
            return $this->registry->getCommandsByCategory($category);
        } catch (\Exception $e) {
            Log::error('Failed to get commands by category', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function getCommandsByPermission(array $userPermissions): array
    {
        try {
            return $this->registry->getCommandsByPermission($userPermissions);
        } catch (\Exception $e) {
            Log::error('Failed to get commands by permission', [
                'permissions' => $userPermissions,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    private function generateCacheKey(string $input, array $context): string
    {
        $contextHash = md5(serialize($context));
        $inputHash = md5(strtolower(trim($input)));

        return 'unified_command:' . $inputHash . ':' . $contextHash;
    }

    private function executeCommand(CommandMatch $result, array $context): mixed
    {
        try {
            $action = $result->getCommand()->getAction();
            $handler = $action['handler'];
            $method = $action['method'];
            $parameters = $action['parameters'] ?? [];

            // Merge context parameters
            $parameters = array_merge($parameters, $context);

            // Resolve handler from container
            $handlerInstance = App::make($handler);

            if (!method_exists($handlerInstance, $method)) {
                throw new \Exception("Method {$method} not found in handler {$handler}");
            }

            // Execute handler method with proper signature handling
            $reflection = new \ReflectionMethod($handlerInstance, $method);
            $methodParams = $reflection->getParameters();

            if (count($methodParams) >= 1) {
                $firstParam = $methodParams[0];

                // Check if first parameter expects int (legacy handle signature)
                if ($firstParam->getType() && $firstParam->getType()->getName() === 'int') {
                    $chatId = $context['chat_id'] ?? 0;
                    $params = array_diff_key($parameters, ['chat_id' => null]);
                    return $handlerInstance->$method($chatId, $params);
                }
            }

            // Default: pass full context array (new signature)
            return $handlerInstance->$method($parameters);

        } catch (\Exception $e) {
            Log::error('Failed to execute command', [
                'command_id' => $result->getCommand()->getId(),
                'action' => $result->getCommand()->getAction(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function createFallbackResult(string $input, array $context): CommandResult
    {
        // Try to find similar commands for suggestions
        $similarCommands = $this->registry->searchCommands($input);
        $suggestions = array_slice($similarCommands, 0, 3);

        return new CommandResult(
            success: false,
            message: 'Command not found',
            data: [
                'input' => $input,
                'suggestions' => $suggestions,
                'fallback_message' => 'Desculpe, não entendi esse comando. Tente uma das opções sugeridas.'
            ],
            type: 'command_not_found'
        );
    }

    private function createResult(?CommandMatch $match, string $type, mixed $data = null): CommandResult
    {
        return new CommandResult(
            success: true,
            message: 'Command processed successfully',
            data: $data,
            type: $type,
            commandMatch: $match
        );
    }

    private function createErrorResult(string $operation, string $error): CommandResult
    {
        return new CommandResult(
            success: false,
            message: "Operation failed: {$operation}",
            data: ['error' => $error],
            type: 'error'
        );
    }
}

class CommandResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public mixed $data = null,
        public string $type = 'unknown',
        public ?CommandMatch $commandMatch = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCommandMatch(): ?CommandMatch
    {
        return $this->commandMatch;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'command_match' => $this->commandMatch ? [
                'command_id' => $this->commandMatch->getCommand()->getId(),
                'confidence' => $this->commandMatch->getConfidence(),
                'matched_input' => $this->commandMatch->getMatchedInput()
            ] : null
        ];
    }
}
