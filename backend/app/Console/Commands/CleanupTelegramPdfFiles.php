<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Contracts\LoggingServiceInterface;
use Carbon\Carbon;

class CleanupTelegramPdfFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:cleanup-pdf-files
                           {--minutes=60 : Delete files older than X minutes}
                           {--dry-run : Show what files would be deleted without deleting them}
                           {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary PDF files generated for Telegram reports';

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
        $minutes = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🧹 Telegram PDF Cleanup");
        $this->info("Looking for PDF files older than {$minutes} minutes...");
        $this->newLine();

        try {
            $reportPath = 'telegram/reports';
            $disk = Storage::disk('local');

            // Check if directory exists
            if (!$disk->exists($reportPath)) {
                $this->info("📁 Directory {$reportPath} doesn't exist. Nothing to clean.");
                return Command::SUCCESS;
            }

            // Get all PDF files in the reports directory
            $allFiles = $disk->files($reportPath);
            $pdfFiles = array_filter($allFiles, fn($file) => str_ends_with(strtolower($file), '.pdf'));

            if (empty($pdfFiles)) {
                $this->info("✅ No PDF files found in {$reportPath}");
                return Command::SUCCESS;
            }

            $this->info("📄 Found " . count($pdfFiles) . " PDF files");

            // Filter files older than specified minutes
            $cutoffTime = Carbon::now()->subMinutes($minutes);
            $oldFiles = [];
            $totalSize = 0;

            foreach ($pdfFiles as $file) {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                $fileSize = $disk->size($file);

                if ($lastModified->lt($cutoffTime)) {
                    $oldFiles[] = [
                        'path' => $file,
                        'modified' => $lastModified,
                        'size' => $fileSize,
                        'age_minutes' => $lastModified->diffInMinutes(Carbon::now())
                    ];
                    $totalSize += $fileSize;
                }
            }

            if (empty($oldFiles)) {
                $this->info("✅ No files older than {$minutes} minutes found");
                return Command::SUCCESS;
            }

            // Display files to be deleted
            $this->warn("🗑️ Found " . count($oldFiles) . " files to delete:");
            $this->newLine();

            $headers = ['File', 'Age (minutes)', 'Size', 'Last Modified'];
            $rows = [];

            foreach ($oldFiles as $file) {
                $rows[] = [
                    basename($file['path']),
                    $file['age_minutes'],
                    $this->formatFileSize($file['size']),
                    $file['modified']->format('Y-m-d H:i:s')
                ];
            }

            $this->table($headers, $rows);
            $this->info("Total size: " . $this->formatFileSize($totalSize));
            $this->newLine();

            if ($dryRun) {
                $this->info("🔍 DRY RUN: Files would be deleted but --dry-run flag is set");
                return Command::SUCCESS;
            }

            // Confirmation
            if (!$force) {
                if (!$this->confirm("Are you sure you want to delete these " . count($oldFiles) . " files?")) {
                    $this->info("❌ Operation cancelled");
                    return Command::SUCCESS;
                }
            }

            // Delete files
            $deletedCount = 0;
            $failedCount = 0;
            $deletedSize = 0;

            foreach ($oldFiles as $file) {
                try {
                    if ($disk->delete($file['path'])) {
                        $deletedCount++;
                        $deletedSize += $file['size'];

                        $this->loggingService->logTelegramEvent('pdf_cleanup_deleted', [
                            'file_path' => $file['path'],
                            'age_minutes' => $file['age_minutes'],
                            'file_size' => $file['size']
                        ], 'info');

                        if ($this->output->isVerbose()) {
                            $this->line("✅ Deleted: " . basename($file['path']));
                        }
                    } else {
                        $failedCount++;
                        $this->error("❌ Failed to delete: " . basename($file['path']));
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $this->error("❌ Error deleting " . basename($file['path']) . ": " . $e->getMessage());

                    $this->loggingService->logTelegramEvent('pdf_cleanup_failed', [
                        'file_path' => $file['path'],
                        'error' => $e->getMessage()
                    ], 'warning');
                }
            }

            // Summary
            $this->newLine();
            $this->info("🎉 Cleanup completed!");
            $this->info("✅ Deleted: {$deletedCount} files (" . $this->formatFileSize($deletedSize) . ")");

            if ($failedCount > 0) {
                $this->warn("⚠️  Failed: {$failedCount} files");
            }

            // Log summary
            $this->loggingService->logTelegramEvent('pdf_cleanup_completed', [
                'total_files_found' => count($pdfFiles),
                'old_files_found' => count($oldFiles),
                'deleted_count' => $deletedCount,
                'failed_count' => $failedCount,
                'deleted_size' => $deletedSize,
                'cutoff_minutes' => $minutes
            ], 'info');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("💥 Error during cleanup: " . $e->getMessage());

            $this->loggingService->logTelegramEvent('pdf_cleanup_error', [
                'error' => $e->getMessage(),
                'cutoff_minutes' => $minutes
            ], 'error');

            return Command::FAILURE;
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
