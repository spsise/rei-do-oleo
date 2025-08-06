<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Contracts\LoggingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

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
