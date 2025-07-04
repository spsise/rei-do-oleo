<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Product\Models\Product;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;

class DatabaseSeederFakeSafe extends Seeder
{
    /**
     * Seed the application's database with fake data for development/testing.
     * This version checks for existing data before creating new ones.
     */
    public function run(): void
    {
        // Run basic seeders first (these handle duplicates internally)
        $this->call([
            RolePermissionSeeder::class,
            ServiceStatusSeeder::class,
            PaymentMethodSeeder::class,
            CategorySeeder::class,
            ServiceCenterSeeder::class,
            UserSeeder::class,
        ]);

        // Check and run fake seeders only if needed
        $this->runFakeSeedersSafely();
    }

    /**
     * Run fake seeders only if data doesn't exist
     */
    private function runFakeSeedersSafely(): void
    {
        // Check clients
        if (Client::count() < 10) {
            $this->call(ClientFakeSeeder::class);
        }

        // Check vehicles
        if (Vehicle::count() < 15) {
            $this->call(VehicleFakeSeeder::class);
        }

        // Check products
        if (Product::count() < 20) {
            $this->call(ProductFakeSeeder::class);
        }

        // Check services
        if (Service::count() < 20) {
            $this->call(ServiceFakeSeeder::class);
        }

        // Check service items
        if (ServiceItem::count() < 30) {
            $this->call(ServiceItemFakeSeeder::class);
        }
    }
}
