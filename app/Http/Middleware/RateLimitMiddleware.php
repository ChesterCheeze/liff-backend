<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, string $key = 'api', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $identifier = $this->resolveRequestSignature($request, $key);

        if ($this->limiter->tooManyAttempts($identifier, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($identifier);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'data' => null,
                'errors' => [
                    'rate_limit' => 'Rate limit exceeded',
                    'retry_after' => $retryAfter,
                ],
            ], 429, [
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $retryAfter,
            ]);
        }

        $this->limiter->hit($identifier, $decayMinutes * 60);

        $response = $next($request);

        $remaining = $maxAttempts - $this->limiter->attempts($identifier);

        // Add rate limiting headers
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ];

        // Handle different response types
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            // BinaryFileResponse doesn't have withHeaders method, use headers property
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }

            return $response;
        }

        return $response->withHeaders($headers);
    }

    protected function resolveRequestSignature(Request $request, string $key): string
    {
        $user = $request->user();

        if ($user) {
            return sprintf('%s:%s:%s', $key, $user->getAuthIdentifier(), $request->ip());
        }

        return sprintf('%s:%s', $key, $request->ip());
    }
}
