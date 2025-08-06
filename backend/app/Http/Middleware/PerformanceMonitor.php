<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Contracts\LoggingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = memory_get_usage(true) - $startMemory;
        $peakMemory = memory_get_peak_usage(true);

        // Log performance metrics
        $this->loggingService->logPerformance(
            'http_request',
            $duration,
            [
                'memory_used' => $memoryUsed,
                'peak_memory' => $peakMemory,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
            ],
            [
                'request_id' => uniqid('perf_', true),
                'user_id' => $request->user()?->id,
            ]
        );

        // Log slow requests as warnings
        if ($duration > 1000) { // More than 1 second
            Log::channel('performance')->warning('Slow API Request', [
                'duration_ms' => round($duration, 2),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
                'memory_used' => $memoryUsed,
                'peak_memory' => $peakMemory,
            ]);
        }

        // Add performance headers to response
        $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');
        $response->headers->set('X-Memory-Usage', round($memoryUsed / 1024 / 1024, 2) . 'MB');

        return $response;
    }
}
