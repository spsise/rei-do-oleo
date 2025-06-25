<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order of dependencies
        $this->call([
            // 1. Basic reference data
            RolePermissionSeeder::class,
            ServiceStatusSeeder::class,
            PaymentMethodSeeder::class,
            CategorySeeder::class,

            // 2. Service centers (before users for foreign key)
            ServiceCenterSeeder::class,

            // 3. Users with roles and service center assignment
            // UserSeeder::class, // Will be created separately if needed

            // 4. Sample data for testing
            // ClientSeeder::class, // Will be created separately if needed
        ]);
    }
}
