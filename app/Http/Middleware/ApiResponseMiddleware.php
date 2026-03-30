<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force API routes to return JSON
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Ensure API responses are always JSON
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            if ($response->getStatusCode() >= 400) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bad Request',
                    'error' => 'This endpoint requires JSON requests only'
                ], $response->getStatusCode());
            }
        }

        return $response;
    }
}