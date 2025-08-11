<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\Channels\TelegramChannel;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookExceptionHandler
{
    public function __construct(
        private TelegramChannel $telegramChannel
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            // Extract chat_id from the request if available
            $chatId = $this->extractChatId($request);

            if ($chatId) {
                $this->sendValidationErrorToTelegram($chatId, $e);
            }

            // Log the validation error
            Log::warning('Telegram webhook validation failed', [
                'chat_id' => $chatId,
                'errors' => $e->errors(),
                'payload' => $request->all()
            ]);

            // Return a proper response to Telegram (200 OK to acknowledge receipt)
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 200); // Use 200 instead of 422 to avoid Telegram retries
        } catch (\Exception $e) {
            // Extract chat_id from the request if available
            $chatId = $this->extractChatId($request);

            if ($chatId) {
                $this->sendGeneralErrorToTelegram($chatId, $e);
            }

            // Log the general error
            Log::error('Telegram webhook general error', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            // Return a proper response to Telegram (200 OK to acknowledge receipt)
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 200); // Use 200 instead of 500 to avoid Telegram retries
        }
    }

    /**
     * Extract chat_id from the request payload
     */
    private function extractChatId(Request $request): ?string
    {
        $payload = $request->all();

        // Try to get chat_id from message
        if (isset($payload['message']['chat']['id'])) {
            return (string) $payload['message']['chat']['id'];
        }

        // Try to get chat_id from callback_query
        if (isset($payload['callback_query']['message']['chat']['id'])) {
            return (string) $payload['callback_query']['message']['chat']['id'];
        }

        return null;
    }

    /**
     * Send validation error message to Telegram chat
     */
    private function sendValidationErrorToTelegram(string $chatId, ValidationException $e): void
    {
        try {
            $errorMessage = $this->formatValidationErrorMessage($e);

            $this->telegramChannel->sendTextMessage($errorMessage, $chatId);

            Log::info('Validation error sent to Telegram chat', [
                'chat_id' => $chatId,
                'error_count' => count($e->errors())
            ]);
        } catch (\Exception $telegramError) {
            Log::error('Failed to send validation error to Telegram', [
                'chat_id' => $chatId,
                'telegram_error' => $telegramError->getMessage(),
                'original_validation_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send general error message to Telegram chat
     */
    private function sendGeneralErrorToTelegram(string $chatId, \Exception $e): void
    {
        try {
            $errorMessage = $this->formatGeneralErrorMessage($e);

            $this->telegramChannel->sendTextMessage($errorMessage, $chatId);

            Log::info('General error sent to Telegram chat', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $telegramError) {
            Log::error('Failed to send general error to Telegram', [
                'chat_id' => $chatId,
                'telegram_error' => $telegramError->getMessage(),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format validation error message for Telegram
     */
    private function formatValidationErrorMessage(ValidationException $e): string
    {
        $message = "❌ *Erro de Validação*\n\n";
        $message .= "Ocorreu um erro ao processar sua mensagem:\n\n";

        foreach ($e->errors() as $field => $errors) {
            $fieldName = $this->getFieldDisplayName($field);
            $message .= "• *{$fieldName}:* " . implode(', ', $errors) . "\n";
        }

        $message .= "\nPor favor, tente novamente com os dados corretos.";

        return $message;
    }

    /**
     * Format general error message for Telegram
     */
    private function formatGeneralErrorMessage(\Exception $e): string
    {
        $message = "⚠️ *Erro do Sistema*\n\n";
        $message .= "Ocorreu um erro inesperado ao processar sua solicitação.\n\n";
        $message .= "Por favor, tente novamente em alguns instantes.\n";
        $message .= "Se o problema persistir, entre em contato com o suporte.";

        return $message;
    }

    /**
     * Get user-friendly field names for validation errors
     */
    private function getFieldDisplayName(string $field): string
    {
        $fieldNames = [
            'update_id' => 'ID da Atualização',
            'message' => 'Mensagem',
            'callback_query' => 'Consulta de Callback',
            'message.chat.id' => 'ID do Chat',
            'message.text' => 'Texto da Mensagem',
            'message.from.id' => 'ID do Usuário',
            'callback_query.id' => 'ID da Consulta',
            'callback_query.data' => 'Dados da Consulta',
            'callback_query.message.chat.id' => 'ID do Chat (Callback)',
        ];

        return $fieldNames[$field] ?? $field;
    }
}
