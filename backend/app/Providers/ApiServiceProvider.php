<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure rate limiting for API
        $this->configureRateLimiting();

        // Configure Sanctum
        $this->configureSanctum();
    }

    /**
     * Configure rate limiting for different API endpoints
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(env('RATE_LIMIT_PER_MINUTE', 60))
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please try again later.',
                        'code' => 429
                    ], 429);
                });
        });

        // Login rate limiting
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(env('RATE_LIMIT_LOGIN_PER_MINUTE', 5))
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many login attempts. Please try again later.',
                        'code' => 429
                    ], 429);
                });
        });

        // Email verification rate limiting
        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(6)
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure Sanctum
     */
    protected function configureSanctum(): void
    {
        // Sanctum configuration if needed
    }
}
