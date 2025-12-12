<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Middleware for Role-Based Access Control (RBAC).
     * Checks if the authenticated user has at least one of the required roles.
     *
     * Usage in routes:
     *   - Single role: middleware('role:admin')
     *   - Multiple roles (OR): middleware('role:admin,moderator')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names (e.g., 'admin', 'moderator', 'owner')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return api_error('Unauthorized. Please login to access this resource.', 401);
        }

        // If no roles specified, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$request->user()->hasAnyRole($roles)) {
            $rolesString = implode(', ', $roles);
            return api_error(
                "Access denied. Required role(s): {$rolesString}.",
                403,
                ['required_roles' => $roles, 'user_roles' => $request->user()->roles->pluck('role_name')->toArray()]
            );
        }

        return $next($request);
    }
}
