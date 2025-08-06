<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Client Domain Services
use App\Domain\Client\Services\ClientService;

// Product Domain Services
use App\Domain\Product\Services\ProductService;

// Service Domain Services
use App\Domain\Service\Services\ServiceService;
use App\Domain\Service\Services\ServiceItemService;

// Service Domain Actions
use App\Domain\Service\Actions\CreateServiceAction;
use App\Domain\Service\Actions\UpdateServiceAction;
use App\Domain\Service\Actions\DeleteServiceAction;
use App\Domain\Service\Actions\UpdateServiceStatusAction;
use App\Domain\Service\Actions\GetServiceStatsAction;

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

        // Service Item Domain Services
        $this->app->singleton(ServiceItemService::class, function ($app) {
            return new ServiceItemService(
                $app->make(\App\Domain\Service\Repositories\ServiceItemRepositoryInterface::class),
                $app->make(\App\Domain\Service\Repositories\ServiceRepositoryInterface::class)
            );
        });

        // Vehicle Domain Services
        $this->app->singleton(VehicleService::class, function ($app) {
            return new VehicleService(
                $app->make(\App\Domain\Client\Repositories\VehicleRepositoryInterface::class)
            );
        });

        // Service Domain Actions
        $this->app->singleton(CreateServiceAction::class, function ($app) {
            return new CreateServiceAction(
                $app->make(ServiceService::class),
                $app->make(\App\Services\DataMappingService::class)
            );
        });

        $this->app->singleton(UpdateServiceAction::class, function ($app) {
            return new UpdateServiceAction(
                $app->make(ServiceService::class),
                $app->make(\App\Services\DataMappingService::class)
            );
        });

        $this->app->singleton(DeleteServiceAction::class, function ($app) {
            return new DeleteServiceAction(
                $app->make(ServiceService::class)
            );
        });

        $this->app->singleton(UpdateServiceStatusAction::class, function ($app) {
            return new UpdateServiceStatusAction(
                $app->make(ServiceService::class)
            );
        });

        $this->app->singleton(GetServiceStatsAction::class, function ($app) {
            return new GetServiceStatsAction(
                $app->make(\App\Domain\Service\Repositories\ServiceRepositoryInterface::class)
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
