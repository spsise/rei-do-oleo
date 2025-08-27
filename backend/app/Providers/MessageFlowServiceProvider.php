<?php

namespace App\Providers;

use App\Contracts\MessageFlowTrackerInterface;
use App\Services\MessageFlowTrackerService;
use Illuminate\Support\ServiceProvider;

class MessageFlowServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra a interface como singleton para compartilhar a mesma instância
        $this->app->singleton(MessageFlowTrackerInterface::class, MessageFlowTrackerService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publica o arquivo de configuração
        $this->publishes([
            __DIR__.'/../../config/message-flow.php' => config_path('message-flow.php'),
        ], 'message-flow-config');
    }
}
