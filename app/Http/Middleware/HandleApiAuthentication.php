<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * HandleApiAuthentication Middleware
 * 
 * This middleware validates the authentication token before it reaches the controller.
 * It provides graceful error handling for expired or invalid tokens.
 * 
 * This is particularly useful for API routes to ensure consistent JSON error responses
 * instead of HTML error pages.
 */
class HandleApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // List of public routes that don't need authentication
        $publicRoutes = [
            'api/signup',
            'api/login',
            'api/forgot-password',
            'api/reset-password',
            'api/hello',
        ];

        // Skip authentication check for public routes
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Only process API requests that need authentication
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // Check if Authorization header is present
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader) {
            Log::warning('API request without authorization header', [
                'endpoint' => $request->path(),
                'method' => $request->method()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authorization header is missing',
                'error' => 'Please provide a valid Bearer token in the Authorization header'
            ], 401);
        }

        // Extract token from "Bearer {token}" format
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authorization format',
                'error' => 'Authorization header must be in the format: Bearer {token}'
            ], 401);
        }

        $token = substr($authHeader, 7); // Remove "Bearer " prefix

        // Check if token exists in database
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            Log::warning('Invalid token used', [
                'endpoint' => $request->path(),
                'token_preview' => substr($token, 0, 10) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
                'error' => 'The provided authentication token is invalid or has been revoked'
            ], 401);
        }

        // Check if token has expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            Log::info('Expired token used', [
                'endpoint' => $request->path(),
                'expired_at' => $accessToken->expires_at->toDateTimeString(),
                'user_id' => $accessToken->tokenable_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
                'error' => 'Your authentication token has expired. Please login again to get a new token.',
                'expired_at' => $accessToken->expires_at->toDateTimeString()
            ], 401);
        }

        // Token is valid, proceed with the request
        return $next($request);
    }
}
