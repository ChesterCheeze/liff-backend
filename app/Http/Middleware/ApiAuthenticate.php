<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next, string $guard = 'sanctum'): Response
    {
        if (! auth($guard)->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        return $next($request);
    }
}
