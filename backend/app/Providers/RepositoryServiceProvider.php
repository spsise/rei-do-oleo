<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Client Domain
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Repositories\ClientRepository;
use App\Domain\Client\Repositories\VehicleRepositoryInterface;
use App\Domain\Client\Repositories\VehicleRepository;

// Product Domain
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepository;
use App\Domain\Product\Repositories\CategoryRepositoryInterface;
use App\Domain\Product\Repositories\CategoryRepository;

// Service Domain
use App\Domain\Service\Repositories\ServiceRepositoryInterface;
use App\Domain\Service\Repositories\ServiceRepository;
use App\Domain\Service\Repositories\ServiceCenterRepositoryInterface;
use App\Domain\Service\Repositories\ServiceCenterRepository;
use App\Domain\Service\Repositories\ServiceItemRepositoryInterface;
use App\Domain\Service\Repositories\ServiceItemRepository;
use App\Domain\Service\Repositories\ServiceTemplateRepositoryInterface;
use App\Domain\Service\Repositories\ServiceTemplateRepository;

// User Domain
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Client Domain Repositories
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);

        // Product Domain Repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);

        // Service Domain Repositories
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceCenterRepositoryInterface::class, ServiceCenterRepository::class);
        $this->app->bind(ServiceItemRepositoryInterface::class, ServiceItemRepository::class);
        $this->app->bind(ServiceTemplateRepositoryInterface::class, ServiceTemplateRepository::class);

        // User Domain Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
