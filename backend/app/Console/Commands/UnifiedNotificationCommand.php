<?php

namespace App\Console\Commands;

use App\Services\UnifiedNotificationService;
use Illuminate\Console\Command;

class UnifiedNotificationCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'notify:send
                            {message : Message to send}
                            {--recipient= : Specific recipient (optional)}
                            {--channels=* : Specific channels (whatsapp, telegram)}
                            {--type=message : Type of notification (message, system-alert, deploy)}
                            {--title= : Title for system alert}
                            {--level=info : Level for system alert (info, warning, error, success)}
                            {--status=success : Status for deploy notification}
                            {--branch=main : Branch for deploy notification}
                            {--commit=test : Commit for deploy notification}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Send notification through unified notification system';

    /**
     * Execute the console command
     */
    public function handle(UnifiedNotificationService $notificationService): int
    {
        $this->info('🚀 Unified Notification System');
        $this->newLine();

        // Show available channels
        $this->info('📋 Available Channels:');
        $channels = $notificationService->getAvailableChannels();
        foreach ($channels as $name => $channel) {
            $status = $channel['enabled'] ? '✅' : '❌';
            $this->line("   {$status} {$name}");
        }

        $this->newLine();

        // Get options
        $message = $this->argument('message');
        $recipient = $this->option('recipient');
        $channels = $this->option('channels');
        $type = $this->option('type');

        $this->info("📤 Sending {$type} notification...");
        $this->line("💬 Message: {$message}");
        if ($recipient) {
            $this->line("👤 Recipient: {$recipient}");
        }
        if (!empty($channels)) {
            $this->line("📡 Channels: " . implode(', ', $channels));
        }

        $this->newLine();

        // Send based on type
        switch ($type) {
            case 'system-alert':
                $title = $this->option('title') ?? 'System Alert';
                $level = $this->option('level') ?? 'info';

                $result = $notificationService->sendSystemAlert(
                    $title,
                    $message,
                    $level,
                    $channels
                );
                break;

            case 'deploy':
                $status = $this->option('status') ?? 'success';
                $branch = $this->option('branch') ?? 'main';
                $commit = $this->option('commit') ?? 'test';

                $deployData = [
                    'status' => $status,
                    'branch' => $branch,
                    'commit' => $commit,
                    'message' => $message,
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                    'output' => 'Test deploy notification'
                ];

                $result = $notificationService->sendDeployNotification($deployData, $channels);
                break;

            default:
                $result = $notificationService->sendMessage($message, $recipient, $channels);
                break;
        }

        // Show results
        if ($result['success']) {
            $this->info('✅ Notification sent successfully!');
            $this->line("📊 Sent to {$result['sent_to_channels']}/{$result['total_channels']} channels");

            if (isset($result['results'])) {
                $this->newLine();
                $this->info('📋 Channel Results:');
                foreach ($result['results'] as $channel => $channelResult) {
                    $status = $channelResult['success'] ? '✅' : '❌';
                    $this->line("   {$status} {$channel}");

                    if (!$channelResult['success']) {
                        $this->line("      Error: " . ($channelResult['error'] ?? 'Unknown error'));
                    }
                }
            }
        } else {
            $this->error('❌ Notification failed!');
            if (isset($result['results'])) {
                foreach ($result['results'] as $channel => $channelResult) {
                    if (!$channelResult['success']) {
                        $this->error("   {$channel}: " . ($channelResult['error'] ?? 'Unknown error'));
                    }
                }
            }
        }

        return $result['success'] ? 0 : 1;
    }
}
