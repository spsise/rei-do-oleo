<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:manage
                            {action : Action to perform (stats|clean|analyze|export)}
                            {--days=365 : Number of days for clean action}
                            {--log-name= : Specific log name to filter}
                            {--dry-run : Show what would be done without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Activity Log records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'stats' => $this->showStats(),
            'clean' => $this->cleanLogs(),
            'analyze' => $this->analyzeLogs(),
            'export' => $this->exportLogs(),
            default => $this->showHelp(),
        };
    }

    /**
     * Show statistics about activity logs
     */
    private function showStats(): int
    {
        $this->info('📊 Activity Log Statistics');
        $this->newLine();

        // Total logs
        $totalLogs = Activity::count();
        $this->line("Total logs: <info>{$totalLogs}</info>");

        // Logs by name
        $logsByName = Activity::select('log_name', DB::raw('count(*) as count'))
            ->groupBy('log_name')
            ->orderBy('count', 'desc')
            ->get();

        $this->line('Logs by name:');
        foreach ($logsByName as $log) {
            $this->line("  • {$log->log_name}: <info>{$log->count}</info>");
        }

        // Recent activity
        $recentLogs = Activity::latest()->take(5)->get();
        $this->newLine();
        $this->line('Recent activity:');
        foreach ($recentLogs as $log) {
            $this->line("  • [{$log->created_at->format('Y-m-d H:i:s')}] {$log->description} ({$log->log_name})");
        }

        // Database size
        $tableSize = DB::select("
            SELECT
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = 'activity_log'
        ");

        if (!empty($tableSize)) {
            $this->newLine();
            $this->line("Table size: <info>{$tableSize[0]->{'Size (MB)'}} MB</info>");
        }

        return Command::SUCCESS;
    }

    /**
     * Clean old activity logs
     */
    private function cleanLogs(): int
    {
        $days = (int) $this->option('days');
        $logName = $this->option('log-name');
        $dryRun = $this->option('dry-run');

        $this->info('🧹 Cleaning Activity Logs');
        $this->newLine();

        $query = Activity::where('created_at', '<', now()->subDays($days));

        if ($logName) {
            $query->where('log_name', $logName);
            $this->line("Filtering by log name: <info>{$logName}</info>");
        }

        $count = $query->count();

        if ($count === 0) {
            $this->warn('No logs found to clean.');
            return Command::SUCCESS;
        }

        $this->line("Found <info>{$count}</info> logs older than <info>{$days}</info> days");

        if ($dryRun) {
            $this->warn('DRY RUN: No logs will be deleted');
            return Command::SUCCESS;
        }

        if ($this->confirm("Delete {$count} logs?")) {
            $deleted = $query->delete();
            $this->info("Successfully deleted {$deleted} logs");
        } else {
            $this->warn('Operation cancelled');
        }

        return Command::SUCCESS;
    }

    /**
     * Analyze activity logs
     */
    private function analyzeLogs(): int
    {
        $this->info('🔍 Activity Log Analysis');
        $this->newLine();

        // Most active users
        $this->line('Most active users:');
        $activeUsers = Activity::select('causer_id', DB::raw('count(*) as count'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        foreach ($activeUsers as $user) {
            $userModel = DB::table('users')->where('id', $user->causer_id)->first();
            $name = $userModel ? $userModel->name : "User {$user->causer_id}";
            $this->line("  • {$name}: <info>{$user->count}</info> activities");
        }

        // Most common activities
        $this->newLine();
        $this->line('Most common activities:');
        $commonActivities = Activity::select('description', DB::raw('count(*) as count'))
            ->groupBy('description')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        foreach ($commonActivities as $activity) {
            $this->line("  • {$activity->description}: <info>{$activity->count}</info> times");
        }

        // Activity by hour
        $this->newLine();
        $this->line('Activity by hour (last 24h):');
        $activityByHour = Activity::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        foreach ($activityByHour as $hour) {
            $this->line("  • {$hour->hour}:00 - <info>{$hour->count}</info> activities");
        }

        return Command::SUCCESS;
    }

    /**
     * Export activity logs
     */
    private function exportLogs(): int
    {
        $this->info('📤 Exporting Activity Logs');
        $this->newLine();

        $logs = Activity::with(['causer', 'subject'])
            ->latest()
            ->take(100)
            ->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.json';
        $path = storage_path('exports/' . $filename);

        // Create exports directory if it doesn't exist
        if (!file_exists(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        $exportData = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'causer_type' => $log->causer_type,
                'causer_id' => $log->causer_id,
                'properties' => $log->properties,
                'created_at' => $log->created_at->toISOString(),
                'causer_name' => $log->causer ? $log->causer->name : null,
                'subject_info' => $log->subject ? [
                    'type' => class_basename($log->subject),
                    'id' => $log->subject->id
                ] : null,
            ];
        });

        file_put_contents($path, json_encode($exportData, JSON_PRETTY_PRINT));

        $this->info("Exported {$logs->count()} logs to: <info>{$path}</info>");

        return Command::SUCCESS;
    }

    /**
     * Show help information
     */
    private function showHelp(): int
    {
        $this->error('Invalid action specified');
        $this->newLine();
        $this->line('Available actions:');
        $this->line('  • stats    - Show statistics about activity logs');
        $this->line('  • clean    - Clean old activity logs');
        $this->line('  • analyze  - Analyze activity patterns');
        $this->line('  • export   - Export logs to JSON file');
        $this->newLine();
        $this->line('Examples:');
        $this->line('  php artisan activitylog:manage stats');
        $this->line('  php artisan activitylog:manage clean --days=30');
        $this->line('  php artisan activitylog:manage clean --log-name=users --dry-run');
        $this->line('  php artisan activitylog:manage analyze');
        $this->line('  php artisan activitylog:manage export');

        return Command::FAILURE;
    }
}
