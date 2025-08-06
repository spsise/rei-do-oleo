<?php

namespace App\Providers;

use App\Channels\WhatsAppChannel;
use App\Services\WhatsAppService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService();
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Register WhatsApp channel
        Notification::extend('whatsapp', function ($app) {
            return new WhatsAppChannel($app->make(WhatsAppService::class));
        });
    }
}
