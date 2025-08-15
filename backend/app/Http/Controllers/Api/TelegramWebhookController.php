<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramWebhookRequest;
use App\Http\Requests\TelegramWebhookSetupRequest;
use App\Http\Resources\TelegramWebhookResource;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\Telegram\TelegramMessageProcessorService;
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
        $startTime = microtime(true);

        try {
            $payload = $request->validated();

            // Quick duplicate check BEFORE detailed validation
            $updateId = $payload['update_id'] ?? null;
            if ($updateId && $this->isDuplicateRequest($updateId)) {
                $this->loggingService->logTelegramEvent('duplicate_webhook_ignored_early', [
                    'update_id' => $updateId,
                    'message' => 'Duplicate webhook detected and ignored before processing'
                ], 'info');

                return TelegramWebhookResource::success('Duplicate webhook ignored', [
                    'success' => true,
                    'status' => 'ignored',
                    'message' => 'Duplicate webhook ignored',
                    'update_id' => $updateId
                ])
                    ->response()
                    ->setStatusCode(200);
            }

            // Mark as processing to prevent race conditions
            if ($updateId) {
                $this->markRequestAsProcessing($updateId);
            }

            // Validate payload structure
            $validation = $this->webhookService->validatePayload($payload);

            if (!$validation['valid']) {
                return TelegramWebhookResource::ignored($validation['message'])
                    ->response()
                    ->setStatusCode(200);
            }

            // Process the webhook payload with timeout protection
            $result = $this->processWithTimeout(function () use ($payload) {
                return $this->messageProcessor->processMessage($payload);
            }, 25); // 25 seconds timeout

            // Check if the result is valid and has the expected structure
            if (!is_array($result) || !isset($result['success'])) {
                $this->loggingService->logTelegramEvent('telegram_webhook_invalid_result', [
                    'error' => 'Invalid result structure from message processor',
                    'result' => $result,
                    'payload' => $payload,
                    'telegram_update_id' => $request->input('update_id'),
                    'timestamp' => now()->toISOString()
                ], 'error');

                return TelegramWebhookResource::error('Invalid processing result')
                    ->response()
                    ->setStatusCode(500);
            }

            // Check if the message was processed successfully
            if ($result['success']) {
                // $duration = (microtime(true) - $startTime) * 1000;

                // $this->loggingService->logTelegramEvent('webhook_processed_successfully', [
                //     'processing_time_ms' => round($duration, 2),
                //     'chat_id' => $request->input('message.chat.id'),
                //     'user_id' => $request->input('message.from.id'),
                // ], 'info');

                return TelegramWebhookResource::success($result['message'] ?? 'Message processed successfully', $result)
                    ->response()
                    ->setStatusCode(200);
            }

            // Check if this is a normal response (not an error)
            $isNormalResponse = isset($result['type']) && in_array($result['type'], [
                'command_not_found',
                'ignored',
                'unauthorized',
                'unsupported',
                'empty_message',
                'voice_conversion_error',
                'audio_conversion_error'
            ]);

            if ($isNormalResponse) {
                return TelegramWebhookResource::success($result['message'] ?? 'Message processed', $result)
                    ->response()
                    ->setStatusCode(200);
            }

            // Handle actual errors (return 500)
            $this->loggingService->logTelegramEvent('telegram_webhook_processing_failed', [
                'error' => 'Message processing failed',
                'error_message' => $result['message'] ?? 'Unknown error',
                'result' => $result,
                'payload' => $payload,
                'telegram_update_id' => $request->input('update_id'),
                'timestamp' => now()->toISOString()
            ], 'error');

            return TelegramWebhookResource::error($result['message'] ?? 'Message processing failed', $result)
                ->response()
                ->setStatusCode(500);

        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

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
     * Process with timeout protection
     */
    private function processWithTimeout(callable $callback, int $timeoutSeconds): array
    {
        $result = null;
        $exception = null;

        // Set up signal handler for timeout
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGALRM, function () {
                throw new \Exception('Processing timeout');
            });
            pcntl_alarm($timeoutSeconds);
        }

        try {
            $result = $callback();
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            if (function_exists('pcntl_alarm')) {
                pcntl_alarm(0); // Cancel alarm
            }
        }

        if ($exception) {
            throw $exception;
        }

        // Ensure we always return an array
        if (!is_array($result)) {
            return [
                'success' => false,
                'type' => 'invalid_result',
                'message' => 'Invalid result from message processor',
                'data' => []
            ];
        }

        return $result;
    }

    /**
     * Check if request is duplicate (quick cache check)
     */
    private function isDuplicateRequest(int $updateId): bool
    {
        try {
            $cacheKey = "telegram_update_processed_{$updateId}";
            return cache()->has($cacheKey);
        } catch (\Exception $e) {
            // If cache fails, log but don't block processing
            $this->loggingService->logException($e, [
                'operation' => 'check_duplicate_update_id_controller',
                'update_id' => $updateId
            ]);
            return false;
        }
    }

    /**
     * Mark request as being processed (to prevent race conditions)
     */
    private function markRequestAsProcessing(int $updateId): void
    {
        try {
            $cacheKey = "telegram_update_processed_{$updateId}";
            // Cache for 1 hour to prevent duplicates
            cache()->put($cacheKey, true, 3600);
        } catch (\Exception $e) {
            // If cache fails, log but don't block processing
            $this->loggingService->logException($e, [
                'operation' => 'mark_update_id_processed_controller',
                'update_id' => $updateId
            ]);
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
