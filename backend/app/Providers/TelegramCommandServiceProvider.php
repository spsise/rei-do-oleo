<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandRegistry;
use App\Services\Telegram\Commands\Cache\CommandCache;
use App\Services\Telegram\Commands\Learning\CommandLearning;
use App\Services\Telegram\Commands\Repositories\CommandConfigRepository;
use App\Contracts\Telegram\Commands\CommandRegistryInterface;

class TelegramCommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(CommandConfigRepository::class);

        // Register cache
        $this->app->singleton(CommandCache::class);

        // Register learning system
        $this->app->singleton(CommandLearning::class);

        // Register registry
        $this->app->singleton(CommandRegistry::class);
        $this->app->bind(CommandRegistryInterface::class, CommandRegistry::class);

        // Register unified system
        $this->app->singleton(UnifiedCommandSystem::class);

        // Bind to interface for easy access
        $this->app->bind('telegram.commands', UnifiedCommandSystem::class);
    }

    public function boot(): void
    {
        // Publish configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/telegram-commands.php' => config_path('telegram-commands.php'),
            ], 'telegram-commands-config');
        }

        // Load commands when service provider boots
        $this->loadCommands();
    }

    private function loadCommands(): void
    {
        try {
            // Only load if database is available
            if ($this->app->runningInConsole() || $this->app->environment('testing')) {
                return;
            }

            $registry = $this->app->make(CommandRegistry::class);
            $registry->reloadCommands();

        } catch (\Exception $e) {
            // Silently fail if database is not available
            // This prevents errors during migrations
        }
    }

    public function provides(): array
    {
        return [
            CommandConfigRepository::class,
            CommandCache::class,
            CommandLearning::class,
            CommandRegistry::class,
            CommandRegistryInterface::class,
            UnifiedCommandSystem::class,
            'telegram.commands'
        ];
    }
}
