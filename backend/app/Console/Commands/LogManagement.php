<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Support\Facades\File;

class LogManagement extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:manage
                            {action : Action to perform (stats|clean|rotate|analyze)}
                            {--channel=* : Specific log channels to process}
                            {--days=30 : Number of days to keep logs}
                            {--dry-run : Show what would be done without executing}';

    /**
     * The console command description.
     */
    protected $description = 'Manage application logs';

    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $channels = $this->option('channel');
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        switch ($action) {
            case 'stats':
                return $this->showStats();
            case 'clean':
                return $this->cleanLogs($channels, $days, $dryRun);
            case 'rotate':
                return $this->rotateLogs($channels, $dryRun);
            case 'analyze':
                return $this->analyzeLogs($channels);
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }

    /**
     * Show log statistics
     */
    private function showStats(): int
    {
        $this->info('📊 Log Statistics');
        $this->newLine();

        $stats = $this->loggingService->getLogStats();

        if (empty($stats)) {
            $this->warn('No log files found.');
            return 0;
        }

        $headers = ['Channel', 'Size (MB)', 'Lines', 'Last Modified'];
        $rows = [];

        foreach ($stats as $channel => $data) {
            $rows[] = [
                $channel,
                round($data['size'] / 1024 / 1024, 2),
                number_format($data['lines']),
                date('Y-m-d H:i:s', $data['last_modified']),
            ];
        }

        $this->table($headers, $rows);
        return 0;
    }

    /**
     * Clean old log files
     */
    private function cleanLogs(array $channels, int $days, bool $dryRun): int
    {
        $this->info('🧹 Cleaning old log files...');
        $this->newLine();

        $logPath = storage_path('logs');
        $cutoffTime = time() - ($days * 24 * 60 * 60);

        $deletedCount = 0;
        $deletedSize = 0;

        $files = File::glob($logPath . '/*.log');

        foreach ($files as $file) {
            $filename = basename($file);
            $fileTime = filemtime($file);
            $fileSize = filesize($file);

            // Check if file is older than cutoff time
            if ($fileTime < $cutoffTime) {
                // Check if we should process this channel
                if (!empty($channels)) {
                    $channelName = str_replace('.log', '', $filename);
                    if (!in_array($channelName, $channels)) {
                        continue;
                    }
                }

                if ($dryRun) {
                    $this->line("Would delete: {$filename} (" . round($fileSize / 1024 / 1024, 2) . " MB)");
                } else {
                    File::delete($file);
                    $this->line("Deleted: {$filename} (" . round($fileSize / 1024 / 1024, 2) . " MB)");
                }

                $deletedCount++;
                $deletedSize += $fileSize;
            }
        }

        if ($deletedCount === 0) {
            $this->info('No old log files found to clean.');
        } else {
            $this->info("Cleaned {$deletedCount} files (" . round($deletedSize / 1024 / 1024, 2) . " MB)");
        }

        return 0;
    }

    /**
     * Rotate log files
     */
    private function rotateLogs(array $channels, bool $dryRun): int
    {
        $this->info('🔄 Rotating log files...');
        $this->newLine();

        $logPath = storage_path('logs');
        $rotatedCount = 0;

        $files = File::glob($logPath . '/*.log');

        foreach ($files as $file) {
            $filename = basename($file);
            $channelName = str_replace('.log', '', $filename);

            // Check if we should process this channel
            if (!empty($channels) && !in_array($channelName, $channels)) {
                continue;
            }

            $fileSize = filesize($file);

            // Only rotate files larger than 10MB
            if ($fileSize > 10 * 1024 * 1024) {
                $timestamp = date('Y-m-d_H-i-s');
                $newFilename = $filename . '.' . $timestamp;
                $newPath = $logPath . '/' . $newFilename;

                if ($dryRun) {
                    $this->line("Would rotate: {$filename} -> {$newFilename}");
                } else {
                    File::move($file, $newPath);
                    $this->line("Rotated: {$filename} -> {$newFilename}");
                }

                $rotatedCount++;
            }
        }

        if ($rotatedCount === 0) {
            $this->info('No log files need rotation.');
        } else {
            $this->info("Rotated {$rotatedCount} files.");
        }

        return 0;
    }

    /**
     * Analyze log files
     */
    private function analyzeLogs(array $channels): int
    {
        $this->info('📈 Analyzing log files...');
        $this->newLine();

        $logPath = storage_path('logs');
        $analysis = [];

        $files = File::glob($logPath . '/*.log');

        foreach ($files as $file) {
            $filename = basename($file);
            $channelName = str_replace('.log', '', $filename);

            // Check if we should process this channel
            if (!empty($channels) && !in_array($channelName, $channels)) {
                continue;
            }

            $content = File::get($file);
            $lines = explode("\n", $content);

            $errorCount = 0;
            $warningCount = 0;
            $infoCount = 0;
            $debugCount = 0;

            foreach ($lines as $line) {
                if (str_contains($line, '.ERROR:')) {
                    $errorCount++;
                } elseif (str_contains($line, '.WARNING:')) {
                    $warningCount++;
                } elseif (str_contains($line, '.INFO:')) {
                    $infoCount++;
                } elseif (str_contains($line, '.DEBUG:')) {
                    $debugCount++;
                }
            }

            $analysis[$channelName] = [
                'total_lines' => count($lines),
                'errors' => $errorCount,
                'warnings' => $warningCount,
                'info' => $infoCount,
                'debug' => $debugCount,
            ];
        }

        if (empty($analysis)) {
            $this->warn('No log files found to analyze.');
            return 0;
        }

        $headers = ['Channel', 'Total Lines', 'Errors', 'Warnings', 'Info', 'Debug'];
        $rows = [];

        foreach ($analysis as $channel => $data) {
            $rows[] = [
                $channel,
                number_format($data['total_lines']),
                number_format($data['errors']),
                number_format($data['warnings']),
                number_format($data['info']),
                number_format($data['debug']),
            ];
        }

        $this->table($headers, $rows);
        return 0;
    }
}
