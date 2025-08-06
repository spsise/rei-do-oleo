<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUnnecessaryUpdates
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Verificar se é uma requisição PUT/PATCH para serviços
        if (in_array($request->method(), ['PUT', 'PATCH']) &&
            str_contains($request->path(), 'services') &&
            $request->route('id')) {

            // Log de atualizações desnecessárias
            Log::info('Service update request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'service_id' => $request->route('id'),
                'user_id' => $request->user()?->id,
                'data_size' => count($request->all()),
                'timestamp' => now(),
            ]);
        }

        return $response;
    }
}
