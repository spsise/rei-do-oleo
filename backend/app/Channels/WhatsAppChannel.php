<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    private WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send the given notification
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        try {
            if (method_exists($notification, 'toWhatsApp')) {
                /** @var array $data */
                $data = $notification->toWhatsApp($notifiable);

                // Handle deploy notifications
                if (isset($data['deploy_data'])) {
                    $result = $this->whatsappService->sendDeployNotification($data['deploy_data']);

                    if ($result['success']) {
                        Log::info('WhatsApp notification sent successfully', [
                            'type' => 'deploy',
                            'sent_to' => $result['sent_to'],
                            'total_recipients' => $result['total_recipients']
                        ]);
                    } else {
                        Log::error('WhatsApp notification failed', [
                            'type' => 'deploy',
                            'error' => $result['error'] ?? 'Unknown error'
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp channel exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
