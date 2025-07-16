<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestDatabaseSafetyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check in testing environment
        if (app()->environment('testing')) {
            $currentDatabase = config('database.connections.mysql.database');
            $expectedDatabase = 'rei_do_oleo_test';

            if ($currentDatabase !== $expectedDatabase) {
                abort(500, "CRITICAL: Tests are running on wrong database! Expected: {$expectedDatabase}, Current: {$currentDatabase}");
            }
        }

        return $next($request);
    }
}
