<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramWebhookRequest;
use App\Http\Requests\TelegramWebhookSetupRequest;
use App\Http\Resources\TelegramWebhookResource;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\TelegramMessageProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramWebhookService $webhookService,
        private TelegramMessageProcessorService $messageProcessor
    ) {}

    /**
     * Handle Telegram webhook
     */
    public function handle(TelegramWebhookRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();

            // Validate payload structure
            $validation = $this->webhookService->validatePayload($payload);
            if (!$validation['valid']) {
                return TelegramWebhookResource::ignored($validation['message'])
                    ->response()
                    ->setStatusCode(200);
            }

            // Process the webhook payload
            $result = $this->messageProcessor->processWebhookPayload($payload);

            if ($result['status'] === 'ignored') {
                return TelegramWebhookResource::ignored($result['message'])
                    ->response()
                    ->setStatusCode(200);
            }

            if (!$result['success']) {
                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(500);
            }

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Telegram webhook controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return TelegramWebhookResource::error('Internal server error')
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Set webhook URL for Telegram bot
     */
    public function setWebhook(TelegramWebhookSetupRequest $request): JsonResponse
    {
        try {
            $webhookUrl = $request->validated()['webhook_url'];
            $result = $this->webhookService->setWebhook($webhookUrl);

            if (!$result['success']) {
                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error setting Telegram webhook', [
                'error' => $e->getMessage()
            ]);

            return TelegramWebhookResource::error('Internal server error')
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): JsonResponse
    {
        try {
            $result = $this->webhookService->getWebhookInfo();

            if (!$result['success']) {
                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            return TelegramWebhookResource::success('Webhook info retrieved', $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error getting Telegram webhook info', [
                'error' => $e->getMessage()
            ]);

            return TelegramWebhookResource::error('Internal server error')
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        try {
            $result = $this->webhookService->deleteWebhook();

            if (!$result['success']) {
                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error deleting Telegram webhook', [
                'error' => $e->getMessage()
            ]);

            return TelegramWebhookResource::error('Internal server error')
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Test bot functionality
     */
    public function test(): JsonResponse
    {
        try {
            $result = $this->webhookService->testBot();

            if (!$result['success']) {
                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Telegram bot test error', [
                'error' => $e->getMessage()
            ]);

            return TelegramWebhookResource::error('Test failed: ' . $e->getMessage())
                ->response()
                ->setStatusCode(500);
        }
    }
}
