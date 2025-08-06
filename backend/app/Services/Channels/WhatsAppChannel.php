<?php

namespace App\Services\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel implements NotificationChannelInterface
{
    private WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send text message via WhatsApp
     *
     * @param string $message
     * @param string|null $recipient
     * @return array
     */
    public function sendTextMessage(string $message, ?string $recipient = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp channel is disabled'
            ];
        }

        try {
            if ($recipient) {
                return $this->whatsappService->sendTextMessage($recipient, $message);
            }

            // Send to configured recipients
            $recipients = config('services.whatsapp.deploy_recipients', []);

            if (empty($recipients)) {
                return [
                    'success' => false,
                    'error' => 'No WhatsApp recipients configured'
                ];
            }

            $results = [];
            foreach ($recipients as $recipient) {
                $results[$recipient] = $this->whatsappService->sendTextMessage($recipient, $message);
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            return [
                'success' => $successCount > 0,
                'sent_to' => $successCount,
                'total_recipients' => count($results),
                'results' => $results
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp channel error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification with data via WhatsApp
     *
     * @param array $data
     * @return array
     */
    public function sendNotification(array $data): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp channel is disabled'
            ];
        }

        try {
            return $this->whatsappService->sendDeployNotification($data);
        } catch (\Exception $e) {
            Log::error('WhatsApp notification error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test WhatsApp connection
     *
     * @return array
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp channel is disabled'
            ];
        }

        try {
            return $this->whatsappService->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get channel name
     *
     * @return string
     */
    public function getChannelName(): string
    {
        return 'whatsapp';
    }

    /**
     * Check if WhatsApp channel is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('services.whatsapp.enabled', true) &&
               !empty(config('services.whatsapp.access_token')) &&
               !empty(config('services.whatsapp.phone_number_id'));
    }
}
