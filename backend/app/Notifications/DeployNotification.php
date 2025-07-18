<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DeployNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private array $deployData;

    /**
     * Create a new notification instance
     *
     * @param array $deployData
     */
    public function __construct(array $deployData)
    {
        $this->deployData = $deployData;
    }

    /**
     * Get the notification's delivery channels
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    /**
     * Get the WhatsApp representation of the notification
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toWhatsApp($notifiable): array
    {
        return [
            'deploy_data' => $this->deployData
        ];
    }

    /**
     * Send the notification via WhatsApp
     *
     * @param mixed $notifiable
     * @return void
     */
    public function sendWhatsApp($notifiable): void
    {
        try {
            $whatsappService = app(WhatsAppService::class);
            $result = $whatsappService->sendDeployNotification($this->deployData);

            if ($result['success']) {
                Log::info('Deploy notification sent successfully via WhatsApp', [
                    'sent_to' => $result['sent_to'],
                    'total_recipients' => $result['total_recipients']
                ]);
            } else {
                Log::error('Failed to send deploy notification via WhatsApp', [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception sending deploy notification via WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get the array representation of the notification
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'deploy_data' => $this->deployData,
            'type' => 'deploy_notification'
        ];
    }
}
