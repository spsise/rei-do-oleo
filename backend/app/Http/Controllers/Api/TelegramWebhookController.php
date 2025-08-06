<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramWebhookRequest;
use App\Http\Requests\TelegramWebhookSetupRequest;
use App\Http\Resources\TelegramWebhookResource;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\TelegramMessageProcessorService;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Http\JsonResponse;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramWebhookService $webhookService,
        private TelegramMessageProcessorService $messageProcessor,
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Handle Telegram webhook
     */
    public function handle(TelegramWebhookRequest $request): JsonResponse
    {
        try {
            $this->loggingService->logTelegramEvent('telegram_webhook_received', [
                'request' => $request->all(),
                'chat_id' => $request->input('message.chat.id'),
                'user_id' => $request->input('message.from.id'),
                'message_id' => $request->input('message.message_id'),
                'message_text' => $request->input('message.text'),
                'message_date' => $request->input('message.date'),
                'message_from' => $request->input('message.from'),
            ], 'info');
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
            $duration = (microtime(true) - microtime(true)) * 1000;

            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_processing',
                'chat_id' => $request->input('message.chat.id'),
                'user_id' => $request->input('message.from.id'),
                'processing_time_ms' => round($duration, 2)
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
            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_setup',
                'webhook_url' => $request->validated()['webhook_url'] ?? 'unknown'
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
            $this->loggingService->logTelegramEvent('telegram_webhook_info_request', [
                'action' => 'get_webhook_info'
            ], 'info');

            $result = $this->webhookService->getWebhookInfo();

            if (!$result['success']) {
                $this->loggingService->logTelegramEvent('telegram_webhook_info_failed', [
                    'error' => $result['message'],
                    'result' => $result
                ], 'error');

                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            return TelegramWebhookResource::success('Webhook info retrieved', $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_info'
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
            $this->loggingService->logTelegramEvent('telegram_webhook_deletion', [
                'action' => 'delete_webhook'
            ], 'info');

            $result = $this->webhookService->deleteWebhook();

            if (!$result['success']) {
                $this->loggingService->logTelegramEvent('telegram_webhook_deletion_failed', [
                    'error' => $result['message'],
                    'result' => $result
                ], 'error');

                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            $this->loggingService->logTelegramEvent('telegram_webhook_deletion_success', [
                'result' => $result
            ], 'success');

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_webhook_deletion'
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
            $this->loggingService->logTelegramEvent('telegram_bot_test', [
                'action' => 'test_bot'
            ], 'info');

            $result = $this->webhookService->testBot();

            if (!$result['success']) {
                $this->loggingService->logTelegramEvent('telegram_bot_test_failed', [
                    'error' => $result['message'],
                    'result' => $result
                ], 'error');

                return TelegramWebhookResource::error($result['message'], $result)
                    ->response()
                    ->setStatusCode(400);
            }

            $this->loggingService->logTelegramEvent('telegram_bot_test_success', [
                'result' => $result
            ], 'success');

            return TelegramWebhookResource::success($result['message'], $result)
                ->response()
                ->setStatusCode(200);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'telegram_bot_test'
            ]);

            return TelegramWebhookResource::error('Test failed: ' . $e->getMessage())
                ->response()
                ->setStatusCode(500);
        }
    }
}
