<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add common headers for API responses
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-API-Version', env('API_VERSION', 'v1'));
        $response->headers->set('X-Powered-By', 'Rei do Óleo API');

        return $response;
    }
}
