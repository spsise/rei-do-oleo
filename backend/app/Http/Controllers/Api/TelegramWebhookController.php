<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    private TelegramBotService $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        $this->telegramBotService = $telegramBotService;
    }

    /**
     * Handle Telegram webhook
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            Log::info('Telegram webhook received', [
                'payload' => $payload
            ]);

            // Check if it's a callback query (button click)
            if (isset($payload['callback_query'])) {
                return $this->handleCallbackQuery($payload['callback_query']);
            }

            // Verify if it's a message
            if (!isset($payload['message'])) {
                return response()->json(['status' => 'ignored', 'message' => 'No message in payload']);
            }

            $message = $payload['message'];

            // Check if it's a text message
            if (!isset($message['text'])) {
                return response()->json(['status' => 'ignored', 'message' => 'No text in message']);
            }

            // Process the message
            $result = $this->telegramBotService->processMessage($message);

            if ($result['success']) {
                Log::info('Telegram message processed successfully', [
                    'chat_id' => $message['chat']['id'] ?? 'unknown',
                    'text' => $message['text'] ?? 'unknown'
                ]);
            } else {
                Log::error('Failed to process Telegram message', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'chat_id' => $message['chat']['id'] ?? 'unknown'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Message processed',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle callback query from inline keyboard buttons
     */
    private function handleCallbackQuery(array $callbackQuery): JsonResponse
    {
        try {
            $callbackQueryId = $callbackQuery['id'] ?? '';
            $chatId = $callbackQuery['message']['chat']['id'] ?? '';
            $callbackData = $callbackQuery['data'] ?? '';

            Log::info('Telegram callback query received', [
                'callback_query_id' => $callbackQueryId,
                'chat_id' => $chatId,
                'callback_data' => $callbackData
            ]);

            // Answer the callback query to remove loading state
            $this->telegramBotService->getTelegramChannel()->answerCallbackQuery($callbackQueryId);

            // Process the callback query
            $result = $this->telegramBotService->processCallbackQuery($callbackQuery);

            if ($result['success']) {
                Log::info('Telegram callback query processed successfully', [
                    'chat_id' => $chatId,
                    'callback_data' => $callbackData
                ]);
            } else {
                Log::error('Failed to process Telegram callback query', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'chat_id' => $chatId,
                    'callback_data' => $callbackData
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Callback query processed',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram callback query error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Set webhook URL for Telegram bot
     */
    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $botToken = config('services.telegram.bot_token');
            $webhookUrl = $request->input('webhook_url');

            if (!$webhookUrl) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Webhook URL is required'
                ], 400);
            }

            $apiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

            $response = \Illuminate\Support\Facades\Http::post($apiUrl, [
                'url' => $webhookUrl
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Telegram webhook set successfully', [
                    'webhook_url' => $webhookUrl,
                    'response' => $data
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook set successfully',
                    'data' => $data
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to set webhook',
                'error' => $response->json()
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error setting Telegram webhook', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): JsonResponse
    {
        try {
            $botToken = config('services.telegram.bot_token');
            $apiUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

            $response = \Illuminate\Support\Facades\Http::get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get webhook info',
                'error' => $response->json()
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error getting Telegram webhook info', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        try {
            $botToken = config('services.telegram.bot_token');
            $apiUrl = "https://api.telegram.org/bot{$botToken}/deleteWebhook";

            $response = \Illuminate\Support\Facades\Http::post($apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Telegram webhook deleted successfully', [
                    'response' => $data
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook deleted successfully',
                    'data' => $data
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete webhook',
                'error' => $response->json()
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error deleting Telegram webhook', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test bot functionality
     */
    public function test(): JsonResponse
    {
        try {
            $botToken = config('services.telegram.bot_token');
            $recipients = config('services.telegram.recipients', []);

            if (empty($recipients)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No recipients configured'
                ], 400);
            }

            $testMessage = "🧪 *Teste do Bot*\n\n" .
                          "Este é um teste do bot de relatórios do Rei do Óleo.\n" .
                          "Se você recebeu esta mensagem, o bot está funcionando!\n\n" .
                          "Use `/help` para ver os comandos disponíveis.\n\n" .
                          "⏰ Teste realizado em: " . now()->format('d/m/Y H:i:s');

            $results = [];
            foreach ($recipients as $recipient) {
                $result = $this->telegramBotService->processMessage([
                    'chat' => ['id' => $recipient],
                    'text' => '/help',
                    'from' => ['id' => $recipient, 'first_name' => 'Test User']
                ]);

                $results[$recipient] = $result;
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            return response()->json([
                'status' => 'success',
                'message' => 'Test completed',
                'sent_to' => $successCount,
                'total_recipients' => count($recipients),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram bot test error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
