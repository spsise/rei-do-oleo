<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class VerifyTestEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:verify-environment {--fix : Try to fix the environment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that the test environment is properly configured';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verifying test environment...');

        // Check environment
        $appEnv = app()->environment();
        $this->line("Environment: {$appEnv}");

        if ($appEnv !== 'testing') {
            $this->error("❌ Wrong environment! Expected: testing, Got: {$appEnv}");

            if ($this->option('fix')) {
                $this->warn("Setting APP_ENV=testing...");
                putenv('APP_ENV=testing');
                $this->info("✅ Environment set to testing");
            } else {
                $this->error("Run with --fix to attempt automatic fix");
                return 1;
            }
        } else {
            $this->info("✅ Environment is correct: {$appEnv}");
        }

        // Check database configuration
        $dbConfig = config('database.connections.mysql');
        $currentDb = $dbConfig['database'] ?? 'unknown';
        $expectedDb = 'rei_do_oleo_test';

        $this->line("Database: {$currentDb}");

        if ($currentDb !== $expectedDb) {
            $this->error("❌ Wrong database! Expected: {$expectedDb}, Got: {$currentDb}");

            if ($this->option('fix')) {
                $this->warn("Attempting to fix database configuration...");

                // Try to reload configuration
                $this->call('config:clear');

                // Check again
                $dbConfig = config('database.connections.mysql');
                $currentDb = $dbConfig['database'] ?? 'unknown';

                if ($currentDb === $expectedDb) {
                    $this->info("✅ Database configuration fixed");
                } else {
                    $this->error("❌ Could not fix database configuration automatically");
                    $this->line("Please check your .env.testing file");
                    return 1;
                }
            } else {
                $this->error("Run with --fix to attempt automatic fix");
                return 1;
            }
        } else {
            $this->info("✅ Database configuration is correct: {$currentDb}");
        }

        // Test database connection
        try {
            DB::connection()->getPdo();
            $this->info("✅ Database connection successful");
        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: " . $e->getMessage());
            return 1;
        }

        // Check if test database exists and has tables
        try {
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            $this->info("✅ Test database has {$tableCount} tables");

            if ($tableCount === 0) {
                $this->warn("⚠️ Test database is empty. You may need to run migrations.");
            }
        } catch (\Exception $e) {
            $this->error("❌ Could not check tables: " . $e->getMessage());
            return 1;
        }

        $this->info("🎉 Test environment verification completed successfully!");
        return 0;
    }
}
