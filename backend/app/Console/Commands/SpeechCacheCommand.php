<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpeechToTextService;

class SpeechCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'speech:cache
                            {action : Action to perform (diagnose, cleanup, clear, stats, force-cleanup)}
                            {--force : Force the action without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Manage speech-to-text cache and diagnose issues';

    /**
     * Execute the console command.
     */
    public function handle(SpeechToTextService $speechService): int
    {
        $action = $this->argument('action');
        $force = $this->option('force');

        $this->info("🔊 Speech-to-Text Cache Management");
        $this->info("Action: {$action}");
        $this->newLine();

        try {
            switch ($action) {
                case 'diagnose':
                    return $this->diagnose($speechService);

                case 'cleanup':
                    return $this->cleanup($speechService, $force);

                case 'clear':
                    return $this->clear($speechService, $force);

                case 'stats':
                    return $this->stats($speechService);

                case 'force-cleanup':
                    return $this->forceCleanup($speechService, $force);

                default:
                    $this->error("Unknown action: {$action}");
                    $this->info("Available actions: diagnose, cleanup, clear, stats, force-cleanup");
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error executing command: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Diagnose cache issues
     */
    private function diagnose(SpeechToTextService $speechService): int
    {
        $this->info("🔍 Diagnosing cache issues...");

        $diagnosis = $speechService->diagnoseCacheIssues();

        if (isset($diagnosis['error'])) {
            $this->error("Diagnosis failed: " . $diagnosis['error']);
            return 1;
        }

        $this->displayDiagnosis($diagnosis);
        return 0;
    }

    /**
     * Clean up expired cache and duplicate logs
     */
    private function cleanup(SpeechToTextService $speechService, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('Are you sure you want to clean up expired cache and duplicate logs?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("🧹 Cleaning up cache...");

        $cleanedCount = $speechService->clearExpiredCache();
        $duplicateLogsResult = $speechService->cleanupDuplicateLogs();

        $this->info("✅ Cleanup completed:");
        $this->info("  - Expired cache entries: {$cleanedCount}");

        if ($duplicateLogsResult['success']) {
            $this->info("  - Duplicate logs cleaned: {$duplicateLogsResult['cleaned_entries']}");
        } else {
            $this->warn("  - Duplicate logs cleanup failed: " . $duplicateLogsResult['error']);
        }

        return 0;
    }

    /**
     * Clear all speech cache
     */
    private function clear(SpeechToTextService $speechService, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('Are you sure you want to clear ALL speech cache? This cannot be undone.')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("🗑️ Clearing all speech cache...");

        $result = $speechService->clearAllSpeechCache();

        if ($result) {
            $this->info("✅ All speech cache cleared successfully.");
        } else {
            $this->error("❌ Failed to clear speech cache.");
            return 1;
        }

        return 0;
    }

    /**
     * Show cache statistics
     */
    private function stats(SpeechToTextService $speechService): int
    {
        $this->info("📊 Cache Statistics:");

        $stats = $speechService->getCacheStats();

        if (isset($stats['error'])) {
            $this->error("Failed to get stats: " . $stats['error']);
            return 1;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', $stats['total_entries']],
                ['Active Entries', $stats['active_entries']],
                ['Expired Entries', $stats['expired_entries']],
                ['Total Size (chars)', $stats['total_size']],
                ['Duplicate Log Flags', $stats['duplicate_log_flags']],
                ['Stuck Processing Flags', $stats['stuck_processing_flags']],
            ]
        );

        if ($stats['oldest_entry']) {
            $this->info("Oldest Entry: " . date('Y-m-d H:i:s', $stats['oldest_entry']));
        }

        if ($stats['newest_entry']) {
            $this->info("Newest Entry: " . date('Y-m-d H:i:s', $stats['newest_entry']));
        }

        return 0;
    }

    /**
     * Force cleanup of all cache-related issues
     */
    private function forceCleanup(SpeechToTextService $speechService, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('This will force cleanup ALL cache issues. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("🚀 Force cleaning up all cache issues...");

        $result = $speechService->forceCleanup();

        if ($result['success']) {
            $this->info("✅ Force cleanup completed successfully:");
            $this->info("  - Expired cache cleared: " . $result['results']['expired_cache_cleared']);
            $this->info("  - Duplicate logs cleaned: " . $result['results']['duplicate_logs_cleaned']);
            $this->info("  - Stuck processing flags cleaned: " . $result['results']['stuck_processing_flags_cleaned']);
            $this->info("  - Orphaned metadata cleaned: " . $result['results']['orphaned_metadata_cleaned']);
        } else {
            $this->error("❌ Force cleanup failed: " . $result['error']);
            return 1;
        }

        return 0;
    }

    /**
     * Display diagnosis results
     */
    private function displayDiagnosis(array $diagnosis): void
    {
        $this->info("📋 Diagnosis Results:");
        $this->newLine();

        // Cache Stats
        $this->info("📊 Cache Statistics:");
        $stats = $diagnosis['cache_stats'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', $stats['total_entries']],
                ['Active Entries', $stats['active_entries']],
                ['Expired Entries', $stats['expired_entries']],
                ['Duplicate Log Flags', $stats['duplicate_log_flags']],
                ['Stuck Processing Flags', $stats['stuck_processing_flags']],
            ]
        );

        // Duplicate Log Analysis
        if (!empty($diagnosis['duplicate_log_analysis'])) {
            $this->info("🔍 Duplicate Log Analysis:");
            $this->table(
                ['Cache Key', 'Age (seconds)', 'Note'],
                array_map(function($item) {
                    return [
                        substr($item['cache_key'], 0, 30) . '...',
                        $item['hit_logged_age'],
                        $item['note']
                    ];
                }, $diagnosis['duplicate_log_analysis'])
            );
        }

        // Processing Flags Analysis
        if (!empty($diagnosis['processing_flags_analysis'])) {
            $this->info("⚙️ Processing Flags Analysis:");
            $this->table(
                ['Cache Key', 'Age (seconds)', 'Status', 'Note'],
                array_map(function($item) {
                    return [
                        substr($item['cache_key'], 0, 30) . '...',
                        $item['processing_age'],
                        $item['stuck'] ? '🔴 Stuck' : '🟢 Active',
                        $item['note']
                    ];
                }, $diagnosis['processing_flags_analysis'])
            );
        }

        // Recommendations
        if (!empty($diagnosis['recommendations'])) {
            $this->newLine();
            $this->warn("💡 Recommendations:");
            foreach ($diagnosis['recommendations'] as $recommendation) {
                $this->line("  • {$recommendation}");
            }
        }

        $this->newLine();
        $this->info("💡 Use 'php artisan speech:cache cleanup' to clean up issues");
        $this->info("💡 Use 'php artisan speech:cache force-cleanup' for aggressive cleanup");
    }
}
