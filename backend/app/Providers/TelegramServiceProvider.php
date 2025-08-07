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
use App\Services\TelegramLoggingService;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\SpeechToTextService;
use App\Services\Channels\TelegramChannel;
use App\Repositories\TelegramRepository;

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
        $this->app->singleton(TelegramLoggingService::class);
        $this->app->singleton(TelegramBotService::class);
        $this->app->singleton(TelegramWebhookService::class);
        $this->app->singleton(TelegramRepository::class);
        $this->app->singleton(TelegramChannel::class);

        // Register report generators
        $this->app->singleton(GeneralReportGenerator::class);
        $this->app->singleton(ServicesReportGenerator::class);
        $this->app->singleton(ProductsReportGenerator::class);

        // Register command handler manager
        $this->app->singleton(TelegramCommandHandlerManager::class);

        // Register speech-to-text service
        $this->app->singleton(SpeechToTextService::class);

        // Register TelegramMessageProcessorService
        $this->app->singleton(\App\Services\TelegramMessageProcessorService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
