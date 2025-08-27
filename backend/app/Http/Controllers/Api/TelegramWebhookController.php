<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramWebhookRequest;
use App\Http\Requests\TelegramWebhookSetupRequest;
use App\Http\Resources\TelegramWebhookResource;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\TelegramMessageProcessorService;
use App\Services\Telegram\TelegramWebhookValidationService;
use App\Services\Channels\TelegramChannel;
use App\Contracts\LoggingServiceInterface;
use App\Contracts\MessageFlowTrackerInterface;
use Illuminate\Http\JsonResponse;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramChannel $telegramChannel,
        private TelegramWebhookService $webhookService,
        private TelegramMessageProcessorService $messageProcessor,
        private TelegramWebhookValidationService $webhookValidationService,
        private LoggingServiceInterface $loggingService,
        private MessageFlowTrackerInterface $flowTracker
    ) {}

    /**
     * Handle Telegram webhook
     */
    public function handle(TelegramWebhookRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        // Inicializa o tracker com ID único
        $this->flowTracker->startTracking(uniqid('webhook_', true));

        try {
            // Check if validation failed
            if ($request->has('validation_errors')) {
                $validationErrors = $request->input('validation_errors');
                $errorMessage = $this->createValidationErrorMessage($validationErrors);

                // Send friendly error message to user via Telegram
                $this->sendFriendlyErrorMessage($request->all(), $errorMessage);

                return TelegramWebhookResource::ignored('Validation failed - friendly message sent to user')
                    ->response()
                    ->setStatusCode(200);
            }

            $payload = $request->validated();

            // Validate payload structure first
            $validation = $this->webhookService->validatePayload($payload);

            if (!$validation['valid']) {
                // Send friendly error message to user via Telegram
                $this->sendFriendlyErrorMessage($payload, $validation['message']);

                return TelegramWebhookResource::ignored($validation['message'])
                    ->response()
                    ->setStatusCode(200);
            }

            // Validate webhook and mark as processing (after payload validation)
            $updateId = $payload['update_id'] ?? null;
            $skipDuplicateCheck = app()->environment('testing');
            if (!$this->webhookValidationService->validateAndMarkProcessing($updateId, $skipDuplicateCheck)) {
                return TelegramWebhookResource::success('Duplicate webhook ignored', [
                    'success' => true,
                    'status' => 'ignored',
                    'message' => 'Duplicate webhook ignored',
                    'update_id' => $updateId
                ])
                    ->response()
                    ->setStatusCode(200);
            }

            // Process the webhook payload with timeout protection
            $result = $this->processWithTimeout(function () use ($payload) {
                return $this->messageProcessor->processWebhookPayload($payload);
            }, 25); // 25 seconds timeout

            // Gera relatório do fluxo APÓS o processamento
            $flowReport = $this->flowTracker->generateFlowReport($payload);
            $userReport = $this->flowTracker->generateUserReport($payload);

            // Finaliza o tracking
            $this->flowTracker->endTracking();

            // Se habilitado, envia relatório para o usuário
            if (config('message-flow.tracking.send_to_user', false)) {
                $this->sendFlowReportToUser($payload, $userReport);
            }

            if (config('message-flow.enabled', false)) {
                $this->loggingService->logTelegramEvent('message_flow_report', [
                    'flow_report' => $flowReport,
                    'user_report' => $userReport,
                    'payload' => $payload
                ], 'info');
            }

            // Check if the result is valid and has the expected structure
            if (!is_array($result) || !isset($result['success'])) {
                $this->loggingService->logTelegramEvent('telegram_webhook_invalid_result', [
                    'error' => 'Invalid result structure from message processor',
                    'result' => $result,
                    'payload' => $payload,
                    'telegram_update_id' => $request->input('update_id'),
                    'timestamp' => now()->toISOString()
                ], 'error');

                // Send friendly error message to user
                $this->sendFriendlyErrorMessage($payload, 'Desculpe, ocorreu um erro interno. Tente novamente em alguns instantes.');

                return TelegramWebhookResource::error('Invalid processing result')
                    ->response()
                    ->setStatusCode(500);
            }

            // Check if this is a normal response (not an error) - check status first
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
                // Send fallback message to user for command_not_found
                if ($result['type'] === 'command_not_found') {
                    $fallbackMessage = $this->createCommandNotFoundFallbackMessage($result);
                    $this->sendFriendlyErrorMessage($payload, $fallbackMessage);
                }

                // Use appropriate resource method based on status
                if (isset($result['status']) && $result['status'] === 'ignored') {
                    return TelegramWebhookResource::ignored($result['message'] ?? 'Message ignored', $result)
                        ->response()
                        ->setStatusCode(200);
                }

                return TelegramWebhookResource::success($result['message'] ?? 'Message processed', $result)
                    ->response()
                    ->setStatusCode(200);
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

            // Handle actual errors (return 500)
            $this->loggingService->logTelegramEvent('telegram_webhook_processing_failed', [
                'error' => 'Message processing failed',
                'error_message' => $result['message'] ?? 'Unknown error',
                'result' => $result,
                'payload' => $payload,
                'telegram_update_id' => $request->input('update_id'),
                'timestamp' => now()->toISOString()
            ], 'error');

            // Send friendly error message to user
            $this->sendFriendlyErrorMessage($payload, $result['message'] ?? 'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente.');

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

            // Send friendly error message to user even for exceptions
            $this->sendFriendlyErrorMessage($request->all(), 'Desculpe, ocorreu um erro inesperado. Nossa equipe foi notificada.');

            return TelegramWebhookResource::error('Internal server error')
                ->response()
                ->setStatusCode(500);
        }
    }

    /**
     * Send flow report to user via Telegram
     */
    private function sendFlowReportToUser(array $payload, string $report): void
    {
        try {
            $chatId = $this->extractChatId($payload);
            if ($chatId) {
                // Send plain text report directly to the user without triggering menus
                $this->telegramChannel->sendTextMessage($report, (string) $chatId);
            }
        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'send_flow_report_to_user',
                'chat_id' => $chatId ?? 'unknown',
                'report' => $report
            ]);
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

    /**
     * Send friendly error message to user via Telegram
     */
    private function sendFriendlyErrorMessage(array $payload, string $errorMessage): void
    {
        try {
            $chatId = $this->extractChatId($payload);

            if (!$chatId) {
                $this->loggingService->logTelegramEvent('telegram_error_message_failed', [
                    'error' => 'Could not extract chat ID from payload',
                    'payload' => $payload,
                    'error_message' => $errorMessage
                ], 'warning');
                return;
            }

            // Create friendly error message with suggestions
            $friendlyMessage = $this->createFriendlyErrorMessage($errorMessage);

            // Send via Telegram API
            $this->telegramBotService->sendErrorMessage($chatId, $friendlyMessage);

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'send_friendly_error_message',
                'payload' => $payload,
                'error_message' => $errorMessage
            ]);
        }
    }

    /**
     * Extract chat ID from various payload types
     */
    private function extractChatId(array $payload): ?int
    {
        // Try to get chat ID from message
        if (isset($payload['message']['chat']['id'])) {
            return (int) $payload['message']['chat']['id'];
        }

        // Try to get chat ID from callback query
        if (isset($payload['callback_query']['message']['chat']['id'])) {
            return (int) $payload['callback_query']['message']['chat']['id'];
        }

        // Try to get chat ID from channel post
        if (isset($payload['channel_post']['chat']['id'])) {
            return (int) $payload['channel_post']['chat']['id'];
        }

        return null;
    }

    /**
     * Create a friendly error message with helpful suggestions
     */
    private function createFriendlyErrorMessage(string $technicalError): string
    {
        $baseMessage = "❌ Ops! Algo deu errado.\n\n";

        // Map technical errors to friendly messages
        $friendlyMessages = [
            'validation_failed' => "Sua mensagem não pôde ser processada. Verifique se está correta e tente novamente.",
            'unauthorized' => "Você não tem permissão para usar este comando. Entre em contato com o administrador.",
            'command_not_found' => "Comando não reconhecido. Use /help para ver os comandos disponíveis.",
            'timeout' => "A operação demorou muito. Tente novamente em alguns instantes.",
            'internal_error' => "Ocorreu um erro interno. Nossa equipe foi notificada.",
            'webhook_validation_failed' => "Mensagem recebida com formato inválido. Tente enviar novamente.",
            'payload_validation_failed' => "Não foi possível processar sua mensagem. Tente novamente.",
        ];

        // Find the most appropriate friendly message
        $friendlyMessage = "Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente ou use /help para ver os comandos disponíveis.";

        foreach ($friendlyMessages as $key => $message) {
            if (stripos($technicalError, $key) !== false) {
                $friendlyMessage = $message;
                break;
            }
        }

        $suggestions = "\n💡 Sugestões:\n";
        $suggestions .= "• Use /help para ver comandos disponíveis\n";
        $suggestions .= "• Tente novamente em alguns instantes\n";
        $suggestions .= "• Se o problema persistir, entre em contato com o suporte\n";

        return $baseMessage . $friendlyMessage . $suggestions;
    }

    /**
     * Create user-friendly validation error message
     */
    private function createValidationErrorMessage(array $validationErrors): string
    {
        $baseMessage = "❌ Sua mensagem não pôde ser processada.\n\n";

        $errorDescriptions = [
            'update_id' => 'ID de atualização inválido',
            'message.chat.id' => 'ID do chat inválido',
            'message.from.id' => 'ID do usuário inválido',
            'message.text' => 'Texto da mensagem inválido',
            'message.voice.file_id' => 'Arquivo de voz inválido',
            'message.audio.file_id' => 'Arquivo de áudio inválido',
            'callback_query.id' => 'ID da consulta inválido',
            'callback_query.data' => 'Dados da consulta inválidos'
        ];

        $friendlyErrors = [];
        foreach ($validationErrors as $field => $errors) {
            $description = $errorDescriptions[$field] ?? 'Campo inválido';
            $friendlyErrors[] = "• {$description}";
        }

        $message = $baseMessage . "Problemas encontrados:\n" . implode("\n", $friendlyErrors);
        $message .= "\n\n💡 Sugestões:\n";
        $message .= "• Verifique se sua mensagem está correta\n";
        $message .= "• Tente novamente\n";
        $message .= "• Use /help para ver comandos disponíveis\n";

        return $message;
    }

    /**
     * Create a friendly fallback message for command_not_found
     */
    private function createCommandNotFoundFallbackMessage(array $result): string
    {
        $baseMessage = "❌ Comando não reconhecido.\n\n";
        $fallbackMessage = $baseMessage . "Desculpe, mas o comando que você tentou usar não foi encontrado. ";

        if (isset($result['message'])) {
            $fallbackMessage .= "A mensagem enviada foi: " . $result['message'] . "\n\n";
        }

        $fallbackMessage .= "Aqui estão alguns comandos que você pode usar:\n";
        $fallbackMessage .= "• /help - Para ver todos os comandos disponíveis\n";
        $fallbackMessage .= "• /start - Para iniciar o bot\n";
        $fallbackMessage .= "• /status - Para verificar o status do bot\n";
        $fallbackMessage .= "• /settings - Para configurar o bot\n";
        $fallbackMessage .= "• /about - Para saber mais sobre o bot\n";

        return $fallbackMessage;
    }
}
