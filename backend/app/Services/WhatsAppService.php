<?php

namespace App\Services;

use App\Traits\HasSafeLogging;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    use HasSafeLogging;

    private string $apiUrl;
    private string $token;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            if ($response->successful()) {
                $this->logWhatsAppEvent('message_sent', [
                    'phone' => $to,
                    'message_type' => 'text',
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json()
                ];
            } else {
                $this->logError('WhatsApp API error', [
                    'phone' => $to,
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'error' => 'API Error',
                    'response' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            $this->logException($e, [
                'phone' => $to,
                'message' => $message
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp template message
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'pt_BR'
                    ]
                ]
            ];

            if (!empty($parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => $parameters
                    ]
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/messages', $payload);

            if ($response->successful()) {
                $this->logWhatsAppEvent('template_message_sent', [
                    'phone' => $to,
                    'message_type' => 'template',
                    'template_name' => $templateName,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json()
                ];
            } else {
                $this->logError('WhatsApp template API error', [
                    'phone' => $to,
                    'template_name' => $templateName,
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'error' => 'Template API Error',
                    'response' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            $this->logException($e, [
                'phone' => $to,
                'template_name' => $templateName,
                'parameters' => $parameters
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send deploy notification
     */
    public function sendDeployNotification(array $recipients, array $deployData): array
    {
        if (empty($recipients)) {
            $this->logWarning('No WhatsApp recipients configured for deploy notifications');
            return ['success' => false, 'error' => 'No recipients configured'];
        }

        $successCount = 0;
        $totalRecipients = count($recipients);

        foreach ($recipients as $recipient) {
            $message = $this->formatDeployMessage($deployData);
            $result = $this->sendMessage($recipient, $message);

            if ($result['success']) {
                $successCount++;
            }
        }

        $this->logInfo('Deploy notification sent', [
            'sent_to' => $successCount,
            'total_recipients' => $totalRecipients,
            'deploy_data' => $deployData
        ]);

        return [
            'success' => $successCount > 0,
            'sent_to' => $successCount,
            'total_recipients' => $totalRecipients
        ];
    }

    /**
     * Format deploy message
     */
    private function formatDeployMessage(array $deployData): string
    {
        $status = $deployData['status'] ?? 'unknown';
        $branch = $deployData['branch'] ?? 'unknown';
        $timestamp = $deployData['timestamp'] ?? now()->format('Y-m-d H:i:s');

        return "🚀 *Deploy Status: {$status}*\n\n" .
               "📋 Branch: `{$branch}`\n" .
               "⏰ Time: {$timestamp}\n\n" .
               "Status: " . ($status === 'success' ? '✅ Success' : '❌ Failed');
    }

    /**
     * Get WhatsApp configuration
     */
    public function getConfig(): array
    {
        return [
            'api_url' => $this->apiUrl,
            'phone_number_id' => $this->phoneNumberId,
            'has_token' => !empty($this->token)
        ];
    }
}
