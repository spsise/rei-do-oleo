<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Product\Models\Product;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeederFakeTransactional extends Seeder
{
    /**
     * Seed the application's database with fake data for development/testing.
     * This version uses transactions for consistency.
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

        // Clean existing fake data completely
        $this->cleanAllFakeData();

        // Run fake seeders with transaction
        DB::beginTransaction();

        try {
            $this->call([
                ClientFakeSeeder::class,
                VehicleFakeSeeder::class,
                ProductFakeSeeder::class,
                ServiceFakeSeeder::class,
                ServiceItemFakeSeeder::class,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Clean all existing fake data
     */
    private function cleanAllFakeData(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Delete in reverse order to avoid foreign key constraints
        ServiceItem::truncate();
        Service::truncate();
        Product::truncate();
        Vehicle::truncate();
        Client::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
