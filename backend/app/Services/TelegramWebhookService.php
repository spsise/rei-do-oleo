<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Contracts\LoggingServiceInterface;

class TelegramWebhookService
{
    private string $botToken;
    private string $apiUrl;

    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Set webhook URL for Telegram bot
     */
    public function setWebhook(string $webhookUrl): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/setWebhook", [
                'url' => $webhookUrl
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $result = [
                    'success' => true,
                    'message' => 'Webhook set successfully',
                    'data' => $data
                ];

                $this->loggingService->logBusinessOperation('telegram_webhook_setup_success', [
                    'webhook_url' => $webhookUrl,
                    'result' => $result
                ], 'success');

                return $result;
            }

            $result = [
                'success' => false,
                'message' => 'Failed to set webhook',
                'error' => $response->json()
            ];

            $this->loggingService->logBusinessOperation('telegram_webhook_setup_failed', [
                'webhook_url' => $webhookUrl,
                'error' => $result['error']
            ], 'error');

            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ];

            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_setup',
                'webhook_url' => $webhookUrl
            ]);

            return $result;
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        try {
            $this->loggingService->logBusinessOperation('telegram_webhook_info_request', [
                'action' => 'get_webhook_info'
            ], 'info');

            $response = Http::get("{$this->apiUrl}/getWebhookInfo");

            if ($response->successful()) {
                $data = $response->json();

                $result = [
                    'success' => true,
                    'data' => $data
                ];

                $this->loggingService->logBusinessOperation('telegram_webhook_info_success', [
                    'result' => $result
                ], 'success');

                return $result;
            }

            $result = [
                'success' => false,
                'message' => 'Failed to get webhook info',
                'error' => $response->json()
            ];

            $this->loggingService->logBusinessOperation('telegram_webhook_info_failed', [
                'error' => $result['error']
            ], 'error');

            return $result;

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_info'
            ]);

            return [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): array
    {
        try {
            $this->loggingService->logBusinessOperation('telegram_webhook_deletion_attempt', [
                'action' => 'delete_webhook'
            ], 'info');

            $response = Http::post("{$this->apiUrl}/deleteWebhook");

            if ($response->successful()) {
                $data = $response->json();

                $result = [
                    'success' => true,
                    'message' => 'Webhook deleted successfully',
                    'data' => $data
                ];

                $this->loggingService->logBusinessOperation('telegram_webhook_deletion_success', [
                    'result' => $result
                ], 'success');

                return $result;
            }

            $result = [
                'success' => false,
                'message' => 'Failed to delete webhook',
                'error' => $response->json()
            ];

            $this->loggingService->logBusinessOperation('telegram_webhook_deletion_failed', [
                'error' => $result['error']
            ], 'error');

            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ];

            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_deletion'
            ]);

            return $result;
        }
    }

    /**
     * Test bot functionality
     */
    public function testBot(): array
    {
        try {
            $this->loggingService->logBusinessOperation('telegram_bot_test_attempt', [
                'action' => 'test_bot'
            ], 'info');

            $recipients = config('services.telegram.recipients', []);

            if (empty($recipients)) {
                $result = [
                    'success' => false,
                    'message' => 'No recipients configured'
                ];

                $this->loggingService->logBusinessOperation('telegram_bot_test_failed', [
                    'error' => 'No recipients configured'
                ], 'error');

                return $result;
            }

            $results = [];
            foreach ($recipients as $recipient) {
                $result = $this->sendTestMessage($recipient);
                $results[$recipient] = $result;
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            $result = [
                'success' => $successCount > 0,
                'message' => 'Test completed',
                'sent_to' => $successCount,
                'total_recipients' => count($recipients),
                'results' => $results
            ];

            $this->loggingService->logBusinessOperation('telegram_bot_test_success', [
                'sent_to' => $successCount,
                'total_recipients' => count($recipients),
                'result' => $result
            ], 'success');

            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ];

            $this->loggingService->logException($e, [
                'operation' => 'telegram_bot_test'
            ]);

            return $result;
        }
    }

    /**
     * Send test message to specific recipient
     */
    private function sendTestMessage(string $recipient): array
    {
        try {
            $testMessage = "🧪 *Teste do Bot*\n\n" .
                          "Este é um teste do bot de relatórios do Rei do Óleo.\n" .
                          "Se você recebeu esta mensagem, o bot está funcionando!\n\n" .
                          "Use `/help` para ver os comandos disponíveis.\n\n" .
                          "⏰ Teste realizado em: " . now()->format('d/m/Y H:i:s');

            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $recipient,
                'text' => $testMessage,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful()) {
                $this->loggingService->logBusinessOperation('test_message_sent_successfully', [
                    'chat_id' => $recipient,
                    'message_type' => 'test'
                ], 'info');

                return [
                    'success' => true,
                    'message' => 'Test message sent successfully'
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_test_message_send',
                'recipient' => $recipient
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate webhook payload
     */
    public function validatePayload(array $payload): array
    {
        if (!isset($payload['update_id'])) {
            return [
                'valid' => false,
                'message' => 'Missing update_id in payload'
            ];
        }

        if (!isset($payload['message']) && !isset($payload['callback_query'])) {
            return [
                'valid' => false,
                'message' => 'No message or callback_query in payload'
            ];
        }

        return [
            'valid' => true,
            'type' => isset($payload['callback_query']) ? 'callback_query' : 'message'
        ];
    }
}
