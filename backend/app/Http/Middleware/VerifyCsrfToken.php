<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        // 'api/health',
        // 'api/documentation',
        // 'api/oauth2-callback',
        // 'docs/*',
        // '/api/*',
        // '/api/v1/*',
        // 'sanctum/csrf-cookie',
        // 'http://localhost:8000/api/*',
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // Se a requisição é para qualquer rota de API, sempre excluir
        if ($request->is('api/*') || str_starts_with($request->path(), 'api/')) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
