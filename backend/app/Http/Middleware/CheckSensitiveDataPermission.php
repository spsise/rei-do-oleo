<?php

namespace App\Http\Middleware;

use App\Support\Helpers\SecurityMaskHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSensitiveDataPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add permission flag to request for use in resources
        $request->attributes->set('can_see_sensitive_data', SecurityMaskHelper::canSeeFullData());

        return $next($request);
    }
}
