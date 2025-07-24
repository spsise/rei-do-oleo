<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Services\Telegram\Reports\GeneralReportGenerator;
use App\Services\Telegram\Reports\ServicesReportGenerator;
use App\Services\Telegram\Reports\ProductsReportGenerator;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Telegram services
        $this->app->singleton(TelegramCommandParser::class);
        $this->app->singleton(TelegramAuthorizationService::class);
        $this->app->singleton(TelegramMenuBuilder::class);

        // Register report generators
        $this->app->singleton(GeneralReportGenerator::class);
        $this->app->singleton(ServicesReportGenerator::class);
        $this->app->singleton(ProductsReportGenerator::class);

        // Register command handler manager
        $this->app->singleton(TelegramCommandHandlerManager::class, function ($app) {
            return new TelegramCommandHandlerManager(
                $app->make(TelegramMenuBuilder::class),
                $app->make(\App\Services\Channels\TelegramChannel::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
