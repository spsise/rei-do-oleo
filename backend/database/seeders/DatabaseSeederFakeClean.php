<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Product\Models\Product;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;

class DatabaseSeederFakeClean extends Seeder
{
    /**
     * Seed the application's database with fake data for development/testing.
     * This version cleans existing fake data before creating new ones.
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

        // Clean existing fake data
        $this->cleanFakeData();

        // Run fake seeders
        $this->call([
            ClientFakeSeeder::class,
            VehicleFakeSeeder::class,
            ProductFakeSeeder::class,
            ServiceFakeSeeder::class,
            ServiceItemFakeSeeder::class,
        ]);
    }

    /**
     * Clean existing fake data
     */
    private function cleanFakeData(): void
    {
        // Delete in reverse order to avoid foreign key constraints
        ServiceItem::truncate();
        Service::truncate();
        Product::truncate();
        Vehicle::truncate();
        Client::truncate();
    }
}
