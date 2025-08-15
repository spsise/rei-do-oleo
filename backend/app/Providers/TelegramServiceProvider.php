<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Services\Telegram\TelegramCommandParser;
use App\Services\Telegram\TelegramCommandHandlerManager;
use App\Services\Telegram\TelegramAuthorizationService;

use App\Services\Telegram\Reports\GeneralReportGenerator;
use App\Services\Telegram\Reports\ServicesReportGenerator;
use App\Services\Telegram\Reports\ProductsReportGenerator;

use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\SpeechToTextService;
use App\Services\Channels\TelegramChannel;


class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register core Telegram services
        $this->app->singleton(TelegramAuthorizationService::class);
        $this->app->singleton(TelegramBotService::class);
        $this->app->singleton(TelegramWebhookService::class);
        $this->app->singleton(TelegramChannel::class);

        // Register legacy services (used by deprecated TelegramMessageProcessorService and debug commands)
        //$this->app->singleton(TelegramCommandParser::class);  // @deprecated - only used in old service + debug commands

        // Register report generators
        $this->app->singleton(GeneralReportGenerator::class);
        $this->app->singleton(ServicesReportGenerator::class);
        $this->app->singleton(ProductsReportGenerator::class);

        // Register command handler manager (used by debug commands)
        //$this->app->singleton(TelegramCommandHandlerManager::class);  // @deprecated - only used in debug commands

        // Register speech-to-text service
        $this->app->singleton(SpeechToTextService::class);

        // Register the NEW TelegramMessageProcessorService (with database integration)
        $this->app->singleton(\App\Services\Telegram\TelegramMessageProcessorService::class, function ($app) {
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

            return new \App\Services\Telegram\TelegramMessageProcessorService(
                $app->make(\App\Services\Telegram\Commands\UnifiedCommandSystem::class),
                $app->make(\App\Services\Telegram\TelegramAuthorizationService::class),
                $speechService,
                $app->make(\App\Services\Channels\TelegramChannel::class),
                $app->make(\App\Contracts\LoggingServiceInterface::class)
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
