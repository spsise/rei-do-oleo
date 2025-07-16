<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // Para APIs, não redirecionar, retornar null para evitar redirecionamento
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        return route('login');
    }

    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'code' => 401
            ], 401));
        }

        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
}
