<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Contracts\LoggingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookLoggingMiddleware
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
        // Log the raw request before any validation
        $this->loggingService->logTelegramEvent('telegram_webhook_raw_received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'raw_body' => $request->getContent(),
            'all_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ], 'info');

        return $next($request);
    }
}
