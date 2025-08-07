<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Contracts\LoggingServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class RequestLoggingMiddleware
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
        // Log Telegram webhook requests before any processing
        if ($request->is('api/telegram/webhook')) {
            try {
                $this->loggingService->logTelegramEvent('telegram_webhook_global_logging', [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'headers' => $request->headers->all(),
                    'raw_body' => $request->getContent(),
                    'all_data' => $request->all(),
                    'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now()->toISOString(),
                    ], 'info');
            } catch (\Exception $e) {
                Log::error('RequestLoggingMiddleware::logTelegramEvent failed', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }

        try {
            return $next($request);
        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'middleware' => 'RequestLoggingMiddleware',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);

            throw $e;
        }
    }
}
