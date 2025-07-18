<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WhatsAppService
{
    private string $apiUrl;
    private string $accessToken;
    private string $phoneNumberId;
    private string $version;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://graph.facebook.com');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->version = config('services.whatsapp.version', 'v18.0');
    }

    /**
     * Send text message via WhatsApp
     *
     * @param string $toPhoneNumber
     * @param string $message
     * @return array
     */
    public function sendTextMessage(string $toPhoneNumber, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/{$this->version}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($toPhoneNumber),
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $toPhoneNumber,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json()
                ];
            }

            Log::error('WhatsApp API error', [
                'to' => $toPhoneNumber,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'to' => $toPhoneNumber
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send template message via WhatsApp
     *
     * @param string $toPhoneNumber
     * @param string $templateName
     * @param array $components
     * @return array
     */
    public function sendTemplateMessage(string $toPhoneNumber, string $templateName, array $components = []): array
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($toPhoneNumber),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'pt_BR'
                    ]
                ]
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/{$this->version}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp template message sent successfully', [
                    'to' => $toPhoneNumber,
                    'template' => $templateName,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json()
                ];
            }

            Log::error('WhatsApp template API error', [
                'to' => $toPhoneNumber,
                'template' => $templateName,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp template service exception', [
                'error' => $e->getMessage(),
                'to' => $toPhoneNumber,
                'template' => $templateName
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send deploy notification
     *
     * @param array $deployData
     * @return array
     */
    public function sendDeployNotification(array $deployData): array
    {
        $recipients = config('services.whatsapp.deploy_recipients', []);

        if (empty($recipients)) {
            Log::warning('No WhatsApp recipients configured for deploy notifications');
            return ['success' => false, 'error' => 'No recipients configured'];
        }

        $message = $this->formatDeployMessage($deployData);
        $results = [];

        foreach ($recipients as $recipient) {
            $result = $this->sendTextMessage($recipient, $message);
            $results[$recipient] = $result;
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        Log::info('Deploy notification sent', [
            'success_count' => $successCount,
            'total_count' => $totalCount,
            'results' => $results
        ]);

        return [
            'success' => $successCount > 0,
            'sent_to' => $successCount,
            'total_recipients' => $totalCount,
            'results' => $results
        ];
    }

    /**
     * Format deploy message
     *
     * @param array $deployData
     * @return string
     */
    private function formatDeployMessage(array $deployData): string
    {
        $status = $deployData['status'] ?? 'unknown';
        $branch = $deployData['branch'] ?? 'unknown';
        $commit = $deployData['commit'] ?? 'unknown';
        $message = $deployData['message'] ?? 'no message';
        $timestamp = $deployData['timestamp'] ?? now()->format('d/m/Y H:i:s');

        $emoji = $status === 'success' ? '✅' : ($status === 'error' ? '❌' : '⚠️');
        $statusText = $status === 'success' ? 'SUCESSO' : ($status === 'error' ? 'ERRO' : 'ATENÇÃO');

        return "🚀 *DEPLOY NOTIFICATION*\n\n" .
               "{$emoji} *Status:* {$statusText}\n" .
               "🌿 *Branch:* {$branch}\n" .
               "🔗 *Commit:* {$commit}\n" .
               "💬 *Message:* {$message}\n" .
               "⏰ *Timestamp:* {$timestamp}\n\n" .
               "Sistema: Rei do Óleo";
    }

    /**
     * Format phone number for WhatsApp API
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if not present (assuming Brazil +55)
        if (!str_starts_with($phoneNumber, '55')) {
            $phoneNumber = '55' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Test WhatsApp connection
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->apiUrl}/{$this->version}/{$this->phoneNumberId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'phone_number' => $response->json('phone_number'),
                    'verified_name' => $response->json('verified_name'),
                    'code_verification_status' => $response->json('code_verification_status')
                ];
            }

            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
