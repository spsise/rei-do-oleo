<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\LoggingServiceInterface;
use App\Services\LoggingService;
use App\Services\FileLoggingService;
use App\Services\ActivityLoggingService;
use App\Services\SafeLoggingService;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the interface to the appropriate implementation based on configuration
        $this->app->bind(LoggingServiceInterface::class, function ($app) {
            $loggingDriver = config('unified-logging.driver', 'activity');

            // Create the primary logger based on configuration
            $primaryLogger = match ($loggingDriver) {
                'file' => new FileLoggingService(),
                'laravel' => new LoggingService(),
                'activity', 'default' => new ActivityLoggingService(),
                default => new ActivityLoggingService(),
            };

            // For now, return the primary logger directly (without SafeLoggingService wrapper)
            // since ActivityLoggingService has its own filtering mechanism
            return $primaryLogger;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration if needed
        $this->publishes([
            __DIR__ . '/../../config/logging.php' => config_path('logging.php'),
        ], 'logging-config');
    }
}
