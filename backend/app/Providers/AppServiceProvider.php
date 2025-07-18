<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Service\Repositories\ServiceTemplateRepositoryInterface;
use App\Domain\Service\Repositories\ServiceTemplateRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Service Template Repository
        $this->app->bind(ServiceTemplateRepositoryInterface::class, ServiceTemplateRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
