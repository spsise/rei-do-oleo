<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Client permissions
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            // Vehicle permissions
            'vehicles.view',
            'vehicles.create',
            'vehicles.update',

            // Service permissions
            'services.view',
            'services.create',
            'services.update',
            'services.complete',
            'services.cancel',

            // Product permissions
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Category permissions
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Service Center permissions
            'service_centers.view',
            'service_centers.create',
            'service_centers.update',
            'service_centers.delete',

            // Dashboard and Reports
            'dashboard.view',
            'reports.view',
            'reports.export',

            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // System permissions
            'system.settings',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - all permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Manager role - most permissions except system settings
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'clients.view', 'clients.create', 'clients.update',
            'vehicles.view', 'vehicles.create', 'vehicles.update',
            'services.view', 'services.create', 'services.update', 'services.complete', 'services.cancel',
            'products.view', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'service_centers.view', 'service_centers.update',
            'dashboard.view', 'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.update'
        ]);

        // Attendant role - front office operations
        $attendant = Role::create(['name' => 'attendant']);
        $attendant->givePermissionTo([
            'clients.view', 'clients.create', 'clients.update',
            'vehicles.view', 'vehicles.create', 'vehicles.update',
            'services.view', 'services.create', 'services.update',
            'products.view',
            'categories.view',
            'service_centers.view',
            'dashboard.view'
        ]);

        // Technician role - service execution
        $technician = Role::create(['name' => 'technician']);
        $technician->givePermissionTo([
            'clients.view',
            'vehicles.view',
            'services.view', 'services.update', 'services.complete',
            'products.view',
            'categories.view',
            'service_centers.view'
        ]);
    }
}
