<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ⚠️ TEMPORARY MIDDLEWARE FOR TESTING ONLY ⚠️
 *
 * This middleware fakes authentication by accepting user_id from query params or headers.
 * TODO: DELETE THIS FILE when Sanctum authentication is implemented.
 *
 * Usage: ?user_id=1 or X-User-Id: 1 header
 */
class FakeAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TEMPORARY: Fake authentication for testing
        // Get user_id from query param or header, default to user ID 1
        $userId = $request->input('user_id') ?? $request->header('X-User-Id') ?? 1;

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Set the authenticated user via Auth facade (needed for policies)
        \Illuminate\Support\Facades\Auth::setUser($user);

        // Also set via request resolver (for request()->user())
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
