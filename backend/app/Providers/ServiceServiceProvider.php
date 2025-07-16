<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Client Domain Services
use App\Domain\Client\Services\ClientService;

// Product Domain Services
use App\Domain\Product\Services\ProductService;

// Service Domain Services
use App\Domain\Service\Services\ServiceService;

// Vehicle Domain Services
use App\Domain\Vehicle\Services\VehicleService;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Client Domain Services
        $this->app->singleton(ClientService::class, function ($app) {
            return new ClientService(
                $app->make(\App\Domain\Client\Repositories\ClientRepositoryInterface::class),
                $app->make(\App\Domain\Client\Repositories\VehicleRepositoryInterface::class)
            );
        });

        // Product Domain Services
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(\App\Domain\Product\Repositories\ProductRepositoryInterface::class)
            );
        });

        // Service Domain Services
        $this->app->singleton(ServiceService::class, function ($app) {
            return new ServiceService(
                $app->make(\App\Domain\Service\Repositories\ServiceRepositoryInterface::class),
                $app->make(\App\Domain\Client\Repositories\ClientRepositoryInterface::class),
                $app->make(\App\Domain\Client\Repositories\VehicleRepositoryInterface::class)
            );
        });

        // Vehicle Domain Services
        $this->app->singleton(VehicleService::class, function ($app) {
            return new VehicleService(
                $app->make(\App\Domain\Client\Repositories\VehicleRepositoryInterface::class)
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