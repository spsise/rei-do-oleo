<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\LoggingServiceInterface;
use App\Services\LoggingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind LoggingService interface to implementation
        $this->app->bind(LoggingServiceInterface::class, LoggingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
