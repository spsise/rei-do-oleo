<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\Product\Models\Product;
use App\Domain\Service\Models\Service;
use App\Domain\Service\Models\ServiceItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeederFakeFinal extends Seeder
{
    /**
     * Seed the application's database with fake data for development/testing.
     * This version handles all duplication issues.
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

        // Run fake seeders in order
        $this->call([
            ClientFakeSeeder::class,
            VehicleFakeSeeder::class,
            ProductFakeSeeder::class,
            ServiceFakeSeeder::class,
            ServiceItemFakeSeederV2::class, // Usar versão melhorada
        ]);

        // Clean any remaining duplicates
        $this->cleanDuplicates();
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

    /**
     * Clean any remaining duplicates
     */
    private function cleanDuplicates(): void
    {
        // Clean duplicate service items
        $duplicateServiceItems = DB::table('service_items')
            ->select('service_id', 'product_id', DB::raw('COUNT(*) as count'))
            ->groupBy('service_id', 'product_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateServiceItems as $duplicate) {
            // Keep only the first record, delete the rest
            $itemsToDelete = ServiceItem::where('service_id', $duplicate->service_id)
                ->where('product_id', $duplicate->product_id)
                ->skip(1)
                ->take($duplicate->count - 1)
                ->get();

            foreach ($itemsToDelete as $item) {
                $item->delete();
            }
        }

        // Clean duplicate products by slug
        $duplicateProducts = DB::table('products')
            ->select('slug', DB::raw('COUNT(*) as count'))
            ->groupBy('slug')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateProducts as $duplicate) {
            // Keep only the first record, delete the rest
            $productsToDelete = Product::where('slug', $duplicate->slug)
                ->skip(1)
                ->take($duplicate->count - 1)
                ->get();

            foreach ($productsToDelete as $product) {
                $product->delete();
            }
        }
    }
}
