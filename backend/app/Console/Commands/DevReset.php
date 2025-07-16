<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DevReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reset 
                            {--fake : Generate fake data after seeding}
                            {--force : Force reset without confirmation}
                            {--seed-only : Only run seeders without migrate:fresh}
                            {--safe : Use safe seeder that checks existing data}
                            {--clean : Clean existing fake data before creating new ones}
                            {--final : Use final seeder that resolves all duplication issues}
                            {--only= : Run only a specific seeder (clients, vehicles, products, services, items)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset development database with fresh migrations and optional fake data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if we're in development environment
        if (!app()->environment(['local', 'development'])) {
            $this->error('This command can only be run in development environment!');
            return 1;
        }

        // Confirmation prompt (unless --force is used)
        if (!$this->option('force')) {
            if (!$this->confirm('This will completely reset your development database. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔄 Starting development database reset...');

        try {
            // Step 1: Fresh migrations (unless --seed-only is used)
            if (!$this->option('seed-only')) {
                $this->info('📊 Running fresh migrations...');
                $this->call('migrate:fresh', ['--force' => true]);
                $this->info('✅ Migrations completed successfully!');
            }

            // Step 2: Run basic seeders
            $this->info('🌱 Running basic seeders...');
            $this->runBasicSeeders();
            $this->info('✅ Basic seeders completed successfully!');

            // Step 3: Generate fake data if requested
            if ($this->option('fake')) {
                $this->info('🎭 Generating fake data...');
                $this->generateFakeData();
                $this->info('✅ Fake data generated successfully!');
            }

            // Step 4: Clear caches
            $this->info('🧹 Clearing application caches...');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            $this->call('cache:clear');
            $this->info('✅ Caches cleared successfully!');

            $this->info('🎉 Development database reset completed successfully!');
            
            // Show summary
            $this->showSummary();

        } catch (\Exception $e) {
            $this->error('❌ Error during database reset: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Run basic seeders (roles, permissions, statuses, etc.)
     */
    private function runBasicSeeders()
    {
        $basicSeeders = [
            'RolePermissionSeeder',
            'ServiceStatusSeeder',
            'PaymentMethodSeeder',
            'CategorySeeder',
            'ServiceCenterSeeder',
            'UserSeeder'
        ];

        foreach ($basicSeeders as $seeder) {
            $this->info("  - Running {$seeder}...");
            try {
                $this->call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                $this->warn("  - Warning: {$seeder} failed - {$e->getMessage()}");
            }
        }
    }

    /**
     * Generate fake data for development
     */
    private function generateFakeData()
    {
        $only = $this->option('only');
        $safe = $this->option('safe');
        $clean = $this->option('clean');
        $final = $this->option('final');

        if ($only) {
            $this->runSpecificSeeder($only);
        } elseif ($safe) {
            $this->runSafeSeeder();
        } elseif ($clean) {
            $this->runCleanSeeder();
        } elseif ($final) {
            $this->runFinalSeeder();
        } else {
            $this->runCompleteSeeder();
        }
    }

    /**
     * Run specific seeder
     */
    private function runSpecificSeeder(string $seeder): void
    {
        $seeders = [
            'clients' => 'ClientFakeSeeder',
            'vehicles' => 'VehicleFakeSeeder',
            'products' => 'ProductFakeSeeder',
            'services' => 'ServiceFakeSeeder',
            'items' => 'ServiceItemFakeSeeder',
        ];

        if (!isset($seeders[$seeder])) {
            $this->error("❌ Seeder '$seeder' not found. Available options: " . implode(', ', array_keys($seeders)));
            return;
        }

        $seederClass = $seeders[$seeder];
        $this->info("🌱 Running $seederClass...");

        try {
            $this->call('db:seed', ['--class' => $seederClass, '--force' => true]);
            $this->info("✅ $seederClass completed successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Error running $seederClass: " . $e->getMessage());
        }
    }

    /**
     * Run complete seeder
     */
    private function runCompleteSeeder(): void
    {
        $this->info('🌱 Running DatabaseSeederFake...');

        try {
            $this->call('db:seed', ['--class' => 'DatabaseSeederFake', '--force' => true]);
            $this->info('✅ DatabaseSeederFake completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error running DatabaseSeederFake: ' . $e->getMessage());

            $this->warn('💡 Trying to run individual seeders...');
            $this->runIndividualSeeders();
        }
    }

    /**
     * Run safe seeder
     */
    private function runSafeSeeder(): void
    {
        $this->info('🌱 Running DatabaseSeederFakeSafe...');

        try {
            $this->call('db:seed', ['--class' => 'DatabaseSeederFakeSafe', '--force' => true]);
            $this->info('✅ DatabaseSeederFakeSafe completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error running DatabaseSeederFakeSafe: ' . $e->getMessage());
        }
    }

    /**
     * Run clean seeder
     */
    private function runCleanSeeder(): void
    {
        $this->info('🧹 Running DatabaseSeederFakeClean...');

        try {
            $this->call('db:seed', ['--class' => 'DatabaseSeederFakeClean', '--force' => true]);
            $this->info('✅ DatabaseSeederFakeClean completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error running DatabaseSeederFakeClean: ' . $e->getMessage());
        }
    }

    /**
     * Run final seeder
     */
    private function runFinalSeeder(): void
    {
        $this->info('🎯 Running DatabaseSeederFakeFinal...');

        try {
            $this->call('db:seed', ['--class' => 'DatabaseSeederFakeFinal', '--force' => true]);
            $this->info('✅ DatabaseSeederFakeFinal completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error running DatabaseSeederFakeFinal: ' . $e->getMessage());
        }
    }

    /**
     * Run individual seeders in case of error
     */
    private function runIndividualSeeders(): void
    {
        $seeders = [
            'ClientFakeSeeder',
            'VehicleFakeSeeder',
            'ProductFakeSeeder',
            'ServiceFakeSeeder',
            'ServiceItemFakeSeeder',
        ];

        foreach ($seeders as $seeder) {
            $this->info("🌱 Running $seeder...");

            try {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
                $this->info("✅ $seeder completed successfully!");
            } catch (\Exception $e) {
                $this->error("❌ Error running $seeder: " . $e->getMessage());
                $this->warn("⚠️ Continuing with next seeder...");
            }
        }
    }

    /**
     * Show summary of database contents
     */
    private function showSummary()
    {
        $this->newLine();
        $this->info('📊 Database Summary:');
        
        $tables = [
            'users' => 'Users',
            'clients' => 'Clients', 
            'vehicles' => 'Vehicles',
            'products' => 'Products',
            'services' => 'Services',
            'service_centers' => 'Service Centers',
            'categories' => 'Categories',
            'payment_methods' => 'Payment Methods',
            'service_statuses' => 'Service Statuses',
            'service_items' => 'Service Items'
        ];

        foreach ($tables as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $this->line("  • {$label}: {$count} records");
            } catch (\Exception $e) {
                $this->line("  • {$label}: Table not found");
            }
        }
    }
}
