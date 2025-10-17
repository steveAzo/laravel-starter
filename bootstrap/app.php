<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register our custom API authentication middleware
        // This will run before Sanctum's auth:sanctum middleware to provide better error messages
        $middleware->api(prepend: [
            \App\Http\Middleware\HandleApiAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Handle Authentication Exceptions
         * This catches expired or invalid tokens and returns proper JSON response
         */
        $exceptions->render(function (AuthenticationException $e, $request) {
            // Only handle API requests (not web routes)
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Token may be expired or invalid.',
                    'error' => $e->getMessage()
                ], 401);
            }
        });

        /**
         * Handle Validation Exceptions
         * Returns validation errors in consistent JSON format
         */
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        /**
         * Handle 404 Not Found
         * Returns JSON response for API routes
         */
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found',
                    'error' => 'The requested resource does not exist'
                ], 404);
            }
        });

        /**
         * Handle 405 Method Not Allowed
         * Returns JSON response for API routes
         */
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'error' => 'The HTTP method used is not supported for this endpoint'
                ], 405);
            }
        });
    })->create();
