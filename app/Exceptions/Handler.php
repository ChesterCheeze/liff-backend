<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    protected function handleApiException(Throwable $exception, Request $request)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
                'timestamp' => now()->toISOString(),
            ], 422);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid authentication token.',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to perform this action.',
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found.',
                'timestamp' => now()->toISOString(),
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'The HTTP method is not allowed for this endpoint.',
                'timestamp' => now()->toISOString(),
            ], 405);
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'The requested model was not found.',
                'timestamp' => now()->toISOString(),
            ], 404);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'timestamp' => now()->toISOString(),
                'retry_after' => $exception->getHeaders()['Retry-After'] ?? null,
            ], 429);
        }

        // For other exceptions in production, don't expose internal details
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'An internal server error occurred.',
                'timestamp' => now()->toISOString(),
            ], 500);
        }

        // In development, show the actual error
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'timestamp' => now()->toISOString(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->take(5)->toArray(),
        ], 500);
    }
}
