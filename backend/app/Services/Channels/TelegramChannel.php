<?php

namespace App\Services\Channels;

use App\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramChannel implements NotificationChannelInterface
{
    private string $botToken;
    private string $apiUrl;
    private array $recipients;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->recipients = config('services.telegram.recipients', []);
    }

    /**
     * Send text message via Telegram
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
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            if ($recipient) {
                return $this->sendToRecipient($recipient, $message);
            }

            // Send to all configured recipients
            if (empty($this->recipients)) {
                return [
                    'success' => false,
                    'error' => 'No Telegram recipients configured'
                ];
            }

            $results = [];
            foreach ($this->recipients as $recipient) {
                $results[$recipient] = $this->sendToRecipient($recipient, $message);
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            return [
                'success' => $successCount > 0,
                'sent_to' => $successCount,
                'total_recipients' => count($results),
                'results' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Telegram channel error', [
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
     * Send message with inline keyboard via Telegram
     *
     * @param string $message
     * @param string $chatId
     * @param array $keyboard
     * @return array
     */
    public function sendMessageWithKeyboard(string $message, string $chatId, array $keyboard): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => $keyboard
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['result']['message_id'] ?? null,
                    'response' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Telegram keyboard message error', [
                'error' => $e->getMessage(),
                'message' => $message,
                'chat_id' => $chatId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Answer callback query (for inline keyboard buttons)
     *
     * @param string $callbackQueryId
     * @param string|null $text
     * @param bool $showAlert
     * @return array
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $data = [
                'callback_query_id' => $callbackQueryId
            ];

            if ($text) {
                $data['text'] = $text;
            }

            if ($showAlert) {
                $data['show_alert'] = $showAlert;
            }

            $response = Http::post("{$this->apiUrl}/answerCallbackQuery", $data);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'response' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Telegram answer callback query error', [
                'error' => $e->getMessage(),
                'callback_query_id' => $callbackQueryId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification with data via Telegram
     *
     * @param array $data
     * @return array
     */
    public function sendNotification(array $data): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $message = $this->formatNotificationMessage($data);
            return $this->sendTextMessage($message);
        } catch (\Exception $e) {
            Log::error('Telegram notification error', [
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
     * Test Telegram connection
     *
     * @return array
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $response = Http::get("{$this->apiUrl}/getMe");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'bot_name' => $data['result']['first_name'] ?? 'Unknown',
                    'bot_username' => $data['result']['username'] ?? 'Unknown',
                    'bot_id' => $data['result']['id'] ?? 'Unknown'
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error',
                'status' => $response->status()
            ];

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
        return 'telegram';
    }

    /**
     * Check if Telegram channel is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('services.telegram.enabled', true) &&
               !empty($this->botToken) &&
               !empty($this->recipients);
    }

    /**
     * Send message to specific recipient
     *
     * @param string $chatId
     * @param string $message
     * @return array
     */
    private function sendToRecipient(string $chatId, string $message): array
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['result']['message_id'] ?? null,
                    'response' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format notification message for Telegram
     *
     * @param array $data
     * @return string
     */
    private function formatNotificationMessage(array $data): string
    {
        $status = $data['status'] ?? 'unknown';
        $branch = $data['branch'] ?? 'unknown';
        $commit = $data['commit'] ?? 'unknown';
        $message = $data['message'] ?? 'no message';
        $timestamp = $data['timestamp'] ?? now()->format('d/m/Y H:i:s');

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
     * Get file info from Telegram
     */
    public function getFile(string $fileId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $response = Http::get("{$this->apiUrl}/getFile", [
                'file_id' => $fileId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'file_path' => $data['result']['file_path'] ?? null,
                    'file_size' => $data['result']['file_size'] ?? null,
                    'file_id' => $data['result']['file_id'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Telegram get file error', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send typing indicator
     */
    public function sendTypingIndicator(string $chatId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendChatAction", [
                'chat_id' => $chatId,
                'action' => 'typing'
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send upload document indicator
     */
    public function sendUploadDocumentIndicator(string $chatId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Telegram channel is disabled'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendChatAction", [
                'chat_id' => $chatId,
                'action' => 'upload_document'
            ]);

            return [
                'success' => $response->successful(),
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
