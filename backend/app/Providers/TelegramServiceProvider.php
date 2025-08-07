<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
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

        // Register TelegramMessageProcessorService with conditional dependencies
        $this->app->singleton(\App\Services\TelegramMessageProcessorService::class, function ($app) {
            $speechService = null;

            try {
                // Try to resolve SpeechToTextService, but don't fail if it's not available
                if (class_exists(SpeechToTextService::class)) {
                    $speechService = $app->make(SpeechToTextService::class);
                }
            } catch (\Exception $e) {
                // Log the error but continue without speech service
                Log::warning('SpeechToTextService not available for TelegramMessageProcessorService', [
                    'error' => $e->getMessage()
                ]);
            }

            return new \App\Services\TelegramMessageProcessorService(
                $app->make(\App\Services\TelegramBotService::class),
                $app->make(\App\Services\Channels\TelegramChannel::class),
                $app->make(\App\Contracts\LoggingServiceInterface::class),
                $speechService
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
