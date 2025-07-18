<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateLineOA
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('lineoa')->check()) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
