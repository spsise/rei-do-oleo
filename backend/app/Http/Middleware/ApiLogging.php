<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogging
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('api.logging.enabled', true)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $requestId = uniqid('api_', true);

        // Log request
        if (config('api.logging.log_requests', true)) {
            $this->logRequest($request, $requestId);
        }

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Log response
        if (config('api.logging.log_responses', false)) {
            $this->logResponse($response, $requestId, $duration);
        }

        // Log errors
        if (config('api.logging.log_errors', true) && $response->getStatusCode() >= 400) {
            $this->logError($request, $response, $requestId, $duration);
        }

        return $response;
    }

    /**
     * Log the incoming request.
     */
    protected function logRequest(Request $request, string $requestId): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ];

        // Only log request body for non-GET requests and exclude sensitive data
        if ($request->method() !== 'GET') {
            $logData['body'] = $this->sanitizeBody($request->all());
        }

        Log::channel(config('api.logging.channel', 'api'))->info('API Request', $logData);
    }

    /**
     * Log the response.
     */
    protected function logResponse(Response $response, string $requestId, float $duration): void
    {
        $logData = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'headers' => $this->sanitizeHeaders($response->headers->all()),
        ];

        // Only log response body for errors or if explicitly enabled
        if ($response->getStatusCode() >= 400) {
            $content = $response->getContent();
            if (is_string($content)) {
                $logData['body'] = json_decode($content, true) ?: $content;
            }
        }

        Log::channel(config('api.logging.channel', 'api'))->info('API Response', $logData);
    }

    /**
     * Log errors.
     */
    protected function logError(Request $request, Response $response, string $requestId, float $duration): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ];

        $content = $response->getContent();
        if (is_string($content)) {
            $logData['error'] = json_decode($content, true) ?: $content;
        }

        Log::channel(config('api.logging.channel', 'api'))->error('API Error', $logData);
    }

    /**
     * Sanitize headers to remove sensitive information.
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token'];

        return collect($headers)->map(function ($value, $key) use ($sensitiveHeaders) {
            $key = strtolower($key);
            if (in_array($key, $sensitiveHeaders)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }

    /**
     * Sanitize request body to remove sensitive information.
     */
    protected function sanitizeBody(array $body): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'token'];

        return collect($body)->map(function ($value, $key) use ($sensitiveFields) {
            if (in_array($key, $sensitiveFields)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }
}
