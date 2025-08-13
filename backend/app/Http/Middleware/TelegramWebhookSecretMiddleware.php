<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Contracts\LoggingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookSecretMiddleware
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretToken = config('services.telegram.webhook_secret');

        // Se não há secret configurado, permite a requisição (modo de desenvolvimento)
        if (empty($secretToken)) {
            $this->loggingService->logTelegramEvent('webhook_secret_not_configured', [
                'warning' => 'Webhook secret not configured - allowing all requests',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ], 'warning');

            return $next($request);
        }

        // Verificar se o header do secret está presente
        $incomingSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if (empty($incomingSecret)) {
            $this->loggingService->logTelegramEvent('webhook_secret_missing', [
                'error' => 'Missing webhook secret token in request',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
                'timestamp' => now()->toISOString()
            ], 'error');

            return response()->json([
                'error' => 'Unauthorized - Missing secret token',
                'message' => 'Webhook secret token is required'
            ], 401);
        }

        // Verificar se o secret é válido
        if (!hash_equals($secretToken, $incomingSecret)) {
            $this->loggingService->logTelegramEvent('webhook_secret_invalid', [
                'error' => 'Invalid webhook secret token',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'incoming_secret' => substr($incomingSecret, 0, 8) . '...', // Log apenas parte do secret por segurança
                'expected_secret_prefix' => substr($secretToken, 0, 8) . '...',
                'timestamp' => now()->toISOString()
            ], 'error');

            return response()->json([
                'error' => 'Unauthorized - Invalid secret token',
                'message' => 'Webhook secret token is invalid'
            ], 401);
        }

        return $next($request);
    }
}
