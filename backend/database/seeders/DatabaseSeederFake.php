<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeederFake extends Seeder
{
    /**
     * Seed the application's database with fake data for development/testing.
     */
    public function run(): void
    {
        // Run seeders in order of dependencies
        $this->call([
            // 1. Basic reference data (mantém os seeders existentes)
            RolePermissionSeeder::class,
            ServiceStatusSeeder::class,
            PaymentMethodSeeder::class,
            CategorySeeder::class,

            // 2. Service centers (before users for foreign key)
            ServiceCenterSeeder::class,

            // 3. Users with roles and service center assignment
            UserSeeder::class,

            // 4. Fake data seeders (novos)
            ClientFakeSeeder::class,
            VehicleFakeSeeder::class,
            ProductFakeSeeder::class,
            ServiceFakeSeeder::class,
            ServiceItemFakeSeeder::class,
        ]);
    }
}
