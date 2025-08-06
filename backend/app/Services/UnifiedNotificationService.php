<?php

namespace App\Services;

use App\Contracts\NotificationChannelInterface;
use App\Services\Channels\WhatsAppChannel;
use App\Services\Channels\TelegramChannel;
use Illuminate\Support\Facades\Log;

class UnifiedNotificationService
{
    private array $channels = [];

    public function __construct()
    {
        $this->registerChannels();
    }

    /**
     * Register available notification channels
     */
    private function registerChannels(): void
    {
        // Register WhatsApp channel
        if (config('services.whatsapp.enabled', true)) {
            $this->channels['whatsapp'] = app(WhatsAppChannel::class);
        }

        // Register Telegram channel
        if (config('services.telegram.enabled', true)) {
            $this->channels['telegram'] = app(TelegramChannel::class);
        }
    }

    /**
     * Send message to all enabled channels
     *
     * @param string $message
     * @param string|null $recipient
     * @param array $channels
     * @return array
     */
    public function sendMessage(string $message, ?string $recipient = null, array $channels = []): array
    {
        $results = [];
        $enabledChannels = empty($channels) ? array_keys($this->channels) : $channels;

        foreach ($enabledChannels as $channelName) {
            if (!isset($this->channels[$channelName])) {
                $results[$channelName] = [
                    'success' => false,
                    'error' => "Channel '{$channelName}' not available"
                ];
                continue;
            }

            $channel = $this->channels[$channelName];

            if (!$channel->isEnabled()) {
                $results[$channelName] = [
                    'success' => false,
                    'error' => "Channel '{$channelName}' is disabled"
                ];
                continue;
            }

            $results[$channelName] = $channel->sendTextMessage($message, $recipient);
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        Log::info('Unified notification sent', [
            'message' => $message,
            'success_count' => $successCount,
            'total_channels' => $totalCount,
            'results' => $results
        ]);

        return [
            'success' => $successCount > 0,
            'sent_to_channels' => $successCount,
            'total_channels' => $totalCount,
            'results' => $results
        ];
    }

    /**
     * Send notification with data to all enabled channels
     *
     * @param array $data
     * @param array $channels
     * @return array
     */
    public function sendNotification(array $data, array $channels = []): array
    {
        $results = [];
        $enabledChannels = empty($channels) ? array_keys($this->channels) : $channels;

        foreach ($enabledChannels as $channelName) {
            if (!isset($this->channels[$channelName])) {
                $results[$channelName] = [
                    'success' => false,
                    'error' => "Channel '{$channelName}' not available"
                ];
                continue;
            }

            $channel = $this->channels[$channelName];

            if (!$channel->isEnabled()) {
                $results[$channelName] = [
                    'success' => false,
                    'error' => "Channel '{$channelName}' is disabled"
                ];
                continue;
            }

            $results[$channelName] = $channel->sendNotification($data);
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        Log::info('Unified notification sent', [
            'data' => $data,
            'success_count' => $successCount,
            'total_channels' => $totalCount,
            'results' => $results
        ]);

        return [
            'success' => $successCount > 0,
            'sent_to_channels' => $successCount,
            'total_channels' => $totalCount,
            'results' => $results
        ];
    }

    /**
     * Send deploy notification
     *
     * @param array $deployData
     * @param array $channels
     * @return array
     */
    public function sendDeployNotification(array $deployData, array $channels = []): array
    {
        return $this->sendNotification($deployData, $channels);
    }

    /**
     * Send system alert
     *
     * @param string $title
     * @param string $message
     * @param string $level
     * @param array $channels
     * @return array
     */
    public function sendSystemAlert(string $title, string $message, string $level = 'info', array $channels = []): array
    {
        $emoji = match($level) {
            'error' => '❌',
            'warning' => '⚠️',
            'success' => '✅',
            default => 'ℹ️'
        };

        $formattedMessage = "🚨 *SYSTEM ALERT*\n\n" .
                           "{$emoji} *{$title}*\n" .
                           "💬 {$message}\n" .
                           "⏰ " . now()->format('d/m/Y H:i:s') . "\n\n" .
                           "Sistema: Rei do Óleo";

        return $this->sendMessage($formattedMessage, null, $channels);
    }

    /**
     * Test all channels
     *
     * @return array
     */
    public function testAllChannels(): array
    {
        $results = [];

        foreach ($this->channels as $channelName => $channel) {
            $results[$channelName] = [
                'enabled' => $channel->isEnabled(),
                'connection' => $channel->isEnabled() ? $channel->testConnection() : null
            ];
        }

        return $results;
    }

    /**
     * Get available channels
     *
     * @return array
     */
    public function getAvailableChannels(): array
    {
        $available = [];

        foreach ($this->channels as $channelName => $channel) {
            $available[$channelName] = [
                'enabled' => $channel->isEnabled(),
                'name' => $channel->getChannelName()
            ];
        }

        return $available;
    }

    /**
     * Get specific channel
     *
     * @param string $channelName
     * @return NotificationChannelInterface|null
     */
    public function getChannel(string $channelName): ?NotificationChannelInterface
    {
        return $this->channels[$channelName] ?? null;
    }
}
