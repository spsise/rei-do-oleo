<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new rate limiter middleware.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        if (!config('api.rate_limiting.enabled', true)) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request, $type);
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);

            Log::warning('API rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'type' => $type,
                'retry_after' => $retryAfter,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests',
                'retry_after' => $retryAfter,
                'code' => 429,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->getTimestamp(),
            ]);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        $remaining = $maxAttempts - $this->limiter->attempts($key);

        return $response->header('X-RateLimit-Limit', $maxAttempts)
                       ->header('X-RateLimit-Remaining', $remaining);
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $user = $request->user();

        if ($user) {
            return sha1($user->getAuthIdentifier() . '|' . $request->ip() . '|' . $type);
        }

        return sha1($request->ip() . '|' . $request->userAgent() . '|' . $type);
    }

    /**
     * Get the maximum number of attempts for the given type.
     */
    protected function getMaxAttempts(string $type): int
    {
        return match ($type) {
            'auth' => config('api.rate_limiting.auth_limit', 5),
            default => config('api.rate_limiting.default_limit', 60),
        };
    }

    /**
     * Get the number of minutes to decay the rate limiter.
     */
    protected function getDecayMinutes(string $type): int
    {
        return match ($type) {
            'auth' => config('api.rate_limiting.auth_decay_minutes', 1),
            default => config('api.rate_limiting.default_decay_minutes', 1),
        };
    }
}
